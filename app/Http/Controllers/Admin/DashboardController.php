<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Support\DashboardYearStats;

class DashboardController extends Controller
{
    /**
     * Statistik dashboard:
     * - total_pembayaran: uang masuk tahun kalender berjalan (Jan–Des);
     * - tingkat_pembayaran: total_pembayaran ÷ nominal tagihan terbayar di tahun yang sama;
     * - pending_payment: jumlah baris tagihan yang statusnya bukan "Lunas".
     */
    public function stats()
    {
        $yearStats = DashboardYearStats::forCalendarYear();

        $tagihanStats = Tagihan::selectRaw('
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_lunas
            ', ['Lunas'])
            ->first();

        $totalMahasiswa = MahasiswaDetail::count();

        $totalLunas = (int) ($tagihanStats->total_lunas ?? 0);
        $totalTagihan = (int) ($tagihanStats->total_tagihan ?? 0);
        $pendingPayment = $totalTagihan - $totalLunas;

        return response()->json([
            'total_mahasiswa' => $totalMahasiswa,
            'total_pembayaran' => $yearStats['total_pembayaran'],
            'tingkat_pembayaran' => $yearStats['tingkat_pembayaran'],
            'tahun_kalender' => $yearStats['tahun_kalender'],
            'pending_payment' => $pendingPayment,
        ]);
    }

    /**
     * Daftar pembayaran terbaru untuk widget dashboard (JSON).
     */
    public function recentPayments()
    {
        $recentPayments = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif')
            ->latest('tanggal_bayar')
            ->limit(10)
            ->get();

        return response()->json($recentPayments);
    }
}
