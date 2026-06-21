<?php

namespace App\Services;

use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ReportDataService
{
    /**
     * @param  string  $jenis  'mahasiswa' atau 'pembayaran'
     * @return \Illuminate\Support\Collection|null
     */
    public function fetch(string $jenis, int $tahun, ?string $semester = null, array $filters = []): ?Collection
    {
        $tahunAkademik = isset($filters['tahun_akademik']) ? trim((string) $filters['tahun_akademik']) : '';
        $angkatan = isset($filters['angkatan']) ? trim((string) $filters['angkatan']) : '';
        $programStudi = isset($filters['program_studi']) ? trim((string) $filters['program_studi']) : '';

        if ($jenis === 'mahasiswa') {
            $angkatanDipakai = $angkatan !== '' ? $angkatan : (string) $tahun;
            $mahasiswaList = MahasiswaDetail::with('user')
                ->where('angkatan', $angkatanDipakai)
                ->when($programStudi !== '', fn ($q) => $q->where('program_studi', $programStudi))
                ->orderBy('npm', 'asc')
                ->get();

            Log::info('Mengambil data laporan mahasiswa angkatan ' . $angkatanDipakai . ($programStudi ? " prodi {$programStudi}" : '') . ': ' . $mahasiswaList->count() . ' record.');

            return $mahasiswaList;
        }

        if ($jenis === 'pembayaran') {
            $query = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaran.userKasir' => fn ($q) => $q->select('id', 'nama_lengkap'),
                'pembayaranAll' => fn ($q) => $q->where('status_dibatalkan', false),
            ]);

            if ($tahunAkademik === '') {
                $query->whereYear('created_at', $tahun);
            } else {
                $query->where('semester_label', 'LIKE', $tahunAkademik . '%');
            }

            if ($angkatan !== '' || $programStudi !== '') {
                $query->whereHas('mahasiswa', function ($mq) use ($angkatan, $programStudi) {
                    if ($angkatan !== '') {
                        $mq->where('angkatan', $angkatan);
                    }
                    if ($programStudi !== '') {
                        $mq->where('program_studi', $programStudi);
                    }
                });
            }

            if ($semester) {
                if ($tahunAkademik !== '') {
                    $query->where('semester_label', 'LIKE', "{$tahunAkademik} {$semester}%");
                } else {
                    $tahunNext = $tahun + 1;
                    $tahunPrev = $tahun - 1;
                    $query->where(function ($q) use ($semester, $tahun, $tahunNext, $tahunPrev) {
                        $q->where('semester_label', 'LIKE', "{$tahunPrev}/{$tahun} {$semester}")
                            ->orWhere('semester_label', 'LIKE', "{$tahun}/{$tahunNext} {$semester}")
                            ->orWhere('semester_label', 'LIKE', "% {$semester}");
                    });
                }
            }

            $data = $query->orderBy('created_at')->get();
            Log::info('Mengambil data laporan pembayaran tahun ' . $tahun . ($semester ? " semester {$semester}" : '') . ': ' . $data->count() . ' record.');

            return $data;
        }

        return null;
    }

    /**
     * Transaksi pembayaran non-dibatalkan dengan tanggal_bayar di tahun kalender.
     */
    public function fetchPembayaranKalender(int $year, array $filters = []): Collection
    {
        $angkatan = trim((string) ($filters['angkatan'] ?? ''));
        $programStudi = trim((string) ($filters['program_studi'] ?? ''));

        $data = Pembayaran::with([
            'tagihan.mahasiswa.user',
            'tagihan.tarif',
            'userKasir:id,nama_lengkap',
        ])
            ->where('status_dibatalkan', false)
            ->whereYear('tanggal_bayar', $year)
            ->when($angkatan !== '' || $programStudi !== '', function ($q) use ($angkatan, $programStudi) {
                $q->whereHas('tagihan.mahasiswa', function ($mq) use ($angkatan, $programStudi) {
                    if ($angkatan !== '') {
                        $mq->where('angkatan', $angkatan);
                    }
                    if ($programStudi !== '') {
                        $mq->where('program_studi', $programStudi);
                    }
                });
            })
            ->orderBy('tanggal_bayar')
            ->get();

        Log::info("Laporan pembayaran kalender {$year}: {$data->count()} transaksi.");

        return $data;
    }

    /**
     * Tagihan diterbitkan di tahun kalender yang masih menunggak (sisa > 0).
     */
    public function fetchTunggakanKalender(int $year, array $filters = []): Collection
    {
        $angkatan = trim((string) ($filters['angkatan'] ?? ''));
        $programStudi = trim((string) ($filters['program_studi'] ?? ''));

        $data = Tagihan::with([
            'mahasiswa.user',
            'tarif',
            'pembayaranAll' => fn ($q) => $q->where('status_dibatalkan', false),
        ])
            ->whereYear('created_at', $year)
            ->when($angkatan !== '' || $programStudi !== '', function ($q) use ($angkatan, $programStudi) {
                $q->whereHas('mahasiswa', function ($mq) use ($angkatan, $programStudi) {
                    if ($angkatan !== '') {
                        $mq->where('angkatan', $angkatan);
                    }
                    if ($programStudi !== '') {
                        $mq->where('program_studi', $programStudi);
                    }
                });
            })
            ->whereIn('status', Tagihan::statusesBelumLunasStorage(), 'and', false)
            ->orderBy('created_at')
            ->get();

        foreach ($data as $tagihan) {
            $tagihan->updateAngsuran();
        }

        Log::info("Laporan tunggakan kalender {$year}: {$data->count()} tagihan.");

        return $data->load('mahasiswa.user', 'tarif', 'pembayaranAll');
    }

    /**
     * Tagihan belum lunas per tahun akademik (logika laporan Yayasan/Admin yang sudah ada).
     */
    public function fetchTunggakanAkademik(int $tahun, array $filters = [], ?string $semester = null): Collection
    {
        $tahunAkademik = trim((string) ($filters['tahun_akademik'] ?? ''));
        $angkatan = trim((string) ($filters['angkatan'] ?? ''));
        $programStudi = trim((string) ($filters['program_studi'] ?? ''));

        $data = Tagihan::with([
            'mahasiswa.user',
            'tarif',
            'pembayaranAll' => fn ($q) => $q->where('status_dibatalkan', false),
        ])
            ->when($tahunAkademik === '', fn ($q) => $q->whereYear('created_at', $tahun))
            ->when($tahunAkademik !== '', fn ($q) => $q->where('semester_label', 'LIKE', $tahunAkademik . '%'))
            ->when($semester && $tahunAkademik !== '', fn ($q) => $q->where('semester_label', 'LIKE', "{$tahunAkademik} {$semester}%"))
            ->when($angkatan !== '' || $programStudi !== '', function ($q) use ($angkatan, $programStudi) {
                $q->whereHas('mahasiswa', function ($mq) use ($angkatan, $programStudi) {
                    if ($angkatan !== '') {
                        $mq->where('angkatan', $angkatan);
                    }
                    if ($programStudi !== '') {
                        $mq->where('program_studi', $programStudi);
                    }
                });
            })
            ->whereIn('status', Tagihan::statusesBelumLunasStorage(), 'and', false)
            ->orderBy('created_at')
            ->get();

        foreach ($data as $tagihan) {
            $tagihan->updateAngsuran();
        }

        return $data->load('mahasiswa.user', 'tarif', 'pembayaranAll');
    }

    /**
     * @return array{jumlah_tagihan: int, total_sisa: float, belum_dibayar: int, belum_lunas: int}
     */
    public function summarizeTunggakan(Collection $data): array
    {
        $belumDibayar = 0;
        $belumLunas = 0;
        $totalSisa = 0.0;

        foreach ($data as $tagihan) {
            $sisa = (float) ($tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan ?? 0);
            $totalSisa += $sisa;
            $paid = (float) ($tagihan->total_angsuran ?? 0);
            if ($paid > 0) {
                $belumLunas++;
            } else {
                $belumDibayar++;
            }
        }

        return [
            'jumlah_tagihan' => $data->count(),
            'total_sisa' => $totalSisa,
            'belum_dibayar' => $belumDibayar,
            'belum_lunas' => $belumLunas,
        ];
    }

    /**
     * @return array{jumlah_transaksi: int, total_uang_masuk: float}
     */
    public function summarizePembayaranKalender(Collection $data): array
    {
        return [
            'jumlah_transaksi' => $data->count(),
            'total_uang_masuk' => (float) $data->sum('jumlah_bayar'),
        ];
    }

    public static function calendarYearLabel(int $year): string
    {
        return "Tahun Kalender {$year} (1 Januari – 31 Desember {$year})";
    }
}
