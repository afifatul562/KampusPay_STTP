<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ▼▼▼ INI BAGIAN YANG DIPERBAIKI ▼▼▼ --}}
    @auth
        {{-- Tampilkan meta tag jika rolenya Admin atau Kasir --}}
        @if(Auth::user()->isAdmin() || Auth::user()->isKasir())
            <meta name="api-token" content="{{ session('api_token') }}">
        @endif
    @endauth
    {{-- ▲▲▲ SELESAI ▲▲▲ --}}

    <title>@yield('title', 'KampusPay STTP')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        {{-- Navbar Universal --}}
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="font-bold text-xl text-gray-800">
                            <span class="mr-2">🏫</span> KampusPay STTP
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <span class="hidden sm:inline-block px-3 py-1 text-sm font-semibold text-white rounded-full
                                @switch(Auth::user()->role)
                                    @case('admin') bg-red-500 @break
                                    @case('kasir') bg-blue-500 @break
                                    @case('mahasiswa') bg-green-500 @break
                                @endswitch
                            ">
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                            <span class="text-sm text-gray-600 hidden sm:block">Halo, {{ Auth::user()->nama_lengkap }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 font-semibold">
                                    Logout
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- Konten Utama --}}
        <main class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6 px-4 sm:px-0">@yield('page-title')</h1>
                <div class="px-4 sm:px-0">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>

