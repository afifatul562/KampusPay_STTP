<?php

namespace App\Support;

class LegacyTagihanGrouping
{
    /**
     * Jenis tagihan yang dipisah per semester perkuliahan saat import/gabung slip.
     */
    public static function usesSemesterInGroupKey(string $namaPembayaran): bool
    {
        $lower = strtolower(trim($namaPembayaran));

        return str_contains($lower, 'uang semester')
            || str_contains($lower, 'kemahasiswaan')
            || str_contains($lower, 'ujian akhir')
            || str_contains($lower, 'bss');
    }

    public static function buildGroupKey(string $npm, string $namaPembayaran, ?int $semesterPerkuliahan): string
    {
        $nama = strtolower(trim($namaPembayaran));
        if (self::usesSemesterInGroupKey($namaPembayaran)) {
            $sem = $semesterPerkuliahan ?? 0;

            return "{$npm}|{$nama}|sem:{$sem}";
        }

        return "{$npm}|{$nama}";
    }
}
