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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id('tagihan_id');
            $table->foreignId('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa_detail')->onDelete('cascade');
            $table->foreignId('tarif_id')->references('tarif_id')->on('tarif_master');
            $table->string('kode_pembayaran')->unique();
            $table->bigInteger('jumlah_tagihan');
            $table->date('tanggal_jatuh_tempo');
            $table->string('status')->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
