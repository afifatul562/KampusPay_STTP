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
        // Tambah kolom untuk cicilan di tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->bigInteger('jumlah_bayar')->nullable()->after('metode_pembayaran'); // Nominal angsuran
            $table->boolean('is_cicilan')->default(false)->after('jumlah_bayar'); // Flag apakah ini cicilan atau lunas
        });

        // Tambah kolom untuk tracking cicilan di tabel tagihan
        Schema::table('tagihan', function (Blueprint $table) {
            $table->bigInteger('total_angsuran')->default(0)->after('jumlah_tagihan'); // Total angsuran yang sudah dibayar
            $table->bigInteger('sisa_pokok')->nullable()->after('total_angsuran'); // Sisa pokok yang belum dibayar
        });

        // Update sisa_pokok untuk data yang sudah ada (sisa_pokok = jumlah_tagihan - total_angsuran)
        // Untuk tagihan yang belum ada pembayaran, sisa_pokok = jumlah_tagihan
        DB::statement('UPDATE tagihan SET sisa_pokok = jumlah_tagihan WHERE sisa_pokok IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['jumlah_bayar', 'is_cicilan']);
        });

        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropColumn(['total_angsuran', 'sisa_pokok']);
        });
    }
};
