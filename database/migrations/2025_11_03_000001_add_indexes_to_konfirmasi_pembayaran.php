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
            $table->dropIndex('konfirmasi_status_index');
            $table->dropIndex('konfirmasi_tagihan_id_index');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($connection->getTablePrefix() . $table);
        return array_key_exists($indexName, $indexes);
    }
};


