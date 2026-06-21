<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\AktivasiStatus;
use App\Models\Setting;
use App\Support\TagihanPayability;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil user yang sedang login beserta detail mahasiswanya
        // Load relasi konfirmasi untuk menampilkan alasan penolakan
        // Load relasi mahasiswa untuk mendapatkan data angkatan dan semester_aktif
        // pembayaranAll (non-dibatalkan) untuk total terverifikasi & sisa tunggakan (cicilan)
        $user = User::with([
            'mahasiswaDetail.tagihan.tarif',
            'mahasiswaDetail.tagihan.konfirmasi',
            'mahasiswaDetail.tagihan.mahasiswa',
            'mahasiswaDetail.tagihan.pembayaranAll' => static function ($q) {
                $q->where('status_dibatalkan', false);
            },
        ])->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $user->mahasiswaDetail->mahasiswa_id)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        $today = now()->toDateString();
        $allTagihan = $user->mahasiswaDetail->tagihan
            ->filter(static fn (Tagihan $t) => TagihanPayability::isVisibleForMahasiswa($t, $aktivasi, $today));

        $totalTerbayar = (float) $allTagihan->sum(static function ($t) {
            return $t->pembayaranAll->sum('jumlah_bayar');
        });

        $totalTunggakan = 0.0;
        $jumlahTunggakan = 0;
        foreach ($allTagihan as $tagihan) {
            $sisa = $tagihan->nominalTunggakan();
            if ($sisa > 0) {
                $totalTunggakan += $sisa;
                $jumlahTunggakan++;
            }
        }

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
        })->sortBy('tanggal_jatuh_tempo')->values();

        $totalMendekati = (float) $tagihanMendekati->sum(static fn ($t) => $t->nominalTunggakan());

        $tagihanAktif = $allTagihan->filter(static function ($t) {
            return $t->masihTunggakan();
        })->sortBy('tanggal_jatuh_tempo');

        return view('mahasiswa.dashboard', [
            'mahasiswa' => $user->mahasiswaDetail,
            'totalTunggakan' => $totalTunggakan,
            'jumlahTunggakan' => $jumlahTunggakan,
            'totalTerbayar' => $totalTerbayar,
            'tagihanJatuhTempo' => $tagihanJatuhTempo,
            'tagihanMendekati' => $tagihanMendekati,
            'jumlahMendekati' => $tagihanMendekati->count(),
            'totalMendekati' => $totalMendekati,
            'tagihanAktif' => $tagihanAktif,
        ]);
    }
}
