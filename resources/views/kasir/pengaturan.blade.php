@extends('layouts.app')

@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')
    @include('layouts.partials.kasir-nav')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium text-gray-900">Ubah Password</h3>
            <p class="mt-1 text-sm text-gray-600">
                Pastikan Anda menggunakan password yang kuat dan mudah diingat.
            </p>
        </div>
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="{{ route('kasir.pengaturan.updatePassword') }}">
                    @csrf

                    {{-- Pesan Sukses --}}
                    @if (session('status') === 'password-updated')
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md" role="alert">
                           Password berhasil diperbarui.
                        </div>
                    @endif

                    {{-- Input Password Lama --}}
                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Password Baru --}}
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                         @error('password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Konfirmasi Password --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
