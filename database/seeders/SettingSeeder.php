<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => config('app.name', 'KampusPay')]
        );

        Cache::forget('settings:key_value_map');
    }
}

