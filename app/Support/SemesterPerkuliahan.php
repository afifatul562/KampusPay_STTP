<?php

namespace App\Support;

use App\Models\MahasiswaDetail;

class SemesterPerkuliahan
{
    /** @var array<string, int> */
    private const ROMAN = [
        'i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5,
        'vi' => 6, 'vii' => 7, 'viii' => 8, 'ix' => 9, 'x' => 10,
        'xi' => 11, 'xii' => 12, 'xiii' => 13, 'xiv' => 14,
    ];

    public static function parse(?string $raw): ?int
    {
        $v = trim((string) $raw);
        if ($v === '') {
            return null;
        }

        if (preg_match('/semester\s*([ivx]+|\d+)/i', $v, $m)) {
            return self::parseToken($m[1]);
        }

        if (preg_match('/^(\d+)$/', $v, $m)) {
            return self::clamp((int) $m[1]);
        }

        if (preg_match('/^([ivx]+)$/i', $v, $m)) {
            return self::parseToken($m[1]);
        }

        return null;
    }

    public static function fromMahasiswa(?MahasiswaDetail $mahasiswa): ?int
    {
        if (! $mahasiswa || $mahasiswa->semester_aktif === null || $mahasiswa->semester_aktif === '') {
            return null;
        }

        return self::clamp((int) $mahasiswa->semester_aktif);
    }

    private static function parseToken(string $token): ?int
    {
        if (ctype_digit($token)) {
            return self::clamp((int) $token);
        }

        $roman = strtolower($token);

        return self::clamp(self::ROMAN[$roman] ?? 0) ?: null;
    }

    private static function clamp(int $n): ?int
    {
        return ($n >= 1 && $n <= 14) ? $n : null;
    }
}
