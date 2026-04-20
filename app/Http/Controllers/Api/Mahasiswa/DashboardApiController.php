<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = User::with('mahasiswaDetail.tagihan.tarif', 'mahasiswaDetail.tagihan.konfirmasi', 'mahasiswaDetail.tagihan.mahasiswa')
            ->find(Auth::id());

        if (!$user || !$user->mahasiswaDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $mahasiswa = $user->mahasiswaDetail;
        $allTagihan = $mahasiswa->tagihan;

        $tagihanBelumLunas = $allTagihan->where('status', 'Belum Lunas');
        $tagihanDitolak = $allTagihan->where('status', 'Ditolak');

        $totalTunggakan = $tagihanBelumLunas->sum('jumlah_tagihan') + $tagihanDitolak->sum('jumlah_tagihan');
        $jumlahTunggakan = $tagihanBelumLunas->count() + $tagihanDitolak->count();
        $totalTerbayar = $allTagihan->where('status', 'Lunas')->sum('jumlah_tagihan');

        $tagihanJatuhTempo = $allTagihan
            ->whereIn('status', ['Belum Lunas', 'Ditolak'])
            ->where('tanggal_jatuh_tempo', '<', Carbon::now())
            ->count();

        $tagihanAktif = $allTagihan
            ->whereIn('status', ['Belum Lunas', 'Ditolak'])
            ->sortBy('tanggal_jatuh_tempo')
            ->values()
            ->map(function ($tagihan) {
                return [
                    'id' => $tagihan->tagihan_id,
                    'kode_pembayaran' => $tagihan->kode_pembayaran,
                    'nama_pembayaran' => $tagihan->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $tagihan->jenis_tampilan,
                    'jumlah_tagihan' => $tagihan->jumlah_tagihan,
                    'sisa_pokok' => $tagihan->sisa_pokok,
                    'status' => $tagihan->status,
                    'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                ];
            });

        return response()->json([
            'success' => true,
            'mahasiswa' => [
                'id' => $mahasiswa->mahasiswa_id,
                'nim' => $mahasiswa->nim ?? null,
                'nama' => $mahasiswa->nama ?? $user->name,
                'angkatan' => $mahasiswa->angkatan ?? null,
                'semester_aktif' => $mahasiswa->semester_aktif ?? null,
                'prodi' => $mahasiswa->program_studi ?? null,
            ],
            'ringkasan' => [
                'total_tunggakan' => $totalTunggakan,
                'jumlah_tunggakan' => $jumlahTunggakan,
                'total_terbayar' => $totalTerbayar,
                'tagihan_jatuh_tempo' => $tagihanJatuhTempo,
            ],
            'tagihan_aktif' => $tagihanAktif,
        ]);
    }
}

