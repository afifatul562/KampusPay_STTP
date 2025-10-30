<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting; // Pastikan Model Setting di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // <-- Tambahkan ini
use Illuminate\Validation\ValidationException; // <-- Tambahkan ini
use Illuminate\Database\QueryException; // <-- Tambahkan ini

class SettingController extends Controller
{
    /**
     * Mengambil semua data pengaturan dari database
     */
    public function getSystemSettings()
    {
        try {
            // Mengambil semua setting dan mengubahnya menjadi format { "key": "value" }
            $settings = Setting::all()->pluck('value', 'key');
            // Bungkus dalam 'data' agar konsisten
            return response()->json(['data' => $settings]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil system settings: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil pengaturan sistem.'], 500);
        }
    }

    /**
     * Menyimpan data pengaturan ke database (dengan transaksi)
     */
    public function updateSystemSettings(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'app_name' => 'required|string|max:255',
                'academic_year' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'account_holder' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction(); // <-- MULAI TRANSAKSI
            try {
                foreach ($validatedData as $key => $value) {
                    Setting::updateOrCreate(
                        ['key' => $key],
                        // Simpan null jika value kosong untuk data bank
                        ['value' => in_array($key, ['bank_name', 'account_holder', 'account_number']) ? ($value ?: null) : $value]
                    );
                }
                DB::commit(); // <-- COMMIT JIKA SUKSES

                Log::info('Pengaturan sistem berhasil diperbarui.');
                return response()->json([
                    'success' => true,
                    'message' => 'Pengaturan sistem berhasil diperbarui!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack(); // <-- ROLLBACK JIKA GAGAL
                Log::error('Gagal saat menyimpan pengaturan sistem: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan pengaturan sistem: ' . $e->getMessage()
                ], 500); // Internal Server Error
            }
        } catch (ValidationException $e) {
            // Tangani error validasi
            Log::warning('Validasi gagal saat update pengaturan: ', $e->errors());
             return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mengambil informasi sistem dasar
     */
    public function getSystemInfo()
    {
         try {
             $info = [
                'php_version'     => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database'        => config('database.default'),
                'server_time'     => now()->toDateTimeString() // Atau pakai format lain jika perlu
             ];
             // Bungkus dalam 'data' agar konsisten
             return response()->json(['data' => $info]);
         } catch (\Exception $e) {
            Log::error('Gagal mengambil system info: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil informasi sistem.'], 500);
        }
    }
}
