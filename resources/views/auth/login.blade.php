@extends('layouts.auth')

@section('title', 'Login - Sistem Pembayaran UKT')

@section('content')
    <h1>SISTEM PEMBAYARAN UKT</h1>
    <p>Masuk untuk Sistem Pembayaran STTP</p>

    {{-- Tampilkan pesan error jika login gagal --}}
    @if($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form ini akan mengirim data ke rute 'login' yang dihandle oleh AuthenticatedSessionController --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Input Username">
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Input Password">
                <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
            </div>
        </div>

        <button type="submit" class="btn-auth">
            Masuk
        </button>
    </form>
@endsection

@push('scripts')
<script>
    // Script ini hanya untuk toggle password, tidak ada lagi fetch API
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    }
</script>
@endpush