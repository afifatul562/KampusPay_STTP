@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Overview Dashboard')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

    <a href="{{ route('admin.mahasiswa') }}" aria-label="Lihat total dan daftar mahasiswa"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl active:scale-[0.99] sm:hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total Mahasiswa</div>
            <div id="total-mahasiswa-value" class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="total-mahasiswa-skeleton" class="w-24 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
        </div>
        <div class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-3 sm:p-4 rounded-xl shadow-lg group-hover:shadow-xl sm:group-hover:scale-110 transition-all duration-300 shrink-0">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </a>

    <a href="{{ route('admin.pembayaran') }}" aria-label="Buka halaman pembayaran dan tagihan"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl active:scale-[0.99] sm:hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total uang masuk</div>
            <div id="total-pembayaran-value" class="font-bold text-gray-900 mt-1 text-left whitespace-nowrap tabular-nums leading-tight max-w-full text-fluid-lg lg:text-fluid-xl">...</div>
            <div id="total-pembayaran-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
            {{-- <p class="text-fluid-xs text-gray-400 mt-1.5 leading-snug">Jumlah terbayar (cicilan), pembayaran tidak dibatalkan</p> --}}
        </div>
        <div class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-3 sm:p-4 rounded-xl shadow-lg group-hover:shadow-xl sm:group-hover:scale-110 transition-all duration-300 shrink-0">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </a>

    <div class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden" aria-label="Kartu ringkasan tingkat pembayaran">
        <div class="absolute inset-0 bg-gradient-to-br from-warning-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-fluid-sm font-medium text-gray-500 mb-1">Tingkat pembayaran</div>
            <div id="tingkat-pembayaran-value" class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="tingkat-pembayaran-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
            {{-- <p class="text-fluid-xs text-gray-400 mt-1.5 leading-snug">Terbayar dibanding total nominal tagihan</p> --}}
        </div>
        <div class="relative z-10 bg-gradient-to-br from-warning-400 to-warning-600 p-3 sm:p-4 rounded-xl shadow-lg sm:group-hover:scale-110 transition-all duration-300 shrink-0">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zm3.707 6.293a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l6-6z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <a href="{{ route('admin.pembayaran') }}?status=pending" aria-label="Buka daftar tagihan yang belum lunas"
       class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl active:scale-[0.99] sm:hover:scale-[1.02] transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <div class="text-fluid-sm font-medium text-gray-500 mb-1">Tagihan belum lunas</div>
            <div id="pending-payment-value" class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">...</div>
            <div id="pending-payment-skeleton" class="w-36 h-8 bg-gray-200 rounded-md animate-pulse mt-1"></div>
            {{-- <p class="text-fluid-xs text-gray-400 mt-1.5 leading-snug">Jumlah tagihan dengan status bukan Lunas</p> --}}
        </div>
        <div class="relative z-10 bg-gradient-to-br from-purple-400 to-purple-600 p-3 sm:p-4 rounded-xl shadow-lg group-hover:shadow-xl sm:group-hover:scale-110 transition-all duration-300 shrink-0">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </a>
