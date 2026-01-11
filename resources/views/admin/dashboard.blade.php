@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Overview Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <a href="{{ route('admin.mahasiswa') }}" aria-label="Lihat total dan daftar mahasiswa"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-sm font-medium text-gray-500 mb-1">Total Mahasiswa</div>
            <div id="total-mahasiswa-value" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="total-mahasiswa-skeleton" class="w-24 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
        </div>
        <div class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-4 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </a>

    <a href="{{ route('admin.pembayaran') }}?status=lunas" aria-label="Buka halaman pembayaran status lunas"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-sm font-medium text-gray-500 mb-1">Total Pembayaran</div>
            <div id="total-pembayaran-value" class="font-bold text-gray-900 mt-1 text-left whitespace-nowrap text-xl lg:text-2xl">...</div>
            <div id="total-pembayaran-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
        </div>
        <div class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-4 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </a>

    <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden" aria-label="Kartu ringkasan tingkat pembayaran">
        <div class="absolute inset-0 bg-gradient-to-br from-warning-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-sm font-medium text-gray-500 mb-1">Tingkat Pembayaran</div>
            <div id="tingkat-pembayaran-value" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="tingkat-pembayaran-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
        </div>
        <div class="relative z-10 bg-gradient-to-br from-warning-400 to-warning-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zm3.707 6.293a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l6-6z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <a href="{{ route('admin.pembayaran') }}?status=pending" aria-label="Buka halaman pembayaran pending"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-sm font-medium text-gray-500 mb-1">Pending Payment</div>
            <div id="pending-payment-value" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="pending-payment-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
        </div>
        <div class="relative z-10 bg-gradient-to-br from-purple-400 to-purple-600 p-4 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </a>
