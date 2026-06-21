<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
        .select2-container .select2-selection--single { min-height: 44px; height: 44px; border-color: #d1d5db; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 44px; padding-left: 12px; color: #374151; font-size: max(16px, 1rem); }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; right: 6px; }
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
<body class="font-sans antialiased bg-gray-100 text-fluid-base">
    {{-- Drawer + hamburger sampai xl (1280px): iPad Pro/Nest Hub lebar 1024 mendapat konten penuh --}}
    <div
        x-data="{
            sidebarOpen: false,
            init() {
                const mql = window.matchMedia('(min-width: 1280px)');
                const sync = () => { if (mql.matches) this.sidebarOpen = false };
                mql.addEventListener('change', sync);
            }
        }"
        @keydown.escape.window="sidebarOpen = false"
        class="flex min-h-0 h-[100dvh] xl:h-screen bg-gray-100">

        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-[1px] xl:hidden"
            style="display: none;"
            aria-hidden="true"></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 flex w-[min(18rem,calc(100vw-2.5rem))] max-w-[min(18rem,100vw)] flex-col bg-gray-800 text-white shadow-xl transform transition-transform duration-300 ease-in-out xl:relative xl:max-w-none xl:w-64 xl:translate-x-0 xl:shrink-0 xl:shadow-none"
            role="navigation"
            aria-label="Menu utama">
            <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-gray-700/80 xl:hidden shrink-0">
                <span class="text-xs font-medium text-gray-400 truncate">Menu</span>
                <button type="button" @click="sidebarOpen = false" class="inline-flex items-center justify-center min-h-touch min-w-touch rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Tutup menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
            @if(Auth::user()->isAdmin())
                @include('layouts.partials.admin-nav')
            @elseif(Auth::user()->isKasir())
                @include('layouts.partials.kasir-nav')
            @elseif(Auth::user()->isMahasiswa())
                @include('layouts.partials.mahasiswa-nav')
            @elseif(Auth::user()->isYayasan())
                @include('layouts.partials.yayasan-nav')
            @endif
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden min-h-0 min-w-0">
            <header class="flex items-center gap-2 sm:gap-3 pt-safe pb-3 sm:pb-4 px-3 sm:px-4 xl:px-6 bg-white border-b shadow-sm shrink-0 pl-[max(0.75rem,env(safe-area-inset-left,0px))] pr-[max(0.75rem,env(safe-area-inset-right,0px))]">
                <button type="button" @click.stop="sidebarOpen = !sidebarOpen" class="xl:hidden shrink-0 inline-flex items-center justify-center min-h-[2.5rem] min-w-[2.5rem] sm:min-h-touch sm:min-w-touch rounded-xl text-gray-600 hover:bg-gray-100 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500" data-tooltip="Buka atau tutup menu" title="Menu" aria-label="Buka menu" :aria-expanded="sidebarOpen">
                    <svg class="w-[1.125rem] h-[1.125rem] sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <h1 class="flex-1 min-w-0 text-fluid-page xl:text-fluid-2xl font-bold text-gray-800 truncate pr-1 [overflow-wrap:anywhere]">@yield('page-title', 'Dashboard')</h1>

                @auth {{-- Hanya tampilkan jika user login --}}
                <div class="flex items-center shrink-0 gap-2 sm:gap-3 xl:gap-4"> {{-- ml-auto untuk dorong ke kanan --}}
                    @if(Auth::user()->isMahasiswa())
                    <div class="relative">
                        <button id="mhsNotifBell" type="button" class="relative inline-flex items-center justify-center min-h-touch min-w-touch sm:w-11 sm:h-11 rounded-full bg-gray-50 border border-gray-200 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Notifikasi pembayaran">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span id="mhsNotifBadge" class="absolute -top-0.5 -right-0.5 min-w-[1.125rem] px-1 rounded-full bg-primary-500 text-white text-fluid-xs font-semibold leading-4 text-center hidden">0</span>
                        </button>
                        <div id="mhsNotifDropdown" class="hidden absolute right-0 mt-2 w-[min(calc(100vw-1.5rem),20rem)] sm:w-80 max-w-[calc(100vw-1rem)] bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                            <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                                <span class="font-semibold text-gray-800 text-sm">Notifikasi</span>
                                <button id="mhsNotifClose" type="button" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                            </div>
                            <div id="mhsNotifList" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <div class="p-4 text-sm text-gray-500">Memuat...</div>
                            </div>
                        </div>
                    </div>
                    @elseif(Auth::user()->isKasir())
                    <div class="relative">
                        <button id="globalNotifBell" type="button" class="relative inline-flex items-center justify-center min-h-touch min-w-touch sm:w-11 sm:h-11 rounded-full bg-gray-50 border border-gray-200 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Notifikasi aktivasi">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span id="globalNotifBadge" class="absolute -top-0.5 -right-0.5 min-w-[1.125rem] px-1 rounded-full bg-primary-500 text-white text-fluid-xs font-semibold leading-4 text-center hidden">0</span>
                        </button>
                        <div id="globalNotifDropdown" class="hidden absolute right-0 mt-2 w-[min(calc(100vw-1.5rem),20rem)] sm:w-80 max-w-[calc(100vw-1rem)] bg-white border border-gray-200 rounded-lg shadow-lg z-50">
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

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 px-3 py-4 sm:px-4 sm:py-5 xl:p-6 pb-safe w-full min-w-0">
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
            const apiUrl = @json(url('/api/kasir/notifications'));
            const readUrlBase = @json(url('/api/kasir/notifications'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let timer = null;

            if (!apiToken || !bellBtn || !dropdown || !listEl) return;

            function escapeHtml(str) {
                return String(str || '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                })[c]);
            }

            async function apiRequest(url) {
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

            function render(items, unread) {
                if (!items || !items.length) {
                    listEl.innerHTML = '<div class="p-4 text-sm text-gray-500">Belum ada notifikasi transfer.</div>';
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                    return;
                }
                const unreadCount = unread ?? items.filter(n => !n.read_at).length;
                if (unreadCount > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
                } else {
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                }
                listEl.innerHTML = items.map(item => {
                    const isUnread = !item.read_at;
                    const when = item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '';
                    const link = item.link || @json(route('kasir.verifikasi.index'));
                    return `
                        <a href="${escapeHtml(link)}" data-id="${escapeHtml(item.id)}" class="kasir-notification-item block p-4 hover:bg-gray-50 ${isUnread ? 'bg-blue-50' : ''}">
                            <p class="text-sm text-gray-800 ${isUnread ? 'font-semibold' : ''}">${escapeHtml(item.message)}</p>
                            <p class="text-[11px] text-gray-400 mt-1">${escapeHtml(when)}</p>
                        </a>
                    `;
                }).join('');

                listEl.querySelectorAll('.kasir-notification-item').forEach((el) => {
                    el.addEventListener('click', function() {
                        const id = this.dataset.id;
                        if (!id || !this.classList.contains('bg-blue-50')) return;
                        fetch(`${readUrlBase}/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${apiToken}`,
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        }).catch(() => {});
                    });
                });
            }

            async function loadNotifs() {
                try {
                    const data = await apiRequest(apiUrl);
                    render(data.data || [], data.unread_count);
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
    @if(Auth::check() && Auth::user()->isMahasiswa())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bellBtn = document.getElementById('mhsNotifBell');
            const badge = document.getElementById('mhsNotifBadge');
            const dropdown = document.getElementById('mhsNotifDropdown');
            const listEl = document.getElementById('mhsNotifList');
            const closeBtn = document.getElementById('mhsNotifClose');
            const apiUrl = @json(route('mahasiswa.notifications.index'));
            const readUrlBase = @json(url('/mahasiswa/notifications'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            if (!bellBtn || !dropdown || !listEl) return;

            function escapeHtml(str) {
                return String(str || '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                })[c]);
            }

            function render(notifications, unread) {
                if (!notifications || !notifications.length) {
                    listEl.innerHTML = '<div class="p-4 text-sm text-gray-500">Belum ada notifikasi.</div>';
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                    return;
                }
                const unreadCount = unread ?? notifications.filter(n => !n.read_at).length;
                if (unreadCount > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
                } else {
                    badge.classList.add('hidden');
                    badge.textContent = '0';
                }
                listEl.innerHTML = notifications.map(item => {
                    const isUnread = !item.read_at;
                    const when = item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '';
                    const link = item.link || @json(route('mahasiswa.pembayaran.index'));
                    return `
                        <a href="${escapeHtml(link)}" data-id="${escapeHtml(item.id)}" class="notification-item block p-4 hover:bg-gray-50 ${isUnread ? 'bg-blue-50' : ''}">
                            <p class="text-sm text-gray-800 ${isUnread ? 'font-semibold' : ''}">${escapeHtml(item.message)}</p>
                            <p class="text-[11px] text-gray-400 mt-1">${escapeHtml(when)}</p>
                        </a>
                    `;
                }).join('');

                listEl.querySelectorAll('.notification-item').forEach((el) => {
                    el.addEventListener('click', function() {
                        const id = this.dataset.id;
                        if (!id || !this.classList.contains('bg-blue-50')) return;
                        fetch(`${readUrlBase}/${id}/read`, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        }).catch(() => {});
                    });
                });
            }

            async function loadNotifs() {
                try {
                    const resp = await fetch(apiUrl, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    });
                    if (!resp.ok) throw new Error('Gagal memuat notifikasi');
                    const json = await resp.json();
                    render(json.data || [], json.unread_count);
                } catch (e) {
                    listEl.innerHTML = '<div class="p-4 text-sm text-red-500">Gagal memuat notifikasi.</div>';
                    badge.classList.add('hidden');
                }
            }

            bellBtn.addEventListener('click', () => {
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) loadNotifs();
            });
            closeBtn?.addEventListener('click', () => dropdown.classList.add('hidden'));
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && e.target !== bellBtn) {
                    dropdown.classList.add('hidden');
                }
            });

            loadNotifs();
            setInterval(loadNotifs, 30000);
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