</div>

    <div class="mt-0 sm:mt-2 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="bg-white p-2.5 sm:p-3 rounded-lg shadow-sm shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-fluid-xl sm:text-fluid-2xl font-bold text-gray-900">Pembayaran terbaru</h2>
                    {{-- <p class="text-fluid-sm text-gray-500 mt-1">Maks. 10 transaksi terakhir; kolom jumlah = nominal pada transaksi tersebut</p> --}}
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5 md:p-6">
        <div class="table-responsive rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200" aria-label="Tabel pembayaran terbaru">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Mahasiswa</th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis Tagihan</th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jumlah bayar</th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-payments-table" class="bg-white divide-y divide-gray-200 text-fluid-sm sm:text-sm">
                    <tr class="skeleton-row">
                        <td class="px-3 sm:px-6 py-4" colspan="5">
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

    /**
     * Kecilkan font nominal sampai muat di kolom teks (samping ikon), tanpa memotong angka.
     */
    function fitTotalPembayaranFont() {
        const el = document.getElementById('total-pembayaran-value');
        if (!el || el.classList.contains('hidden')) return;
        const container = el.parentElement;
        if (!container || container.clientWidth < 8) return;

        const isLg = window.matchMedia('(min-width: 1024px)').matches;
        const maxPx = isLg ? 30 : 22;
        const minPx = 10;

        el.style.fontSize = maxPx + 'px';
        if (el.scrollWidth <= container.clientWidth) return;

        let lo = minPx, hi = maxPx, best = minPx;
        while (lo <= hi) {
            const mid = (lo + hi) >> 1;
            el.style.fontSize = mid + 'px';
            if (el.scrollWidth <= container.clientWidth) {
                best = mid;
                lo = mid + 1;
            } else {
                hi = mid - 1;
            }
        }
        el.style.fontSize = best + 'px';
    }

    function debounce(fn, wait) {
        let t;
        return function () {
            clearTimeout(t);
            t = setTimeout(fn, wait);
        };
    }

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
            pembayaranEl.textContent = formattedPembayaran;
            requestAnimationFrame(() => requestAnimationFrame(fitTotalPembayaranFont));

            toggleSkeleton('tingkat-pembayaran-value', 'tingkat-pembayaran-skeleton', false);
            document.getElementById('tingkat-pembayaran-value').textContent = (data.tingkat_pembayaran || 0) + '%';

            toggleSkeleton('pending-payment-value', 'pending-payment-skeleton', false);
            document.getElementById('pending-payment-value').textContent = data.pending_payment || 0;

        }).catch(error => {
            console.error('Error fetching stats:', error)
            Swal.fire({ icon: 'error', title: 'Gagal Memuat Statistik', text: error.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
        });
    }

    function loadRecentPayments() {
        const tbody = document.getElementById('recent-payments-table');

        function createCell(text, classes = []) {
            const cell = document.createElement('td');
            cell.textContent = text;
            cell.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap ' + classes.join(' ');
            return cell;
        }

        fetchData("{{ route('admin.dashboard.recentPayments') }}").then(response => {
            const data = Array.isArray(response) ? response : (response && response.data ? response.data : []);
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                renderEmptyState(tbody, {
                    colspan: 5,
                    title: 'Tidak ada pembayaran terbaru',
                    message: 'Belum ada riwayat transaksi pembayaran.',
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

                const tagihan = p.tagihan || {};
                const mhs = tagihan.mahasiswa || {};
                const user = mhs.user || {};

                const cellMahasiswa = document.createElement('td');
                cellMahasiswa.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap';
                const divNama = document.createElement('div');
                divNama.className = 'font-medium text-gray-900';
                divNama.textContent = user.nama_lengkap || '—';
                const divNpm = document.createElement('div');
                divNpm.className = 'text-gray-500';
                divNpm.textContent = mhs.npm || '—';
                cellMahasiswa.appendChild(divNama);
                cellMahasiswa.appendChild(divNpm);
                tr.appendChild(cellMahasiswa);

                const jenisLabel = tagihan.jenis_tampilan || (tagihan.tarif && tagihan.tarif.nama_pembayaran) || '—';
                tr.appendChild(createCell(jenisLabel, ['text-gray-700']));

                const cellJumlah = document.createElement('td');
                cellJumlah.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right';
                const bayar = parseFloat(p.jumlah_bayar);
                const tagih = parseFloat(tagihan.jumlah_tagihan);
                const fmt = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number.isFinite(n) ? n : 0);
                const mainEl = document.createElement('div');
                mainEl.className = 'font-medium text-gray-800';
                mainEl.textContent = fmt(bayar);
                cellJumlah.appendChild(mainEl);
                if (Number.isFinite(tagih) && Number.isFinite(bayar) && Math.round(tagih) !== Math.round(bayar)) {
                    const sub = document.createElement('div');
                    sub.className = 'text-fluid-xs text-gray-400 mt-0.5';
                    sub.textContent = 'Tagihan: ' + fmt(tagih);
                    cellJumlah.appendChild(sub);
                }
                tr.appendChild(cellJumlah);

                const tanggalSumber = p.tanggal_bayar;
                const formattedTanggal = tanggalSumber ? new Date(tanggalSumber).toLocaleDateString('id-ID') : '—';
                tr.appendChild(createCell(formattedTanggal, ['text-gray-500']));

                const cellStatus = document.createElement('td');
                cellStatus.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center';
                const t = tagihan;
                const paid = parseFloat(t.total_angsuran) || 0;
                let statusText = t.status;
                let badgeClass = 'bg-yellow-100 text-yellow-800';
                if (t.status === 'Lunas') {
                    statusText = 'Lunas';
                    badgeClass = 'bg-green-100 text-green-800';
                } else if (t.status === 'Menunggu Verifikasi Transfer') {
                    statusText = 'Menunggu Verifikasi';
                    badgeClass = 'bg-amber-100 text-amber-800';
                } else if (t.status === 'Menunggu Pembayaran Tunai') {
                    statusText = 'Menunggu Tunai';
                    badgeClass = 'bg-blue-100 text-blue-800';
                } else if (t.status === 'Ditolak') {
                    badgeClass = 'bg-orange-100 text-orange-800';
                } else if (t.status === 'Belum Lunas' || t.status === 'Belum Dibayarkan') {
                    statusText = paid > 0 ? 'Belum Lunas' : 'Belum Dibayarkan';
                    badgeClass = paid > 0 ? 'bg-amber-100 text-amber-800' : 'bg-yellow-100 text-yellow-800';
                }
                const statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}">${statusText}</span>`;

                cellStatus.innerHTML = statusBadge;
                tr.appendChild(cellStatus);

                tbody.appendChild(tr);
            });
        }).catch(error => {
            console.error('Error fetching recent payments:', error);
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat data pembayaran terbaru. ${error.message}</td></tr>`;
        });
    }

    window.addEventListener('resize', debounce(fitTotalPembayaranFont, 120));

    loadStats();
    loadRecentPayments();
});
</script>
@endpush
