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
        // Kolom username & role sudah didefinisikan di 0001_01_01_000000_create_users_table.
    }

    public function down(): void
    {
        //
    }
};
