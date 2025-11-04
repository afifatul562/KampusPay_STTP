@extends('layouts.app')

@section('title', 'Pilih Metode Pembayaran')
@section('page-title', 'Pilih Metode Pembayaran')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Card utama --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">Pilih Metode untuk Tagihan</h2>
            </div>

            {{-- Info Tagihan --}}
            <div class="p-6 space-y-4 border-b">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Tagihan</span>
                    <span class="font-semibold text-gray-900 text-right">{{ $tagihan->tarif->nama_pembayaran }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Jumlah</span>
                    <span class="text-2xl font-bold text-blue-600">Rp. {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Jatuh Tempo</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}</span>
                </div>
            </div>

            {{-- Form Pilihan Metode --}}
            {{-- Form ini akan mengirim ke route 'proses-metode' yang baru kita buat --}}
            <form action="{{ route('mahasiswa.pembayaran.proses-metode', $tagihan->tagihan_id) }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">Silakan pilih salah satu metode pembayaran yang Anda inginkan:</p>

                    {{--
                        PENTING:
                        Kedua tombol ini adalah 'submit', tapi punya 'name' dan 'value'
                        name="metode" value="transfer"
                        name="metode" value="tunai"
                        Controller akan membaca 'value' ini.
                    --}}

                    {{-- Pilihan 1: Transfer --}}
                    <button type="submit" name="metode" value="transfer"
                            class="group w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="text-left">
                            <p class="font-semibold text-gray-800 group-hover:text-primary-700">Bayar via Transfer Bank</p>
                            <p class="text-sm text-gray-500">Upload bukti transfer untuk diverifikasi oleh kasir.</p>
                        </div>
                        <div class="bg-gradient-to-br from-primary-100 to-primary-200 p-3 rounded-lg group-hover:from-primary-200 group-hover:to-primary-300 transition-all duration-200">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </button>

                    {{-- Pilihan 2: Tunai --}}
                    <button type="submit" name="metode" value="tunai"
                            class="group w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-success-500 hover:bg-success-50 focus:outline-none focus:ring-2 focus:ring-success-500 transition-all duration-200 shadow-sm hover:shadow-md">
                        <div class="text-left">
                            <p class="font-semibold text-gray-800 group-hover:text-success-700">Bayar Tunai di Kasir</p>
                            <p class="text-sm text-gray-500">Pilih ini dan datang ke kasir untuk pembayaran tunai.</p>
                        </div>
                        <div class="bg-gradient-to-br from-success-100 to-success-200 p-3 rounded-lg group-hover:from-success-200 group-hover:to-success-300 transition-all duration-200">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tombol Kembali --}}
        <div class="mt-6 text-center">
            <a href="{{ route('mahasiswa.pembayaran.index') }}" class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-800 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Tagihan
            </a>
        </div>
    </div>
@endsection
