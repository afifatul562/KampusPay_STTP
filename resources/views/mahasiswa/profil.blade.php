@extends('layouts.app')

@section('title', 'Profil Mahasiswa')
@section('page-title', 'Profil Mahasiswa')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: Informasi Pribadi --}}
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-lg text-center">
            {{-- Foto Profil & Nama --}}
            <div class="mb-4">
                <div class="w-24 h-24 rounded-full mx-auto bg-gray-200 flex items-center justify-center mb-2">
                    {{-- Placeholder untuk inisial nama --}}
                    <span class="text-3xl font-bold text-gray-500">{{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}</span>
                    {{-- Anda bisa menggantinya dengan <img src="..." > jika ada foto profil --}}
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $user->nama_lengkap ?? 'N/A' }}</h2>
                <p class="text-sm text-gray-500">{{ $detail->npm ?? 'N/A' }}</p>
            </div>

            {{-- Detail Akademik --}}
            <div class="text-left space-y-3 text-sm border-t pt-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Program Studi</span>
                    <span class="font-semibold text-gray-800 text-right">{{ $detail->program_studi ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Angkatan</span>
                    <span class="font-semibold text-gray-800">{{ $detail->angkatan ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Semester Aktif</span>
                    <span class="font-semibold text-gray-800">{{ $detail->semester_aktif ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span class="font-semibold text-gray-800">{{ $user->email ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan Keuangan --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                Ringkasan Keuangan
            </h3>
            <div class="space-y-4">
                {{-- Total Terbayar --}}
                <div class="bg-green-50 p-4 rounded-lg flex justify-between items-center">
                    <span class="font-medium text-green-800">Total Terbayar</span>
                    <span class="font-bold text-lg text-green-800">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                </div>
                {{-- Total Tunggakan --}}
                <div class="bg-red-50 p-4 rounded-lg flex justify-between items-center">
                    <span class="font-medium text-red-800">Total Tunggakan</span>
                    <span class="font-bold text-lg text-red-800">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</span>
                </div>

                <div class="border-t pt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pembayaran Selesai</span>
                        <span class="font-semibold text-gray-800">{{ $pembayaranSelesai }} Transaksi</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pembayaran Tertunda</span>
                        <span class="font-semibold text-gray-800">{{ $jumlahTunggakan }} Tagihan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
