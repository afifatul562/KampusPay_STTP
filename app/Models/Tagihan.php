<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KonfirmasiPembayaran;

class Tagihan extends Model
{
    use HasFactory;

    /**
     * Label jenis tagihan untuk UI/API.
     * Untuk Uang Semester, kemahasiswaan, atau ujian akhir: pakai nama di tarif_master + angka semester
     * perkuliahan mahasiswa dalam kurung, mis. "Uang Semester (8)", "Uang Kemahasiswaan (8)".
     * Jenis lain: hanya nama tarif (tanpa semester_label).
     */
    protected $appends = ['jenis_tampilan'];

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
        'available_from',
        'semester_label',
        'is_bss',
        'status'
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'available_from' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaDetail::class, 'mahasiswa_id');
    }

    public function getJenisTampilanAttribute(): string
    {
        $nama = trim((string) (optional($this->tarif)->nama_pembayaran ?? 'Tagihan'));
        $namaLower = strtolower($nama);
        $semNum = $this->semesterAktifMahasiswa();

        $pakaiKurungSemester = str_contains($namaLower, 'uang semester')
            || str_contains($namaLower, 'kemahasiswaan')
            || str_contains($namaLower, 'ujian akhir');

        if ($pakaiKurungSemester && $semNum !== null) {
            return $nama . ' (' . $semNum . ')';
        }

        return $nama;
    }

    /**
     * Angka semester perkuliahan mahasiswa (1, 2, …) atau null jika tidak ada.
     */
    protected function semesterAktifMahasiswa(): ?int
    {
        $m = $this->mahasiswa;
        if (!$m || $m->semester_aktif === null || $m->semester_aktif === '') {
            return null;
        }
        $n = (int) $m->semester_aktif;

        return $n > 0 ? $n : null;
    }

    public function tarif()
    {
        return $this->belongsTo(TarifMaster::class, 'tarif_id');
    }

    public function konfirmasi()
    {
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
     * Status antrian yang tidak boleh ditimpa hanya oleh hitungan angsuran (masih proses bisnis).
     */
    public static function waitingPaymentStatuses(): array
    {
        return [
            'Menunggu Verifikasi Transfer',
            'Menunggu Pembayaran Tunai',
        ];
    }

    /**
     * Label tampilan: di DB semua "belum ada/setengah bayar" disimpan sebagai Belum Lunas;
     * UI boleh bedakan "belum pernah bayar" lewat total_angsuran.
     */
    public function displayStatusLabel(): string
    {
        if ($this->status === 'Lunas') {
            return 'Lunas';
        }
        $paid = (float) ($this->total_angsuran ?? 0);
        if (($this->status === 'Belum Lunas' || $this->status === 'Belum Dibayarkan') && $paid <= 0) {
            return 'Belum Dibayarkan';
        }

        return (string) $this->status;
    }

    /**
     * Update total angsuran dan sisa pokok setelah pembayaran.
     * Satu nilai status untuk "belum lunas": Belum Lunas (sinkron admin/kasir/mahasiswa).
     */
    public function updateAngsuran(): void
    {
        $totalAngsuran = (float) ($this->pembayaranAll()
            ->where('status_dibatalkan', false)
            ->sum('jumlah_bayar') ?? 0);

        $this->total_angsuran = $totalAngsuran;
        $this->sisa_pokok = max(0, (float) $this->jumlah_tagihan - $totalAngsuran);

        $waiting = in_array($this->status, self::waitingPaymentStatuses(), true);

        if ($this->sisa_pokok <= 0) {
            $this->status = 'Lunas';
        } elseif ($totalAngsuran > 0) {
            $this->status = 'Belum Lunas';
        } elseif ($waiting) {
            // Masih menunggu verifikasi/tunai; belum ada pembayaran terverifikasi
        } elseif ($this->status === 'Ditolak') {
            // Tetap ditolak sampai mahasiswa mengunggah ulang / admin mengubah
        } else {
            $this->status = 'Belum Lunas';
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
