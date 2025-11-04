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
        Schema::table('pembayaran', function (Blueprint $table) {
            if (! $this->hasIndex('pembayaran', 'pembayaran_konfirmasi_id_index')) {
                $table->index('konfirmasi_id', 'pembayaran_konfirmasi_id_index');
            }
            if (! $this->hasIndex('pembayaran', 'pembayaran_diverifikasi_oleh_index')) {
                $table->index('diverifikasi_oleh', 'pembayaran_diverifikasi_oleh_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex('pembayaran_konfirmasi_id_index');
            $table->dropIndex('pembayaran_diverifikasi_oleh_index');
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


