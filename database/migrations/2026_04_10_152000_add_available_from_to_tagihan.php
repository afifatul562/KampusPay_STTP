<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->date('available_from')->nullable()->after('tanggal_jatuh_tempo');
            $table->index('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropIndex(['available_from']);
            $table->dropColumn('available_from');
        });
    }
};

