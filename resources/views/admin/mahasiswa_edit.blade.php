@extends('layouts.app')

@section('title', 'Admin - Edit Mahasiswa')
@section('page-title', 'Edit Mahasiswa')

@section('content')
    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">
            Edit Data: <span class="font-bold">{{ $mahasiswa->user->nama_lengkap }}</span>
        </h3>

        {{-- Tampilkan error validasi jika ada --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{--
          Catatan: 'mahasiswa_id' mungkin adalah ID dari tabel mahasiswa_detail.
          Pastikan route 'admin.mahasiswa.update' sudah ada di api.php/web.php
        --}}
        <form action="{{ route('admin.mahasiswa.update', $mahasiswa->mahasiswa_id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Penting untuk method update --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nama Lengkap --}}
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap"
                           value="{{ old('nama_lengkap', $mahasiswa->user->nama_lengkap) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $mahasiswa->user->email) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- NPM (Read-only) --}}
                <div>
                    <label for="npm" class="block text-sm font-medium text-gray-700">NPM (Tidak bisa diubah)</label>
                    <input type="text" id="npm"
                           value="{{ $mahasiswa->npm }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                </div>

                {{-- Program Studi --}}
                <div>
                    <label for="program_studi" class="block text-sm font-medium text-gray-700">Program Studi</label>
                    <input type="text" name="program_studi" id="program_studi"
                           value="{{ old('program_studi', $mahasiswa->program_studi) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Semester Aktif --}}
                <div>
                    <label for="semester_aktif" class="block text-sm font-medium text-gray-700">Semester Aktif</label>
                    <input type="number" name="semester_aktif" id="semester_aktif"
                           value="{{ old('semester_aktif', $mahasiswa->semester_aktif) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Angkatan (Read-only) --}}
                <div>
                    <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan (Tidak bisa diubah)</label>
                    <input type="text" id="angkatan"
                           value="{{ $mahasiswa->angkatan }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.mahasiswa') }}" class="px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
