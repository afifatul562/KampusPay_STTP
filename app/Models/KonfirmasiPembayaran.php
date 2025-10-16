<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonfirmasiPembayaran extends Model
{
    use HasFactory;
    protected $table = 'konfirmasi_pembayaran';

    protected $primaryKey = 'konfirmasi_id';
    protected $fillable = [
        'tagihan_id',
        'file_bukti_pembayaran',
        'status_verifikasi'
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'konfirmasi_id');
    }
}
