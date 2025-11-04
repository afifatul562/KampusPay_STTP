@extends('layouts.app')

@section('title', 'Ubah Password')
@section('page-title', 'Ubah Password')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-primary-100 to-primary-200 p-3 rounded-lg shadow-sm">
                <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengaturan Akun</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola keamanan akun Anda</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Deskripsi --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Ubah Password</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Pastikan Anda menggunakan password yang kuat dan mudah diingat untuk menjaga keamanan akun Anda.
                </p>
            </div>
        </div>

        {{-- Kolom Kanan: Form --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <form method="POST" action="{{ route('mahasiswa.profil.password.update') }}" class="space-y-4">
                    @csrf

                    {{-- Pesan Sukses --}}
                    @if (session('status') === 'password-updated')
                        <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border-l-4 border-green-500" role="alert">
                            <div class="flex items-center justify-center w-8 h-8 bg-green-500 rounded-full mr-3 flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div><span class="font-medium">Berhasil!</span> Password Anda telah berhasil diperbarui.</div>
                        </div>
                    @endif

                    {{-- PERUBAHAN: Input Password Lama (Dengan Ikon) --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <div class="relative mt-1"> {{-- Tambah wrapper --}}
                            <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required> {{-- Tambah pr-10 --}}

                            {{-- Tombol Ikon Mata --}}
                            <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex items-center pr-3" style="background:transparent; border:none; cursor:pointer;" aria-label="Toggle password visibility">
                                {{-- Ikon Mata Terbuka (Heroicons) --}}
                                <svg class="icon-eye h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{-- Ikon Mata Tertutup (Heroicons) --}}
                                <svg class="icon-eye-slash h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L12 12" />
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PERUBAHAN: Input Password Baru (Dengan Ikon) --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <div class="relative mt-1"> {{-- Tambah wrapper --}}
                            <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required> {{-- Tambah pr-10 --}}

                            {{-- Tombol Ikon Mata --}}
                            <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex items-center pr-3" style="background:transparent; border:none; cursor:pointer;" aria-label="Toggle password visibility">
                                {{-- Ikon Mata Terbuka (Heroicons) --}}
                                <svg class="icon-eye h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{-- Ikon Mata Tertutup (Heroicons) --}}
                                <svg class="icon-eye-slash h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L12 12" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PERUBAHAN: Input Konfirmasi Password (Dengan Ikon) --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <div class="relative mt-1"> {{-- Tambah wrapper --}}
                            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required> {{-- Tambah pr-10 --}}

                            {{-- Tombol Ikon Mata --}}
                            <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex items-center pr-3" style="background:transparent; border:none; cursor:pointer;" aria-label="Toggle password visibility">
                                {{-- Ikon Mata Terbuka (Heroicons) --}}
                                <svg class="icon-eye h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{-- Ikon Mata Tertutup (Heroicons) --}}
                                <svg class="icon-eye-slash h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- PERUBAHAN: Tambahkan Script di Bawah --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Temukan semua tombol toggle di halaman
        const toggleButtons = document.querySelectorAll('.js-toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Temukan input password di dalam div.relative yang sama
                const inputField = button.parentElement.querySelector('input');

                // Temukan kedua ikon di dalam tombol
                const iconEye = button.querySelector('.icon-eye');
                const iconEyeSlash = button.querySelector('.icon-eye-slash');

                // Toggle tipe input
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    iconEye.classList.add('hidden');
                    iconEyeSlash.classList.remove('hidden');
                } else {
                    inputField.type = 'password';
                    iconEye.classList.remove('hidden');
                    iconEyeSlash.classList.add('hidden');
                }
            });
        });
    });
</script>
@endpush