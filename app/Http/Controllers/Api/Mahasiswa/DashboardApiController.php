<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\Setting;
use App\Models\User;
use App\Support\TagihanPayability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = User::with([
            'mahasiswaDetail.tagihan.tarif',
            'mahasiswaDetail.tagihan.konfirmasi',
            'mahasiswaDetail.tagihan.mahasiswa',
            'mahasiswaDetail.tagihan.pembayaranAll' => static function ($q) {
                $q->where('status_dibatalkan', false);
            },
        ])->find(Auth::id());

        if (!$user || !$user->mahasiswaDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $mahasiswa = $user->mahasiswaDetail;
        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('semester_label', $semester)
            ->latest()
            ->first();
        $today = now()->toDateString();

        $allTagihan = $mahasiswa->tagihan
            ->filter(static fn ($t) => TagihanPayability::isVisibleForMahasiswa($t, $aktivasi, $today));

        $totalTunggakan = (float) $allTagihan->sum(static fn ($t) => $t->nominalTunggakan());
        $jumlahTunggakan = $allTagihan->filter(static fn ($t) => $t->masihTunggakan())->count();

        $totalTerbayar = (float) $allTagihan->sum(static function ($tagihan) {
            return $tagihan->totalTerverifikasi();
        });

        $tagihanJatuhTempo = $allTagihan->filter(static function ($t) {
            return $t->masihTunggakan()
                && Carbon::parse($t->tanggal_jatuh_tempo)->isPast();
        })->count();

        $batasMendekati = now()->addDays(7)->endOfDay();
        $tagihanMendekati = $allTagihan->filter(static function ($t) use ($batasMendekati) {
            if (!$t->masihTunggakan()) {
                return false;
            }
            $due = Carbon::parse($t->tanggal_jatuh_tempo);

            return !$due->isPast() && $due->lessThanOrEqualTo($batasMendekati);
        });

        $tagihanAktif = $allTagihan
            ->filter(static fn ($t) => $t->masihTunggakan())
            ->sortBy('tanggal_jatuh_tempo')
            ->values()
            ->map(function ($tagihan) {
                $status = $tagihan->statusForMobileApi();

                return [
                    'id' => $tagihan->tagihan_id,
                    'kode_pembayaran' => $tagihan->kode_pembayaran,
                    'nama_pembayaran' => $tagihan->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $tagihan->jenis_tampilan,
                    'jumlah_tagihan' => $tagihan->jumlah_tagihan,
                    'sisa_pokok' => $tagihan->nominalTunggakan(),
                    'status' => $status['status'],
                    'status_kode' => $status['status_kode'],
                    'status_verifikasi' => $status['status_verifikasi'],
                    'status_warna' => $status['status_warna'],
                    'status_warna_bg' => $status['status_warna_bg'],
                    'status_warna_teks' => $status['status_warna_teks'],
                    'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                ];
            });

        return response()->json([
            'success' => true,
            'mahasiswa' => [
                'id' => $mahasiswa->mahasiswa_id,
                'npm' => $mahasiswa->npm,
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
                'tagihan_mendekati' => $tagihanMendekati->count(),
                'total_mendekati' => (float) $tagihanMendekati->sum(static fn ($t) => $t->nominalTunggakan()),
                'notifikasi_belum_dibaca' => $user->unreadNotifications()->count(),
            ],
            'tagihan_aktif' => $tagihanAktif,
        ]);
    }
}
