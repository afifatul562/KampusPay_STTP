<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
}

