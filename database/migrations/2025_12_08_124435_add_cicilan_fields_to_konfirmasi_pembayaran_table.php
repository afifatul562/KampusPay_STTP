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
            $table->boolean('is_cicilan')->default(false)->after('status_verifikasi');
            $table->bigInteger('jumlah_bayar')->nullable()->after('is_cicilan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfirmasi_pembayaran', function (Blueprint $table) {
            $table->dropColumn(['is_cicilan', 'jumlah_bayar']);
        });
    }
};
