<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_master', function (Blueprint $table) {
            $table->string('billing_code')->nullable()->after('angkatan');
            $table->json('rule_json')->nullable()->after('billing_code');

            $table->unique('billing_code');
        });
    }

    public function down(): void
    {
        Schema::table('tarif_master', function (Blueprint $table) {
            $table->dropUnique(['billing_code']);
            $table->dropColumn(['billing_code', 'rule_json']);
        });
    }
};

