<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        @if(Auth::user()->isAdmin() || Auth::user()->isKasir() || Auth::user()->isMahasiswa())
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
                <div class="flex items-center ml-auto gap-4"> {{-- ml-auto untuk dorong ke kanan --}}
                    @if(Auth::user()->isKasir())
                    <div class="relative">
                        <button id="globalNotifBell" type="button" class="relative inline-flex items-center justify-center w-11 h-11 rounded-full bg-gray-50 border border-gray-200 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span id="globalNotifBadge" class="absolute -top-1 -right-1 min-w-[18px] px-1 rounded-full bg-primary-500 text-white text-[11px] font-semibold leading-4 text-center hidden">0</span>
                        </button>
                        <div id="globalNotifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                            <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                                <span class="font-semibold text-gray-800 text-sm">Notifikasi Aktivasi</span>
                                <button id="globalNotifClose" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                            </div>
                            <div id="globalNotifList" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <div class="p-4 text-sm text-gray-500">Memuat...</div>
                            </div>
                        </div>
                    </div>
                    @endif
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
    @if(Auth::check() && Auth::user()->isKasir())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            const bellBtn = document.getElementById('globalNotifBell');
            const badge = document.getElementById('globalNotifBadge');
            const dropdown = document.getElementById('globalNotifDropdown');
            const listEl = document.getElementById('globalNotifList');
            const closeBtn = document.getElementById('globalNotifClose');
            const apiUrl = "{{ route('kasir.aktivasi.notifications') }}";
            let timer = null;

            if (!apiToken || !bellBtn || !dropdown || !listEl) return;

            async function apiRequest(url) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const resp = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`,
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });
                if (!resp.ok) throw new Error('Gagal memuat notifikasi');
                return resp.json();
            }

            function render(items) {
                if (!items || !items.length) {
                    listEl.innerHTML = '<div class="p-4 text-sm text-gray-500">Belum ada notifikasi.</div>';
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                    return;
                }
                badge.classList.remove('hidden');
                badge.textContent = items.length;
                listEl.innerHTML = items.map(item => {
                    const statusLabel = item.status === 'aktif' ? 'Aktif' : 'BSS';
                    const badgeClass = item.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                    const nama = item.mahasiswa?.user?.nama_lengkap || 'Mahasiswa';
                    const npm = item.mahasiswa?.npm || '';
                    const updated = item.updated_at ? new Date(item.updated_at).toLocaleString('id-ID') : '';
                    return `
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">${nama}</div>
                                    <div class="text-xs text-gray-500">${npm}</div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${badgeClass}">${statusLabel}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">Semester: ${item.semester_label || '-'}</div>
                            <div class="text-[11px] text-gray-400">${updated}</div>
                        </div>
                    `;
                }).join('');
            }

            async function loadNotifs() {
                try {
                    const data = await apiRequest(apiUrl);
                    render(data.data || []);
                } catch (e) {
                    console.warn(e.message);
                    listEl.innerHTML = `<div class="p-4 text-sm text-red-500">${e.message || 'Gagal memuat notifikasi.'}</div>`;
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                }
            }

            bellBtn.addEventListener('click', () => {
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    loadNotifs();
                }
            });
            closeBtn?.addEventListener('click', () => dropdown.classList.add('hidden'));
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && e.target !== bellBtn) {
                    dropdown.classList.add('hidden');
                }
            });

            loadNotifs();
            timer = setInterval(loadNotifs, 20000);
        });
    </script>
    @endif
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

    {{-- Global error handler untuk 419 CSRF Token Mismatch --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args).then(response => {
                    if (response.status === 419) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesi Berakhir',
                                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                                confirmButtonText: 'Login',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = '/login';
                            });
                        } else {
                            if (confirm('Sesi Anda telah berakhir. Silakan login kembali.')) {
                                window.location.href = '/login';
                            }
                        }
                        return Promise.reject(new Error('CSRF token mismatch'));
                    }
                    return response;
                });
            };
        });
    </script>
</body>
</html>
