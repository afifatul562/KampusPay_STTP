@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
    </svg>';
    $emptyIcon = '<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-page-header
        title="Riwayat Transaksi Lunas"
        subtitle="Daftar semua pembayaran yang telah berhasil diselesaikan"
        :icon="$headerIcon">
    </x-page-header>

    @if($riwayat->count() > 0)
        <x-data-table
            :headers="['Tanggal Bayar', 'Jenis Pembayaran', 'Jumlah', 'Metode', 'Diverifikasi Oleh', 'Aksi']"
            aria-label="Tabel riwayat pembayaran">
            @foreach ($riwayat as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                        {{ optional($item->pembayaran)->created_at->isoFormat('D MMM YYYY, HH:mm') ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                        {{ $item->tarif->nama_pembayaran ?? 'N/A' }}
                        <p class="text-xs text-gray-400 mt-1 font-mono">Kode: {{ $item->kode_pembayaran }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                        Rp {{ number_format($item->jumlah_tagihan ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ optional($item->pembayaran)->metode_pembayaran ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        {{ optional(optional($item->pembayaran)->verifier)->nama_lengkap ?? 'Sistem' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if ($item->pembayaran)
                            <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}"
                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-gradient-to-r from-primary-100 to-primary-200 hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200">
                               <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Kwitansi
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if ($riwayat->hasPages())
                <x-slot:pagination>
                    {{ $riwayat->links() }}
                </x-slot:pagination>
            @endif
        </x-data-table>
    @else
        <x-empty-state
            title="Tidak ada riwayat"
            message="Belum ada riwayat pembayaran."
            :icon="$emptyIcon" />
    @endif
</div>
@endsection
