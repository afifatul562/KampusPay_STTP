<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\MahasiswaDetail;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;
    /**
     * Menampilkan riwayat laporan.
     */
    public function index()
    {
        try {
            $reports = Report::latest('created_at')->get(['*']);
            // Konsisten dengan format { data: [...] }
            return response()->json(['data' => $reports]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil riwayat laporan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil riwayat laporan.'], 500);
        }
    }

    /**
     * Mengambil data laporan berdasarkan jenis dan tahun.
     *
     * @param string $jenis 'mahasiswa' atau 'pembayaran'
     * @param int $tahun Tahun (YYYY)
     * @param string|null $semester Semester (Ganjil/Genap) - hanya untuk pembayaran
     * @return \Illuminate\Support\Collection|null
     */
    private function getReportData($jenis, int $tahun, $semester = null, array $filters = [])
    {
        $data = null;
        $tahunAkademik = isset($filters['tahun_akademik']) ? trim((string) $filters['tahun_akademik']) : '';
        $angkatan = isset($filters['angkatan']) ? trim((string) $filters['angkatan']) : '';
        $programStudi = isset($filters['program_studi']) ? trim((string) $filters['program_studi']) : '';

        if ($jenis === 'mahasiswa') {
            // Filter mahasiswa berdasarkan angkatan (tahun yang dipilih / filter angkatan)
            $angkatanDipakai = $angkatan !== '' ? $angkatan : $tahun;
            $mahasiswaList = MahasiswaDetail::with('user')
                            ->where('angkatan', $angkatanDipakai)
                            ->when($programStudi !== '', fn($q) => $q->where('program_studi', $programStudi))
                            ->orderBy('npm', 'asc')
                            ->get();
            // Kembalikan sebagai array biasa (tidak perlu grouping berdasarkan semester)
            $data = $mahasiswaList;
            Log::info('Mengambil data laporan mahasiswa angkatan ' . $angkatanDipakai . ($programStudi ? " prodi {$programStudi}" : '') . ': ' . $mahasiswaList->count() . ' record.');

        } elseif ($jenis === 'pembayaran') {
            $query = Tagihan::with([
                    'mahasiswa.user',
                    'tarif',
                    'pembayaran.userKasir' => fn($q)=>$q->select('id','nama_lengkap'),
                    'pembayaranAll' => function($q) {
                        $q->where('status_dibatalkan', false);
                    }
                ]); // Eager load kasir dan semua pembayaran

            // Jika tahun akademik tersedia, jangan kunci ke created_at year (karena tagihan TA 2025/2026 bisa dibuat di 2026).
            // Jika tidak ada tahun akademik, fallback ke filter tahun kalender created_at.
            if ($tahunAkademik === '') {
                $query->whereYear('created_at', $tahun);
            } else {
                $query->where('semester_label', 'LIKE', $tahunAkademik . '%');
            }

            if ($angkatan !== '' || $programStudi !== '') {
                $query->whereHas('mahasiswa', function($mq) use ($angkatan, $programStudi) {
                    if ($angkatan !== '') {
                        $mq->where('angkatan', $angkatan);
                    }
                    if ($programStudi !== '') {
                        $mq->where('program_studi', $programStudi);
                    }
                });
            }
            
            if ($semester) {
                // semester_label contoh: "2025/2026 Ganjil"
                if ($tahunAkademik !== '') {
                    $query->where('semester_label', 'LIKE', "{$tahunAkademik} {$semester}%");
                } else {
                    // fallback lama jika hanya punya tahun kalender
                    $tahunNext = $tahun + 1;
                    $tahunPrev = $tahun - 1;
                    $query->where(function($q) use ($semester, $tahun, $tahunNext, $tahunPrev) {
                        $q->where('semester_label', 'LIKE', "{$tahunPrev}/{$tahun} {$semester}")
                          ->orWhere('semester_label', 'LIKE', "{$tahun}/{$tahunNext} {$semester}")
                          ->orWhere('semester_label', 'LIKE', "% {$semester}");
                    });
                }
            }

            $data = $query->orderBy('created_at')
                        ->get();
            Log::info('Mengambil data laporan pembayaran tahun ' . $tahun . ($semester ? " semester {$semester}" : '') . ': ' . $data->count() . ' record.');
        }
        return $data;
    }

    /**
     * Mengambil data untuk preview.
     */
    public function preview(Request $request)
    {
        Log::info('Memproses permintaan preview laporan.', $request->all());
        $validated = $request->validate([
            'jenis_laporan' => 'required|string|in:mahasiswa,pembayaran',
            'tahun' => 'required|digits:4',
            'semester' => 'nullable|string|in:Ganjil,Genap',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);
        $jenis = $validated['jenis_laporan'];
        $tahun = (int) $validated['tahun'];
        $semester = $validated['semester'] ?? null;
        $filters = [
            'tahun_akademik' => $validated['tahun_akademik'] ?? null,
            'angkatan' => $validated['angkatan'] ?? null,
            'program_studi' => $validated['program_studi'] ?? null,
        ];
        Log::info("Preview Jenis: {$jenis}, Tahun: {$tahun}, Semester: " . ($semester ?? 'Semua'));

        try {
            // Panggil method refactor dengan parameter semester
            $data = $this->getReportData($jenis, $tahun, $semester, $filters);

            if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data preview untuk {$jenis} tahun {$tahun}.");
                 // Kembalikan data kosong agar frontend tahu tidak ada data
                 return response()->json(['data' => []]);
            }

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            Log::error("Error saat mengambil data preview: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data preview: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Membuat file laporan PDF dan menyimpan riwayat.
     */
    public function store(Request $request)
    {
        Log::info('Memproses permintaan generate laporan PDF.', $request->all());
        $validated = $request->validate([
            'jenis_laporan' => 'required|string|in:mahasiswa,pembayaran',
            'tahun' => 'required|digits:4',
            'semester' => 'nullable|string|in:Ganjil,Genap',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);
        $jenis = $validated['jenis_laporan'];
        $tahun = (int) $validated['tahun'];
        $semester = $validated['semester'] ?? null;
        $filters = [
            'tahun_akademik' => $validated['tahun_akademik'] ?? null,
            'angkatan' => $validated['angkatan'] ?? null,
            'program_studi' => $validated['program_studi'] ?? null,
        ];
        $periodeFormatted = (string) $tahun . ($semester ? " {$semester}" : '');
        $fileName = "laporan_{$jenis}_{$tahun}" . ($semester ? "_{$semester}" : '') . "_" . time() . ".pdf";
        $data = null;

        // 1. Ambil Data (gunakan method refactor dengan parameter semester)
        try {
            $data = $this->getReportData($jenis, $tahun, $semester, $filters);
            if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data untuk generate PDF {$jenis} tahun {$tahun}.");
                // Kembalikan error agar user tahu tidak ada data
                return response()->json(['success' => false, 'message' => 'Tidak ada data untuk periode yang dipilih.'], 404); // 404 Not Found or 400 Bad Request
            }
        } catch (\Exception $e) {
            Log::error("Error ambil data untuk PDF: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data laporan.'], 500);
        }

        // 2. Generate PDF & Simpan File + Riwayat (Dalam Transaksi)
        $filePath = null;
        DB::beginTransaction();
        try {
            $pdf = app('dompdf.wrapper');
            // Load view PDF template (pastikan view ini ada: resources/views/pdf/laporan_template.blade.php)
            $pdf->loadView('pdf.laporan_template', compact('data', 'jenis', 'periodeFormatted'));

            $directory = 'public/reports'; // Simpan di storage/app/public/reports
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory); // Buat direktori jika belum ada
            }
            $filePath = $directory . '/' . $fileName;

            // Simpan file PDF ke storage
            $saveSuccess = Storage::put($filePath, $pdf->output());
            if (!$saveSuccess) {
                throw new \Exception("Gagal menyimpan file PDF ke storage.");
            }
            Log::info("Laporan PDF disimpan di: storage/app/{$filePath}");

            // Simpan riwayat ke database
            Report::create([
                'jenis_laporan' => $jenis,
                'periode' => $periodeFormatted,
                'file_name' => $fileName,
            ]);
            Log::info("Riwayat laporan disimpan ke database.");

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Laporan PDF berhasil dibuat dan riwayat disimpan.']);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error saat store laporan PDF: " . $e->getMessage());

            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
                $reason = $e instanceof QueryException ? 'gagal menyimpan riwayat ke DB' : 'error saat proses penyimpanan';
                Log::warning("File PDF {$fileName} dihapus karena {$reason}.");
            }

            return response()->json(['success' => false, 'message' => 'Gagal membuat file PDF atau menyimpan riwayat: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Mendownload file laporan atau menampilkannya inline.
     */
    public function download(Request $request, int|string $reportId)
    {
        Log::info("Mencoba akses laporan ID: {$reportId}");
        try {
            // Gunakan Route Model Binding jika memungkinkan (jika parameter di route adalah {report})
            // Jika tidak, pakai findOrFail
            $report = Report::findOrFail($reportId);
            $this->authorize('view', $report);
            $fileName = $report->file_name;
            // Path relatif di dalam disk 'public' (storage/app/public/reports/)
            $relativePath = 'reports/' . $fileName;
            // Path relatif untuk Storage facade (storage/app/public/reports)
            $storagePath = 'public/' . $relativePath;

            Log::info("Mencari file di storage path: storage/app/{$storagePath}");

            if (!Storage::exists($storagePath)) {
                Log::error("File laporan tidak ditemukan. Path: {$storagePath}. Report ID: {$reportId}");
                abort(404, 'File laporan tidak ditemukan di storage.');
            }

            // Dapatkan path absolut ke file di storage
            $absolutePath = Storage::path($storagePath);

            // Cek apakah request minta view inline
            if ($request->has('view') && $request->input('view') == '1') {
                // Tampilkan Inline (View)
                Log::info("Menampilkan file inline: {$fileName}");
                // response()->file() lebih cocok untuk menampilkan file
                return response()->file($absolutePath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"'
                ]);
            } else {
                // Paksa Download
                Log::info("Memulai download file: {$fileName}");
                // Streaming download agar hemat memori
                $stream = Storage::readStream($storagePath);
                return response()->streamDownload(function () use ($stream) {
                    fpassthru($stream);
                    if (is_resource($stream)) { fclose($stream); }
                }, $fileName, [
                    'Content-Type' => 'application/pdf'
                ]);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Riwayat laporan ID {$reportId} tidak ditemukan di database.");
            abort(404, 'Riwayat laporan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("Error saat akses laporan ID {$reportId}: " . $e->getMessage());
            abort(500, 'Gagal mengakses atau mendownload laporan.');
        }
    }

    /**
     * Menghapus riwayat laporan dan file PDF terkait (sudah bagus).
     */
    public function destroy(Report $report) // Menggunakan Route Model Binding
    {
        Log::info("Mencoba hapus laporan ID: {$report->id}, File: {$report->file_name}");
        $this->authorize('delete', $report);
        $filePath = 'public/reports/' . $report->file_name; // Path di dalam storage/app

        DB::beginTransaction();
        try {
            // Hapus file dari storage jika ada
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                Log::info("File laporan {$report->file_name} berhasil dihapus dari storage.");
            } else {
                Log::warning("File laporan {$report->file_name} tidak ditemukan di storage saat mencoba menghapus.");
            }

            // Hapus record dari database
            Report::destroy($report->getKey());
            Log::info("Riwayat laporan ID {$report->id} berhasil dihapus dari database.");

            DB::commit();
            return response()->noContent(204); // Standar response untuk DELETE sukses

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat hapus laporan ID {$report->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus laporan: ' . $e->getMessage()], 500);
        }
    }
}
