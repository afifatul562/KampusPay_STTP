<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Report;
use App\Models\MahasiswaDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class LaporanController extends Controller
{
    private function applyMahasiswaFiltersToPembayaranQuery(Builder $query, Request $request): Builder
    {
        $tahunAkademik = trim((string) $request->input('tahun_akademik', ''));
        $angkatan = trim((string) $request->input('angkatan', ''));
        $programStudi = trim((string) $request->input('program_studi', ''));

        if ($tahunAkademik !== '') {
            $query->whereHas('tagihan', function($tq) use ($tahunAkademik) {
                $tq->where('semester_label', 'LIKE', $tahunAkademik . '%');
            });
        }
        if ($angkatan !== '' || $programStudi !== '') {
            $query->whereHas('tagihan.mahasiswa', function($mq) use ($angkatan, $programStudi) {
                if ($angkatan !== '') {
                    $mq->where('angkatan', $angkatan);
                }
                if ($programStudi !== '') {
                    $mq->where('program_studi', $programStudi);
                }
            });
        }

        return $query;
    }

    /**
     * Menampilkan halaman laporan kasir.
     */
    public function index(Request $request)
    {
        // Tentukan periode (bulan dan tahun)
        $selectedYear = $request->input('tahun', Carbon::now()->year);
        $selectedMonth = $request->input('bulan', Carbon::now()->month);

        $kasirId = Auth::id();

        // Query dasar untuk pembayaran pada periode yang dipilih
        $query = Pembayaran::with('tagihan.tarif', 'tagihan.mahasiswa')
            ->where('diverifikasi_oleh', '=', $kasirId, 'and')
            ->where('status_dibatalkan', '=', false, 'and')
            ->whereYear('tanggal_bayar', '=', $selectedYear, 'and')
            ->whereMonth('tanggal_bayar', '=', $selectedMonth, 'and');

        $this->applyMahasiswaFiltersToPembayaranQuery($query, $request);

        $totalPenerimaan = $query->clone()->get()->sum('jumlah_bayar') ?? 0;
        $jumlahTransaksi = $query->clone()->count();

        $laporanPerJenis = $query->clone()
            ->join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id', 'inner', false)
            ->join('mahasiswa_detail', 'tagihan.mahasiswa_id', '=', 'mahasiswa_detail.mahasiswa_id', 'inner', false)
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id', 'inner', false)
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('tagihan.semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.angkatan', trim((string) $request->input('angkatan')));
            })
            ->when(trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.program_studi', trim((string) $request->input('program_studi')));
            })
            ->select(
                'tarif_master.nama_pembayaran',
                DB::raw('SUM(pembayaran.jumlah_bayar) as total_nominal'),
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi')
            )
            ->groupBy('tarif_master.nama_pembayaran')
            ->orderBy('total_nominal', 'desc')
            ->get();

        $chartLabels = $laporanPerJenis->pluck('nama_pembayaran');
        $chartData = $laporanPerJenis->pluck('total_nominal');

        $masterAngkatan = MahasiswaDetail::query()
            ->select('angkatan')
            ->whereNotNull('angkatan', 'and')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');
        $masterProdi = MahasiswaDetail::query()
            ->select('program_studi')
            ->whereNotNull('program_studi', 'and')
            ->distinct()
            ->orderBy('program_studi', 'asc')
            ->pluck('program_studi');

        $selectedTahunAkademik = (string) $request->input('tahun_akademik', '');
        $selectedAngkatan = (string) $request->input('angkatan', '');
        $selectedProgramStudi = (string) $request->input('program_studi', '');

        return view('kasir.laporan', compact(
            'selectedYear',
            'selectedMonth',
            'selectedTahunAkademik',
            'selectedAngkatan',
            'selectedProgramStudi',
            'masterAngkatan',
            'masterProdi',
            'totalPenerimaan',
            'jumlahTransaksi',
            'laporanPerJenis',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Ekspor rangkuman laporan per jenis pembayaran menjadi CSV untuk periode terpilih.
     */
    public function exportCsv(Request $request)
    {
        $selectedYear = (int) $request->input('tahun', Carbon::now()->year);
        $selectedMonth = (int) $request->input('bulan', Carbon::now()->month);
        $kasirId = Auth::id();

        $rows = Pembayaran::join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id', 'inner', false)
            ->join('mahasiswa_detail', 'tagihan.mahasiswa_id', '=', 'mahasiswa_detail.mahasiswa_id', 'inner', false)
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id', 'inner', false)
            ->where('pembayaran.diverifikasi_oleh', '=', $kasirId, 'and')
            ->where('pembayaran.status_dibatalkan', '=', false, 'and')
            ->whereYear('pembayaran.tanggal_bayar', '=', $selectedYear, 'and')
            ->whereMonth('pembayaran.tanggal_bayar', '=', $selectedMonth, 'and')
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('tagihan.semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.angkatan', trim((string) $request->input('angkatan')));
            })
            ->when(trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.program_studi', trim((string) $request->input('program_studi')));
            })
            ->groupBy('tarif_master.nama_pembayaran')
            ->select(
                'tarif_master.nama_pembayaran as jenis_pembayaran',
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi'),
                DB::raw('SUM(pembayaran.jumlah_bayar) as total_nominal')
            )
            ->orderBy('total_nominal', 'desc')
            ->get();

        $filename = sprintf('laporan-kasir-%04d-%02d.csv', $selectedYear, $selectedMonth);
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $selectedYear, $selectedMonth) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 agar Excel Windows membaca dengan benar
            fprintf($out, "\xEF\xBB\xBF");
            // Header file
            fputcsv($out, [
                'Laporan Kasir',
                'Periode',
                sprintf('%02d-%04d', $selectedMonth, $selectedYear)
            ]);
            fputcsv($out, []);
            // Header kolom
            fputcsv($out, ['Jenis Pembayaran', 'Jumlah Transaksi', 'Total Nominal (Rp)']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->jenis_pembayaran,
                    (int) $row->jumlah_transaksi,
                    (int) $row->total_nominal,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor rangkuman laporan menjadi PDF untuk periode terpilih.
     */
    public function exportPdf(Request $request)
    {
        $selectedYear = (int) $request->input('tahun', Carbon::now()->year);
        $selectedMonth = (int) $request->input('bulan', Carbon::now()->month);
        $kasirId = Auth::id();

        $laporanPerJenis = Pembayaran::join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id', 'inner', false)
            ->join('mahasiswa_detail', 'tagihan.mahasiswa_id', '=', 'mahasiswa_detail.mahasiswa_id', 'inner', false)
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id', 'inner', false)
            ->where('pembayaran.diverifikasi_oleh', '=', $kasirId, 'and')
            ->where('pembayaran.status_dibatalkan', '=', false, 'and')
            ->whereYear('pembayaran.tanggal_bayar', '=', $selectedYear, 'and')
            ->whereMonth('pembayaran.tanggal_bayar', '=', $selectedMonth, 'and')
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('tagihan.semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.angkatan', trim((string) $request->input('angkatan')));
            })
            ->when(trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $q->where('mahasiswa_detail.program_studi', trim((string) $request->input('program_studi')));
            })
            ->groupBy('tarif_master.nama_pembayaran')
            ->select(
                'tarif_master.nama_pembayaran as nama_pembayaran',
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi'),
                DB::raw('SUM(pembayaran.jumlah_bayar) as total_nominal')
            )
            ->orderBy('total_nominal', 'desc')
            ->get();

        $totalPenerimaan = $laporanPerJenis->sum('total_nominal');
        $jumlahTransaksi = $laporanPerJenis->sum('jumlah_transaksi');

        $pdf = Pdf::loadView('kasir.laporan_pdf', [
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'laporanPerJenis' => $laporanPerJenis,
            'totalPenerimaan' => $totalPenerimaan,
            'jumlahTransaksi' => $jumlahTransaksi,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('laporan-kasir-%04d-%02d.pdf', $selectedYear, $selectedMonth);
        return $pdf->download($filename);
    }

    /**
     * Preview laporan tunggakan untuk kasir
     */
    public function previewTunggakan(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $tahun = (int) $request->tahun;
        $kasirId = Auth::id();
        $tahunAkademik = trim((string) $request->input('tahun_akademik', ''));

        try {
            $data = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaranAll' => function($q) use ($kasirId) {
                    $q->where('status_dibatalkan', false)
                      ->where('diverifikasi_oleh', $kasirId);
                }
            ])
            // Jika tahun akademik ada, jangan kunci created_at per tahun.
            ->when($tahunAkademik === '', function($q) use ($tahun) {
                $q->whereYear('created_at', '=', $tahun, 'and');
            })
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '' || trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $angkatan = trim((string) $request->input('angkatan', ''));
                $prodi = trim((string) $request->input('program_studi', ''));
                $q->whereHas('mahasiswa', function($mq) use ($angkatan, $prodi) {
                    if ($angkatan !== '') $mq->where('angkatan', $angkatan);
                    if ($prodi !== '') $mq->where('program_studi', $prodi);
                });
            })
            ->whereIn('status', Tagihan::statusesBelumLunasStorage(), 'and', false)
            ->orderBy('created_at')
            ->get();

            foreach ($data as $tagihan) {
                $tagihan->updateAngsuran();
            }
            $data->load('mahasiswa.user', 'tarif', 'pembayaranAll');

            Log::info('Preview laporan tunggakan kasir tahun ' . $tahun . ': ' . $data->count() . ' record.');

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error preview laporan tunggakan kasir: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data preview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview laporan pembayaran untuk kasir (filter berdasarkan kasir yang login)
     */
    public function previewPembayaran(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $tahun = (int) $request->tahun;
        $kasirId = Auth::id();
        $tahunAkademik = trim((string) $request->input('tahun_akademik', ''));

        try {
            $data = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaranAll' => function($q) use ($kasirId) {
                    $q->where('status_dibatalkan', false)
                      ->where('diverifikasi_oleh', $kasirId);
                }
            ])
            // Jika tahun akademik ada, jangan kunci created_at per tahun.
            ->when($tahunAkademik === '', function($q) use ($tahun) {
                $q->whereYear('created_at', '=', $tahun, 'and');
            })
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '' || trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $angkatan = trim((string) $request->input('angkatan', ''));
                $prodi = trim((string) $request->input('program_studi', ''));
                $q->whereHas('mahasiswa', function($mq) use ($angkatan, $prodi) {
                    if ($angkatan !== '') $mq->where('angkatan', $angkatan);
                    if ($prodi !== '') $mq->where('program_studi', $prodi);
                });
            })
            ->whereHas('pembayaranAll', function($q) use ($kasirId) {
                $q->where('status_dibatalkan', false)
                  ->where('diverifikasi_oleh', $kasirId);
            })
            ->where(function($q) {
                $q->where('status', 'Lunas')
                  ->orWhere(function($q2) {
                      $q2->where('status', 'Belum Lunas')
                         ->where('total_angsuran', '>', 0);
                  });
            })
            ->orderBy('created_at')
            ->get();

            foreach ($data as $tagihan) {
                $tagihan->updateAngsuran();
            }
            $data->load('mahasiswa.user', 'tarif', 'pembayaranAll');

            Log::info('Preview laporan pembayaran kasir tahun ' . $tahun . ': ' . $data->count() . ' record.');

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error preview laporan pembayaran kasir: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data preview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF laporan pembayaran untuk kasir dan simpan riwayat
     */
    public function generatePembayaranPdf(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $tahun = (int) $request->tahun;
        $kasirId = Auth::id();
        $kasir = Auth::user();
        $tahunAkademik = trim((string) $request->input('tahun_akademik', ''));

        try {
            $data = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaranAll' => function($q) use ($kasirId) {
                    $q->where('status_dibatalkan', false)
                      ->where('diverifikasi_oleh', $kasirId);
                }
            ])
            // Jika tahun akademik ada, jangan kunci created_at per tahun.
            ->when($tahunAkademik === '', function($q) use ($tahun) {
                $q->whereYear('created_at', '=', $tahun, 'and');
            })
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '' || trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $angkatan = trim((string) $request->input('angkatan', ''));
                $prodi = trim((string) $request->input('program_studi', ''));
                $q->whereHas('mahasiswa', function($mq) use ($angkatan, $prodi) {
                    if ($angkatan !== '') $mq->where('angkatan', $angkatan);
                    if ($prodi !== '') $mq->where('program_studi', $prodi);
                });
            })
            ->whereHas('pembayaranAll', function($q) use ($kasirId) {
                $q->where('status_dibatalkan', false)
                  ->where('diverifikasi_oleh', $kasirId);
            })
            ->where(function($q) {
                $q->where('status', 'Lunas')
                  ->orWhere(function($q2) {
                      $q2->where('status', 'Belum Lunas')
                         ->where('total_angsuran', '>', 0);
                  });
            })
            ->orderBy('created_at')
            ->get();

            if ($data->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data pembayaran untuk tahun yang dipilih.'], 404);
            }

            foreach ($data as $tagihan) {
                $tagihan->updateAngsuran();
            }
            // Reload data setelah update
            $data->load('pembayaranAll');

            // Group data by status
            $groupedData = $data->groupBy('status');

            $fileName = sprintf('laporan-pembayaran-kasir-%s-%04d-%s.pdf', $kasir->username, $tahun, time());
            $filePath = null;

            DB::beginTransaction();
            try {
                $pdf = Pdf::loadView('kasir.laporan_pembayaran_pdf', [
                    'data' => $data,
                    'groupedData' => $groupedData,
                    'tahun' => $tahun,
                    'kasir' => $kasir,
                    'jenis' => 'pembayaran-kasir',
                    'periodeFormatted' => (string) $tahun,
                ])->setPaper('a4', 'portrait');

                $directory = 'public/reports';
                if (!Storage::exists($directory)) {
                    Storage::makeDirectory($directory);
                }
                $filePath = $directory . '/' . $fileName;

                // Simpan file PDF ke storage
                $saveSuccess = Storage::put($filePath, $pdf->output());
                if (!$saveSuccess) {
                    throw new \Exception("Gagal menyimpan file PDF ke storage.");
                }

                // Simpan riwayat ke database
                $report = Report::create([
                    'jenis_laporan' => 'pembayaran-kasir',
                    'periode' => (string) $tahun,
                    'file_name' => $fileName,
                    'user_id' => $kasirId,
                ]);

                DB::commit();

                Log::info("Laporan PDF kasir disimpan: {$fileName}");

                $downloadUrl = route('kasir.reports.download', ['reportId' => $report->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'PDF berhasil di-generate dan disimpan.',
                    'download_url' => $downloadUrl,
                    'file_name' => $fileName
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error generate PDF laporan pembayaran kasir: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF laporan tunggakan untuk kasir dan simpan riwayat
     */
    public function generateTunggakanPdf(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'tahun_akademik' => 'nullable|string|max:20',
            'angkatan' => 'nullable|string|max:10',
            'program_studi' => 'nullable|string|max:100',
        ]);

        $tahun = (int) $request->tahun;
        $kasirId = Auth::id();
        $kasir = Auth::user();
        $tahunAkademik = trim((string) $request->input('tahun_akademik', ''));

        try {
            $data = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaranAll' => function($q) use ($kasirId) {
                    $q->where('status_dibatalkan', false)
                      ->where('diverifikasi_oleh', $kasirId);
                }
            ])
            // Jika tahun akademik ada, jangan kunci created_at per tahun.
            ->when($tahunAkademik === '', function($q) use ($tahun) {
                $q->whereYear('created_at', '=', $tahun, 'and');
            })
            ->when(trim((string) $request->input('tahun_akademik', '')) !== '', function($q) use ($request) {
                $ta = trim((string) $request->input('tahun_akademik', ''));
                $q->where('semester_label', 'LIKE', $ta . '%');
            })
            ->when(trim((string) $request->input('angkatan', '')) !== '' || trim((string) $request->input('program_studi', '')) !== '', function($q) use ($request) {
                $angkatan = trim((string) $request->input('angkatan', ''));
                $prodi = trim((string) $request->input('program_studi', ''));
                $q->whereHas('mahasiswa', function($mq) use ($angkatan, $prodi) {
                    if ($angkatan !== '') $mq->where('angkatan', $angkatan);
                    if ($prodi !== '') $mq->where('program_studi', $prodi);
                });
            })
            ->whereIn('status', Tagihan::statusesBelumLunasStorage(), 'and', false)
            ->orderBy('created_at')
            ->get();

            if ($data->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data tunggakan untuk tahun yang dipilih.'], 404);
            }

            foreach ($data as $tagihan) {
                $tagihan->updateAngsuran();
            }
            // Reload data setelah update
            $data->load('pembayaranAll');

            // Group data by status
            $groupedData = $data->groupBy('status');

            $fileName = sprintf('laporan-tunggakan-kasir-%s-%04d-%s.pdf', $kasir->username, $tahun, time());
            $filePath = null;

            DB::beginTransaction();
            try {
                $pdf = Pdf::loadView('kasir.laporan_tunggakan_pdf', [
                    'data' => $data,
                    'groupedData' => $groupedData,
                    'tahun' => $tahun,
                    'kasir' => $kasir,
                    'jenis' => 'tunggakan-kasir',
                    'periodeFormatted' => (string) $tahun,
                ])->setPaper('a4', 'portrait');

                $directory = 'public/reports';
                if (!Storage::exists($directory)) {
                    Storage::makeDirectory($directory);
                }
                $filePath = $directory . '/' . $fileName;

                // Simpan file PDF ke storage
                $saveSuccess = Storage::put($filePath, $pdf->output());
                if (!$saveSuccess) {
                    throw new \Exception("Gagal menyimpan file PDF ke storage.");
                }

                // Simpan riwayat ke database
                $report = Report::create([
                    'jenis_laporan' => 'tunggakan-kasir',
                    'periode' => (string) $tahun,
                    'file_name' => $fileName,
                    'user_id' => $kasirId,
                ]);

                DB::commit();

                Log::info("Laporan PDF tunggakan kasir disimpan: {$fileName}");

                $downloadUrl = route('kasir.reports.download', ['reportId' => $report->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'PDF berhasil di-generate dan disimpan.',
                    'download_url' => $downloadUrl,
                    'file_name' => $fileName
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error generate PDF laporan tunggakan kasir: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil riwayat laporan kasir
     */
    public function indexRiwayat(Request $request)
    {
        $kasirId = Auth::id();
        $reports = Report::where('user_id', '=', $kasirId, 'and')
            ->whereIn('jenis_laporan', ['pembayaran-kasir', 'tunggakan-kasir'], 'and', false)
            ->latest('created_at')
            ->get(['*']);

        return response()->json(['data' => $reports]);
    }

    /**
     * Download atau view laporan kasir
     */
    public function downloadRiwayat(Request $request, int|string $reportId)
    {
        $kasirId = Auth::id();
        $report = Report::where('id', '=', $reportId, 'and')
            ->where('user_id', '=', $kasirId, 'and')
            ->whereIn('jenis_laporan', ['pembayaran-kasir', 'tunggakan-kasir'], 'and', false)
            ->firstOrFail();

        $storagePath = 'public/reports/' . $report->file_name;

        if (!Storage::exists($storagePath)) {
            abort(404, 'File laporan tidak ditemukan.');
        }

        $absolutePath = Storage::path($storagePath);

        if ($request->has('view') && $request->input('view') == '1') {
            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $report->file_name . '"'
            ]);
        } else {
            $stream = Storage::readStream($storagePath);
            return response()->streamDownload(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) { fclose($stream); }
            }, $report->file_name, [
                'Content-Type' => 'application/pdf'
            ]);
        }
    }

    /**
     * Hapus laporan kasir
     */
    public function destroyRiwayat(int|string $reportId)
    {
        $kasirId = Auth::id();
        $report = Report::where('id', '=', $reportId, 'and')
            ->where('user_id', '=', $kasirId, 'and')
            ->whereIn('jenis_laporan', ['pembayaran-kasir', 'tunggakan-kasir'], 'and', false)
            ->firstOrFail();

        $storagePath = 'public/reports/' . $report->file_name;

        DB::beginTransaction();
        try {
            // Hapus file
            if (Storage::exists($storagePath)) {
                Storage::delete($storagePath);
            }

            // Hapus riwayat
            Report::destroy($report->getKey());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Laporan berhasil dihapus.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error hapus laporan kasir: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus laporan.'], 500);
        }
    }
}

