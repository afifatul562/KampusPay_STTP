<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Untuk auto-size kolom
use Carbon\Carbon;                            // Import Carbon
use Illuminate\Support\Facades\Log;            // Import Log

class TransaksiKasirExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters; // Ganti nama variabel agar lebih jelas

    /**
     * Terima array filter dari controller.
     *
     * @param array $filters Filter dari request query string
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
        Log::info('Initializing TransaksiKasirExport with filters:', $filters);
    }

    /**
    * Query data pembayaran berdasarkan filter yang diterima.
    *
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        $kasirId = Auth::id();
        Log::info('Exporting for Kasir ID: ' . $kasirId);

        // Query dasar
        $query = Pembayaran::with([
                'tagihan' => function($q) {
                    $q->with(['mahasiswa' => function($q2) {
                        $q2->with('user:id,nama_lengkap');
                    }, 'tarif:tarif_id,nama_pembayaran']);
                }
            ])
            ->where('diverifikasi_oleh', $kasirId)
            ->select('pembayaran.*') // Penting jika ada join/filter relasi
            ->latest('tanggal_bayar');

        // Terapkan filter
        Log::info('Applying filters to export query:', $this->filters);

        // Filter 'hari_ini'
        if (isset($this->filters['filter']) && $this->filters['filter'] === 'hari_ini') {
            Log::info('Export filter: hari_ini');
            $query->whereDate('pembayaran.tanggal_bayar', Carbon::today());
        }

        // Filter 'jenis_filter'
        if (!empty($this->filters['jenis_filter'])) {
            $jenis = $this->filters['jenis_filter'];
            Log::info('Export filter: jenis_filter = ' . $jenis);
            $query->whereHas('tagihan.tarif', function ($q) use ($jenis) {
                $q->where('nama_pembayaran', $jenis);
            });
        }

        // Filter 'start_date'
        if (!empty($this->filters['start_date'])) {
            try {
                $startDate = Carbon::parse($this->filters['start_date'])->startOfDay();
                Log::info('Export filter: start_date >= ' . $startDate->toDateString());
                $query->where('pembayaran.tanggal_bayar', '>=', $startDate);
            } catch (\Exception $e) {
                Log::warning('Export filter: Invalid start_date format ignored: ' . $this->filters['start_date']);
            }
        }

        // Filter 'end_date'
        if (!empty($this->filters['end_date'])) {
            try {
                $endDate = Carbon::parse($this->filters['end_date'])->endOfDay();
                Log::info('Export filter: end_date <= ' . $endDate->toDateString());
                $query->where('pembayaran.tanggal_bayar', '<=', $endDate);
            } catch (\Exception $e) {
                 Log::warning('Export filter: Invalid end_date format ignored: ' . $this->filters['end_date']);
            }
        }

        Log::info('Final export query SQL (bindings omitted): ' . $query->toSql());
        return $query;
    }

    /**
     * Definisikan header kolom Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        // Sesuaikan dengan method map()
        return [
            'Tanggal Bayar',
            'Nama Mahasiswa',
            'NPM',
            'Jenis Pembayaran',
            'Jumlah (Rp)',
            'Metode Pembayaran',
            'Kode Pembayaran Tagihan',
            'ID Pembayaran Internal'
        ];
    }

    /**
     * Format data per baris untuk Excel.
     *
     * @param Pembayaran $pembayaran
     * @return array
     */
    public function map($pembayaran): array
    {
        $tagihan = $pembayaran->tagihan; // Akses relasi

        return [
            // Gunakan format yang lebih mudah dibaca Excel atau isoFormat
            Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('DD MMM YYYY, HH:mm:ss'),
            optional(optional($tagihan->mahasiswa)->user)->nama_lengkap ?? 'N/A',
            optional($tagihan->mahasiswa)->npm ?? 'N/A',
            optional($tagihan->tarif)->nama_pembayaran ?? 'N/A',
            optional($tagihan)->jumlah_tagihan ?? 0,
            $pembayaran->metode_pembayaran,
            optional($tagihan)->kode_pembayaran ?? 'N/A',
            $pembayaran->pembayaran_id,
        ];
    }
}
