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
        Schema::create('tarif_master', function (Blueprint $table) {
            $table->id('tarif_id');
            $table->string('nama_pembayaran');
            $table->bigInteger('nominal');
            $table->string('program_studi')->nullable();
            $table->string('angkatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_master');
    }
};
