<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';
    protected $primaryKey = 'tagihan_id';
    protected $fillable = [
        'mahasiswa_id',
        'tarif_id',
        'kode_pembayaran',
        'jumlah_tagihan',
        'tanggal_jatuh_tempo',
        'status'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaDetail::class, 'mahasiswa_id');
    }

    public function tarif()
    {
        return $this->belongsTo(TarifMaster::class, 'tarif_id');
    }

    public function konfirmasiPembayaran()
    {
        return $this->hasMany(KonfirmasiPembayaran::class, 'tagihan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }
}
