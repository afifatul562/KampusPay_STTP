<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KonfirmasiPembayaran;

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

    public function konfirmasi()
    {
        // Gunakan hasOne(...)->latestOfMany()
        // Ini akan otomatis mengambil HANYA 1 data konfirmasi TERBARU
        // yang terkait dengan tagihan ini (misal: konfirmasi Ditolak yang terakhir).
        return $this->hasOne(KonfirmasiPembayaran::class, 'tagihan_id', 'tagihan_id')
        ->latestOfMany('konfirmasi_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }
}
