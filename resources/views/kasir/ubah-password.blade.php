@extends('layouts.app')

@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Deskripsi --}}
        <div class="md:col-span-1">
            <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
            <p class="mt-1 text-sm text-gray-600">
                Pastikan Anda menggunakan password yang kuat dan mudah diingat untuk menjaga keamanan akun Anda.
            </p>
        </div>

        {{-- Kolom Kanan: Form --}}
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <form method="POST" action="{{ route('kasir.pengaturan.updatePassword') }}" class="space-y-4">
                    @csrf
                    @method('PUT') {{-- Menggunakan method PUT untuk update --}}

                    {{-- Pesan Sukses --}}
                    @if (session('status') === 'password-updated')
                        <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100" role="alert">
                            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <div>
                                <span class="font-medium">Sukses!</span> Password berhasil diperbarui.
                            </div>
                        </div>
                    @endif

                    {{-- PERUBAHAN: Input Password Lama (Dengan Ikon) --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <div class="relative mt-1">
                            <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required>

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
                        <div class="relative mt-1">
                            <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required>

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
                        <div class="relative mt-1">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10" required>

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
                        <button type="submit" class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
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