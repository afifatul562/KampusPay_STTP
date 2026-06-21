<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_slip_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')
                ->references('tagihan_id')
                ->on('tagihan')
                ->cascadeOnDelete();
            $table->string('kode_slip')->unique();
            $table->timestamps();

            $table->index('tagihan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_slip_aliases');
    }
};
