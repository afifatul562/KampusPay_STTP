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
        // Update pembayaran yang jumlah_bayar-nya null atau 0
        // dengan nilai dari tagihan.jumlah_tagihan
        DB::statement('
            UPDATE pembayaran p
            INNER JOIN tagihan t ON p.tagihan_id = t.tagihan_id
            SET p.jumlah_bayar = t.jumlah_tagihan
            WHERE p.jumlah_bayar IS NULL OR p.jumlah_bayar = 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback, karena ini hanya update data
    }
};
