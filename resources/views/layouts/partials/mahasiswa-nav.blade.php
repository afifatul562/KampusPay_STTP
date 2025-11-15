<div class="flex items-center justify-center py-5 px-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-900">
    <div class="flex items-center gap-3">
        <div class="relative">
            <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo Kampus" class="h-10 w-auto rounded-full ring-2 ring-white/20">
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-success-500 rounded-full border-2 border-gray-800"></div>
        </div>
        <span class="text-white text-lg font-bold" data-app-name="1">{{ config('app.name', 'KampusPay') }}</span>
    </div>
</div>
<nav class="mt-2 px-2 py-4">
    <a href="{{ route('mahasiswa.dashboard') }}" aria-current="{{ request()->routeIs('mahasiswa.dashboard*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.dashboard*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="font-medium">Overview</span>
    </a>
    <a href="{{ route('mahasiswa.pembayaran.index') }}" aria-current="{{ request()->routeIs('mahasiswa.pembayaran.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.pembayaran.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span class="font-medium">Pembayaran</span>
    </a>
    <a href="{{ route('mahasiswa.riwayat.index') }}" aria-current="{{ request()->routeIs('mahasiswa.riwayat.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.riwayat.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-medium">Riwayat</span>
    </a>
    <a href="{{ route('mahasiswa.laporan.index') }}" aria-current="{{ request()->routeIs('mahasiswa.laporan.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('mahasiswa.laporan.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <span class="font-medium">Laporan</span>
    </a>
    {{-- <a href="{{ route('mahasiswa.profil') }}" class="flex items-center gap-4 px-6 py-3 transition-colors duration-200 {{ request()->routeIs('mahasiswa.profil*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <span class="font-medium">Profil</span>
    </a> --}}
</nav>