</div>

    {{-- Tabel Pembayaran Terbaru --}}
    <div class="mt-6 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-5 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Pembayaran Terbaru</h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar pembayaran terakhir yang masuk</p>
                </div>
            </div>
        </div>

        <div class="p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" aria-label="Tabel pembayaran terbaru">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-payments-table" class="bg-white divide-y divide-gray-200 text-sm">
                    <tr class="skeleton-row">
                        <td class="px-6 py-4" colspan="5">
                            <div class="space-y-3">
                                <div class="h-4 bg-gray-200 rounded w-full animate-pulse"></div>
                                <div class="h-4 bg-gray-200 rounded w-5/6 animate-pulse"></div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
            </table>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Fungsi untuk mengambil data dari API
    async function fetchData(url) {
        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        if (!apiToken) {
            Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
            return Promise.reject('Token tidak ditemukan');
        }
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${apiToken}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        if (response.status === 401) {
            Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Sesi Anda telah berakhir. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
            throw new Error('Unauthorized');
        }
        const data = await response.json().catch(() => null);
        if (!response.ok) { console.error("Fetch data error:", data); throw new Error(data?.message || 'Gagal mengambil data.'); }
        return data;
    }

    // Fungsi untuk toggle skeleton loading
    function toggleSkeleton(valueElId, skeletonElId, showSkeleton) {
        const valueEl = document.getElementById(valueElId);
        const skeletonEl = document.getElementById(skeletonElId);
        if (showSkeleton) {
            valueEl.classList.add('hidden');
            skeletonEl.classList.remove('hidden');
        } else {
            valueEl.classList.remove('hidden');
            skeletonEl.classList.add('hidden');
        }
    }

    // Fungsi untuk memuat statistik dashboard
    function loadStats() {
        toggleSkeleton('total-mahasiswa-value', 'total-mahasiswa-skeleton', true);
        toggleSkeleton('total-pembayaran-value', 'total-pembayaran-skeleton', true);
        toggleSkeleton('tingkat-pembayaran-value', 'tingkat-pembayaran-skeleton', true);
        toggleSkeleton('pending-payment-value', 'pending-payment-skeleton', true);

        fetchData("{{ route('admin.dashboard.stats') }}").then(data => {
            toggleSkeleton('total-mahasiswa-value', 'total-mahasiswa-skeleton', false);
            document.getElementById('total-mahasiswa-value').textContent = data.total_mahasiswa || 0;

            toggleSkeleton('total-pembayaran-value', 'total-pembayaran-skeleton', false);
            const pembayaranEl = document.getElementById('total-pembayaran-value');
            const formattedPembayaran = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data.total_pembayaran || 0);
            const textLength = formattedPembayaran.length;
            pembayaranEl.classList.remove('text-xl', 'lg:text-2xl', 'text-lg', 'lg:text-xl', 'text-base', 'lg:text-lg');
            if (textLength > 17) { pembayaranEl.classList.add('text-base', 'lg:text-lg'); }
            else if (textLength > 13) { pembayaranEl.classList.add('text-lg', 'lg:text-xl'); }
            else { pembayaranEl.classList.add('text-xl', 'lg:text-2xl'); }
            pembayaranEl.textContent = formattedPembayaran;

            toggleSkeleton('tingkat-pembayaran-value', 'tingkat-pembayaran-skeleton', false);
            document.getElementById('tingkat-pembayaran-value').textContent = (data.tingkat_pembayaran || 0) + '%';

            toggleSkeleton('pending-payment-value', 'pending-payment-skeleton', false);
            document.getElementById('pending-payment-value').textContent = data.pending_payment || 0;

        }).catch(error => {
            console.error('Error fetching stats:', error)
            Swal.fire({ icon: 'error', title: 'Gagal Memuat Statistik', text: error.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
        });
    }

    // Fungsi untuk memuat pembayaran terbaru
    function loadRecentPayments() {
        const tbody = document.getElementById('recent-payments-table');

        // Fungsi helper untuk membuat sel (td)
        function createCell(text, classes = []) {
            const cell = document.createElement('td');
            cell.textContent = text;
            cell.className = 'px-6 py-4 whitespace-nowrap ' + classes.join(' ');
            return cell;
        }

        fetchData("{{ route('admin.dashboard.recentPayments') }}").then(response => {
            const data = response.data || response;
            tbody.innerHTML = ''; // Kosongkan skeleton

            if (!data || data.length === 0) {
                renderEmptyState(tbody, {
                    colspan: 5,
                    title: 'Tidak ada pembayaran terbaru',
                    message: '🎉 Tidak ada data pembayaran terbaru.',
                    icon: `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    `
                });
                return;
            }

            data.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';

                // 1. Sel Mahasiswa (Nama & NPM)
                const cellMahasiswa = document.createElement('td');
                cellMahasiswa.className = 'px-6 py-4 whitespace-nowrap';
                const divNama = document.createElement('div');
                divNama.className = 'font-medium text-gray-900';
                divNama.textContent = p.tagihan.mahasiswa.user.nama_lengkap; // Aman
                const divNpm = document.createElement('div');
                divNpm.className = 'text-gray-500';
                divNpm.textContent = p.tagihan.mahasiswa.npm; // Aman
                cellMahasiswa.appendChild(divNama);
                cellMahasiswa.appendChild(divNpm);
                tr.appendChild(cellMahasiswa);

                // 2. Sel Jenis Tagihan
                tr.appendChild(createCell(p.tagihan.tarif.nama_pembayaran, ['text-gray-700']));

                // 3. Sel Jumlah
                const formattedJumlah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p.tagihan.jumlah_tagihan);
                tr.appendChild(createCell(formattedJumlah, ['font-medium', 'text-gray-800', 'text-right']));

                // 4. Sel Tanggal
                const formattedTanggal = new Date(p.tanggal_bayar).toLocaleDateString('id-ID');
                tr.appendChild(createCell(formattedTanggal, ['text-gray-500']));

                // 5. Sel Status (Satu-satunya yang butuh innerHTML, tapi aman)
                const cellStatus = document.createElement('td');
                cellStatus.className = 'px-6 py-4 whitespace-nowrap text-center';
                let statusText = p.tagihan.status === 'Belum Lunas' ? 'Belum Dibayarkan' : p.tagihan.status;
                const statusBadge = p.tagihan.status === 'Lunas'
                    ? `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>`
                    : `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">${statusText}</span>`; // statusText aman karena dari logic kita, bukan user input

                cellStatus.innerHTML = statusBadge; // Aman
                tr.appendChild(cellStatus);

                tbody.appendChild(tr);
            });
        }).catch(error => {
            console.error('Error fetching recent payments:', error);
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat data pembayaran terbaru. ${error.message}</td></tr>`;
        });
    }

    loadStats();
    loadRecentPayments();
});
</script>
@endpush
