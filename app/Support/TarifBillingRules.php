<?php

namespace App\Support;

use App\Models\MahasiswaDetail;
use App\Models\TarifMaster;

/**
 * Aturan penagihan default per jenis tarif (digabung dengan rule_json di DB bila ada).
 */
class TarifBillingRules
{
    public static function effectiveRule(TarifMaster $tarif): array
    {
        $stored = is_array($tarif->rule_json) ? $tarif->rule_json : [];

        return array_merge(self::defaultsForName((string) $tarif->nama_pembayaran), $stored);
    }

    public static function skipOnAktivasi(TarifMaster $tarif): bool
    {
        $rule = self::effectiveRule($tarif);
        $mode = mb_strtolower(trim((string) ($rule['generation_mode'] ?? 'aktivasi')));

        if ($mode === 'wisuda_form') {
            return true;
        }

        $nama = mb_strtolower(trim((string) $tarif->nama_pembayaran));

        return str_contains($nama, 'wisuda');
    }

    public static function isUasTarifName(string $namaLower): bool
    {
        if (self::isLegacyUasTestName($namaLower)) {
            return false;
        }

        return str_contains($namaLower, 'ujian akhir semester')
            || str_contains($namaLower, 'ujian akhir')
            || preg_match('/\buas\b/u', $namaLower) === 1;
    }

    /**
     * Nama tarif UAS uji coba / tidak resmi (mis. "UAS" Rp 4 juta).
     */
    public static function isLegacyUasTestName(string $namaLower): bool
    {
        $n = trim($namaLower);

        return $n === 'uas' || $n === 'uang ujian akhir semester uas';
    }

    public static function isPklTarifName(string $namaLower): bool
    {
        return str_contains($namaLower, 'pkl')
            || str_contains($namaLower, 'praktik kuliah');
    }

    public static function isKpTarifName(string $namaLower): bool
    {
        if (self::isPklTarifName($namaLower)) {
            return false;
        }

        return str_contains($namaLower, 'uang kp')
            || preg_match('/\bkp\b/u', $namaLower) === 1
            || str_contains($namaLower, 'kerja praktek');
    }

    /**
     * Tarif sekali seumur hidup per mahasiswa (tidak digenerate ulang saat aktivasi).
     */
    public static function isOncePerMahasiswaTarifName(string $namaPembayaran): bool
    {
        return self::isSem1OnlyTarifName(mb_strtolower(trim($namaPembayaran)));
    }

    public static function isSem1OnlyTarifName(string $namaLower): bool
    {
        if (str_contains($namaLower, 'pembangunan')) {
            return true;
        }

        return KampusTarifCatalog::isPpmPkkmbTarifName($namaLower);
    }

    public static function appliesToMahasiswa(
        TarifMaster $tarif,
        MahasiswaDetail $mahasiswa,
        string $semesterLabel,
    ): bool {
        $rule = self::effectiveRule($tarif);
        $sem = (int) $mahasiswa->semester_aktif;
        $prodi = (string) $mahasiswa->program_studi;

        if (!empty($rule['only_program_studi']) && is_array($rule['only_program_studi'])) {
            $allowed = array_map('strval', $rule['only_program_studi']);
            if (!in_array($prodi, $allowed, true)) {
                return false;
            }
        }

        if (!empty($rule['semester_by_program_studi']) && is_array($rule['semester_by_program_studi'])) {
            $requiredSem = $rule['semester_by_program_studi'][$prodi] ?? null;
            if ($requiredSem === null || $sem !== (int) $requiredSem) {
                return false;
            }
        }

        if (!empty($rule['only_even_semester'])) {
            if (!str_contains(mb_strtolower($semesterLabel), 'genap')) {
                return false;
            }
        }
        if (!empty($rule['only_odd_semester'])) {
            if (!str_contains(mb_strtolower($semesterLabel), 'ganjil')) {
                return false;
            }
        }

        if (!empty($rule['only_semester_in']) && is_array($rule['only_semester_in'])) {
            if (!in_array($sem, array_map('intval', $rule['only_semester_in']), true)) {
                return false;
            }
        }

        if (!empty($rule['only_semester_range']) && is_array($rule['only_semester_range'])) {
            $min = isset($rule['only_semester_range'][0]) ? (int) $rule['only_semester_range'][0] : null;
            $max = isset($rule['only_semester_range'][1]) ? (int) $rule['only_semester_range'][1] : null;
            if ($min !== null && $sem < $min) {
                return false;
            }
            if ($max !== null && $sem > $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function defaultsForName(string $namaPembayaran): array
    {
        $n = mb_strtolower(trim($namaPembayaran));
        $uasOffset = (int) config('academic.uas_due_offset_days', 7);

        $baseDue = [
            'due_trigger' => 'uas_minus_days',
            'due_offset_days' => $uasOffset,
        ];

        if (str_contains($n, 'wisuda')) {
            return ['generation_mode' => 'wisuda_form'];
        }

        if (self::isPklTarifName($n)) {
            return array_merge($baseDue, [
                'only_semester_in' => [5],
                'only_program_studi' => ['D3 Teknik Komputer'],
            ]);
        }

        if (self::isKpTarifName($n)) {
            return array_merge($baseDue, [
                'only_program_studi' => ['S1 Informatika', 'S1 Teknik Sipil'],
                'semester_by_program_studi' => [
                    'S1 Informatika' => 7,
                    'S1 Teknik Sipil' => 7,
                ],
                'available_trigger' => 'semester_start_plus_days',
                'available_offset_days' => 0,
            ]);
        }

        if (str_contains($n, 'skripsi')) {
            return array_merge($baseDue, [
                'only_semester_in' => [8],
                'only_program_studi' => ['S1 Informatika', 'S1 Teknik Sipil'],
            ]);
        }

        if (str_contains($n, 'tugas akhir')) {
            return array_merge($baseDue, [
                'only_semester_in' => [6],
                'only_program_studi' => ['D3 Teknik Komputer'],
            ]);
        }

        if (self::isSem1OnlyTarifName($n)) {
            return array_merge($baseDue, [
                'only_semester_in' => [1],
            ]);
        }

        return $baseDue;
    }
}
