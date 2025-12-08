<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentication') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_kampus.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Menggunakan class Tailwind untuk styling --}}
<body class="font-sans text-gray-900 antialiased">

    <div class="relative min-h-screen flex items-center justify-center bg-gray-900">

        <div class="absolute inset-0 w-full h-full bg-cover bg-center" style="background-image: url('{{ asset('images/background-login.jpg') }}'); filter: blur(4px) brightness(0.6);"></div>

        <div class="relative z-10 w-full max-w-md px-6 py-8 mx-4 bg-gray-800/80 backdrop-blur-sm shadow-2xl rounded-lg">
            @yield('content')
        </div>

    </div>

    @stack('scripts')
</body>
</html>
