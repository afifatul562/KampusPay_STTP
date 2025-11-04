<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    // Izinkan kolom 'key' dan 'value' untuk diisi secara massal
    protected $fillable = ['key', 'value'];

    /**
     * Ambil semua settings dalam bentuk key=>value dan cache hasilnya.
     * Tidak mengubah API yang ada; dipakai opsional oleh controller.
     *
     * @param int $ttlSeconds
     * @return array<string, mixed>
     */
    public static function getCachedMap(int $ttlSeconds = 600): array
    {
        $ttl = (int) env('SETTINGS_CACHE_TTL', $ttlSeconds);
        return Cache::remember('settings:key_value_map', $ttl, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }
}