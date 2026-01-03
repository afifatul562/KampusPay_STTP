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
    private const DEFAULT_APP_NAME = 'KampusPay';
    /**
     * Mengambil semua data pengaturan dari database
     */
    public function getSystemSettings()
    {
        try {
            // Ambil settings menggunakan cache helper untuk efisiensi
            $settings = Setting::getCachedMap();

            $settings['app_name'] = self::DEFAULT_APP_NAME;

            // Tambahkan computed defaults jika belum ada: academic_year & semester
            if (!isset($settings['academic_year']) || !isset($settings['semester'])) {
                [$computedYear, $computedSemester] = $this->computeAcademicYearAndSemester();
                $settings['academic_year'] = $settings['academic_year'] ?? $computedYear;
                $settings['semester'] = $settings['semester'] ?? $computedSemester;
            }
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
                // academic_year & semester dihitung otomatis
                'academic_year' => 'nullable|string|max:255',
                'semester' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:255',
                'account_holder' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction(); // <-- MULAI TRANSAKSI
            try {
                // Hitung academic_year & semester otomatis
                [$computedYear, $computedSemester] = $this->computeAcademicYearAndSemester();
                $validatedData['academic_year'] = $computedYear;
                $validatedData['semester'] = $computedSemester;

                $settingsToPersist = array_merge(
                    ['app_name' => self::DEFAULT_APP_NAME],
                    $validatedData
                );

                foreach ($settingsToPersist as $key => $value) {
                    Setting::updateOrCreate(
                        ['key' => $key],
                        // Simpan null jika value kosong untuk data bank
                        ['value' => in_array($key, ['bank_name', 'account_holder', 'account_number']) ? ($value ?: null) : $value]
                    );
                }
                DB::commit(); // <-- COMMIT JIKA SUKSES

                // Hapus cache settings agar pembacaan berikutnya segar
                \Illuminate\Support\Facades\Cache::forget('settings:key_value_map');

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

    /**
     * Hitung Tahun Akademik dan Semester aktif otomatis.
     * Aturan kampus: Tahun ajaran aktif dimulai Oktober s/d pertengahan Juli.
     * - Semester Ganjil: Okt (10) s/d Feb (2)
     * - Semester Genap: Mar (3) s/d Jul (7)
     * Libur: Agustus-September.
     *
     * @return array{0:string,1:string} ["YYYY/YYYY+1", "Ganjil|Genap"]
     */
    private function computeAcademicYearAndSemester(): array
    {
        $now = now(); // Saat ini 1 Januari 2026
        $y = (int) $now->year;
        $m = (int) $now->month;

        // SEMESTER: Januari (1) masuk ke sini -> Ganjil
        if ($m >= 10 || $m <= 2) {
            $semester = 'Ganjil';
        } elseif ($m >= 3 && $m <= 7) {
            $semester = 'Genap';
        } else {
            $semester = 'Libur';
        }

        // TAHUN AKADEMIK: Januari (1) masuk ke else -> 2025/2026
        if ($m >= 10) {
            $startYear = $y;
            $endYear = $y + 1;
        } else {
            $startYear = $y - 1; // 2026 - 1 = 2025
            $endYear = $y;     // 2026
        }

        return [sprintf('%d/%d', $startYear, $endYear), $semester];
    }
}
