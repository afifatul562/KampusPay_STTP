<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        @if(Auth::user()->isAdmin() || Auth::user()->isKasir())
            <meta name="api-token" content="{{ session('api_token') }}">
        @endif
    @endauth
    <title>@yield('title', 'KampusPay STTP')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_kampus.png') }}">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- jQuery & Select2 for searchable selects (used in admin pembayaran modal) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr for datepicker (dd/mm/yyyy) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <style>
        /* Make Select2 look closer to Tailwind form inputs */
        .select2-container .select2-selection--single { height: 38px; border-color: #d1d5db; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 12px; color: #374151; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 6px; }
        .select2-container--default.select2-container--open .select2-selection--single { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .select2-dropdown { z-index: 60; }
        /* Ensure flatpickr calendar appears above modal overlay */
        .flatpickr-calendar { z-index: 70; }
        
        /* Custom Tooltip Styles */
        [data-tooltip] {
            position: relative;
        }
        
        /* Jangan ubah cursor untuk button/link yang sudah punya cursor pointer */
        button[data-tooltip], a[data-tooltip] {
            cursor: pointer;
        }
        
        /* Untuk elemen lain yang bukan button/link, gunakan cursor help */
        [data-tooltip]:not(button):not(a) {
            cursor: help;
        }
        
        [data-tooltip]:hover::before,
        [data-tooltip]:hover::after {
            opacity: 1;
            pointer-events: auto;
        }
        
        [data-tooltip]::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 8px;
            padding: 6px 12px;
            background-color: #1f2937;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease-in-out;
            z-index: 1000;
        }
        
        [data-tooltip]::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 2px;
            border: 5px solid transparent;
            border-top-color: #1f2937;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease-in-out;
            z-index: 1000;
        }
        
        /* Tooltip positioning variants */
        [data-tooltip-position="top"]::before {
            bottom: 100%;
            top: auto;
            transform: translateX(-50%);
        }
        
        [data-tooltip-position="top"]::after {
            bottom: 100%;
            top: auto;
            transform: translateX(-50%);
        }
        
        [data-tooltip-position="bottom"]::before {
            top: 100%;
            bottom: auto;
            margin-top: 8px;
            margin-bottom: 0;
        }
        
        [data-tooltip-position="bottom"]::after {
            top: 100%;
            bottom: auto;
            margin-top: 2px;
            margin-bottom: 0;
            border-top-color: transparent;
            border-bottom-color: #1f2937;
        }
        
        [data-tooltip-position="left"]::before {
            right: 100%;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 8px;
            margin-bottom: 0;
        }
        
        [data-tooltip-position="left"]::after {
            right: 100%;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 2px;
            margin-bottom: 0;
            border-top-color: transparent;
            border-left-color: #1f2937;
        }
        
        [data-tooltip-position="right"]::before {
            left: 100%;
            right: auto;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 8px;
            margin-bottom: 0;
        }
        
        [data-tooltip-position="right"]::after {
            left: 100%;
            right: auto;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 2px;
            margin-bottom: 0;
            border-top-color: transparent;
            border-right-color: #1f2937;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100">

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 text-white transform transition-transform duration-300 ease-in-out md:relative md:translate-x-0">
            @if(Auth::user()->isAdmin())
                @include('layouts.partials.admin-nav')
            @elseif(Auth::user()->isKasir())
                @include('layouts.partials.kasir-nav')
            @elseif(Auth::user()->isMahasiswa())
                @include('layouts.partials.mahasiswa-nav')
            @endif
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-4 bg-white border-b shadow-sm">
                <button @click.stop="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 focus:outline-none" data-tooltip="Toggle menu" title="Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <h1 class="text-2xl font-bold text-gray-800 hidden md:block">@yield('page-title', 'Dashboard')</h1>

                @auth {{-- Hanya tampilkan jika user login --}}
                <div class="flex items-center ml-auto"> {{-- ml-auto untuk dorong ke kanan --}}

                    {{-- Notifikasi mahasiswa dinonaktifkan --}}



                </div>
                @endauth

                @auth
                    @include('layouts.partials.user-dropdown')
                @endauth
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    @if(session('success') || session('error') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            try{
                const msg = @json(session('success') ?? session('error') ?? session('info'));
                const type = @json(session('success') ? 'success' : (session('error') ? 'error' : 'info'));
                if (window.Swal) {
                    Swal.fire({ icon: type, title: msg, timer: 1800, showConfirmButton: false });
                } else {
                    alert(msg);
                }
            }catch(e){}
        });
    </script>
    @endif
</body>
</html>
