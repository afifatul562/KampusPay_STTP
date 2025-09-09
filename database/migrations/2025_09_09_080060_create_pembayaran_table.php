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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('pembayaran_id');
            $table->foreignId('tagihan_id')->references('tagihan_id')->on('tagihan');
            $table->foreignId('konfirmasi_id')->references('konfirmasi_id')->on('konfirmasi_pembayaran');
            $table->foreignId('diverifikasi_oleh')->references('id')->on('users');
            $table->date('tanggal_bayar');
            $table->string('metode_pembayaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
