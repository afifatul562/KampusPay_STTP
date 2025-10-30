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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
                <button @click.stop="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <h1 class="text-2xl font-bold text-gray-800 hidden md:block">@yield('page-title', 'Dashboard')</h1>

                @auth {{-- Hanya tampilkan jika user login --}}
                <div class="flex items-center ml-auto"> {{-- ml-auto untuk dorong ke kanan --}}

                    @auth
                        @if(Auth::user()->isMahasiswa()) {{-- Hanya tampilkan untuk mahasiswa --}}

                            {{-- Wrapper Dropdown Notifikasi --}}
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open"
                                        class="relative p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                        aria-label="Notifikasi">
                                    <!-- Ikon Lonceng -->
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.017 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                    </svg>

                                    {{-- Badge Jumlah Notifikasi --}}
                                    @php
                                        $unreadCount = auth()->user()->unreadNotifications->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white" aria-hidden="true"></span>
                                    @endif
                                </button>

                                {{-- Konten Dropdown Notifikasi --}}
                                <div x-show="open"
                                     x-transition... {{-- Atribut transisi tetap sama --}}
                                     class="origin-top-right absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                     role="menu" aria-orientation="vertical" tabindex="-1"
                                     style="display: none;">

                                    <div class="flex justify-between items-center px-4 py-2 text-sm font-semibold text-gray-700 border-b">
                                        Notifikasi
                                    </div>

                                    {{-- Loop Notifikasi --}}
                                    @forelse (auth()->user()->notifications->take(10) as $notification)
                                        <a href="{{ $notification->data['link'] ?? '#' }}"
                                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b {{ $loop->last ? 'border-b-0' : '' }} notification-item {{ $notification->read_at ? '' : 'bg-blue-50 font-semibold' }}"
                                           data-id="{{ $notification->id }}"
                                           role="menuitem" tabindex="-1">
                                            <p class="truncate">{{ $notification->data['message'] ?? 'Notifikasi Baru' }}</p>
                                            <p class="text-xs text-gray-500 mt-1 {{ $notification->read_at ? '' : 'font-normal' }}">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </a>
                                    @empty
                                        {{-- ... Pesan "Tidak ada notifikasi" ... --}}
                                    @endforelse
                                </div>
                            </div> {{-- Akhir wrapper dropdown --}}

                        @endif {{-- Akhir @if(Auth::user()->isMahasiswa()) --}}
                    @endauth



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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Logika untuk menandai notifikasi sebagai sudah dibaca saat diklik
            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                item.addEventListener('click', function (event) {
                    const notificationId = this.dataset.id;
                    const url = `/notifications/${notificationId}/mark-as-read`; // Gunakan URL langsung

                    // Hanya kirim request jika notifikasi belum dibaca (ada class bg-blue-50)
                    if (this.classList.contains('bg-blue-50')) {
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Notification marked as read:', notificationId);
                                // Hapus highlight biru secara visual
                                this.classList.remove('bg-blue-50', 'font-semibold');
                                this.querySelector('.text-xs').classList.remove('font-normal'); // Kembalikan font tanggal
                                // Kurangi angka badge (jika ada) - ini lebih kompleks, bisa ditambahkan nanti
                            } else {
                                console.error('Failed to mark notification as read');
                            }
                        })
                        .catch(error => {
                            console.error('Error marking notification as read:', error);
                        });
                    }
                    // Biarkan link berjalan normal (redirect) setelah request dikirim
                });
            });
        });

        // Opsional: Fungsi untuk menandai semua sebagai sudah dibaca saat dropdown dibuka
        // function markAllAsReadIfOpen() {
        //    // Tambahkan logika AJAX untuk memanggil route 'mark all as read' di sini
        //    // Jika berhasil, hilangkan badge merah
        // }
    </script>
</body>
</html>
