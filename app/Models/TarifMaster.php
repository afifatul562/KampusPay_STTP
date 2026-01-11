<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TarifMaster extends Model
{
    use HasFactory;

    protected $table = 'tarif_master';
    protected $primaryKey = 'tarif_id';
    protected $fillable = [
        'nama_pembayaran',
        'nominal',
        'program_studi',
        'angkatan'
    ];

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'tarif_id', 'tarif_id');
    }

    /**
     * Ambil daftar tarif ter-cache (opsional digunakan oleh controller/view)
     *
     * @param int $ttlSeconds
     * @return \Illuminate\Support\Collection
     */
    public static function getCachedAll(int $ttlSeconds = 600)
    {
        $ttl = (int) env('TARIF_CACHE_TTL', $ttlSeconds);
        return Cache::remember('tarif_master:all', $ttl, fn () => static::query()->orderBy('nama_pembayaran')->get());
    }
}
