<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanSlipAlias;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncImportKasirFromCsvCommand extends Command
{
    protected $signature = 'pembayaran:sync-kasir-csv
                            {file : Path ke file CSV pembayaran (sama seperti saat import)}
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}
                            {--semua : Perbarui semua baris yang cocok, bukan hanya yang masih Kasir Import}';

    protected $description = 'Perbaiki diverifikasi_oleh pembayaran import dari kolom kasir_username di CSV';

    public function handle(): int
    {
        $path = $this->resolveCsvPath((string) $this->argument('file'));
        if ($path === null) {
            $this->error('File CSV tidak ditemukan.');
            $this->line('Path yang dicoba: '.(string) $this->argument('file'));
            $this->line('Tips: upload CSV ke storage/app/import/ lalu jalankan:');
            $this->line('  php artisan pembayaran:sync-kasir-csv storage/app/import/pembayaran.csv --dry-run');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $onlyImportKasir = ! (bool) $this->option('semua');

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang diubah.');
        }

        $rows = $this->readCsvAssoc($path);
        if ($rows === []) {
            $this->error('File CSV kosong atau header tidak valid.');

            return self::FAILURE;
        }

        $importKasirIds = $this->importKasirUserIds();
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $preview = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $line = (int) ($row['__line'] ?? 0);
                $kode = $this->normalizeKodePembayaran($row['kode_pembayaran'] ?? '');
                $kasirUsername = $this->resolveKasirUsernameFromRow($row);

                if ($kode === '') {
                    $this->line("Baris {$line}: dilewati (kode_pembayaran kosong).");
                    $skipped++;
                    continue;
                }

                if ($kasirUsername === '') {
                    $this->line("Baris {$line}: dilewati (kasir_username kosong).");
                    $skipped++;
                    continue;
                }

                $tagihan = $this->resolveTagihanBySlipKode($kode);
                if (! $tagihan) {
                    $this->error("Baris {$line}: tagihan/slip {$kode} tidak ditemukan.");
                    $errors++;
                    continue;
                }

                $tanggal = $this->resolveTanggalBayar($row);
                if ($tanggal === null) {
                    $this->error("Baris {$line}: tanggal_bayar tidak valid.");
                    $errors++;
                    continue;
                }

                $metode = trim((string) ($row['metode_pembayaran'] ?? 'Tunai')) ?: 'Tunai';
                $jumlahBayar = $this->parseInt($row['jumlah_bayar'] ?? '');

                $pembayaran = Pembayaran::query()
                    ->with('verifier')
                    ->where('tagihan_id', $tagihan->tagihan_id)
                    ->whereDate('tanggal_bayar', $tanggal)
                    ->where('jumlah_bayar', $jumlahBayar)
                    ->where('metode_pembayaran', $metode)
                    ->first();

                if (! $pembayaran) {
                    $this->error("Baris {$line}: pembayaran tidak ditemukan ({$kode}, {$tanggal}, {$jumlahBayar}, {$metode}).");
                    $errors++;
                    continue;
                }

                $kasir = User::query()
                    ->where('role', 'kasir')
                    ->where(function ($q) use ($kasirUsername) {
                        $q->where('username', $kasirUsername)
                            ->orWhereRaw('LOWER(username) = ?', [mb_strtolower($kasirUsername)]);
                    })
                    ->first();

                if (! $kasir) {
                    $this->error("Baris {$line}: kasir username '{$kasirUsername}' tidak ditemukan.");
                    $errors++;
                    continue;
                }

                if ($onlyImportKasir && ! in_array((int) $pembayaran->diverifikasi_oleh, $importKasirIds, true)) {
                    $verifierName = $pembayaran->verifier?->nama_lengkap
                        ?? $pembayaran->verifier?->username
                        ?? (string) $pembayaran->diverifikasi_oleh;
                    $this->line("Baris {$line}: dilewati (sudah diverifikasi {$verifierName}, bukan Kasir Import).");
                    $skipped++;
                    continue;
                }

                if ((int) $pembayaran->diverifikasi_oleh === (int) $kasir->id) {
                    $skipped++;
                    continue;
                }

                $from = $pembayaran->verifier?->nama_lengkap
                    ?? $pembayaran->verifier?->username
                    ?? (string) $pembayaran->diverifikasi_oleh;
                $to = $kasir->nama_lengkap ?? $kasir->username;

                $preview[] = [
                    $pembayaran->pembayaran_id,
                    $kode,
                    $tanggal,
                    number_format($jumlahBayar, 0, ',', '.'),
                    $from,
                    $to,
                ];

                if (! $dryRun) {
                    $pembayaran->update(['diverifikasi_oleh' => $kasir->id]);
                }

                $updated++;
            }

            if ($errors > 0) {
                DB::rollBack();
                $this->error("Gagal: {$errors} baris bermasalah. Tidak ada perubahan disimpan.");

                return self::FAILURE;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($preview !== []) {
            $this->table(
                ['ID', 'Kode', 'Tanggal', 'Jumlah', 'Kasir Lama', 'Kasir Baru'],
                $preview,
            );
        }

        $this->info(($dryRun ? 'Akan diperbarui' : 'Diperbarui').": {$updated}, dilewati: {$skipped}.");

        return self::SUCCESS;
    }

    private function resolveCsvPath(string $input): ?string
    {
        $candidates = array_unique(array_filter([
            $input,
            base_path($input),
            storage_path('app/import/'.basename($input)),
            storage_path('app/'.ltrim($input, '/')),
        ]));

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    private function importKasirUserIds(): array
    {
        $fallbackUsername = (string) config('accounts.import_kasir_username', 'kasir');

        return User::query()
            ->where('role', 'kasir')
            ->where(function ($q) use ($fallbackUsername) {
                $q->where('username', $fallbackUsername)
                    ->orWhere('nama_lengkap', 'Kasir Import');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function resolveTagihanBySlipKode(string $kode): ?Tagihan
    {
        $direct = Tagihan::query()->where('kode_pembayaran', $kode)->first();
        if ($direct) {
            return $direct;
        }

        $alias = TagihanSlipAlias::query()->where('kode_slip', $kode)->first();
        if (! $alias) {
            return null;
        }

        return Tagihan::query()->find($alias->tagihan_id);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readCsvAssoc(string $path): array
    {
        $h = fopen($path, 'r');
        if (! $h) {
            return [];
        }

        $header = fgetcsv($h);
        if (! $header) {
            fclose($h);

            return [];
        }

        $header = array_map(fn ($v) => $this->normalizeCsvHeaderKey((string) $v), $header);

        $rows = [];
        $line = 1;
        while (($row = fgetcsv($h)) !== false) {
            $line++;
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $assoc['__line'] = $line;
            $rows[] = $assoc;
        }

        fclose($h);

        return $rows;
    }

    private function normalizeCsvHeaderKey(string $raw): string
    {
        $s = mb_strtolower(trim($raw));
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = preg_replace('/\s+/', '_', $s) ?? $s;

        $aliases = [
            'nomor_slip' => 'kode_pembayaran',
            'no_slip' => 'kode_pembayaran',
            'tgl_bayar' => 'tanggal_bayar',
            'tanggal_setor' => 'tanggal_bayar',
            'username_kasir' => 'kasir_username',
            'kasir' => 'kasir_username',
            'verifikator' => 'kasir_username',
        ];

        return $aliases[$s] ?? $s;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveKasirUsernameFromRow(array $row): string
    {
        foreach (['kasir_username', 'username_kasir', 'kasir', 'verifikator'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveTanggalBayar(array $row): ?string
    {
        foreach (['tanggal_bayar', 'tanggal', 'tgl_bayar'] as $key) {
            $parsed = $this->parseDate((string) ($row[$key] ?? ''));
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }

    private function parseDate(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $v, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        $ts = strtotime($v);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function parseInt(string $value): int
    {
        $v = trim($value);
        if ($v === '') {
            return 0;
        }

        $digits = preg_replace('/[^\d-]/', '', $v);

        return (int) ($digits === '' ? 0 : $digits);
    }

    private function normalizeKodePembayaran(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (preg_match('/^[\d,.]+[eE][+\-]?\d+$/', $value)) {
            $value = sprintf('%.0f', (float) str_replace(',', '.', $value));
        }

        return $value;
    }
}
