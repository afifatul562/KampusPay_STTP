<?php

namespace App\Console\Commands;

use App\Notifications\TagihanBaruCreated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTagihanBaruNotificationLinksCommand extends Command
{
    protected $signature = 'notifications:fix-tagihan-baru-links
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Perbaiki link notifikasi tagihan baru lama agar mengarah ke halaman pilih metode pembayaran';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang diubah.');
        }

        $rows = DB::table('notifications')
            ->where('type', TagihanBaruCreated::class)
            ->orderBy('created_at')
            ->get(['id', 'data']);

        if ($rows->isEmpty()) {
            $this->info('Tidak ada notifikasi TagihanBaruCreated di database.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;
        $preview = [];

        foreach ($rows as $row) {
            $data = json_decode($row->data, true);
            if (! is_array($data)) {
                $skipped++;
                continue;
            }

            $tagihanId = $data['tagihan_id'] ?? null;
            if (! is_numeric($tagihanId)) {
                $skipped++;
                continue;
            }

            $newLink = $this->buildPilihMetodeLink((int) $tagihanId);
            $oldLink = (string) ($data['link'] ?? '');

            if ($oldLink === $newLink) {
                $skipped++;

                continue;
            }

            $preview[] = [
                substr($row->id, 0, 8).'…',
                $tagihanId,
                $oldLink !== '' ? $oldLink : '(kosong)',
                $newLink,
            ];

            if (! $dryRun) {
                $data['link'] = $newLink;
                DB::table('notifications')
                    ->where('id', $row->id)
                    ->update(['data' => json_encode($data)]);
            }

            $updated++;
        }

        if ($preview !== []) {
            $this->table(['Notifikasi', 'Tagihan ID', 'Link lama', 'Link baru'], $preview);
        }

        if ($updated === 0) {
            $this->info('Semua notifikasi tagihan baru sudah memakai link yang benar.');
        } elseif ($dryRun) {
            $this->info("{$updated} notifikasi akan diperbarui. Jalankan tanpa --dry-run untuk menyimpan.");
        } else {
            $this->info("{$updated} notifikasi berhasil diperbarui.");
        }

        if ($skipped > 0) {
            $this->line("{$skipped} notifikasi dilewati (sudah benar atau data tidak lengkap).");
        }

        return self::SUCCESS;
    }

    private function buildPilihMetodeLink(int $tagihanId): string
    {
        return rtrim((string) config('app.public_url', config('app.url')), '/')
            .route('mahasiswa.pembayaran.pilih-metode', $tagihanId, false);
    }
}
