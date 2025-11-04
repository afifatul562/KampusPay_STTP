@extends('layouts.auth')

@section('title', 'Login - KampusPay STTP')

@section('content')
    <div class="text-center">
        <h1 class="text-2xl font-bold text-white mb-1">
            Selamat Datang!
        </h1>
        <p class="text-gray-400 mb-8">
            Masuk ke akun KampusPay STTP Anda.
        </p>
    </div>

    {{-- Tampilkan pesan error jika login gagal --}}
    @if($errors->any())
        <div class="bg-red-500/10 border border-red-400 text-red-300 px-4 py-3 rounded-lg relative mb-6 text-sm" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Form ini akan mengirim data ke rute 'login' yang dihandle oleh AuthenticatedSessionController --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Input Username --}}
        <div class="mb-5">
            <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan Username Anda"
                   class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 placeholder-gray-400">
        </div>

        {{-- Input Password --}}
        <div class="mb-6">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
            <div class="relative">
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan Password Anda"
                       class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pr-10 placeholder-gray-400">

                {{-- Tombol Toggle Ikon Mata (SVG) --}}
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white">
                    {{-- Ikon Mata Terbuka (default) --}}
                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    {{-- Ikon Mata Tertutup (disembunyikan) --}}
                    <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 .847 0 1.668.124 2.454.354M7.5 7.5l9 9M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="w-full text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-3 text-center shadow-md hover:shadow-lg transition-all duration-300">
            Masuk
        </button>
    </form>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
        }
    }
</script>
@endpush
