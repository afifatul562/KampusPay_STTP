<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\MahasiswaDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_lengkap',
        'username',
        'email',
        'password',
        'role',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function mahasiswaDetail()
    {
        return $this->hasOne(MahasiswaDetail::class);
    }

    /**
     * Mengambil atribut name (alias untuk nama_lengkap)
     */
    public function getNameAttribute()
    {
        return $this->nama_lengkap;
    }

    /**
     * Mengatur atribut name (alias untuk nama_lengkap)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['nama_lengkap'] = $value;
    }

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
