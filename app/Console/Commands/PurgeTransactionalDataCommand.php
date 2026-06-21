<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeTransactionalDataCommand extends Command
{
    protected $signature = 'db:purge-transactional
                            {--force : Wajib di production; tanpa ini hanya dry-run}';

    protected $description = 'Hapus data transaksi. Pertahankan mahasiswa, admin, kasir, tarif, settings, academic_calendar.';

    /** @var list<string> */
    private array $tablesToClear = [
        'pembayaran',
        'konfirmasi_pembayaran',
        'wisuda_registrations',
        'tagihan_slip_aliases',
        'tagihan',
        'aktivasi_statuses',
        'notifications',
        'reports',
        'device_fcm_tokens',
        'sessions',
        'personal_access_tokens',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if (! $force) {
            $this->warn('Mode dry-run. Tidak ada data yang dihapus.');
            $this->line('Jalankan dengan --force untuk benar-benar menghapus.');
        }

        $this->info('Data yang DIPERTAHANKAN: mahasiswa_detail, mahasiswa_program, users (mahasiswa/admin/kasir), tarif_master, settings, academic_calendar');
        $this->newLine();

        if ($force && ! $this->confirm('Yakin hapus semua data transaksi? Tidak bisa di-undo.', false)) {
            $this->comment('Dibatalkan.');

            return self::SUCCESS;
        }

        $driver = DB::connection()->getDriverName();

        if ($force) {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }
        }

        foreach ($this->tablesToClear as $table) {
            if (! Schema::hasTable($table)) {
                $this->line("  skip {$table} (tabel tidak ada)");

                continue;
            }

            $count = DB::table($table)->count();

            if ($force) {
                DB::table($table)->delete();
            }

            $this->line(sprintf('  %s %s (%d baris)', $force ? 'hapus' : 'akan hapus', $table, $count));
        }

        $rolesToKeep = ['mahasiswa', 'admin', 'kasir'];
        $usersQuery = DB::table('users')->whereNotIn('role', $rolesToKeep);
        $userCount = (clone $usersQuery)->count();

        if ($force && $userCount > 0) {
            $usersQuery->delete();
        }

        $this->line(sprintf('  %s users selain mahasiswa/admin/kasir (%d baris)', $force ? 'hapus' : 'akan hapus', $userCount));

        if ($force) {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        $this->newLine();
        $this->info($force ? 'Selesai. Data transaksi telah dihapus.' : 'Dry-run selesai. Tambahkan --force untuk eksekusi.');

        return self::SUCCESS;
    }
}
