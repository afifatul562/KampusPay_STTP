@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Overview')

@section('content')
    {{-- Menu Tab Konsisten --}}
    <div class="flex bg-gray-200 rounded-lg p-1 mb-6 text-sm sm:text-base flex-wrap">
        <a href="{{ route('admin.dashboard') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.mahasiswa') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.pembayaran') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.tarif') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Tarif</a>
        <a href="{{ route('admin.laporan') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.laporan') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.pengaturan') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}" class="flex-1 text-center py-2 px-2 rounded-lg transition-all {{ request()->routeIs('admin.registrasi') ? 'bg-white shadow font-semibold text-gray-800' : 'text-gray-600' }}">Registrasi</a>
    </div>

    {{-- Kartu Info Konsisten --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Total Mahasiswa</div>
            <div class="text-3xl font-bold text-gray-900" id="total-mahasiswa">...</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Total Pembayaran</div>
            <div class="text-3xl font-bold text-gray-900" id="total-pembayaran">...</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Tingkat Pembayaran</div>
            <div class="text-3xl font-bold text-gray-900" id="tingkat-pembayaran">...</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Tagihan Pending</div>
            <div class="text-3xl font-bold text-gray-900" id="pending-payment">...</div>
        </div>
    </div>

    {{-- Tabel Konsisten --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">💳 Pembayaran Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NPM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-payments-table" class="bg-white divide-y divide-gray-200">
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        async function fetchData(url) {
            // Kita ambil token dari meta tag yang ada di layout/app.blade.php
            const apiToken = document.querySelector('meta[name="api-token"]').getAttribute('content');

            if (!apiToken) {
                alert('Sesi tidak ditemukan. Harap login kembali.');
                window.location.href = '/login';
                return;
            }

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`
                }
            });

            if (response.status === 401) {
                alert('Sesi tidak valid. Harap login kembali.');
                window.location.href = '/login';
                throw new Error('Unauthorized');
            }
            if (!response.ok) throw new Error('Gagal mengambil data.');

            return response.json();
        }

        function loadStats() {
            const url = "{{ route('admin.dashboard.stats') }}";
            fetchData(url)
                .then(data => {
                    if (!data) {
                        throw new Error('Data statistik tidak tersedia');
                    }
                    // Beri nilai default 0 jika data tidak ada (null)
                    document.getElementById('total-mahasiswa').textContent = data.total_mahasiswa || 0;
                    const totalPembayaranFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data.total_pembayaran || 0);
                    document.getElementById('total-pembayaran').textContent = totalPembayaranFormatted;
                    document.getElementById('tingkat-pembayaran').textContent = (data.tingkat_pembayaran || 0) + '%';
                    document.getElementById('pending-payment').textContent = data.pending_payment || 0;
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                    // Tampilkan pesan error di UI
                    document.getElementById('total-mahasiswa').textContent = 'N/A';
                    document.getElementById('total-pembayaran').textContent = 'N/A';
                    document.getElementById('tingkat-pembayaran').textContent = 'N/A';
                    document.getElementById('pending-payment').textContent = 'N/A';
                    // Tampilkan notifikasi error
                    alert('Gagal memuat data statistik dashboard. Silakan refresh halaman atau hubungi administrator.');
                });
        }

        function loadRecentPayments() {
            const url = "{{ route('admin.dashboard.recentPayments') }}";
            fetchData(url)
                .then(data => {
                    const tbody = document.getElementById('recent-payments-table');
                    tbody.innerHTML = '';

                    if (!data || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada pembayaran terbaru.</td></tr>';
                        return;
                    }

                    data.forEach(p => {
                        // Periksa apakah properti yang dibutuhkan ada sebelum mengaksesnya
                        const pembayaranId = p.id || p.pembayaran_id || 'N/A';
                        const tanggalBayar = p.tanggal_bayar ? new Date(p.tanggal_bayar).toLocaleDateString('id-ID') : 'N/A';
                        
                        // Periksa struktur data tagihan dan relasi
                        const npm = p.tagihan && p.tagihan.mahasiswa ? p.tagihan.mahasiswa.npm : 'N/A';
                        const namaLengkap = p.tagihan && p.tagihan.mahasiswa && p.tagihan.mahasiswa.user ? p.tagihan.mahasiswa.user.nama_lengkap : 'N/A';
                        const namaPembayaran = p.tagihan && p.tagihan.tarif ? p.tagihan.tarif.nama_pembayaran : 'N/A';
                        const jumlahTagihan = p.tagihan ? p.tagihan.jumlah_tagihan || 0 : 0;
                        const status = p.tagihan ? p.tagihan.status || 'N/A' : 'N/A';

                        const row = `
                            <tr>
                                <td class="px-6 py-4">${pembayaranId}</td>
                                <td class="px-6 py-4">${tanggalBayar}</td>
                                <td class="px-6 py-4">${npm}</td>
                                <td class="px-6 py-4">${namaLengkap}</td>
                                <td class="px-6 py-4">${namaPembayaran}</td>
                                <td class="px-6 py-4">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(jumlahTagihan)}</td>
                                <td class="px-6 py-4"><span class="status-lunas">${status}</span></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error fetching recent payments:', error);
                    const tbody = document.getElementById('recent-payments-table');
                    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Gagal memuat data pembayaran terbaru.</td></tr>';
                });
        }

        loadStats();
        loadRecentPayments();
    });
</script>
@endpush