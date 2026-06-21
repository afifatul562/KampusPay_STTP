<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\AcademicPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixSemesterLabelsCommand extends Command
{
    protected $signature = 'academic:fix-semester-labels
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Perbaiki semester_label yang salah akibat aturan tahun akademik lama (tahun kalender = awal TA)';

    /** @var list<array{table:string,id_column:string,id:int|string,from:string,to:string}> */
    private array $changes = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang diubah.');
        }

        $this->scanTable('tagihan', 'tagihan_id', 'semester_label');
        $this->scanTable('aktivasi_statuses', 'id', 'semester_label');

        if (Schema::hasTable('academic_calendar')) {
            $this->scanTable('academic_calendar', 'id', 'semester_label');
        }

        if ($this->changes === []) {
            $this->info('Tidak ada semester_label yang perlu diperbaiki.');
        } else {
            $this->table(['Tabel', 'ID', 'Dari', 'Menjadi'], array_map(
                fn (array $c) => [$c['table'], $c['id'], $c['from'], $c['to']],
                $this->changes
            ));

            if (! $dryRun) {
                foreach ($this->changes as $change) {
                    DB::table($change['table'])
                        ->where($change['id_column'], $change['id'])
                        ->update(['semester_label' => $change['to']]);
                }
                $this->info('Diperbarui: ' . count($this->changes) . ' baris.');
            }
        }

        [$year, $semester] = AcademicPeriod::compute();
        $this->line('');
        $this->info("Periode aktif (hitungan sistem): {$year} {$semester}");

        if (! $dryRun) {
            Setting::updateOrCreate(['key' => 'academic_year'], ['value' => $year]);
            Setting::updateOrCreate(['key' => 'semester'], ['value' => $semester]);
            Cache::forget('settings:key_value_map');
            $this->info('Pengaturan academic_year & semester diselaraskan.');
        }

        return self::SUCCESS;
    }

    private function scanTable(string $table, string $idColumn, string $labelColumn): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $rows = DB::table($table)
            ->whereNotNull($labelColumn)
            ->where($labelColumn, '!=', '')
            ->get([$idColumn, $labelColumn, 'created_at']);

        foreach ($rows as $row) {
            $from = trim((string) $row->{$labelColumn});
            $to = $this->correctedLabel($from, $row->created_at);

            if ($to === null || $to === $from) {
                continue;
            }

            $this->changes[] = [
                'table' => $table,
                'id_column' => $idColumn,
                'id' => $row->{$idColumn},
                'from' => $from,
                'to' => $to,
            ];
        }
    }

    /**
     * Koreksi label yang memakai tahun kalender sebagai awal TA (Jan–Sep),
     * mis. "2026/2027 Genap" di bulan Mei 2026 → "2025/2026 Genap".
     */
    private function correctedLabel(string $label, mixed $createdAt): ?string
    {
        if (! preg_match('/^(\d{4})\/(\d{4})\s+(Ganjil|Genap|Libur)$/', $label, $m)) {
            return null;
        }

        $startYear = (int) $m[1];
        $endYear = (int) $m[2];
        $semesterName = $m[3];

        if ($endYear !== $startYear + 1) {
            return null;
        }

        $at = $createdAt ? Carbon::parse($createdAt) : now();
        $calendarYear = (int) $at->year;
        $month = (int) $at->month;

        // Jan–Sep: TA aktif dimulai tahun sebelumnya; bug lama memakai tahun kalender sebagai awal.
        if ($month >= 10 || $startYear !== $calendarYear) {
            return null;
        }

        $fixedStart = $startYear - 1;

        return sprintf('%d/%d %s', $fixedStart, $fixedStart + 1, $semesterName);
    }
}
