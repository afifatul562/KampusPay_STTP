<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard mahasiswa.
     */
    public function index()
    {
        $user = User::with('mahasiswaDetail.tagihan.tarif', 'mahasiswaDetail.tagihan.konfirmasi', 'mahasiswaDetail.tagihan.mahasiswa')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $allTagihan = $user->mahasiswaDetail->tagihan;

        // 1. Data untuk Kartu Statistik
        // Tagihan aktif termasuk yang "Belum Lunas" dan "Ditolak" (karena perlu dibayar ulang)
        $tagihanBelumLunas = $allTagihan->where('status', 'Belum Lunas');
        $tagihanDitolak = $allTagihan->where('status', 'Ditolak');
        $totalTunggakan = $tagihanBelumLunas->sum('jumlah_tagihan') + $tagihanDitolak->sum('jumlah_tagihan');
        $jumlahTunggakan = $tagihanBelumLunas->count() + $tagihanDitolak->count();
        $totalTerbayar = $allTagihan->where('status', 'Lunas')->sum('jumlah_tagihan');

        // 2. Data untuk Notifikasi Jatuh Tempo
        // Termasuk tagihan yang ditolak dalam perhitungan jatuh tempo
        $tagihanJatuhTempo = $allTagihan->whereIn('status', ['Belum Lunas', 'Ditolak'])
                                       ->where('tanggal_jatuh_tempo', '<', Carbon::now())
                                       ->count();

        // 3. Ambil tagihan yang belum lunas atau ditolak untuk ditampilkan di daftar
        // Tagihan ditolak perlu ditampilkan karena mahasiswa perlu membayar ulang
        $tagihanAktif = $allTagihan->whereIn('status', ['Belum Lunas', 'Ditolak'])
                                   ->sortBy('tanggal_jatuh_tempo');

        return view('mahasiswa.dashboard', [
            'mahasiswa' => $user->mahasiswaDetail,
            'totalTunggakan' => $totalTunggakan,
            'jumlahTunggakan' => $jumlahTunggakan,
            'totalTerbayar' => $totalTerbayar,
            'tagihanJatuhTempo' => $tagihanJatuhTempo,
            'tagihanAktif' => $tagihanAktif,
        ]);
    }
}
