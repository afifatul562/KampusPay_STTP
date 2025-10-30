<div class="flex items-center justify-center py-4 px-6 border-b border-gray-700">
    <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo Kampus" class="h-10 w-auto rounded-full">
    <span class="ml-3 text-white text-xl font-semibold">KampusPay</span>
</div>
<nav class="mt-4">
    <a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-4 px-6 py-3 transition-colors duration-200 {{ request()->routeIs('kasir.dashboard*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span class="font-medium">Proses Bayar</span>
    </a>
    <a href="{{ route('kasir.verifikasi.index') }}" class="flex items-center gap-4 px-6 py-3 transition-colors duration-200 {{ request()->routeIs('kasir.verifikasi.index*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-medium">Verifikasi</span>
    </a>
    <a href="{{ route('kasir.transaksi.index') }}" class="flex items-center gap-4 px-6 py-3 transition-colors duration-200 {{ request()->routeIs('kasir.transaksi.index*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        <span class="font-medium">Transaksi</span>
    </a>
    <a href="{{ route('kasir.laporan.index') }}" class="flex items-center gap-4 px-6 py-3 transition-colors duration-200 {{ request()->routeIs('kasir.laporan.index*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        <span class="font-medium">Laporan</span>
    </a>
</nav>
