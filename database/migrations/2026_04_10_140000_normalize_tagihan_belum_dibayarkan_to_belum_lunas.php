<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Samakan status lama "Belum Dibayarkan" ke "Belum Lunas" agar filter & tampilan konsisten.
     */
    public function up(): void
    {
        DB::table('tagihan')
            ->where('status', 'Belum Dibayarkan')
            ->update(['status' => 'Belum Lunas']);
    }

    public function down(): void
    {
        // Tidak mengembalikan label lama secara aman (bisa bentrok dengan data baru).
    }
};
