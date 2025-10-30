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
use Illuminate\Support\Facades\DB; // Pastikan ini ada
use Illuminate\Database\QueryException; // Untuk catch error DB spesifik

class ReportController extends Controller
{
    /**
     * Menampilkan riwayat laporan.
     */
    public function index()
    {
        try {
            $reports = Report::latest()->get();
            // Konsisten dengan format { data: [...] }
            return response()->json(['data' => $reports]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil riwayat laporan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil riwayat laporan.'], 500);
        }
    }

    // ==========================================================
    // !! METHOD BARU UNTUK MENGAMBIL DATA (REFACTOR) !!
    // ==========================================================
    /**
     * Mengambil data laporan berdasarkan jenis dan periode.
     *
     * @param string $jenis 'mahasiswa' atau 'pembayaran'
     * @param \Carbon\Carbon $periode Objek Carbon periode (Y-m)
     * @return \Illuminate\Support\Collection|null
     */
    private function getReportData($jenis, Carbon $periode)
    {
        $data = null;
        if ($jenis === 'mahasiswa') {
            // Laporan mahasiswa biasanya tidak difilter per bulan, ambil semua
            $mahasiswaList = MahasiswaDetail::with('user')
                            ->orderBy('semester_aktif', 'asc')
                            ->orderBy('npm', 'asc')
                            ->get();
            // Kelompokkan berdasarkan semester_aktif
            $data = $mahasiswaList->groupBy('semester_aktif');
            Log::info('Mengambil data laporan mahasiswa: ' . $mahasiswaList->count() . ' record.');

        } elseif ($jenis === 'pembayaran') {
            $data = Tagihan::with(['mahasiswa.user', 'tarif', 'pembayaran.userKasir' => fn($q)=>$q->select('id','nama_lengkap')]) // Eager load kasir
                        ->whereYear('created_at', $periode->year)
                        ->whereMonth('created_at', $periode->month)
                        ->orderBy('created_at') // Urutkan tagihan
                        ->get();
            Log::info('Mengambil data laporan pembayaran periode ' . $periode->format('Y-m') . ': ' . $data->count() . ' record.');
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
            'periode' => 'required|date_format:Y-m',
        ]);
        $jenis = $validated['jenis_laporan'];
        $periode = Carbon::createFromFormat('Y-m', $validated['periode']);
        Log::info("Preview Jenis: {$jenis}, Periode: {$periode->format('Y-m')}");

        try {
            // Panggil method refactor
            $data = $this->getReportData($jenis, $periode);

            if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data preview untuk {$jenis} periode {$periode->format('Y-m')}.");
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
            'periode' => 'required|date_format:Y-m',
        ]);
        $jenis = $validated['jenis_laporan'];
        $periode = Carbon::createFromFormat('Y-m', $validated['periode']);
        $periodeFormatted = $periode->isoFormat('MMMM Y');
        $fileName = "laporan_{$jenis}_{$periode->format('Y_m')}_" . time() . ".pdf";
        $data = null;

        // 1. Ambil Data (gunakan method refactor)
        try {
            $data = $this->getReportData($jenis, $periode);
             if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty()) || (is_array($data) && empty($data))) {
                Log::warning("Tidak ada data untuk generate PDF {$jenis} periode {$periode->format('Y-m')}.");
                // Kembalikan error agar user tahu tidak ada data
                return response()->json(['success' => false, 'message' => 'Tidak ada data untuk periode yang dipilih.'], 404); // 404 Not Found or 400 Bad Request
            }
        } catch (\Exception $e) {
            Log::error("Error ambil data untuk PDF: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data laporan.'], 500);
        }

        // 2. Generate PDF & Simpan File + Riwayat (Dalam Transaksi)
        $filePath = null;
        DB::beginTransaction(); // <-- MULAI TRANSAKSI
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
                'periode' => $periode->format('Y-m'),
                'file_name' => $fileName,
            ]);
            Log::info("Riwayat laporan disimpan ke database.");

            DB::commit(); // <-- COMMIT JIKA SEMUA SUKSES

            return response()->json(['success' => true, 'message' => 'Laporan PDF berhasil dibuat dan riwayat disimpan.']);

        } catch (\Exception $e) {
            DB::rollBack(); // <-- ROLLBACK JIKA ADA ERROR

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
            // Gunakan Route Model Binding jika memungkinkan (jika parameter di route adalah {report})
            // Jika tidak, pakai findOrFail
            $report = Report::findOrFail($reportId);
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
                // Storage::download() lebih mudah untuk download dari storage disk
                return Storage::download($storagePath, $fileName);
                 // Alternatif: return response()->download($absolutePath, $fileName);
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
