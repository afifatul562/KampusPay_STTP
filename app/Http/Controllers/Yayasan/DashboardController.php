<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\KonfirmasiPembayaran;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Setting;
use App\Models\Tagihan;
use App\Support\AcademicPeriod;
use App\Support\DashboardYearStats;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard monitoring yayasan (read-only).
     */
    public function index()
    {
        $yearStats = DashboardYearStats::forCalendarYear();

        $tagihanStats = Tagihan::selectRaw("
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = 'Lunas' THEN 1 ELSE 0 END) as total_lunas
            ")
            ->first();

        $totalMahasiswa = MahasiswaDetail::query()->count('mahasiswa_id');
        $totalLunas = (int) ($tagihanStats->total_lunas ?? 0);
        $totalTagihan = (int) ($tagihanStats->total_tagihan ?? 0);
        $pendingPayment = $totalTagihan - $totalLunas;

        $recentPayments = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'tagihan.pembayaranAll')
            ->where('status_dibatalkan', false)
            ->latest('tanggal_bayar')
            ->limit(10)
            ->get();

        $semesterAktif = Setting::currentSemesterLabel();
        $sudahAktivasi = (int) AktivasiStatus::query()
            ->where('semester_label', '=', $semesterAktif)
            ->distinct()
            ->count('mahasiswa_id');

        $tahunAkademik = AcademicPeriod::currentAcademicYear();

        return view('yayasan.dashboard', [
            'stats' => [
                'total_mahasiswa' => $totalMahasiswa,
                'total_pembayaran' => $yearStats['total_pembayaran'],
                'tingkat_pembayaran' => $yearStats['tingkat_pembayaran'],
                'tahun_kalender' => $yearStats['tahun_kalender'],
                'pending_payment' => $pendingPayment,
            ],
            'operasional' => [
                'menunggu_verifikasi' => KonfirmasiPembayaran::query()
                    ->whereIn('status_verifikasi', ['Menunggu Verifikasi', 'Ditinjau'], 'and', false)
                    ->whereHas('tagihan', fn ($q) => $q->where('status', '=', 'Menunggu Verifikasi Transfer'))
                    ->count(),
                'semester_label' => $semesterAktif,
                'aktivasi_aktif' => AktivasiStatus::query()
                    ->where('semester_label', '=', $semesterAktif)
                    ->where('status', '=', 'aktif')
                    ->count('id'),
                'aktivasi_bss' => AktivasiStatus::query()
                    ->where('semester_label', '=', $semesterAktif)
                    ->where('status', '=', 'bss')
                    ->count('id'),
                'belum_aktivasi' => max(0, $totalMahasiswa - $sudahAktivasi),
            ],
            'semesterCompare' => [
                'tahun_akademik' => $tahunAkademik,
                'ganjil' => $this->semesterCollectionStats($tahunAkademik, 'Ganjil'),
                'genap' => $this->semesterCollectionStats($tahunAkademik, 'Genap'),
            ],
            'monthlyPenerimaan' => $this->monthlyPenerimaan(6),
            'recentPayments' => $recentPayments,
        ]);
    }

    /**
     * @return array{total_tagihan: int, total_lunas: int, tingkat: float, terbayar: int}
     */
    private function semesterCollectionStats(string $tahunAkademik, string $semester): array
    {
        $labelPrefix = "{$tahunAkademik} {$semester}";

        $baseQuery = Tagihan::query()->where('semester_label', 'LIKE', $labelPrefix . '%');
        $totalTagihan = (clone $baseQuery)->count('tagihan_id');
        $totalLunas = (clone $baseQuery)->where('status', 'Lunas')->count('tagihan_id');
        $totalNominal = (float) (clone $baseQuery)->sum('jumlah_tagihan');

        $terbayar = (float) Pembayaran::query()
            ->where('status_dibatalkan', false)
            ->whereHas('tagihan', fn ($q) => $q->where('semester_label', 'LIKE', $labelPrefix . '%'))
            ->sum('jumlah_bayar');

        $tingkat = $totalNominal > 0 ? min(100, ($terbayar / $totalNominal) * 100) : 0;

        return [
            'total_tagihan' => $totalTagihan,
            'total_lunas' => $totalLunas,
            'tingkat' => round($tingkat, 1),
            'terbayar' => (int) round($terbayar),
        ];
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    private function monthlyPenerimaan(int $months = 6): array
    {
        $rows = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $total = (float) Pembayaran::query()
                ->where('status_dibatalkan', false)
                ->whereYear('tanggal_bayar', '=', $date->year, 'and')
                ->whereMonth('tanggal_bayar', '=', $date->month, 'and')
                ->sum('jumlah_bayar');

            $rows[] = [
                'label' => $date->locale('id')->translatedFormat('M Y'),
                'total' => (int) round($total),
            ];
        }

        return $rows;
    }
}
