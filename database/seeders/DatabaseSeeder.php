<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seeder database aplikasi.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            AdminUserSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
