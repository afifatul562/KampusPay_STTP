<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tagihan;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'pembayaran_id';
    protected $fillable = [
        'tagihan_id',
        'konfirmasi_id',
        'diverifikasi_oleh',
        'tanggal_bayar',
        'metode_pembayaran',
        'alasan_ditolak',
        'alasan_pembatalan',
        'status_dibatalkan',
        'tanggal_pembatalan'
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function userKasir()
    {
        // Asumsi kolom 'diverifikasi_oleh' di tabel 'pembayaran'
        // berisi 'id' dari tabel 'users' (kasir).
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id');
    }

    public function konfirmasi()
    {
        return $this->belongsTo(KonfirmasiPembayaran::class, 'konfirmasi_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'tanggal_pembatalan' => 'datetime',
        'status_dibatalkan' => 'boolean',
    ];
}
