{{-- Menu Tab Konsisten untuk Admin --}}
<div class="flex bg-gray-200 rounded-lg p-1 mb-6 text-sm sm:text-base flex-wrap">
    <a href="{{ route('admin.dashboard') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Overview
    </a>
    <a href="{{ route('admin.mahasiswa') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.mahasiswa') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Mahasiswa
    </a>
    <a href="{{ route('admin.pembayaran') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.pembayaran') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Pembayaran
    </a>
    <a href="{{ route('admin.tarif') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.tarif') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Tarif
    </a>
    <a href="{{ route('admin.laporan') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.laporan') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Laporan
    </a>
    <a href="{{ route('admin.pengaturan') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.pengaturan') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Pengaturan
    </a>
    <a href="{{ route('admin.registrasi') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.registrasi') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Registrasi
    </a>
</div>

