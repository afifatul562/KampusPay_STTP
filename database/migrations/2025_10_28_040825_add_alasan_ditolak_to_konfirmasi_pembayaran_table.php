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
            $table->text('alasan_ditolak')->nullable()->after('status_verifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfirmasi_pembayaran', function (Blueprint $table) {
            //
        });
    }
};
