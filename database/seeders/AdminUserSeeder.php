<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Menjalankan seeder database.
     */
    public function run(): void
    {
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'password123');

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );
    }
}
