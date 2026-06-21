<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KonfirmasiPembayaran;
use App\Support\TarifBillingRules;

class Tagihan extends Model
{
    use HasFactory;

    /**
     * Label jenis tagihan untuk UI/API.
     * Untuk Uang Semester, kemahasiswaan, atau ujian akhir: pakai nama di tarif_master + angka semester
     * perkuliahan tagihan (semester_perkuliahan) atau fallback semester aktif mahasiswa, mis. "Uang Semester (8)".
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
        'semester_perkuliahan',
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
        $semNum = $this->semesterUntukTampilan();

        $pakaiKurungSemester = str_contains($namaLower, 'uang semester')
            || str_contains($namaLower, 'kemahasiswaan')
            || str_contains($namaLower, 'ujian akhir');

        if ($pakaiKurungSemester && $semNum !== null) {
            return $nama . ' (' . $semNum . ')';
        }

        return $nama;
    }

    /**
     * Angka semester untuk label UI: prioritas kolom tagihan, lalu semester aktif mahasiswa.
     */
    protected function semesterUntukTampilan(): ?int
    {
        if ($this->semester_perkuliahan !== null && (int) $this->semester_perkuliahan > 0) {
            return (int) $this->semester_perkuliahan;
        }

        $m = $this->mahasiswa;
        if (! $m || $m->semester_aktif === null || $m->semester_aktif === '') {
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
     * Minimal nominal cicilan (50% jumlah tagihan per item, atau sisa pokok jika sudah di bawah setengah).
     */
    public function minimalCicilan(): float
    {
        $sisa = $this->nominalTunggakan();
        if ($sisa <= 0) {
            return 0.0;
        }

        $half = ceil((float) $this->jumlah_tagihan / 2);
        if ($sisa <= $half) {
            return $sisa;
        }

        return $half;
    }

    /**
     * Validasi nominal cicilan tanpa mengekspos batas minimal ke pengguna.
     */
    public function cicilanAmountValid(float $amount): bool
    {
        $sisa = $this->nominalTunggakan();
        if ($amount <= 0 || $amount > $sisa + 0.00001) {
            return false;
        }

        return $amount >= $this->minimalCicilan() - 0.00001;
    }

    /**
     * Cek apakah tagihan ini wajib lunas (tidak boleh dicicil).
     */
    public function isWajibLunas(): bool
    {
        if ($this->is_bss) {
            return true;
        }

        $namaPembayaran = mb_strtolower(trim((string) (optional($this->tarif)->nama_pembayaran ?? '')));

        if ($namaPembayaran === '') {
            return false;
        }

        if (str_contains($namaPembayaran, 'kemahasiswaan')) {
            return true;
        }

        if (str_contains($namaPembayaran, 'bss')) {
            return true;
        }

        return TarifBillingRules::isUasTarifName($namaPembayaran);
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
     * Status yang masih dianggap tunggakan (belum lunas di kasir).
     */
    public static function statusesTunggakan(): array
    {
        return [
            'Belum Lunas',
            'Belum Dibayarkan',
            'Ditolak',
            'Menunggu Verifikasi Transfer',
            'Menunggu Pembayaran Tunai',
        ];
    }

    /**
     * Nilai status di database untuk tagihan belum lunas (termasuk data legacy).
     */
    public static function statusesBelumLunasStorage(): array
    {
        return ['Belum Lunas', 'Belum Dibayarkan'];
    }

    public function scopeMasihTunggakan(Builder $query): Builder
    {
        return $query->where('status', '!=', 'Lunas');
    }

    /**
     * Total pembayaran terverifikasi (non-dibatalkan).
     */
    public function totalTerverifikasi(): float
    {
        if ($this->relationLoaded('pembayaranAll')) {
            return (float) $this->pembayaranAll
                ->where('status_dibatalkan', false)
                ->sum('jumlah_bayar');
        }

        return (float) ($this->total_angsuran ?? 0);
    }

    /**
     * Nominal yang masih harus dibayar. Termasuk status menunggu tunai/transfer di kasir.
     */
    public function nominalTunggakan(): float
    {
        if ($this->status === 'Lunas') {
            return 0.0;
        }

        $terverifikasi = $this->totalTerverifikasi();
        $pokok = (float) ($this->jumlah_tagihan ?? 0);
        $sisaDariPembayaran = max(0.0, $pokok - $terverifikasi);

        if ($this->sisa_pokok !== null && $this->sisa_pokok !== '') {
            $sisa = max(0.0, (float) $this->sisa_pokok);
        } else {
            $sisa = $sisaDariPembayaran;
        }

        // Pilih bayar tunai/transfer: tetap tunggakan sampai kasir memverifikasi lunas.
        if (in_array($this->status, self::waitingPaymentStatuses(), true)) {
            $sisa = max($sisa, $sisaDariPembayaran);
        }

        return $sisa;
    }

    public function masihTunggakan(): bool
    {
        return $this->nominalTunggakan() > 0.00001;
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

        if ($this->status === 'Menunggu Verifikasi Transfer') {
            $this->loadMissing('konfirmasi');
            if ($this->konfirmasi && $this->konfirmasi->status_verifikasi === 'Ditinjau') {
                return 'Sedang Ditinjau Kasir';
            }
        }

        $paid = $this->totalTerverifikasi();
        if (($this->status === 'Belum Lunas' || $this->status === 'Belum Dibayarkan') && $paid <= 0) {
            return 'Belum Dibayarkan';
        }

        return (string) $this->status;
    }

    /**
     * Kunci warna badge status (selaras web mahasiswa & mobile).
     */
    public function displayStatusBadgeVariant(): string
    {
        if ($this->status === 'Lunas') {
            return 'hijau';
        }

        if ($this->status === 'Menunggu Verifikasi Transfer') {
            return 'kuning';
        }

        if ($this->status === 'Menunggu Pembayaran Tunai') {
            return 'biru';
        }

        if ($this->status === 'Ditolak') {
            return 'oranye';
        }

        $paid = $this->totalTerverifikasi();
        if ($this->status === 'Belum Lunas' && $paid > 0) {
            return 'amber';
        }

        return 'merah';
    }

    /**
     * Kelas Tailwind badge status (web mahasiswa).
     */
    public function displayStatusBadgeTailwindClasses(): string
    {
        return match ($this->displayStatusBadgeVariant()) {
            'hijau' => 'bg-green-100 text-green-800',
            'kuning' => 'bg-yellow-100 text-yellow-800',
            'biru' => 'bg-blue-100 text-blue-800',
            'oranye' => 'bg-orange-100 text-orange-800',
            'amber' => 'bg-amber-100 text-amber-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Kelas Tailwind border kiri kartu tagihan (web mahasiswa).
     */
    public function displayStatusBorderTailwindClasses(): string
    {
        return match ($this->displayStatusBadgeVariant()) {
            'hijau' => 'border-l-4 border-green-500',
            'kuning' => 'border-l-4 border-yellow-500',
            'biru' => 'border-l-4 border-blue-500',
            'oranye' => 'border-l-4 border-orange-500',
            default => 'border-l-4 border-red-500',
        };
    }

    /**
     * Warna badge untuk API/mobile (hex selaras Tailwind web).
     *
     * @return array{status_warna: string, status_warna_bg: string, status_warna_teks: string}
     */
    public function displayStatusBadgeColors(): array
    {
        return match ($this->displayStatusBadgeVariant()) {
            'hijau' => [
                'status_warna' => 'hijau',
                'status_warna_bg' => '#DCFCE7',
                'status_warna_teks' => '#166534',
            ],
            'kuning' => [
                'status_warna' => 'kuning',
                'status_warna_bg' => '#FEF9C3',
                'status_warna_teks' => '#854D0E',
            ],
            'biru' => [
                'status_warna' => 'biru',
                'status_warna_bg' => '#DBEAFE',
                'status_warna_teks' => '#1E40AF',
            ],
            'oranye' => [
                'status_warna' => 'oranye',
                'status_warna_bg' => '#FFEDD5',
                'status_warna_teks' => '#9A3412',
            ],
            'amber' => [
                'status_warna' => 'amber',
                'status_warna_bg' => '#FEF3C7',
                'status_warna_teks' => '#92400E',
            ],
            default => [
                'status_warna' => 'merah',
                'status_warna_bg' => '#FEE2E2',
                'status_warna_teks' => '#991B1B',
            ],
        };
    }

    /**
     * Payload status untuk API/mobile: label tampilan + kode internal DB + warna badge.
     *
     * @return array{
     *     status: string,
     *     status_kode: string,
     *     status_verifikasi: ?string,
     *     status_warna: string,
     *     status_warna_bg: string,
     *     status_warna_teks: string
     * }
     */
    public function statusForMobileApi(): array
    {
        $this->loadMissing('konfirmasi');

        return array_merge(
            [
                'status' => $this->displayStatusLabel(),
                'status_kode' => (string) $this->status,
                'status_verifikasi' => $this->konfirmasi?->status_verifikasi,
            ],
            $this->displayStatusBadgeColors(),
        );
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
        if ($this->status === 'Lunas') {
            return true;
        }

        if (in_array($this->status, self::waitingPaymentStatuses(), true)) {
            return false;
        }

        return $this->nominalTunggakan() <= 0.00001;
    }

    /**
     * Cek apakah tagihan menunggak (jatuh tempo lewat dan belum lunas)
     */
    public function isMenunggak()
    {
        return !$this->isLunas() && now()->greaterThan($this->tanggal_jatuh_tempo);
    }
}
