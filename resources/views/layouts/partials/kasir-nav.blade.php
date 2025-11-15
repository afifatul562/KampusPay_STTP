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
    <a href="{{ route('kasir.dashboard') }}" aria-current="{{ request()->routeIs('kasir.dashboard*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('kasir.dashboard*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span class="font-medium">Proses Bayar (Tunai)</span>
    </a>
    <a href="{{ route('kasir.verifikasi.index') }}" aria-current="{{ request()->routeIs('kasir.verifikasi.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('kasir.verifikasi.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-medium">Verifikasi (Transfer)</span>
    </a>
    <a href="{{ route('kasir.transaksi.index') }}" aria-current="{{ request()->routeIs('kasir.transaksi.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('kasir.transaksi.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        <span class="font-medium">Riwayat Pembayaran</span>
    </a>
    <a href="{{ route('kasir.laporan.index') }}" aria-current="{{ request()->routeIs('kasir.laporan.index*') ? 'page' : '' }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all duration-200 {{ request()->routeIs('kasir.laporan.index*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        <span class="font-medium">Laporan</span>
    </a>
</nav>
