<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'metode_pembayaran'
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function konfirmasi()
    {
        return $this->belongsTo(KonfirmasiPembayaran::class, 'konfirmasi_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
