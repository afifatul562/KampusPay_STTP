<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\MahasiswaDetail;
use App\Models\Tagihan;
use Carbon\Carbon;
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
            $reports = Report::latest()->get();
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
    private function getReportData($jenis, int $tahun, $semester = null)
    {
        $data = null;
        if ($jenis === 'mahasiswa') {
            // Filter mahasiswa berdasarkan angkatan (tahun yang dipilih)
            $mahasiswaList = MahasiswaDetail::with('user')
                            ->where('angkatan', $tahun)
                            ->orderBy('npm', 'asc')
                            ->get();
            $data = $mahasiswaList;
            Log::info('Mengambil data laporan mahasiswa angkatan ' . $tahun . ': ' . $mahasiswaList->count() . ' record.');

        } elseif ($jenis === 'pembayaran') {
            $query = Tagihan::with([
                    'mahasiswa.user',
                    'tarif',
                    'pembayaran.userKasir' => fn($q)=>$q->select('id','nama_lengkap'),
                    'pembayaranAll' => function($q) {
                        $q->where('status_dibatalkan', false);
                    }
                ]) // Eager load kasir dan semua pembayaran
                        ->whereYear('created_at', $tahun);

            // Filter berdasarkan semester jika dipilih
            if ($semester) {
                // Format semester_label: "2025/2026 Ganjil" atau "2025/2026 Genap"
                // Untuk tahun 2026:
                //   - Semester Ganjil: bisa "2025/2026 Ganjil" atau "2026/2027 Ganjil"
                //   - Semester Genap: bisa "2025/2026 Genap" atau "2026/2027 Genap"
                // Kita filter dengan mencari semester_label yang:
                //   1. Berakhiran dengan semester yang dipilih (Ganjil atau Genap)
                //   2. Tahun akademik yang sesuai dengan tahun yang dipilih
                // Format: "YYYY/YYYY+1 Semester" atau "YYYY-1/YYYY Semester"
                $tahunNext = $tahun + 1;
                $tahunPrev = $tahun - 1;
                $query->where(function($q) use ($semester, $tahun, $tahunNext, $tahunPrev) {
                    // Format "YYYY/YYYY+1 Semester" atau "YYYY-1/YYYY Semester"
                    $q->where('semester_label', 'LIKE', "{$tahunPrev}/{$tahun} {$semester}")
                      ->orWhere('semester_label', 'LIKE', "{$tahun}/{$tahunNext} {$semester}")
                      ->orWhere('semester_label', 'LIKE', "% {$semester}"); // Fallback: cari yang berakhiran dengan semester
                });
            }

            $data = $query->orderBy('created_at') // Urutkan tagihan
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
        ]);
        $jenis = $validated['jenis_laporan'];
        $tahun = (int) $validated['tahun'];
        $semester = $validated['semester'] ?? null;
        Log::info("Preview Jenis: {$jenis}, Tahun: {$tahun}, Semester: " . ($semester ?? 'Semua'));

        try {
            $data = $this->getReportData($jenis, $tahun, $semester);

            if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data preview untuk {$jenis} tahun {$tahun}.");
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
        ]);
        $jenis = $validated['jenis_laporan'];
        $tahun = (int) $validated['tahun'];
        $semester = $validated['semester'] ?? null;
        $periodeFormatted = (string) $tahun . ($semester ? " {$semester}" : '');
        $fileName = "laporan_{$jenis}_{$tahun}" . ($semester ? "_{$semester}" : '') . "_" . time() . ".pdf";
        $data = null;

        try {
            $data = $this->getReportData($jenis, $tahun, $semester);
            if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data untuk generate PDF {$jenis} tahun {$tahun}.");
                return response()->json(['success' => false, 'message' => 'Tidak ada data untuk periode yang dipilih.'], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error ambil data untuk PDF: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data laporan.'], 500);
        }

        // Generate PDF & Simpan File + Riwayat (Dalam Transaksi)
        $filePath = null;
        DB::beginTransaction();
        try {
            $pdf = app('dompdf.wrapper');
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
                'periode' => (string) $tahun,
                'file_name' => $fileName,
            ]);
            Log::info("Riwayat laporan disimpan ke database.");

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Laporan PDF berhasil dibuat dan riwayat disimpan.']);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error saat store laporan PDF: " . $e->getMessage());

            // Hapus file PDF jika sudah terlanjur dibuat tapi gagal simpan DB
            if ($filePath && Storage::exists($filePath) && $e instanceof QueryException) {
                Storage::delete($filePath);
                Log::warning("File PDF {$fileName} dihapus karena gagal menyimpan riwayat ke DB.");
            }
            // Hapus juga jika gagal saat proses penyimpanan file itu sendiri
             else if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
                Log::warning("File PDF {$fileName} dihapus karena error saat proses penyimpanan file.");
            }

            return response()->json(['success' => false, 'message' => 'Gagal membuat file PDF atau menyimpan riwayat: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Mendownload file laporan atau menampilkannya inline.
     */
    public function download(Request $request, $reportId)
    {
        Log::info("Mencoba akses laporan ID: {$reportId}");
        try {
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
                Log::info("Menampilkan file inline: {$fileName}");
                return response()->file($absolutePath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"'
                ]);
            } else {
                Log::info("Memulai download file: {$fileName}");
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
     * Menghapus riwayat laporan dan file PDF terkait.
     */
    public function destroy(Report $report)
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
            $report->delete();
            Log::info("Riwayat laporan ID {$report->id} berhasil dihapus dari database.");

            DB::commit();
            return response()->noContent(); // Standar response untuk DELETE sukses

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat hapus laporan ID {$report->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus laporan: ' . $e->getMessage()], 500);
        }
    }
}
