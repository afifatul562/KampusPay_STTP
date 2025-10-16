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
        $totalMahasiswa = \App\Models\MahasiswaDetail::count();
        $totalTagihan = Tagihan::count();
        $totalLunas = Tagihan::where('status', 'Lunas')->count();
        $pendingPayment = $totalTagihan - $totalLunas;

        $tingkatPembayaran = $totalTagihan > 0 ? ($totalLunas / $totalTagihan) * 100 : 0;

        $totalPembayaran = Tagihan::where('status', 'Lunas')->sum('jumlah_tagihan');

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