<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportDataService;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ReportDataService $reportData) {}

    /**
     * Menampilkan riwayat laporan.
     */
    public function index()
    {
        try {
            $reports = Report::latest('created_at')->get(['*']);

            return response()->json(['data' => $reports]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil riwayat laporan: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal mengambil riwayat laporan.'], 500);
        }
    }

    /**
     * Mengambil data laporan berdasarkan jenis dan periode.
     */
    protected function getReportData(array $validated): ?Collection
    {
        $jenis = $validated['jenis_laporan'];
        $mode = $validated['periode_mode'] ?? 'akademik';
        $tahun = (int) $validated['tahun'];
        $semester = $validated['semester'] ?? null;
        $filters = [
            'tahun_akademik' => $validated['tahun_akademik'] ?? null,
            'angkatan' => $validated['angkatan'] ?? null,
            'program_studi' => $validated['program_studi'] ?? null,
        ];

        if ($jenis === 'mahasiswa') {
            $data = $this->reportData->fetch('mahasiswa', $tahun, null, $filters);

            return $data instanceof Collection ? $data : collect($data ?? []);
        }

        if ($mode === 'kalender') {
            if ($jenis === 'pembayaran') {
                return $this->reportData->fetchPembayaranKalender($tahun, $filters);
            }

            if ($jenis === 'tunggakan') {
                return $this->reportData->fetchTunggakanKalender($tahun, $filters);
            }
        }

        if ($jenis === 'pembayaran') {
            $data = $this->reportData->fetch('pembayaran', $tahun, $semester, $filters);

            return $data instanceof Collection ? $data : collect($data ?? []);
        }

        if ($jenis === 'tunggakan') {
            return $this->reportData->fetchTunggakanAkademik($tahun, $filters, $semester);
        }

        return null;
    }

    /**
     * Mengambil data untuk preview.
     */
    public function preview(Request $request)
    {
        Log::info('Memproses permintaan preview laporan.', $request->all());

        try {
            $validated = $this->validateReportFilters($request);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }

        Log::info('Preview Jenis: ' . $validated['jenis_laporan'] . ', Mode: ' . ($validated['periode_mode'] ?? 'akademik'));

        try {
            $data = $this->getReportData($validated);

            if ($data === null || $data->isEmpty()) {
                Log::warning('Tidak ada data preview untuk laporan.');

                return response()->json(['data' => [], 'meta' => $this->buildMeta($validated, collect())]);
            }

            return response()->json([
                'data' => $data,
                'meta' => $this->buildMeta($validated, $data),
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data preview: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal mengambil data preview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Membuat file laporan PDF dan menyimpan riwayat.
     */
    public function store(Request $request)
    {
        Log::info('Memproses permintaan generate laporan PDF.', $request->all());

        try {
            $validated = $this->validateReportFilters($request);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }

        $jenis = $validated['jenis_laporan'];
        $mode = $validated['periode_mode'] ?? 'akademik';
        $periodeFormatted = $this->formatPeriode($validated);
        $fileName = sprintf(
            'laporan_%s_%s_%s.pdf',
            $jenis,
            preg_replace('/[^a-zA-Z0-9_-]+/', '-', $periodeFormatted),
            time()
        );

        try {
            $data = $this->getReportData($validated);
            if ($data === null || $data->isEmpty()) {
                Log::warning("Tidak ada data untuk generate PDF {$jenis}.");

                return response()->json(['success' => false, 'message' => 'Tidak ada data untuk periode yang dipilih.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error ambil data untuk PDF: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal mengambil data laporan.'], 500);
        }

        $filePath = null;
        DB::beginTransaction();
        try {
            $pdf = app('dompdf.wrapper');

            if ($jenis === 'tunggakan') {
                $pdf->loadView('yayasan.pdf.laporan_tunggakan', [
                    'data' => $data,
                    'periodeFormatted' => $periodeFormatted,
                    'summaryTunggakan' => $this->reportData->summarizeTunggakan($data),
                ]);
            } elseif ($mode === 'kalender' && $jenis === 'pembayaran') {
                $pdf->loadView('pdf.laporan_pembayaran_kalender', [
                    'data' => $data,
                    'periodeFormatted' => $periodeFormatted,
                    'summaryPembayaran' => $this->reportData->summarizePembayaranKalender($data),
                    'contextLabel' => 'Admin',
                ])->setPaper('a4', 'landscape');
            } else {
                $pdf->loadView('pdf.laporan_template', [
                    'data' => $data,
                    'jenis' => $jenis,
                    'periodeFormatted' => $periodeFormatted,
                ]);
            }

            $directory = 'public/reports';
            if (! Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            $filePath = $directory . '/' . $fileName;

            $saveSuccess = Storage::put($filePath, $pdf->output());
            if (! $saveSuccess) {
                throw new \Exception('Gagal menyimpan file PDF ke storage.');
            }
            Log::info("Laporan PDF disimpan di: storage/app/{$filePath}");

            Report::create([
                'jenis_laporan' => $jenis . ($mode === 'kalender' ? '-kalender' : ''),
                'periode' => $periodeFormatted,
                'file_name' => $fileName,
            ]);
            Log::info('Riwayat laporan disimpan ke database.');

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Laporan PDF berhasil dibuat dan riwayat disimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error saat store laporan PDF: ' . $e->getMessage());

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
            $report = Report::findOrFail($reportId);
            $this->authorize('view', $report);
            $fileName = $report->file_name;
            $relativePath = 'reports/' . $fileName;
            $storagePath = 'public/' . $relativePath;

            Log::info("Mencari file di storage path: storage/app/{$storagePath}");

            if (! Storage::exists($storagePath)) {
                Log::error("File laporan tidak ditemukan. Path: {$storagePath}. Report ID: {$reportId}");
                abort(404, 'File laporan tidak ditemukan di storage.');
            }

            $absolutePath = Storage::path($storagePath);

            if ($request->has('view') && $request->input('view') == '1') {
                Log::info("Menampilkan file inline: {$fileName}");

                return response()->file($absolutePath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                ]);
            }

            Log::info("Memulai download file: {$fileName}");
            $stream = Storage::readStream($storagePath);

            return response()->streamDownload(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
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
        $filePath = 'public/reports/' . $report->file_name;

        DB::beginTransaction();
        try {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                Log::info("File laporan {$report->file_name} berhasil dihapus dari storage.");
            } else {
                Log::warning("File laporan {$report->file_name} tidak ditemukan di storage saat mencoba menghapus.");
            }

            Report::destroy($report->getKey());
            Log::info("Riwayat laporan ID {$report->id} berhasil dihapus dari database.");

            DB::commit();

            return response()->noContent(204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat hapus laporan ID {$report->id}: " . $e->getMessage());

            return response()->json(['message' => 'Gagal menghapus laporan: ' . $e->getMessage()], 500);
        }
    }

    private function validateReportFilters(Request $request): array
    {
        if ($request->filled('jenis_laporan') && $request->input('jenis_laporan') !== 'mahasiswa' && ! $request->filled('periode_mode')) {
            $request->merge(['periode_mode' => 'akademik']);
        }

        $validated = $request->validate([
            'jenis_laporan' => 'required|string|in:mahasiswa,pembayaran,tunggakan',
            'periode_mode' => 'nullable|string|in:akademik,kalender',
            'tahun' => 'nullable|digits:4',
            'tahun_akademik' => 'nullable|string|max:20',
            'tahun_kalender' => 'nullable|digits:4',
            'semester' => 'nullable|string|in:Ganjil,Genap',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $jenis = $validated['jenis_laporan'];

        if ($jenis === 'mahasiswa') {
            $validated['periode_mode'] = 'akademik';
            $tahun = (int) ($validated['tahun'] ?? 0);
            if ($tahun < 2000) {
                throw ValidationException::withMessages([
                    'tahun' => 'Tahun angkatan tidak valid.',
                ]);
            }
            $validated['tahun'] = $tahun;

            return $validated;
        }

        $mode = $validated['periode_mode'] ?? 'akademik';
        $validated['periode_mode'] = $mode;

        if ($mode === 'kalender') {
            $year = (int) ($validated['tahun_kalender'] ?? $validated['tahun'] ?? now()->year);
            $validated['tahun_kalender'] = (string) $year;
            $validated['tahun'] = $year;
            $validated['tahun_akademik'] = null;
            $validated['semester'] = null;
        } else {
            $ay = trim((string) ($validated['tahun_akademik'] ?? ''));
            if ($ay === '' || ! preg_match('/^(\d{4})\/\d{4}$/', $ay, $m)) {
                throw ValidationException::withMessages([
                    'tahun_akademik' => 'Format tahun akademik tidak valid (contoh: 2025/2026).',
                ]);
            }
            $validated['tahun_akademik'] = $ay;
            $validated['tahun'] = (int) $m[1];
        }

        return $validated;
    }

    private function formatPeriode(array $validated): string
    {
        if ($validated['jenis_laporan'] === 'mahasiswa') {
            return 'Angkatan ' . $validated['tahun'];
        }

        if (($validated['periode_mode'] ?? 'akademik') === 'kalender') {
            return ReportDataService::calendarYearLabel((int) $validated['tahun']);
        }

        $semester = $validated['semester'] ?? null;
        $tahunAkademik = trim((string) ($validated['tahun_akademik'] ?? ''));

        return ($tahunAkademik !== '' ? $tahunAkademik : (string) $validated['tahun']) . ($semester ? " {$semester}" : '');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMeta(array $validated, Collection $data): array
    {
        $meta = [
            'periode_mode' => $validated['periode_mode'] ?? 'akademik',
            'jenis_laporan' => $validated['jenis_laporan'],
            'periode_formatted' => $this->formatPeriode($validated),
        ];

        if ($validated['jenis_laporan'] === 'tunggakan') {
            $meta['summary_tunggakan'] = $this->reportData->summarizeTunggakan($data);
        }

        if (($validated['periode_mode'] ?? '') === 'kalender' && $validated['jenis_laporan'] === 'pembayaran') {
            $meta['summary_pembayaran'] = $this->reportData->summarizePembayaranKalender($data);
        }

        return $meta;
    }
}
