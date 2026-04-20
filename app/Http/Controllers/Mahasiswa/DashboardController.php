<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\AktivasiStatus;
use App\Models\Setting;
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
            ->filter(static function (Tagihan $t) use ($aktivasi, $today) {
                if ($aktivasi && $aktivasi->status === 'bss') {
                    return (bool) $t->is_bss && (!$t->available_from || (string) $t->available_from->toDateString() <= $today);
                }
                return !$t->is_bss && (!$t->available_from || (string) $t->available_from->toDateString() <= $today);
            });

        // 1. Kartu statistik: tunggakan = sisa belum dibayar; terbayar = jumlah terverifikasi kasir (semua cicilan)
        $totalTerbayar = (float) $allTagihan->sum(static function ($t) {
            return $t->pembayaranAll->sum('jumlah_bayar');
        });

        $totalTunggakan = 0.0;
        $jumlahTunggakan = 0;
        foreach ($allTagihan as $tagihan) {
            $sisa = self::sisaBelumBayar($tagihan);
            if ($sisa > 0) {
                $totalTunggakan += $sisa;
                $jumlahTunggakan++;
            }
        }

        // 2. Notifikasi jatuh tempo: tagihan yang masih ada sisa dan lewat jatuh tempo
        $tagihanJatuhTempo = $allTagihan->filter(static function ($t) {
            return self::sisaBelumBayar($t) > 0
                && Carbon::parse($t->tanggal_jatuh_tempo)->isPast();
        })->count();

        // 3. Tagihan yang masih ada sisa (selaras dengan kartu & jumlah tunggakan), urut jatuh tempo
        $tagihanAktif = $allTagihan->filter(static function ($t) {
            return self::sisaBelumBayar($t) > 0;
        })->sortBy('tanggal_jatuh_tempo');

        return view('mahasiswa.dashboard', [
            'mahasiswa' => $user->mahasiswaDetail,
            'totalTunggakan' => $totalTunggakan,
            'jumlahTunggakan' => $jumlahTunggakan,
            'totalTerbayar' => $totalTerbayar,
            'tagihanJatuhTempo' => $tagihanJatuhTempo,
            'tagihanAktif' => $tagihanAktif,
        ]);
    }

    /**
     * Sisa tagihan setelah pembayaran terverifikasi (non-dibatalkan). Lunas = 0.
     */
    private static function sisaBelumBayar(Tagihan $tagihan): float
    {
        if ($tagihan->status === 'Lunas') {
            return 0.0;
        }

        $terverifikasi = $tagihan->relationLoaded('pembayaranAll')
            ? (float) $tagihan->pembayaranAll->sum('jumlah_bayar')
            : (float) ($tagihan->total_angsuran ?? 0);

        if ($tagihan->sisa_pokok !== null) {
            return max(0.0, (float) $tagihan->sisa_pokok);
        }

        return max(0.0, (float) $tagihan->jumlah_tagihan - $terverifikasi);
    }
}
