<?php

namespace App\Support;

use App\Models\Pembayaran;
use App\Models\Tagihan;

/**
 * Statistik dashboard berbasis tahun kalender (Jan–Des).
 *
 * Total uang masuk = pembayaran non-dibatalkan di tahun tersebut.
 * Tingkat pembayaran = total uang masuk ÷ nominal tagihan yang menerima
 * pembayaran di tahun yang sama (opsi 3).
 */
class DashboardYearStats
{
    /**
     * @return array{
     *   tahun_kalender: int,
     *   total_pembayaran: int,
     *   tingkat_pembayaran: float,
     * }
     */
    public static function forCalendarYear(?int $year = null): array
    {
        $year = $year ?? (int) now()->year;

        $totalDibayar = (float) Pembayaran::query()
            ->where('status_dibatalkan', false)
            ->whereYear('tanggal_bayar', $year)
            ->sum('jumlah_bayar');

        $totalNominal = (float) Tagihan::query()
            ->whereHas('pembayaranAll', static function ($q) use ($year) {
                $q->where('status_dibatalkan', false)
                    ->whereYear('tanggal_bayar', $year);
            })
            ->sum('jumlah_tagihan');

        $tingkat = $totalNominal > 0
            ? min(100, ($totalDibayar / $totalNominal) * 100)
            : 0.0;

        return [
            'tahun_kalender' => $year,
            'total_pembayaran' => (int) round($totalDibayar),
            'tingkat_pembayaran' => round($tingkat, 2),
        ];
    }
}
