<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaDetail extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_detail';
    protected $primaryKey = 'mahasiswa_id';
    protected $fillable = [
        'user_id',
        'npm',
        'program_studi',
        'angkatan',
        'semester_aktif',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Tagihan.
     */
    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'mahasiswa_id', 'mahasiswa_id');
    }
}
