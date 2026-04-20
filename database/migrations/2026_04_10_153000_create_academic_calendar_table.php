<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendar', function (Blueprint $table) {
            $table->id();
            $table->string('semester_label')->unique();
            $table->date('semester_start_at')->nullable();
            $table->date('uts_start_at')->nullable();
            $table->date('uas_start_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar');
    }
};

