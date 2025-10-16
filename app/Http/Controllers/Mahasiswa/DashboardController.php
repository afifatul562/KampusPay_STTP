<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil user yang sedang login beserta detail mahasiswanya
        $user = User::with('mahasiswaDetail.tagihan.tarif')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $allTagihan = $user->mahasiswaDetail->tagihan;

        // 1. Data untuk Kartu Statistik
        $totalTunggakan = $allTagihan->where('status', 'Belum Lunas')->sum('jumlah_tagihan');
        $jumlahTunggakan = $allTagihan->where('status', 'Belum Lunas')->count();
        $totalTerbayar = $allTagihan->where('status', 'Lunas')->sum('jumlah_tagihan');

        // 2. Data untuk Notifikasi Jatuh Tempo
        $tagihanJatuhTempo = $allTagihan->where('status', 'Belum Lunas')
                                       ->where('tanggal_jatuh_tempo', '<', Carbon::now())
                                       ->count();

        // 3. Ambil hanya tagihan yang belum lunas untuk ditampilkan di daftar
        $tagihanAktif = $allTagihan->where('status', 'Belum Lunas')
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