{{-- Menu Tab Konsisten untuk Kasir --}}
<div class="flex bg-gray-200 rounded-lg p-1 mb-6 text-sm sm:text-base flex-wrap">
    <a href="{{ route('kasir.dashboard') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('kasir.dashboard') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Proses Pembayaran
    </a>
    <a href="{{ route('kasir.transaksi.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('kasir.transaksi.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Transaksi
    </a>
    <a href="{{ route('kasir.laporan.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('kasir.laporan.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Laporan
    </a>
    <a href="{{ route('kasir.verifikasi.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('kasir.verifikasi.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Verifikasi
    </a>
    {{-- ▼▼▼ LINK YANG HILANG SUDAH DITAMBAHKAN DI SINI ▼▼▼ --}}
    <a href="{{ route('kasir.pengaturan.index') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('kasir.pengaturan.index') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">
        Pengaturan
    </a>
</div>

