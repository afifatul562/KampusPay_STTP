@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
    $emptyIcon = '<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-page-header
        title="Laporan"
        subtitle="Lihat dan ekspor laporan pembayaran"
        :icon="$headerIcon">
    </x-page-header>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tab-bulanan" class="tab-button active border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Laporan Bulanan
            </button>
            <button id="tab-pembayaran" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Laporan Pembayaran
            </button>
        </nav>
    </div>

    {{-- Tab Content: Laporan Bulanan --}}
    <div id="content-bulanan" class="tab-content">

    <!-- Filter Card -->
    <x-card title="Pilih Periode Laporan">
        <x-slot:header>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="{{ route('kasir.laporan.exportCsv', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}">
                    <x-gradient-button variant="success" size="md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-14a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Ekspor CSV
                    </x-gradient-button>
                </a>
                <a href="{{ route('kasir.laporan.exportPdf', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}">
                    <x-gradient-button variant="danger" size="md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Ekspor PDF
                    </x-gradient-button>
                </a>
            </div>
        </x-slot:header>
        <form action="{{ route('kasir.laporan.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-auto">
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" id="tahun" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <x-gradient-button type="submit" variant="primary" size="md" class="w-full">Tampilkan</x-gradient-button>
            </div>
        </form>
    </x-card>

    <!-- Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Total Penerimaan</div>
                <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
         <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Jumlah Transaksi</div>
                <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">{{ $jumlahTransaksi }}</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path></svg>
            </div>
        </div>
    </div>

    <!-- Tabel Rangkuman -->
    @if($laporanPerJenis->count() > 0)
        <x-data-table
            title="Rangkuman per Jenis Pembayaran"
            :headers="['Jenis Pembayaran', 'Jumlah Transaksi', 'Total Nominal']"
            aria-label="Tabel rangkuman jenis pembayaran">
            @foreach ($laporanPerJenis as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama_pembayaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700">{{ $item->jumlah_transaksi }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800 font-medium">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @else
        <x-empty-state
            title="Tidak ada data"
            message="Tidak ada data untuk periode ini."
            :icon="$emptyIcon" />
    @endif
    </div>

    {{-- Tab Content: Laporan Pembayaran --}}
    <div id="content-pembayaran" class="tab-content hidden">
        <x-card title="Generate Laporan Pembayaran">
            <form id="reportForm" class="space-y-4">
                <div>
                    <label for="jenis_laporan_pembayaran" class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                    <select id="jenis_laporan_pembayaran" name="jenis_laporan" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="pembayaran" selected>Laporan Pembayaran</option>
                        <option value="tunggakan">Laporan Tunggakan</option>
                    </select>
                </div>
                <div>
                    <label for="tahun_pembayaran" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <select id="tahun_pembayaran" name="tahun" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></select>
                </div>
                <x-gradient-button type="submit" id="viewReportBtn" variant="primary" size="md" class="w-full" aria-label="Tampilkan Laporan">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    View Laporan
                </x-gradient-button>
                <x-gradient-button type="button" id="generatePdfBtn" variant="success" size="md" class="hidden w-full mt-2" aria-label="Generate PDF dan simpan riwayat">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generate PDF & Simpan Riwayat
                </x-gradient-button>
            </form>
        </x-card>

        {{-- Area Preview --}}
        <x-card id="reportPreviewArea" class="hidden" title="Preview Laporan" aria-live="polite">
            <div id="reportPreviewContent" class="overflow-x-auto">
                <p class="text-gray-500">Data preview akan muncul di sini...</p>
            </div>
        </x-card>

        {{-- Tabel Riwayat Laporan --}}
        <x-data-table
            title="Riwayat Laporan"
            :headers="['Tanggal Dibuat', 'Jenis Laporan', 'Periode', 'Nama File', 'Aksi']"
            aria-label="Tabel riwayat laporan">
            <tr id="loading-history-row">
                <td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td>
            </tr>
        </x-data-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBulanan = document.getElementById('tab-bulanan');
    const tabPembayaran = document.getElementById('tab-pembayaran');
    const contentBulanan = document.getElementById('content-bulanan');
    const contentPembayaran = document.getElementById('content-pembayaran');

    function switchTab(activeTab) {
        // Reset all tabs
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-primary-500', 'text-primary-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Activate selected tab
        if (activeTab === 'bulanan') {
            tabBulanan.classList.add('active', 'border-primary-500', 'text-primary-600');
            tabBulanan.classList.remove('border-transparent', 'text-gray-500');
            contentBulanan.classList.remove('hidden');
        } else {
            tabPembayaran.classList.add('active', 'border-primary-500', 'text-primary-600');
            tabPembayaran.classList.remove('border-transparent', 'text-gray-500');
            contentPembayaran.classList.remove('hidden');
        }
    }

    tabBulanan.addEventListener('click', () => switchTab('bulanan'));
    tabPembayaran.addEventListener('click', () => switchTab('pembayaran'));

    // Laporan Pembayaran Logic
    const reportForm = document.getElementById('reportForm');
    const viewReportBtn = document.getElementById('viewReportBtn');
    const generatePdfBtn = document.getElementById('generatePdfBtn');
    const reportPreviewArea = document.getElementById('reportPreviewArea');
    const reportPreviewContent = document.getElementById('reportPreviewContent');
    const tahunSelect = document.getElementById('tahun_pembayaran');

    // Populate tahun
    (function populateYears(){
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 4;
        tahunSelect.innerHTML = '<option value="" disabled selected>Pilih Tahun</option>';
        for (let y = currentYear; y >= startYear; y--) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            tahunSelect.appendChild(opt);
        }
    })();

    const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    async function apiRequest(url, method = 'GET', body = null) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${apiToken}`,
                'X-CSRF-TOKEN': csrfToken,
            }
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const resp = await fetch(url, opts);
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok) {
            throw new Error(data.message || 'Permintaan gagal');
        }
        return data;
    }

    let currentPreviewData = null;
    let currentReportParams = null;

    const jenisLaporanSelect = document.getElementById('jenis_laporan_pembayaran');

    reportForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const tahun = tahunSelect.value;
        const jenisLaporan = jenisLaporanSelect.value;
        if (!tahun) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Silakan pilih tahun terlebih dahulu.' });
            return;
        }

        viewReportBtn.disabled = true;
        viewReportBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memuat...';

        try {
            const endpoint = jenisLaporan === 'tunggakan'
                ? `/api/kasir/reports/tunggakan/preview?tahun=${encodeURIComponent(tahun)}`
                : `/api/kasir/reports/preview?tahun=${encodeURIComponent(tahun)}`;
            const response = await apiRequest(endpoint);
            currentPreviewData = response.data || [];
            currentReportParams = { tahun: parseInt(tahun), jenis_laporan: jenisLaporan };

            if (currentPreviewData.length === 0) {
                reportPreviewContent.innerHTML = '<p class="text-gray-500 text-center py-8">Tidak ada data untuk tahun yang dipilih.</p>';
                reportPreviewArea.classList.remove('hidden');
                generatePdfBtn.classList.add('hidden');
            } else {
                renderPreviewTable(currentPreviewData, jenisLaporan);
                reportPreviewArea.classList.remove('hidden');
                generatePdfBtn.classList.remove('hidden');
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Gagal memuat data laporan.' });
        } finally {
            viewReportBtn.disabled = false;
            viewReportBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>View Laporan';
        }
    });

    function renderPreviewTable(data, jenisLaporan = 'pembayaran') {
        reportPreviewContent.innerHTML = '';

        if (!data || data.length === 0) {
            reportPreviewContent.innerHTML = `
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-4 text-gray-500">Tidak ada data untuk laporan ini.</p>
                </div>
            `;
            return;
        }

        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200 text-sm';
        const thead = document.createElement('thead');
        thead.className = 'bg-gray-50';
        const tbody = document.createElement('tbody');
        tbody.className = 'bg-white divide-y divide-gray-200';

        // Helper functions
        function createHeaderCell(text, alignRight = false) {
            const th = document.createElement('th');
            th.scope = 'col';
            th.className = 'px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
            if (alignRight) th.classList.add('text-right');
            th.textContent = text;
            return th;
        }

        function createDataCell(text, alignRight = false, extraClasses = []) {
            const td = document.createElement('td');
            td.className = 'px-4 py-2 ' + extraClasses.join(' ');
            if (alignRight) td.classList.add('text-right');
            td.textContent = text ?? '-';
            return td;
        }

        function createStatusCell(statusText, isLunasOrAktif) {
            const td = document.createElement('td');
            td.className = 'px-4 py-2 text-center';
            let bgColor = isLunasOrAktif ? 'bg-green-100' : 'bg-yellow-100';
            let textColor = isLunasOrAktif ? 'text-green-800' : 'text-yellow-800';
            if (statusText === 'Belum Lunas') {
                bgColor = 'bg-orange-100';
                textColor = 'text-orange-800';
            }
            td.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${bgColor} ${textColor}">${statusText}</span>`;
            return td;
        }

        function createMahasiswaCell(nama, npm) {
            const td = document.createElement('td');
            td.className = 'px-4 py-2';
            const divNama = document.createElement('div');
            divNama.textContent = nama ?? '-';
            const divNpm = document.createElement('div');
            divNpm.className = 'text-gray-500';
            divNpm.textContent = npm ?? '';
            td.appendChild(divNama);
            td.appendChild(divNpm);
            return td;
        }

        // Header
        const headerRow = document.createElement('tr');
        headerRow.appendChild(createHeaderCell('Kode Pembayaran'));
        headerRow.appendChild(createHeaderCell('Mahasiswa'));
        headerRow.appendChild(createHeaderCell('Jenis Tagihan'));
        headerRow.appendChild(createHeaderCell('Jumlah', true));
        headerRow.appendChild(createHeaderCell('Status'));
        headerRow.appendChild(createHeaderCell('Tgl Bayar'));
        thead.appendChild(headerRow);

        const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

        // Data rows
        data.forEach(item => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            const pembayaranAll = item.pembayaranAll || [];
            const totalAngsuran = item.total_angsuran || 0;
            const sisaPokok = item.sisa_pokok !== undefined ? item.sisa_pokok : (item.jumlah_tagihan || 0) - totalAngsuran;

            // Tentukan status berdasarkan total_angsuran dan sisa_pokok
            let statusText = 'Belum Dibayarkan';
            if (item.status === 'Lunas' || sisaPokok <= 0) {
                statusText = 'Lunas';
            } else if (totalAngsuran > 0) {
                // Sudah ada cicilan tapi belum lunas
                statusText = 'Belum Lunas';
            } else {
                // Belum ada pembayaran sama sekali
                statusText = 'Belum Dibayarkan';
            }

            const isLunas = item.status === 'Lunas' || sisaPokok <= 0;

            let tglBayar = '-';
            if (totalAngsuran > 0 && pembayaranAll.length > 0) {
                const pembayaranTerakhir = pembayaranAll.sort((a, b) =>
                    new Date(b.tanggal_bayar) - new Date(a.tanggal_bayar)
                )[0];
                if (pembayaranTerakhir && pembayaranTerakhir.tanggal_bayar) {
                    tglBayar = new Date(pembayaranTerakhir.tanggal_bayar).toLocaleDateString('id-ID');
                }
            }

            // Di preview, selalu tampilkan jumlah_tagihan (bukan sisa_pokok atau total_angsuran)
            const jumlahTampil = item.jumlah_tagihan || 0;

            row.appendChild(createDataCell(item.kode_pembayaran));
            row.appendChild(createMahasiswaCell(item.mahasiswa?.user?.nama_lengkap, item.mahasiswa?.npm));
            row.appendChild(createDataCell(item.tarif?.nama_pembayaran));
            row.appendChild(createDataCell(rupiahFormat.format(jumlahTampil), true));

            row.appendChild(createStatusCell(statusText, isLunas));
            row.appendChild(createDataCell(tglBayar));

            tbody.appendChild(row);
        });

        table.appendChild(thead);
        table.appendChild(tbody);
        reportPreviewContent.appendChild(table);
    }

    generatePdfBtn.addEventListener('click', async function() {
        if (!currentReportParams) {
            Swal.fire({ icon: 'warning', title: 'Aksi Belum Sesuai', text: 'Harap klik "View Laporan" terlebih dahulu.' });
            return;
        }

        const submitButton = this;
        const originalButtonHTML = submitButton.innerHTML;
        submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Membuat PDF...`;
        submitButton.disabled = true;

        try {
            const endpoint = currentReportParams.jenis_laporan === 'tunggakan'
                ? `/api/kasir/reports/tunggakan/generate`
                : `/api/kasir/reports/generate`;
            const response = await apiRequest(endpoint, 'POST', {
                tahun: String(currentReportParams.tahun)
            });

            if (response.success || response.message?.includes('berhasil')) {
                Swal.fire({ icon: 'success', title: 'Berhasil Dibuat!', text: response.message || 'Laporan PDF berhasil dibuat!', timer: 1500, showConfirmButton: false });
                loadReportHistory();
                reportPreviewArea.classList.add('hidden');
                submitButton.classList.add('hidden');
                currentPreviewData = null;
                currentReportParams = null;
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Membuat PDF', text: response.message || 'Error tidak diketahui' });
            }
        } catch (err) {
            let errorContent;
            if (err.status === 422 && err.errors) {
                errorContent = document.createElement('div');
                const p = document.createElement('p');
                p.textContent = "Input tidak valid:";
                errorContent.appendChild(p);
                const ul = document.createElement('ul');
                ul.className = 'list-disc list-inside text-left mt-2';
                Object.values(err.errors).forEach(e => {
                    const li = document.createElement('li');
                    li.textContent = e.join(', ');
                    ul.appendChild(li);
                });
                errorContent.appendChild(ul);
            } else {
                errorContent = 'Terjadi kesalahan saat membuat PDF: ' + (err.message || 'Error tidak diketahui');
            }
            Swal.fire({ icon: 'error', title: 'Error PDF', html: errorContent });
        } finally {
            submitButton.innerHTML = originalButtonHTML;
            submitButton.disabled = false;
        }
    });

    // Load riwayat laporan
    const reportHistoryTableElement = document.querySelector('table[aria-label="Tabel riwayat laporan"]');
    const reportHistoryTable = reportHistoryTableElement ? reportHistoryTableElement.querySelector('tbody') : null;

    function loadReportHistory() {
        if (!reportHistoryTable) return;

        const historyUrl = `/api/kasir/reports/riwayat`;
        reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td></tr>';

        function createHistoryCell(text, classes = []) {
            const td = document.createElement('td');
            td.className = 'px-6 py-4 whitespace-nowrap ' + classes.join(' ');
            td.textContent = text ?? '-';
            return td;
        }

        apiRequest(historyUrl).then(response => {
            const data = response.data || [];
            reportHistoryTable.innerHTML = '';

            if (!data || data.length === 0) {
                reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada laporan yang dibuat. Silakan generate laporan baru.</td></tr>';
                return;
            }

            data.forEach(report => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';

                const reportId = report.id;
                const viewUrl = `/kasir/reports/download/${reportId}?view=1`;
                const downloadUrl = `/kasir/reports/download/${reportId}`;

                // Format jenis laporan untuk display
                let jenisLaporan = report.jenis_laporan;
                if (report.jenis_laporan === 'pembayaran-kasir') {
                    jenisLaporan = 'Laporan Pembayaran';
                } else if (report.jenis_laporan === 'tunggakan-kasir') {
                    jenisLaporan = 'Laporan Tunggakan';
                }

                tr.appendChild(createHistoryCell(new Date(report.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' }), ['text-gray-700']));
                tr.appendChild(createHistoryCell(jenisLaporan, ['font-medium', 'text-gray-900']));
                tr.appendChild(createHistoryCell(report.periode || '-', ['text-gray-500']));
                tr.appendChild(createHistoryCell(report.file_name || '-', ['text-gray-500', 'font-mono']));

                const cellAksi = document.createElement('td');
                cellAksi.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
                cellAksi.innerHTML = `
                    <div class="flex justify-end items-center gap-3">
                        <a href="${viewUrl}" target="_blank" class="text-blue-600 hover:text-blue-900" title="Lihat PDF">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                        <a href="${downloadUrl}" download class="text-green-600 hover:text-green-900" title="Download PDF">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </a>
                        <button class="delete-report-btn text-red-600 hover:text-red-900" data-id="${reportId}" title="Hapus Riwayat & File">
                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                `;
                tr.appendChild(cellAksi);
                reportHistoryTable.appendChild(tr);
            });

            // Attach event listeners untuk tombol delete
            document.querySelectorAll('.delete-report-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const reportId = this.getAttribute('data-id');
                    const fileName = this.closest('tr')?.querySelector('td:nth-child(4)')?.textContent || `ID ${reportId}`;
                    deleteReport(reportId, fileName);
                });
            });

        }).catch(error => {
            console.error('Error fetching report history:', error);
            Swal.fire({ icon: 'error', title: 'Gagal Memuat Riwayat', text: `Gagal memuat riwayat laporan: ${error.message}` });
            reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat riwayat.</td></tr>';
        });
    }

    function deleteReport(reportId, fileName) {
        Swal.fire({
            title: 'Anda Yakin?',
            text: `Yakin hapus riwayat & file "${fileName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                apiRequest(`/api/kasir/reports/${reportId}`, 'DELETE').then(response => {
                    Swal.fire({ icon: 'success', title: 'Dihapus!', text: response.message || 'Laporan berhasil dihapus.', timer: 1500, showConfirmButton: false });
                    loadReportHistory();
                }).catch(err => {
                    Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: 'Gagal menghapus laporan: ' + err.message });
                });
            }
        });
    }

    // Load riwayat saat halaman dimuat
    if (reportHistoryTable) {
        loadReportHistory();
    }
});
</script>
@endpush
