<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_program', function (Blueprint $table) {
            // 1:1 dengan mahasiswa_detail
            $table->unsignedBigInteger('mahasiswa_id')->primary();
            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')
                ->on('mahasiswa_detail')
                ->onDelete('cascade');

            $table->string('program_type')->default('reguler'); // kip|reguler|eksklusif
            $table->unsignedTinyInteger('kip_scheme')->nullable(); // 1|2 (khusus KIP)

            $table->boolean('is_transfer')->default(false);
            $table->unsignedTinyInteger('start_semester')->default(1);

            $table->timestamps();

            $table->index(['program_type', 'kip_scheme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_program');
    }
};

