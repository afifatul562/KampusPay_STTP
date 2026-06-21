<?php

namespace App\Support;

use App\Models\MahasiswaDetail;

/**
 * Syarat pendaftaran wisuda (Fase 2).
 */
class WisudaEligibility
{
    /** @var array<string, int> */
    protected const MIN_SEMESTER_BY_PRODI = [
        'S1 Informatika' => 8,
        'S1 Teknik Sipil' => 8,
        'D3 Teknik Komputer' => 6,
    ];

    public static function minSemesterForProdi(string $programStudi): ?int
    {
        return self::MIN_SEMESTER_BY_PRODI[$programStudi] ?? null;
    }

    /**
     * @return array{eligible: bool, reason: ?string, min_semester: ?int, skripsi_label: string}
     */
    public static function assess(MahasiswaDetail $mahasiswa): array
    {
        $prodi = (string) $mahasiswa->program_studi;
        $minSem = self::minSemesterForProdi($prodi);
        $sem = (int) $mahasiswa->semester_aktif;

        $skripsiLabel = str_contains(mb_strtolower($prodi), 'd3')
            ? 'Tugas Akhir'
            : 'Skripsi';

        if ($minSem === null) {
            return [
                'eligible' => false,
                'reason' => 'Program studi Anda belum mendukung pendaftaran wisuda melalui sistem.',
                'min_semester' => null,
                'skripsi_label' => $skripsiLabel,
            ];
        }

        if ($sem < $minSem) {
            return [
                'eligible' => false,
                'reason' => "Minimal semester {$minSem} untuk {$prodi}. Semester aktif Anda: {$sem}.",
                'min_semester' => $minSem,
                'skripsi_label' => $skripsiLabel,
            ];
        }

        return [
            'eligible' => true,
            'reason' => null,
            'min_semester' => $minSem,
            'skripsi_label' => $skripsiLabel,
        ];
    }
}
