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
            $table->text('alasan_pembatalan')->nullable()->after('alasan_ditolak');
            $table->boolean('status_dibatalkan')->default(false)->after('alasan_pembatalan');
            $table->timestamp('tanggal_pembatalan')->nullable()->after('status_dibatalkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['alasan_pembatalan', 'status_dibatalkan', 'tanggal_pembatalan']);
        });
    }
};
