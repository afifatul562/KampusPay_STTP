<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan periode (bulan dan tahun)
        $selectedYear = $request->input('tahun', Carbon::now()->year);
        $selectedMonth = $request->input('bulan', Carbon::now()->month);

        $kasirId = Auth::id();

        // Query dasar untuk pembayaran pada periode yang dipilih
        $query = Pembayaran::with('tagihan.tarif')
            ->where('diverifikasi_oleh', $kasirId)
            ->whereYear('tanggal_bayar', $selectedYear)
            ->whereMonth('tanggal_bayar', $selectedMonth);

        // 1. Data untuk Kartu Statistik
        $totalPenerimaan = $query->clone()->get()->sum('tagihan.jumlah_tagihan');
        $jumlahTransaksi = $query->clone()->count();

        // 2. Data untuk Tabel Rangkuman & Grafik
        $laporanPerJenis = $query->clone()
            ->join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id')
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id')
            ->select(
                'tarif_master.nama_pembayaran',
                DB::raw('SUM(tagihan.jumlah_tagihan) as total_nominal'),
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi')
            )
            ->groupBy('tarif_master.nama_pembayaran')
            ->orderBy('total_nominal', 'desc')
            ->get();

        // 3. Siapkan data khusus untuk Chart.js
        $chartLabels = $laporanPerJenis->pluck('nama_pembayaran');
        $chartData = $laporanPerJenis->pluck('total_nominal');

        return view('kasir.laporan', compact(
            'selectedYear',
            'selectedMonth',
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

        $rows = Pembayaran::join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id')
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id')
            ->where('pembayaran.diverifikasi_oleh', $kasirId)
            ->whereYear('pembayaran.tanggal_bayar', $selectedYear)
            ->whereMonth('pembayaran.tanggal_bayar', $selectedMonth)
            ->groupBy('tarif_master.nama_pembayaran')
            ->select(
                'tarif_master.nama_pembayaran as jenis_pembayaran',
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi'),
                DB::raw('SUM(tagihan.jumlah_tagihan) as total_nominal')
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

        $laporanPerJenis = Pembayaran::join('tagihan', 'pembayaran.tagihan_id', '=', 'tagihan.tagihan_id')
            ->join('tarif_master', 'tagihan.tarif_id', '=', 'tarif_master.tarif_id')
            ->where('pembayaran.diverifikasi_oleh', $kasirId)
            ->whereYear('pembayaran.tanggal_bayar', $selectedYear)
            ->whereMonth('pembayaran.tanggal_bayar', $selectedMonth)
            ->groupBy('tarif_master.nama_pembayaran')
            ->select(
                'tarif_master.nama_pembayaran as nama_pembayaran',
                DB::raw('COUNT(pembayaran.pembayaran_id) as jumlah_transaksi'),
                DB::raw('SUM(tagihan.jumlah_tagihan) as total_nominal')
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
}

