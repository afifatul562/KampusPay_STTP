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
        Schema::table('konfirmasi_pembayaran', function (Blueprint $table) {
            // Index untuk kolom yang sering difilter/di-join
            if (! $this->hasIndex('konfirmasi_pembayaran', 'konfirmasi_status_index')) {
                $table->index('status_verifikasi', 'konfirmasi_status_index');
            }
            if (! $this->hasIndex('konfirmasi_pembayaran', 'konfirmasi_tagihan_id_index')) {
                $table->index('tagihan_id', 'konfirmasi_tagihan_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfirmasi_pembayaran', function (Blueprint $table) {
            if ($this->hasIndex('konfirmasi_pembayaran', 'konfirmasi_status_index')) {
                $table->dropIndex('konfirmasi_status_index');
            }
            if ($this->hasIndex('konfirmasi_pembayaran', 'konfirmasi_tagihan_id_index')) {
                $table->dropIndex('konfirmasi_tagihan_id_index');
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

        return false;
    }
};


