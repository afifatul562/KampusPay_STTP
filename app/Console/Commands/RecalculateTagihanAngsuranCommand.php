<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use Illuminate\Console\Command;

class RecalculateTagihanAngsuranCommand extends Command
{
    protected $signature = 'tagihan:recalculate-angsuran
                            {--dry-run : Tampilkan jumlah tanpa menyimpan}';

    protected $description = 'Hitung ulang total_angsuran dan sisa_pokok semua tagihan dari data pembayaran';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang diubah.');
        }

        $updated = 0;
        $query = Tagihan::query()->orderBy('tagihan_id', 'asc');

        $bar = $this->output->createProgressBar((clone $query)->count('tagihan_id'));
        $bar->start();

        foreach ($query->cursor() as $tagihan) {
            if (! $dryRun) {
                $tagihan->updateAngsuran();
            }
            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("{$updated} tagihan akan dihitung ulang. Jalankan tanpa --dry-run untuk menyimpan.");
        } else {
            $this->info("{$updated} tagihan berhasil dihitung ulang.");
        }

        return self::SUCCESS;
    }
}
