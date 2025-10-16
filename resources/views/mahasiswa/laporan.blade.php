@extends('layouts.app')

@section('title', 'Laporan Mahasiswa')
@section('page-title', 'Laporan Mahasiswa')

@section('content')
    {{-- 1. Panggil menu navigasi di sini agar posisinya benar --}}
    @include('layouts.partials.mahasiswa-nav')

    {{-- 2. Tampilkan notifikasi jika ada --}}
    @if (session('report_error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal</p>
            <p>{{ session('report_error') }}</p>
        </div>
    @endif

    {{-- 3. Baru tampilkan sisa kontennya --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Laporan Histori Pembayaran --}}
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <h3 class="text-xl font-semibold mb-1">Laporan Histori Pembayaran</h3>
            <p class="text-sm text-gray-500 mb-4">Generate laporan lengkap histori pembayaran.</p>
            <form action="{{ route('mahasiswa.laporan.histori.download') }}" method="GET" class="flex-grow flex flex-col">
                <div class="space-y-4 flex-grow">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Download PDF
                    </button>
                </div>
            </form>
        </div>

        {{-- Laporan Data Tunggakan --}}
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <h3 class="text-xl font-semibold mb-1">Laporan Data Tunggakan</h3>
            <p class="text-sm text-gray-500 mb-4">Laporan detail semua tunggakan pembayaran.</p>

            <div class="flex-grow">
                @if($tunggakan->isEmpty())
                    <div class="bg-green-100 text-green-800 p-4 rounded-md text-center h-full flex flex-col justify-center">
                        <p class="font-semibold">Selamat!</p>
                        <p>Anda tidak memiliki tunggakan aktif.</p>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 p-4 rounded-md">
                        <p>Anda memiliki <strong>{{ $tunggakan->count() }} tunggakan</strong> dengan total <strong>Rp {{ number_format($tunggakan->sum('jumlah_tagihan'), 0, ',', '.') }}</strong>.</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                 @if($tunggakan->isEmpty())
                    <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed" disabled>
                        Download PDF
                    </button>
                 @else
                    <a href="{{ route('mahasiswa.laporan.tunggakan.download') }}" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 text-center no-underline">
                        Download PDF
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

