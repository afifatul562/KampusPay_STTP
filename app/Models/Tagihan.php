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
        'total_angsuran',
        'sisa_pokok',
        'tanggal_jatuh_tempo',
        'semester_label',
        'is_bss',
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

    /**
     * Relasi untuk semua pembayaran (termasuk cicilan)
     */
    public function pembayaranAll()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    /**
     * Relasi untuk pembayaran cicilan saja
     */
    public function pembayaranCicilan()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id')
            ->where('is_cicilan', true)
            ->where('status_dibatalkan', false);
    }

    /**
     * Cek apakah tagihan ini wajib lunas (tidak boleh dicicil)
     */
    public function isWajibLunas()
    {
        $namaPembayaran = strtolower($this->tarif->nama_pembayaran ?? '');
        $wajibLunas = [
            'uang kemahasiswaan',
            'uang ujian akhir',
            'ujian akhir semester',
            'ujian akhir'
        ];

        foreach ($wajibLunas as $wajib) {
            if (str_contains($namaPembayaran, $wajib)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update total angsuran dan sisa pokok setelah pembayaran
     */
    public function updateAngsuran()
    {
        // Hitung total dari semua pembayaran yang tidak dibatalkan (termasuk cicilan dan lunas)
        $totalAngsuran = $this->pembayaranAll()
            ->where('status_dibatalkan', false)
            ->sum('jumlah_bayar') ?? 0;

        $this->total_angsuran = $totalAngsuran;
        $this->sisa_pokok = max(0, $this->jumlah_tagihan - $totalAngsuran);

        // Status penyesuaian otomatis
        if ($this->sisa_pokok <= 0) {
            // Lunas jika sudah tidak ada sisa
            $this->status = 'Lunas';
        } else {
            // Jika masih ada sisa, tentukan status berdasarkan total_angsuran
            // kecuali jika status 'Ditolak' (biarkan ditolak sampai ada aksi ulang)
            if ($this->status !== 'Ditolak') {
                if ($totalAngsuran > 0) {
                    // Sudah ada pembayaran tapi belum lunas
                    $this->status = 'Belum Lunas';
                } else {
                    // Belum ada pembayaran sama sekali
                    $this->status = 'Belum Dibayarkan';
                }
            }
        }

        $this->save();
    }

    /**
     * Cek apakah tagihan sudah lunas berdasarkan cicilan
     */
    public function isLunas()
    {
        return $this->sisa_pokok <= 0 || $this->status === 'Lunas';
    }

    /**
     * Cek apakah tagihan menunggak (jatuh tempo lewat dan belum lunas)
     */
    public function isMenunggak()
    {
        return !$this->isLunas() && now()->greaterThan($this->tanggal_jatuh_tempo);
    }
}
