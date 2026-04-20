<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
class DashboardController extends Controller
{
    /**
     * Statistik dashboard:
     * - total_pembayaran: SUM(jumlah_bayar) pada pembayaran yang tidak dibatalkan;
     * - tingkat_pembayaran: total_pembayaran dibanding total nominal tagihan (penyebut);
     * - pending_payment: jumlah baris tagihan yang statusnya bukan "Lunas".
     */
    public function stats()
    {
        $tagihanStats = Tagihan::selectRaw('
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_lunas,
                SUM(COALESCE(jumlah_tagihan, 0)) as total_nominal
            ', ['Lunas'])
            ->first();

        // Total rupiah yang sudah masuk (termasuk cicilan / belum lunas penuh)
        $totalDibayar = (float) Pembayaran::where('status_dibatalkan', false)->sum('jumlah_bayar');

        $totalMahasiswa = MahasiswaDetail::count();

        $totalLunas = (int) ($tagihanStats->total_lunas ?? 0);
        $totalTagihan = (int) ($tagihanStats->total_tagihan ?? 0);
        $totalNominal = (float) ($tagihanStats->total_nominal ?? 0);

        $pendingPayment = $totalTagihan - $totalLunas;
        $tingkatPembayaran = $totalNominal > 0
            ? min(100, ($totalDibayar / $totalNominal) * 100)
            : 0;

        return response()->json([
            'total_mahasiswa' => $totalMahasiswa,
            'total_pembayaran' => (int) round($totalDibayar),
            'tingkat_pembayaran' => round($tingkatPembayaran, 2),
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
