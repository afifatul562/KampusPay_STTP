<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_master', function (Blueprint $table) {
            if (Schema::hasColumn('tarif_master', 'default_due_in_days')) {
                $table->dropColumn('default_due_in_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tarif_master', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_due_in_days')->nullable()->after('nominal');
        });
    }
};
