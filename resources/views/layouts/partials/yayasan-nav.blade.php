<div class="flex items-center justify-center py-4 sm:py-5 px-4 sm:px-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-900">
    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
        <div class="relative shrink-0">
            <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo Kampus" class="h-9 w-9 sm:h-10 sm:w-10 object-cover rounded-full ring-2 ring-white/20">
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-indigo-400 rounded-full border-2 border-gray-800"></div>
        </div>
        <span class="text-white text-fluid-lg font-bold truncate" data-app-name="1">{{ config('app.name', 'KampusPay') }}</span>
    </div>
</div>
<nav class="mt-2 px-2 py-4" role="navigation" aria-label="Navigasi Yayasan">
    <a href="{{ route('yayasan.dashboard') }}" aria-label="Menu Monitoring" aria-current="{{ request()->routeIs('yayasan.dashboard*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 min-h-touch rounded-lg transition-all duration-200 {{ request()->routeIs('yayasan.dashboard*') ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="font-medium text-fluid-sm">Monitoring</span>
    </a>
    <a href="{{ route('yayasan.laporan.index') }}" aria-label="Menu Laporan" aria-current="{{ request()->routeIs('yayasan.laporan*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 min-h-touch rounded-lg transition-all duration-200 {{ request()->routeIs('yayasan.laporan*') ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <span class="font-medium text-fluid-sm">Laporan</span>
    </a>
</nav>
<div class="mx-4 mb-4 rounded-lg border border-indigo-500/30 bg-indigo-900/40 px-3 py-2.5">
    <p class="text-fluid-xs text-indigo-200 leading-snug">
        <span class="font-semibold text-indigo-100">Mode pantau</span> — hanya melihat data, tanpa hak mengubah.
    </p>
</div>
