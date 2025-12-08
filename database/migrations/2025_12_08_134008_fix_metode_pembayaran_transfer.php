<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update pembayaran yang punya konfirmasi_id (via transfer)
        // tapi metode_pembayaran-nya bukan 'Transfer'
        DB::statement("
            UPDATE pembayaran
            SET metode_pembayaran = 'Transfer'
            WHERE konfirmasi_id IS NOT NULL
            AND metode_pembayaran != 'Transfer'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback, karena ini hanya update data
    }
};
