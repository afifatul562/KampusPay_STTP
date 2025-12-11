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
        Schema::create('aktivasi_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa_detail')->onDelete('cascade');
            $table->string('semester_label');
            $table->enum('status', ['aktif', 'bss']);
            $table->foreignId('chosen_by_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('chosen_by_role')->nullable(); // mahasiswa/kasir
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'semester_label'], 'uniq_aktivasi_per_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivasi_statuses');
    }
};

