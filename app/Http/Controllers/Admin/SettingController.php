<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Pastikan Model Setting di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * ▼▼▼ METHOD BARU: Mengambil semua data pengaturan dari database ▼▼▼
     */
    public function getSystemSettings()
    {
        // Mengambil semua setting dan mengubahnya menjadi format { "key": "value" }
        // Contoh: { "app_name": "KampusPay", "academic_year": "2025/2026" }
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Menyimpan data pengaturan ke database
     */
    public function updateSystemSettings(Request $request)
    {
        $validatedData = $request->validate([
            'app_name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
        ]);

        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan sistem berhasil diperbarui!'
        ]);
    }

    public function getSystemInfo()
    {
        return response()->json([
            'php_version'     => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database'        => config('database.default'),
            'server_time'     => now()->toDateTimeString()
        ]);
    }
}