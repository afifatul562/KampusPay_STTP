<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah index untuk kolom yang sering difilter pada tabel tagihan
        Schema::table('tagihan', function (Blueprint $table) {
            if (! $this->hasIndex('tagihan', 'tagihan_status_index')) {
                $table->index('status', 'tagihan_status_index');
            }
            if (! $this->hasIndex('tagihan', 'tagihan_mahasiswa_id_index')) {
                $table->index('mahasiswa_id', 'tagihan_mahasiswa_id_index');
            }
            if (! $this->hasIndex('tagihan', 'tagihan_tanggal_jatuh_tempo_index')) {
                $table->index('tanggal_jatuh_tempo', 'tagihan_tanggal_jatuh_tempo_index');
            }
        });

        // Tambah index untuk kolom yang sering difilter pada tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            if (! $this->hasIndex('pembayaran', 'pembayaran_tanggal_bayar_index')) {
                $table->index('tanggal_bayar', 'pembayaran_tanggal_bayar_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            if ($this->hasIndex('tagihan', 'tagihan_status_index')) {
                $table->dropIndex('tagihan_status_index');
            }
            if ($this->hasIndex('tagihan', 'tagihan_mahasiswa_id_index')) {
                $table->dropIndex('tagihan_mahasiswa_id_index');
            }
            if ($this->hasIndex('tagihan', 'tagihan_tanggal_jatuh_tempo_index')) {
                $table->dropIndex('tagihan_tanggal_jatuh_tempo_index');
            }
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if ($this->hasIndex('pembayaran', 'pembayaran_tanggal_bayar_index')) {
                $table->dropIndex('pembayaran_tanggal_bayar_index');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $tableName = $connection->getTablePrefix() . $table;

        if ($driver === 'sqlite') {
            $rows = $connection->select("PRAGMA index_list('$tableName')");
            foreach ($rows as $row) {
                $name = $row->name ?? $row['name'] ?? null;
                if ($name === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $indexes = $connection->select(
                'SHOW INDEX FROM `' . $tableName . '` WHERE Key_name = ?',
                [$indexName]
            );

            return ! empty($indexes);
        }

        // Best-effort fallback: let the migration attempt create/drop the index.
        return false;
    }
};


