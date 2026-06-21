<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wisuda_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')
                ->references('mahasiswa_id')
                ->on('mahasiswa_detail')
                ->cascadeOnDelete();
            $table->foreignId('tagihan_id')
                ->nullable()
                ->references('tagihan_id')
                ->on('tagihan')
                ->nullOnDelete();
            $table->boolean('confirmed_skripsi')->default(false);
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->unique('mahasiswa_id', 'uniq_wisuda_reg_per_mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wisuda_registrations');
    }
};
