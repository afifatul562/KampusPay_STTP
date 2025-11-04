@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
    $emptyIcon = '<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-page-header
        title="Laporan Bulanan"
        subtitle="Lihat dan ekspor laporan pembayaran bulanan"
        :icon="$headerIcon">
    </x-page-header>

    <!-- Filter Card -->
    <x-card title="Pilih Periode Laporan">
        <x-slot:header>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="{{ route('kasir.laporan.exportCsv', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}">
                    <x-gradient-button variant="success" size="md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-14a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Ekspor CSV
                    </x-gradient-button>
                </a>
                <a href="{{ route('kasir.laporan.exportPdf', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}">
                    <x-gradient-button variant="danger" size="md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Ekspor PDF
                    </x-gradient-button>
                </a>
            </div>
        </x-slot:header>
        <form action="{{ route('kasir.laporan.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-auto">
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" id="tahun" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <x-gradient-button type="submit" variant="primary" size="md" class="w-full">Tampilkan</x-gradient-button>
            </div>
        </form>
    </x-card>

    <!-- Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Total Penerimaan</div>
                <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
         <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Jumlah Transaksi</div>
                <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">{{ $jumlahTransaksi }}</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path></svg>
            </div>
        </div>
    </div>

    <!-- Tabel Rangkuman -->
    @if($laporanPerJenis->count() > 0)
        <x-data-table 
            title="Rangkuman per Jenis Pembayaran"
            :headers="['Jenis Pembayaran', 'Jumlah Transaksi', 'Total Nominal']"
            aria-label="Tabel rangkuman jenis pembayaran">
            @foreach ($laporanPerJenis as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama_pembayaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700">{{ $item->jumlah_transaksi }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800 font-medium">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @else
        <x-empty-state
            title="Tidak ada data"
            message="Tidak ada data untuk periode ini."
            :icon="$emptyIcon" />
    @endif
</div>
@endsection
