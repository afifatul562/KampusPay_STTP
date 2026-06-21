<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Periode tahun akademik & semester aktif (aturan kampus).
 *
 * Tahun akademik YYYY/(YYYY+1) dimulai semester Ganjil (~Oktober).
 * Jan–Sep (sampai sebelum Oktober) masih TA yang sama dengan Ganjil sebelumnya.
 */
class AcademicPeriod
{
    /**
     * @return array{0:string,1:string} ["YYYY/YYYY+1", "Ganjil|Genap|Libur"]
     */
    public static function compute(?Carbon $at = null): array
    {
        $now = $at ?? now();
        $y = (int) $now->year;
        $m = (int) $now->month;

        if ($m >= 10 || $m <= 2) {
            $semester = 'Ganjil';
        } elseif ($m >= 3 && $m <= 7) {
            $semester = 'Genap';
        } else {
            $semester = 'Libur';
        }

        // Oktober dst: TA baru (2025/2026). Jan–Sep: masih TA tahun sebelumnya (2025/2026 di Mei 2026).
        $startYear = $m >= 10 ? $y : $y - 1;

        return [sprintf('%d/%d', $startYear, $startYear + 1), $semester];
    }

    public static function currentAcademicYear(?Carbon $at = null): string
    {
        return self::compute($at)[0];
    }

    public static function currentSemester(?Carbon $at = null): string
    {
        return self::compute($at)[1];
    }

    public static function currentSemesterLabel(?Carbon $at = null): string
    {
        [$year, $semester] = self::compute($at);

        return trim($year . ' ' . $semester);
    }
}
