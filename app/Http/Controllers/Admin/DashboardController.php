<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik utama untuk dashboard.
     * (Menggantikan getDashboardStats)
     */
    public function stats()
    {
        // Kueri 1: Mengambil data statistik dari tabel Tagihan dalam satu kali jalan
        $tagihanStats = Tagihan::selectRaw("
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = 'Lunas' THEN 1 ELSE 0 END) as total_lunas,
                SUM(CASE WHEN status = 'Lunas' THEN jumlah_tagihan ELSE 0 END) as total_pembayaran
            ")
            ->first();

        // Kueri 2: Tetap terpisah karena beda tabel
        $totalMahasiswa = \App\Models\MahasiswaDetail::count(); // Perbaikan kecil: lihat poin 3

        // Hitung sisanya di PHP (lebih cepat)
        $totalLunas = $tagihanStats->total_lunas ?? 0;
        $totalTagihan = $tagihanStats->total_tagihan ?? 0;
        $totalPembayaran = $tagihanStats->total_pembayaran ?? 0;

        $pendingPayment = $totalTagihan - $totalLunas;
        $tingkatPembayaran = $totalTagihan > 0 ? ($totalLunas / $totalTagihan) * 100 : 0;

        return response()->json([
            'total_mahasiswa' => $totalMahasiswa,
            'total_pembayaran' => (int) $totalPembayaran,
            'tingkat_pembayaran' => round($tingkatPembayaran, 2),
            'pending_payment' => $pendingPayment,
        ]);
    }

    /**
     * Mengambil data pembayaran terakhir.
     * (Menggantikan getRecentPayments)
     */
    public function recentPayments()
    {
        // Eager load relasi untuk efisiensi
        // Pastikan kita load semua relasi sampai ke user
        $recentPayments = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif')
        ->latest('tanggal_bayar') // Lebih baik urutkan berdasarkan tanggal bayar
        ->limit(10)
        ->get();

        return response()->json($recentPayments);
    }

    /**
     * Mengambil data registrasi user terbaru.
     * (Menggantikan getRecentRegistrations)
     */
    public function recentRegistrations()
    {
        $registrations = User::with('mahasiswaDetail')
            ->whereIn('role', ['mahasiswa', 'kasir'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($registrations);
    }
}
