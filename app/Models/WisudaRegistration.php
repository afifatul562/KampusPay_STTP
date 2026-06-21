<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WisudaRegistration extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'tagihan_id',
        'confirmed_skripsi',
        'registered_at',
    ];

    protected $casts = [
        'confirmed_skripsi' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(MahasiswaDetail::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id', 'tagihan_id');
    }
}
