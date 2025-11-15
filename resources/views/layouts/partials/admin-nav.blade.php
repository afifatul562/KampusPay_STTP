<div class="flex items-center justify-center py-5 px-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-900">
    <div class="flex items-center gap-3">
        <div class="relative">
            <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo Kampus" class="h-10 w-auto rounded-full ring-2 ring-white/20">
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-success-500 rounded-full border-2 border-gray-800"></div>
        </div>
        <span class="text-white text-lg font-bold" data-app-name="1">{{ config('app.name', 'KampusPay') }}</span>
    </div>
</div>
<nav class="mt-2 px-2 py-4" role="navigation" aria-label="Navigasi Admin">
    <a href="{{ route('admin.dashboard') }}" aria-label="Menu Overview" aria-current="{{ request()->routeIs('admin.dashboard*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="font-medium">Overview</span>
    </a>
    <a href="{{ route('admin.mahasiswa') }}" aria-label="Menu Mahasiswa" aria-current="{{ request()->routeIs('admin.mahasiswa*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.mahasiswa*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span class="font-medium">Mahasiswa</span>
    </a>
    <a href="{{ route('admin.tarif') }}" aria-label="Menu Tarif" aria-current="{{ request()->routeIs('admin.tarif*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.tarif*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17h.01M7 13h5a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5a2 2 0 012-2z"></path></svg>
        <span class="font-medium">Tarif</span>
    </a>
    <a href="{{ route('admin.pembayaran') }}" aria-label="Menu Tagihan" aria-current="{{ request()->routeIs('admin.pembayaran*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.pembayaran*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        <span class="font-medium">Tagihan</span>
    </a>
    <a href="{{ route('admin.laporan') }}" aria-label="Menu Laporan" aria-current="{{ request()->routeIs('admin.laporan*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.laporan*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        <span class="font-medium">Laporan</span>
    </a>
    <a href="{{ route('admin.pengaturan') }}" aria-label="Menu Pengaturan" aria-current="{{ request()->routeIs('admin.pengaturan*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.pengaturan*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <span class="font-medium">Pengaturan</span>
    </a>
    <a href="{{ route('admin.users.index') }}" aria-label="Menu User" aria-current="{{ request()->routeIs('admin.users.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        <span class="font-medium">User</span>
    </a>
</nav>
