<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransaksiKasirExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function query()
    {
        $query = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif')
            ->where('diverifikasi_oleh', Auth::id())
            ->latest('tanggal_bayar');

            if (!empty($this->filter['jenis_filter'])) {
                $query->whereHas('tagihan.tarif', function ($q) {
                    $q->where('nama_pembayaran', $this->filter['jenis_filter']);
                });
            }

            return $query;

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal Bayar',
            'NPM',
            'Nama Mahasiswa',
            'Jenis Pembayaran',
            'Jumlah',
            'Metode Pembayaran',
        ];
    }

    public function map($pembayaran): array
    {
        return [
            \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('Y-m-d H:i:s'),
            $pembayaran->tagihan->mahasiswa->npm ?? 'N/A',
            $pembayaran->tagihan->mahasiswa->user->nama_lengkap ?? 'N/A',
            $pembayaran->tagihan->tarif->nama_pembayaran ?? 'N/A',
            $pembayaran->tagihan->jumlah_tagihan ?? 0,
            $pembayaran->metode_pembayaran,
        ];
    }
}