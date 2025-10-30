@extends('layouts.app')

@section('title', 'Laporan Mahasiswa')
@section('page-title', 'Laporan Mahasiswa')

@section('content')
    {{-- Notifikasi jika ada error dari backend --}}
    @if (session('report_error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            <div><span class="font-medium">Gagal!</span> {{ session('report_error') }}</div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-lg flex flex-col">
            <h3 class="text-xl font-semibold mb-1 text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Laporan Histori Pembayaran
            </h3>
            <p class="text-sm text-gray-500 mb-4">Generate laporan lengkap riwayat pembayaran Anda dalam format PDF.</p>

            <form action="{{ route('mahasiswa.laporan.histori.download') }}" method="GET" class="flex-grow flex flex-col">
                <div class="space-y-4 flex-grow">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Histori (PDF)
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-lg flex flex-col">
            <h3 class="text-xl font-semibold mb-1 text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Laporan Data Tunggakan
            </h3>
            <p class="text-sm text-gray-500 mb-4">Unduh laporan berisi semua tunggakan pembayaran yang masih aktif.</p>

            <div class="flex-grow">
                @if($tunggakan->isEmpty())
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg text-center h-full flex flex-col justify-center items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="font-semibold">Selamat!</p>
                        <p class="text-sm">Anda tidak memiliki tunggakan aktif.</p>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 p-4 rounded-lg">
                        <p>Anda memiliki <strong>{{ $tunggakan->count() }} tunggakan</strong> dengan total <strong>Rp {{ number_format($tunggakan->sum('jumlah_tagihan'), 0, ',', '.') }}</strong>.</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                 @if($tunggakan->isEmpty())
                    <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed" disabled>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Tunggakan (PDF)
                    </button>
                 @else
                    <a href="{{ route('mahasiswa.laporan.tunggakan.download') }}" class="w-full inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 text-center no-underline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Tunggakan (PDF)
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
