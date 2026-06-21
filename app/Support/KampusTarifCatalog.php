<?php

namespace App\Support;

/**
 * Daftar tarif resmi kampus (nama + nominal referensi + rule penagihan).
 * Admin dapat mengubah nominal lewat UI; command sync memperbarui rule & melengkapi baris yang belum ada.
 */
class KampusTarifCatalog
{
    /** Nama tarif resmi UAS per semester. */
    public const NAMA_UAS = 'Uang Ujian Akhir Semester';

    /** Nama tarif gabungan maba semester 1. */
    public const NAMA_PPMB_ALMAMATER_CB = 'Uang PPMB, Almamater dan CB';

    /**
     * Varian nama dari Access / import yang merujuk tarif PPMB semester 1 yang sama.
     *
     * @return list<string>
     */
    public static function ppmPkkmbAliasNames(): array
    {
        return [
            'Uang PKKMB + Almamater + CB',
            'Uang PKKMB, Almamater dan CB',
            'Uang PPMB + Almamater + CB',
            'Uang PKKMB + Almamater + CB + PPMB',
        ];
    }

    public static function isPpmPkkmbTarifName(string $namaLower): bool
    {
        $n = mb_strtolower(trim($namaLower));

        if ($n === mb_strtolower(self::NAMA_PPMB_ALMAMATER_CB)) {
            return true;
        }

        foreach (self::ppmPkkmbAliasNames() as $alias) {
            if ($n === mb_strtolower($alias)) {
                return true;
            }
        }

        $hasMabaFee = str_contains($n, 'pkkmb') || str_contains($n, 'ppmb');
        $hasBundle = str_contains($n, 'almamater') || str_contains($n, 'cb');

        return $hasMabaFee && $hasBundle;
    }

    /**
     * Nama tarif lama / uji coba yang harus diganti atau dihapus.
     *
     * @return list<string>
     */
    public static function legacyTarifNames(): array
    {
        return [
            'Uang Ujian Akhir',
            'Ujian Akhir Semester',
            'UAS',
        ];
    }

    /**
     * @return list<array{
     *     nama_pembayaran: string,
     *     nominal: int,
     *     program_studi: ?string,
     *     angkatan: ?string,
     *     rule_json: array<string, mixed>
     * }>
     */
    public static function definitions(): array
    {
        $uasOffset = (int) config('academic.uas_due_offset_days', 7);
        $bssName = (string) config('academic.bss_tarif_name', 'Administrasi BSS');

        $baseDue = [
            'due_trigger' => 'uas_minus_days',
            'due_offset_days' => $uasOffset,
        ];

        $sem1Rule = array_merge($baseDue, ['only_semester_in' => [1]]);

        $items = [
            [
                'nama_pembayaran' => 'Uang Kemahasiswaan',
                'nominal' => 50_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => $baseDue,
            ],
            [
                'nama_pembayaran' => self::NAMA_UAS,
                'nominal' => 250_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => $baseDue,
            ],
            [
                'nama_pembayaran' => 'Uang Pembangunan',
                'nominal' => 3_000_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => $sem1Rule,
            ],
            [
                'nama_pembayaran' => self::NAMA_PPMB_ALMAMATER_CB,
                'nominal' => 900_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => $sem1Rule,
            ],
            [
                'nama_pembayaran' => 'Uang PKL',
                'nominal' => 750_000,
                'program_studi' => 'D3 Teknik Komputer',
                'angkatan' => null,
                'rule_json' => array_merge($baseDue, [
                    'only_semester_in' => [5],
                    'only_program_studi' => ['D3 Teknik Komputer'],
                ]),
            ],
            [
                'nama_pembayaran' => 'Uang Tugas Akhir',
                'nominal' => 1_750_000,
                'program_studi' => 'D3 Teknik Komputer',
                'angkatan' => null,
                'rule_json' => array_merge($baseDue, [
                    'only_semester_in' => [6],
                    'only_program_studi' => ['D3 Teknik Komputer'],
                ]),
            ],
            [
                'nama_pembayaran' => 'Uang KP',
                'nominal' => 750_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => array_merge($baseDue, [
                    'only_program_studi' => ['S1 Informatika', 'S1 Teknik Sipil'],
                    'semester_by_program_studi' => [
                        'S1 Informatika' => 7,
                        'S1 Teknik Sipil' => 7,
                    ],
                    'available_trigger' => 'semester_start_plus_days',
                    'available_offset_days' => 0,
                ]),
            ],
            [
                'nama_pembayaran' => 'Uang Skripsi',
                'nominal' => 2_000_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => array_merge($baseDue, [
                    'only_semester_in' => [8],
                    'only_program_studi' => ['S1 Informatika', 'S1 Teknik Sipil'],
                ]),
            ],
            [
                'nama_pembayaran' => 'Uang Wisuda',
                'nominal' => 3_500_000,
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => ['generation_mode' => 'wisuda_form'],
            ],
            [
                'nama_pembayaran' => $bssName,
                'nominal' => (int) config('academic.bss_amount', 200_000),
                'program_studi' => null,
                'angkatan' => null,
                'rule_json' => [],
            ],
        ];

        foreach (self::uangSemesterMatrix() as $row) {
            $items[] = [
                'nama_pembayaran' => 'Uang Semester',
                'nominal' => $row['nominal'],
                'program_studi' => $row['program_studi'],
                'angkatan' => $row['angkatan'],
                'rule_json' => $baseDue,
            ];
        }

        return $items;
    }

    /**
     * Matrix uang semester: angkatan × prodi (admin bisa tambah baris baru lewat UI).
     *
     * @return list<array{angkatan: string, program_studi: string, nominal: int}>
     */
    public static function uangSemesterMatrix(): array
    {
        $prodis = ['S1 Teknik Sipil', 'D3 Teknik Komputer', 'S1 Informatika'];
        $rows = [];

        foreach ($prodis as $prodi) {
            $rows[] = [
                'angkatan' => '2022',
                'program_studi' => $prodi,
                'nominal' => 3_000_000,
            ];
        }

        foreach (['2023', '2024', '2025'] as $angkatan) {
            $rows[] = [
                'angkatan' => $angkatan,
                'program_studi' => 'D3 Teknik Komputer',
                'nominal' => 3_250_000,
            ];
            foreach (['S1 Teknik Sipil', 'S1 Informatika'] as $prodi) {
                $rows[] = [
                    'angkatan' => $angkatan,
                    'program_studi' => $prodi,
                    'nominal' => 3_500_000,
                ];
            }
        }

        return $rows;
    }

    /**
     * Kunci unik baris tarif (nama + prodi + angkatan).
     */
    public static function rowKey(string $nama, ?string $prodi, ?string $angkatan): string
    {
        return mb_strtolower(trim($nama))
            . '|' . mb_strtolower(trim((string) ($prodi ?? '')))
            . '|' . mb_strtolower(trim((string) ($angkatan ?? '')));
    }
}
