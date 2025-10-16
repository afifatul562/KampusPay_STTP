@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran')
@section('page-title', 'Konfirmasi Pembayaran')

@section('content')
    @include('layouts.partials.mahasiswa-nav')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Kolom Kiri: Instruksi & Detail --}}
        <div>
            {{-- Instruksi Pembayaran --}}
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold mb-4 border-b pb-3">1. Instruksi Pembayaran</h3>
                <p class="text-sm text-gray-600 mb-4">Silakan lakukan transfer ke rekening berikut:</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Bank</span>
                        <span class="font-semibold text-gray-800">{{ $settings['bank_name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Atas Nama</span>
                        <span class="font-semibold text-gray-800">{{ $settings['account_holder'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">No. Rekening</span>
                        <span class="font-semibold text-gray-800">{{ $settings['account_number'] ?? 'N/A' }}</span>
                    </div>
                </div>
                 <p class="text-xs text-gray-500 mt-4">Setelah melakukan pembayaran, mohon upload bukti transfer di formulir sebelah.</p>
            </div>

            {{-- Detail Tagihan --}}
            <div class="bg-white p-6 rounded-lg shadow-md">
                 <h3 class="text-lg font-semibold mb-4 border-b pb-3">Detail Tagihan</h3>
                 <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis Pembayaran</span>
                        <span class="font-semibold">{{ $tagihan->tarif->nama_pembayaran }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jumlah Tagihan</span>
                        <span class="font-bold text-2xl text-orange-500">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kode Pembayaran</span>
                        <span class="font-mono">{{ $tagihan->kode_pembayaran }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jatuh Tempo</span>
                        <span>{{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Upload --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4 border-b pb-3">2. Upload Bukti Transfer</h3>
            <form action="{{ route('mahasiswa.pembayaran.konfirmasi.store', $tagihan->tagihan_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-4">
                    <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">Pilih File (Gambar: JPG, PNG, max 2MB)</label>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100" required>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-700 transition-colors">
                        Kirim Konfirmasi
                    </button>
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" class="block text-center mt-3 text-sm text-gray-600 hover:underline">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

