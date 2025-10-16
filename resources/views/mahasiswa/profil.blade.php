@extends('layouts.app')

@section('title', 'Profil Mahasiswa')
@section('page-title', 'Profil Mahasiswa')

@section('content')
    {{-- 1. Panggil menu navigasi di sini --}}
    @include('layouts.partials.mahasiswa-nav')

    {{-- 2. Tampilkan notifikasi jika ada --}}
    @if (session('status') === 'password-updated')
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil</p>
            <p>Password Anda telah berhasil diperbarui.</p>
        </div>
    @endif

    {{-- 3. Baru tampilkan sisa kontennya --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kartu Informasi Pribadi --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Informasi Pribadi</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <span class="text-gray-500 col-span-1">Nama Lengkap</span>
                <span class="font-semibold col-span-2">{{ $user->nama_lengkap ?? 'N/A' }}</span>

                <span class="text-gray-500 col-span-1">NPM</span>
                <span class="font-semibold col-span-2">{{ $detail->npm ?? 'N/A' }}</span>

                <span class="text-gray-500 col-span-1">Program Studi</span>
                <span class="font-semibold col-span-2">{{ $detail->program_studi ?? 'N/A' }}</span>

                <span class="text-gray-500 col-span-1">Angkatan</span>
                <span class="font-semibold col-span-2">{{ $detail->angkatan ?? 'N/A' }}</span>

                <span class="text-gray-500 col-span-1">Semester Aktif</span>
                <span class="font-semibold col-span-2">{{ $detail->semester_aktif ?? 'N/A' }}</span>

                <span class="text-gray-500 col-span-1">Email</span>
                <span class="font-semibold col-span-2">{{ $user->email ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Kartu Ringkasan Keuangan --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Ringkasan Keuangan</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Total Terbayar</span>
                    <span class="font-bold text-lg text-green-600">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Total Tunggakan</span>
                    <span class="font-bold text-lg text-red-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</span>
                </div>
                <hr class="my-2">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Pembayaran Selesai</span>
                    <span class="font-semibold">{{ $pembayaranSelesai }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Pembayaran Tertunda</span>
                    <span class="font-semibold">{{ $jumlahTunggakan }}</span>
                </div>
            </div>
        </div>

        {{-- Kartu Ubah Password --}}
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
            <h3 class="text-xl font-semibold mb-4">Ubah Password</h3>
            <form action="{{ route('mahasiswa.profil.updatePassword') }}" method="POST" class="max-w-md">
                @csrf

                @if ($errors->updatePassword->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul>
                            @foreach ($errors->updatePassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

