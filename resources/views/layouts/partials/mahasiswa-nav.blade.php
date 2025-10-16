<div class="flex bg-gray-200 rounded-lg p-1 mb-6 text-sm sm:text-base flex-wrap">
    <a href="{{ route('mahasiswa.dashboard') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Overview</a>
    <a href="{{ route('mahasiswa.pembayaran.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('mahasiswa.pembayaran.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Pembayaran</a>
    <a href="{{ route('mahasiswa.riwayat.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('mahasiswa.riwayat.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Riwayat</a>
    <a href="{{ route('mahasiswa.laporan.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('mahasiswa.laporan.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Laporan</a>
    <a href="{{ route('mahasiswa.profil') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('mahasiswa.profil') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Profil</a>
</div>
