<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Support\KampusTarifCatalog;
use App\Support\TarifBillingRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncKampusTarifCommand extends Command
{
    protected $signature = 'tarif:sync-kampus
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}
                            {--force-nominal : Perbarui nominal tarif yang sudah ada ke referensi kampus}';

    protected $description = 'Sinkronkan tarif_master dengan data tarif resmi kampus STTP';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $forceNominal = (bool) $this->option('force-nominal');

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang diubah.');
        }

        $stats = [
            'upserted' => 0,
            'migrated_tagihan' => 0,
            'fixed_amounts' => 0,
            'removed' => 0,
        ];

        $run = function () use ($forceNominal, &$stats) {
            $officialUas = $this->upsertCatalog($forceNominal, $stats);
            $this->migrateLegacyUasTarifs($officialUas, $stats);
            $this->migratePpmPkkmbAliases($stats);
            $this->consolidateMisscopedTarifs($stats);
            $this->removeOrphanLegacyTarifs($stats);
        };

        if ($dryRun) {
            DB::beginTransaction();
            try {
                $run();
            } finally {
                DB::rollBack();
            }
        } else {
            DB::transaction($run);
            Cache::forget('tarif_master:all');
        }

        $this->table(
            ['Aksi', 'Jumlah'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all()
        );

        if (! $dryRun) {
            $this->info('Sinkronisasi tarif kampus selesai.');
        }

        return self::SUCCESS;
    }

    protected function upsertCatalog(bool $forceNominal, array &$stats): TarifMaster
    {
        $officialUas = null;

        foreach (KampusTarifCatalog::definitions() as $def) {
            $query = TarifMaster::query()
                ->where('nama_pembayaran', $def['nama_pembayaran']);

            if ($def['program_studi'] === null) {
                $query->where(function ($q) {
                    $q->whereNull('program_studi')->orWhere('program_studi', '');
                });
            } else {
                $query->where('program_studi', $def['program_studi']);
            }

            if ($def['angkatan'] === null) {
                $query->where(function ($q) {
                    $q->whereNull('angkatan')->orWhere('angkatan', '');
                });
            } else {
                $query->where('angkatan', $def['angkatan']);
            }

            /** @var TarifMaster|null $existing */
            $existing = $query->first();

            $payload = [
                'nama_pembayaran' => $def['nama_pembayaran'],
                'nominal' => $def['nominal'],
                'program_studi' => $def['program_studi'],
                'angkatan' => $def['angkatan'],
                'rule_json' => $def['rule_json'],
            ];

            if ($existing) {
                $update = ['rule_json' => $def['rule_json']];
                if ($forceNominal || (int) $existing->nominal <= 0) {
                    $update['nominal'] = $def['nominal'];
                }
                $existing->fill($update);
                $existing->save();
                $tarif = $existing->fresh();
                $this->line("  ✓ Perbarui rule: {$def['nama_pembayaran']}" . $this->scopeLabel($def));
            } else {
                $tarif = TarifMaster::create($payload);
                $this->line("  + Buat: {$def['nama_pembayaran']}" . $this->scopeLabel($def));
            }

            if ($def['nama_pembayaran'] === KampusTarifCatalog::NAMA_UAS) {
                $officialUas = $tarif;
            }

            $stats['upserted']++;
        }

        if (! $officialUas) {
            $officialUas = TarifMaster::query()
                ->where('nama_pembayaran', KampusTarifCatalog::NAMA_UAS)
                ->where(function ($q) {
                    $q->whereNull('program_studi')->orWhere('program_studi', '');
                })
                ->where(function ($q) {
                    $q->whereNull('angkatan')->orWhere('angkatan', '');
                })
                ->firstOrFail();
        }

        return $officialUas;
    }

    /**
     * @param array{nama_pembayaran: string, program_studi: ?string, angkatan: ?string} $def
     */
    protected function scopeLabel(array $def): string
    {
        $parts = array_filter([
            $def['program_studi'] ?? null,
            $def['angkatan'] ? 'angk. '.$def['angkatan'] : null,
        ]);

        return $parts === [] ? '' : ' (' . implode(', ', $parts) . ')';
    }

    protected function migrateLegacyUasTarifs(TarifMaster $officialUas, array &$stats): void
    {
        $legacyNames = KampusTarifCatalog::legacyTarifNames();

        $legacyTarifs = TarifMaster::query()
            ->whereIn('nama_pembayaran', $legacyNames, 'and', false)
            ->where('tarif_id', '!=', $officialUas->tarif_id)
            ->get();

        foreach ($legacyTarifs as $legacy) {
            $count = Tagihan::query()->where('tarif_id', $legacy->tarif_id)->count('tagihan_id');

            if ($count > 0) {
                Tagihan::query()
                    ->where('tarif_id', $legacy->tarif_id)
                    ->update(['tarif_id' => $officialUas->tarif_id]);

                $stats['migrated_tagihan'] += $count;
                $this->line("  → Pindahkan {$count} tagihan dari \"{$legacy->nama_pembayaran}\" ke \"" . KampusTarifCatalog::NAMA_UAS . '"');
            }

            $fixed = Tagihan::query()
                ->where('tarif_id', $officialUas->tarif_id)
                ->where('jumlah_tagihan', '>', 500_000)
                ->whereIn('status', ['Belum Lunas', 'Ditolak'], 'and', false)
                ->where('total_angsuran', 0)
                ->update([
                    'jumlah_tagihan' => $officialUas->nominal,
                    'sisa_pokok' => null,
                ]);

            $stats['fixed_amounts'] += $fixed;

            if (Tagihan::query()->where('tarif_id', $legacy->tarif_id)->count('tagihan_id') === 0) {
                TarifMaster::query()->whereKey($legacy->tarif_id)->delete();
                $stats['removed']++;
                $this->line("  ✗ Hapus tarif legacy: {$legacy->nama_pembayaran} (Rp " . number_format((int) $legacy->nominal, 0, ',', '.') . ')');
            } else {
                $this->warn("  ! Tarif legacy \"{$legacy->nama_pembayaran}\" masih dipakai, tidak dihapus.");
            }
        }

        // Perbaiki tagihan UAS resmi yang masih nominal uji coba
        $fixedOfficial = Tagihan::query()
            ->where('tarif_id', $officialUas->tarif_id)
            ->where('jumlah_tagihan', '>', 500_000)
            ->whereIn('status', ['Belum Lunas', 'Ditolak'], 'and', false)
            ->where('total_angsuran', 0)
            ->update([
                'jumlah_tagihan' => $officialUas->nominal,
                'sisa_pokok' => null,
            ]);

        $stats['fixed_amounts'] += $fixedOfficial;
    }

    /**
     * Gabungkan varian nama PKKMB/PPMB dari import Access ke tarif kanonik semester 1.
     */
    protected function migratePpmPkkmbAliases(array &$stats): void
    {
        $canonical = TarifMaster::query()
            ->where('nama_pembayaran', KampusTarifCatalog::NAMA_PPMB_ALMAMATER_CB)
            ->where(function ($q) {
                $q->whereNull('program_studi')->orWhere('program_studi', '');
            })
            ->where(function ($q) {
                $q->whereNull('angkatan')->orWhere('angkatan', '');
            })
            ->first();

        if (! $canonical) {
            return;
        }

        $aliases = TarifMaster::query()
            ->where('tarif_id', '!=', $canonical->tarif_id)
            ->get()
            ->filter(fn (TarifMaster $t) => KampusTarifCatalog::isPpmPkkmbTarifName(
                mb_strtolower((string) $t->nama_pembayaran)
            ));

        foreach ($aliases as $alias) {
            $count = Tagihan::query()->where('tarif_id', $alias->tarif_id)->count('tagihan_id');
            if ($count > 0) {
                Tagihan::query()
                    ->where('tarif_id', $alias->tarif_id)
                    ->update(['tarif_id' => $canonical->tarif_id]);
                $stats['migrated_tagihan'] += $count;
                $this->line("  → Pindahkan {$count} tagihan dari \"{$alias->nama_pembayaran}\" ke \"" . KampusTarifCatalog::NAMA_PPMB_ALMAMATER_CB . '"');
            }

            TarifMaster::query()->whereKey($alias->tarif_id)->delete();
            $stats['removed']++;
            $this->line("  ✗ Hapus alias PPMB/PKKMB: {$alias->nama_pembayaran}");
        }
    }

    /**
     * Gabungkan tarif duplikat yang seharusnya global (tanpa angkatan/prodi spesifik).
     */
    protected function consolidateMisscopedTarifs(array &$stats): void
    {
        $globalNames = [
            'Uang Kemahasiswaan',
            KampusTarifCatalog::NAMA_UAS,
            'Uang Pembangunan',
            KampusTarifCatalog::NAMA_PPMB_ALMAMATER_CB,
            'Uang KP',
            'Uang Skripsi',
            'Uang Wisuda',
        ];

        foreach ($globalNames as $nama) {
            $canonical = TarifMaster::query()
                ->where('nama_pembayaran', $nama)
                ->where(function ($q) {
                    $q->whereNull('program_studi')->orWhere('program_studi', '');
                })
                ->where(function ($q) {
                    $q->whereNull('angkatan')->orWhere('angkatan', '');
                })
                ->first();

            if (!$canonical) {
                continue;
            }

            $duplicates = TarifMaster::query()
                ->where('nama_pembayaran', $nama)
                ->where('tarif_id', '!=', $canonical->tarif_id)
                ->get();

            foreach ($duplicates as $dup) {
                $count = Tagihan::query()->where('tarif_id', $dup->tarif_id)->count('tagihan_id');
                if ($count > 0) {
                    Tagihan::query()
                        ->where('tarif_id', $dup->tarif_id)
                        ->update(['tarif_id' => $canonical->tarif_id]);
                    $stats['migrated_tagihan'] += $count;
                    $this->line("  → Pindahkan {$count} tagihan \"{$nama}\" (scope lama) ke tarif kanonik");
                }

                TarifMaster::query()->whereKey($dup->tarif_id)->delete();
                $stats['removed']++;
                $this->line("  ✗ Hapus tarif duplikat: {$nama}" . $this->scopeLabel([
                    'program_studi' => $dup->program_studi,
                    'angkatan' => $dup->angkatan,
                ]));
            }
        }
    }

    protected function removeOrphanLegacyTarifs(array &$stats): void
    {
        TarifMaster::query()
            ->whereIn('nama_pembayaran', KampusTarifCatalog::legacyTarifNames(), 'and', false)
            ->withCount('tagihan')
            ->get()
            ->each(function (TarifMaster $tarif) use (&$stats) {
                if ($tarif->tagihan_count > 0) {
                    return;
                }

                $nama = mb_strtolower($tarif->nama_pembayaran);
                if (TarifBillingRules::isLegacyUasTestName($nama) || in_array($tarif->nama_pembayaran, KampusTarifCatalog::legacyTarifNames(), true)) {
                    TarifMaster::query()->whereKey($tarif->tarif_id)->delete();
                    $stats['removed']++;
                    $this->line("  ✗ Hapus tarif tidak dipakai: {$tarif->nama_pembayaran}");
                }
            });
    }
}
