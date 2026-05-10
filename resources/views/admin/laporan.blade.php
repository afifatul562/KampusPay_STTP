@extends('layouts.app')

@section('title', 'Admin - Laporan')
@section('page-title', 'Laporan')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
@endphp

<div class="space-y-4 sm:space-y-6">
    <x-page-header
        title="Laporan"
        subtitle="Generate dan kelola laporan data mahasiswa serta pembayaran"
        :icon="$headerIcon">
    </x-page-header>

    <x-card title="Generate Laporan">
        <form id="reportForm" class="space-y-4">
            {{-- Form fields --}}
            <div>
                <label for="jenis_laporan" class="block text-fluid-sm font-medium text-gray-700">Jenis Laporan</label>
                <select id="jenis_laporan" name="jenis_laporan" required class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" disabled selected>Pilih Jenis Laporan</option>
                    <option value="mahasiswa">Data Mahasiswa</option>
                    <option value="pembayaran">Laporan Pembayaran</option>
                </select>
            </div>
            {{-- Tahun dihitung otomatis dari Tahun Akademik --}}
            <input type="hidden" id="tahun" name="tahun" value="">
            <div id="semester_pembayaran_wrap" class="hidden">
                <label for="semester_pembayaran" class="block text-fluid-sm font-medium text-gray-700">Semester <span class="text-gray-500 font-normal">(opsional, laporan pembayaran)</span></label>
                <select id="semester_pembayaran" name="semester" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" selected>Semua semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>
            <div>
                <label for="tahun_akademik" class="block text-fluid-sm font-medium text-gray-700">Tahun Akademik</label>
                <select id="tahun_akademik" name="tahun_akademik" required class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm"></select>
            </div>
            <div>
                <label for="angkatan" class="block text-fluid-sm font-medium text-gray-700">Angkatan <span class="text-gray-500 font-normal">(opsional)</span></label>
                <select id="angkatan" name="angkatan" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" selected>Semua angkatan</option>
                </select>
            </div>
            <div>
                <label for="program_studi" class="block text-fluid-sm font-medium text-gray-700">Program Studi <span class="text-gray-500 font-normal">(opsional)</span></label>
                <select id="program_studi" name="program_studi" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" selected>Semua program studi</option>
                </select>
            </div>
            {{-- Tombol View --}}
            <x-gradient-button type="submit" id="viewReportBtn" variant="primary" size="md" class="w-full" aria-label="Tampilkan Laporan">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                View Laporan
            </x-gradient-button>
            {{-- Tombol Generate PDF --}}
            <x-gradient-button type="button" id="generatePdfBtn" variant="success" size="md" class="hidden w-full mt-2" aria-label="Generate PDF dan simpan riwayat">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Generate PDF & Simpan Riwayat
            </x-gradient-button>
        </form>
    </x-card>

    {{-- Area Preview --}}
    <x-card id="reportPreviewArea" class="hidden" title="Preview Laporan" aria-live="polite">
        <div id="reportPreviewContent" class="table-responsive rounded-lg">
            <p class="text-gray-500">Data preview akan muncul di sini...</p>
        </div>
    </x-card>

    {{-- Tabel Riwayat --}}
    <x-data-table
        title="Riwayat Laporan"
        :headers="['Tanggal Dibuat', 'Jenis Laporan', 'Periode', 'Nama File', 'Aksi']"
        aria-label="Tabel riwayat laporan">
        <tr id="loading-history-row">
            <td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td>
        </tr>
    </x-data-table>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        const reportForm = document.getElementById('reportForm');
        const viewReportBtn = document.getElementById('viewReportBtn');
        const generatePdfBtn = document.getElementById('generatePdfBtn');
        const reportPreviewArea = document.getElementById('reportPreviewArea');
        const reportPreviewContent = document.getElementById('reportPreviewContent');
        const reportHistoryTableElement = document.querySelector('table[aria-label="Tabel riwayat laporan"]');
        const reportHistoryTable = reportHistoryTableElement ? reportHistoryTableElement.querySelector('tbody') : null;
        const tahunSelect = document.getElementById('tahun');
        const jenisLaporanSelect = document.getElementById('jenis_laporan');
        const semesterPembayaranWrap = document.getElementById('semester_pembayaran_wrap');
        const tahunAkademikSelect = document.getElementById('tahun_akademik');
        const angkatanSelect = document.getElementById('angkatan');
        const prodiSelect = document.getElementById('program_studi');

        let currentPreviewData = null;
        let currentReportParams = null;

        const previewUrl = "{{ route('admin.reports.preview') }}";
        const generateUrl = "{{ route('admin.reports.store') }}";
        const historyUrl = "{{ route('admin.reports.index') }}";
        const deleteReportUrlTpl = "{{ route('admin.reports.destroy', ['report' => ':id']) }}";

        // Tahun tidak dipilih manual; diset dari Tahun Akademik (2026/2027 -> 2026)

        function syncSemesterFieldVisibility() {
            if (!semesterPembayaranWrap || !jenisLaporanSelect) return;
            semesterPembayaranWrap.classList.toggle('hidden', jenisLaporanSelect.value !== 'pembayaran');
        }
        if (jenisLaporanSelect) {
            jenisLaporanSelect.addEventListener('change', syncSemesterFieldVisibility);
            syncSemesterFieldVisibility();
        }

        function buildReportPayload(form) {
            const jenis = form.elements.jenis_laporan.value;
            const ay = tahunAkademikSelect && tahunAkademikSelect.value ? String(tahunAkademikSelect.value).trim() : '';
            const matchAy = /^(\d{4})\/(\d{4})$/.exec(ay);
            const tahun = matchAy ? matchAy[1] : '';
            if (tahunSelect) tahunSelect.value = tahun;

            const payload = { jenis_laporan: jenis, tahun: tahun, tahun_akademik: ay };
            if (jenis === 'pembayaran') {
                const semEl = document.getElementById('semester_pembayaran');
                const s = semEl && semEl.value ? String(semEl.value).trim() : '';
                if (s) payload.semester = s;
            }
            const ang = angkatanSelect && angkatanSelect.value ? String(angkatanSelect.value).trim() : '';
            const prodi = prodiSelect && prodiSelect.value ? String(prodiSelect.value).trim() : '';
            if (ang) payload.angkatan = ang;
            if (prodi) payload.program_studi = prodi;
            return payload;
        }

        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) {
            console.error('apiRequest util tidak tersedia');
            return;
        }

        (function populateAcademicYears() {
            if (!tahunAkademikSelect) return;
            const nowYear = new Date().getFullYear();
            const startYear = nowYear - 4;
            tahunAkademikSelect.innerHTML = '';
            for (let y = nowYear; y >= startYear; y--) {
                const ay = `${y}/${y + 1}`;
                const opt = document.createElement('option');
                opt.value = ay;
                opt.textContent = ay;
                tahunAkademikSelect.appendChild(opt);
            }
            // Default: tahun akademik berjalan
            tahunAkademikSelect.value = `${nowYear}/${nowYear + 1}`;
        })();

        (async function populateAngkatanProdi() {
            if (!angkatanSelect || !prodiSelect) return;
            try {
                const mahasiswaUrl = "{{ route('admin.mahasiswa.index') }}";
                const resp = await apiRequest(mahasiswaUrl);
                const list = resp.data || resp || [];
                const angkatanSet = new Set();
                const prodiSet = new Set();
                list.forEach(m => {
                    if (m && m.angkatan) angkatanSet.add(String(m.angkatan));
                    if (m && m.program_studi) prodiSet.add(String(m.program_studi));
                });
                const angkatan = Array.from(angkatanSet).sort().reverse();
                const prodi = Array.from(prodiSet).sort();
                angkatanSelect.innerHTML = '<option value="" selected>Semua angkatan</option>';
                angkatan.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a;
                    opt.textContent = a;
                    angkatanSelect.appendChild(opt);
                });
                prodiSelect.innerHTML = '<option value="" selected>Semua program studi</option>';
                prodi.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    prodiSelect.appendChild(opt);
                });
            } catch (e) {
                console.warn('Gagal memuat master mahasiswa untuk filter laporan', e);
            }
        })();

        function loadReportHistory() {
            if (!reportHistoryTable) return;
            reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td></tr>';

            function createHistoryCell(text, classes = []) {
                const td = document.createElement('td');
                td.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap ' + classes.join(' ');
                td.textContent = text ?? '-';
                return td;
            }

            apiRequest(historyUrl).then(response => {
                const data = response.data || response;
                reportHistoryTable.innerHTML = '';
                if (!data || data.length === 0) {
                    renderEmptyState(reportHistoryTable, {
                        colspan: 5,
                        title: 'Belum ada laporan',
                        message: 'Belum ada laporan yang dibuat. Silakan generate laporan baru.',
                        icon: `
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        `
                    });
                    return;
                }

                data.forEach(report => {
                    const tr = document.createElement('tr'); tr.className = 'hover:bg-gray-50';
                    const reportId = report.id || report.report_id; if (!reportId) return;
                    const baseUrl = "{{ route('admin.reports.download', ['report' => ':id']) }}".replace(':id', reportId);
                    const viewUrl = baseUrl + '?view=1'; const downloadUrl = baseUrl;

                    tr.appendChild(createHistoryCell(new Date(report.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' }), ['text-gray-700']));
                    tr.appendChild(createHistoryCell(report.jenis_laporan, ['font-medium', 'text-gray-900']));
                    tr.appendChild(createHistoryCell(report.periode, ['text-gray-500']));
                    tr.appendChild(createHistoryCell(report.file_name, ['text-gray-500', 'font-mono']));

                    const cellAksi = document.createElement('td'); cellAksi.className = 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right text-fluid-sm sm:text-sm font-medium';
                    cellAksi.innerHTML = `
                        <div class="flex justify-end items-center gap-3">
                            <a href="${viewUrl}" target="_blank" class="text-blue-600 hover:text-blue-900" title="Lihat PDF"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a>
                            <a href="${downloadUrl}" download class="text-green-600 hover:text-green-900" title="Download PDF"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg></a>
                            <button class="delete-report-btn text-red-600 hover:text-red-900" data-id="${reportId}" title="Hapus Riwayat & File"><svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </div>`;
                    tr.appendChild(cellAksi);
                    reportHistoryTable.appendChild(tr);
                });
            }).catch(error => {
                console.error('Error fetching report history:', error);
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Riwayat', text: `Gagal memuat riwayat laporan: ${error.message}` });
                reportHistoryTable.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat riwayat.</td></tr>`;
            });
        }

        function displayReportPreview(data, type) {
            reportPreviewContent.innerHTML = '';
            if (!data || (Array.isArray(data) && data.length === 0) || (typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length === 0) ) {
                renderEmptyState(reportPreviewContent, {
                    title: 'Tidak ada data',
                    message: 'Tidak ada data untuk laporan ini.',
                    icon: `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    `
                });
                return;
            }

            const table = document.createElement('table');
            table.className = 'min-w-full divide-y divide-gray-200 text-sm';
            const thead = document.createElement('thead');
            thead.className = 'bg-gray-50';
            const tbody = document.createElement('tbody');
            tbody.className = 'bg-white divide-y divide-gray-200';
            let headers = [];

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
                let bgColor = isLunasOrAktif ? 'bg-green-100' : 'bg-red-100';
                let textColor = isLunasOrAktif ? 'text-green-800' : 'text-red-800';
                if (type === 'pembayaran' && !isLunasOrAktif) {
                    bgColor = 'bg-yellow-100';
                    textColor = 'text-yellow-800';
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

            const headerRow = document.createElement('tr');
            if (type === 'mahasiswa') {
                headers = ['NPM', 'Nama Lengkap', 'Email', 'Program Studi', 'Angkatan', 'Status'];
                headerRow.appendChild(createHeaderCell('NPM'));
                headerRow.appendChild(createHeaderCell('Nama Lengkap'));
                headerRow.appendChild(createHeaderCell('Email'));
                headerRow.appendChild(createHeaderCell('Program Studi'));
                headerRow.appendChild(createHeaderCell('Angkatan'));
                headerRow.appendChild(createHeaderCell('Status'));
            } else if (type === 'pembayaran') {
                headers = ['Kode Pembayaran', 'Mahasiswa', 'Jenis Tagihan', 'Jumlah', 'Status', 'Tgl Bayar'];
                headerRow.appendChild(createHeaderCell('Kode Pembayaran'));
                headerRow.appendChild(createHeaderCell('Mahasiswa'));
                headerRow.appendChild(createHeaderCell('Jenis Tagihan'));
                headerRow.appendChild(createHeaderCell('Jumlah', true));
                headerRow.appendChild(createHeaderCell('Status'));
                headerRow.appendChild(createHeaderCell('Tgl Bayar'));
            }
            thead.appendChild(headerRow);

            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

            if (type === 'mahasiswa' && Array.isArray(data)) {
                // Data mahasiswa: array datar
                data.forEach((item, index) => {
                    const dataRow = document.createElement('tr');
                    dataRow.appendChild(createDataCell(item.npm));
                    dataRow.appendChild(createDataCell(item.user?.nama_lengkap));
                    dataRow.appendChild(createDataCell(item.user?.email));
                    dataRow.appendChild(createDataCell(item.program_studi));
                    dataRow.appendChild(createDataCell(item.angkatan));
                    dataRow.appendChild(createStatusCell(item.status || '-', item.status === 'Aktif'));
                    tbody.appendChild(dataRow);
                });
            } else if (type === 'mahasiswa' && typeof data === 'object' && !Array.isArray(data)) {
                // Format lama: data terkelompok per semester
                Object.entries(data).sort(([semA], [semB]) => semA - semB).forEach(([semester, mahasiswaGroup]) => {
                    const semesterRow = document.createElement('tr');
                    const semesterCell = document.createElement('td');
                    semesterCell.colSpan = headers.length;
                    semesterCell.className = 'px-4 py-2 font-semibold text-gray-700 bg-gray-100';
                    semesterCell.textContent = `Semester ${semester}`;
                    semesterRow.appendChild(semesterCell);
                    tbody.appendChild(semesterRow);

                    mahasiswaGroup.forEach(item => {
                        const dataRow = document.createElement('tr');
                        dataRow.appendChild(createDataCell(item.npm));
                        dataRow.appendChild(createDataCell(item.user?.nama_lengkap));
                        dataRow.appendChild(createDataCell(item.user?.email));
                        dataRow.appendChild(createDataCell(item.program_studi));
                        dataRow.appendChild(createDataCell(item.angkatan));
                        dataRow.appendChild(createStatusCell(item.status || '-', item.status === 'Aktif'));
                        tbody.appendChild(dataRow);
                    });
                });
            } else if (Array.isArray(data)) {
                data.forEach(item => {
                    const dataRow = document.createElement('tr');
                    if (type === 'pembayaran') {
                        let statusText = item.status === 'Belum Lunas' ? 'Belum Dibayarkan' : item.status;
                        const isLunas = item.status === 'Lunas';
                        const tglBayar = item.pembayaran ? new Date(item.pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-';

                        dataRow.appendChild(createDataCell(item.kode_pembayaran));
                        dataRow.appendChild(createMahasiswaCell(item.mahasiswa?.user?.nama_lengkap, item.mahasiswa?.npm));
                        dataRow.appendChild(createDataCell(item.jenis_tampilan || item.tarif?.nama_pembayaran));
                        dataRow.appendChild(createDataCell(rupiahFormat.format(item.jumlah_tagihan || 0), true));
                        dataRow.appendChild(createStatusCell(statusText, isLunas));
                        dataRow.appendChild(createDataCell(tglBayar));
                    }
                    tbody.appendChild(dataRow);
                });
            }

            table.appendChild(thead);
            table.appendChild(tbody);
            reportPreviewContent.appendChild(table);
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
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) return;
                const deleteUrl = deleteReportUrlTpl.replace(':id', String(reportId));
                apiRequest(deleteUrl, 'DELETE')
                    .then((response) => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus!',
                            text: response.message || 'Laporan berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        loadReportHistory();
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Hapus',
                            text: 'Gagal menghapus laporan: ' + (err && err.message ? err.message : ''),
                        });
                    });
            });
        }

        if (!reportForm || !viewReportBtn || !generatePdfBtn || !reportPreviewArea || !reportPreviewContent) {
            console.error('Elemen form laporan tidak lengkap');
        }

        if (reportForm && viewReportBtn && generatePdfBtn && reportPreviewArea && reportPreviewContent) {
            reportForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = buildReportPayload(this);
                if (!formData.jenis_laporan || !formData.tahun_akademik || !formData.tahun) {
                    Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Harap pilih jenis laporan dan Tahun Akademik.' });
                    return;
                }
                currentReportParams = formData;
                const submitButton = viewReportBtn;
                const originalButtonHTML = submitButton.innerHTML;
                submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memuat Preview...`;
                submitButton.disabled = true;
                generatePdfBtn.classList.add('hidden');
                reportPreviewArea.classList.add('hidden');

                apiRequest(previewUrl, 'POST', formData)
                    .then(response => {
                        currentPreviewData = response.data || response;
                        const hasData = currentPreviewData && (
                            (Array.isArray(currentPreviewData) && currentPreviewData.length > 0) ||
                            (typeof currentPreviewData === 'object' && !Array.isArray(currentPreviewData) && Object.keys(currentPreviewData).length > 0)
                        );
                        if (hasData) {
                            displayReportPreview(currentPreviewData, formData.jenis_laporan);
                            reportPreviewArea.classList.remove('hidden');
                            generatePdfBtn.classList.remove('hidden');
                            generatePdfBtn.classList.add('flex', 'justify-center', 'items-center');
                        } else {
                            renderEmptyState(reportPreviewContent, {
                                title: 'Tidak ada data preview',
                                message: 'Tidak ada data preview untuk periode ini.',
                                icon: `
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        `,
                            });
                            reportPreviewArea.classList.remove('hidden');
                            generatePdfBtn.classList.add('hidden');
                        }
                    })
                    .catch(err => {
                        let errorContent;
                        if (err && err.status === 422 && err.errors) {
                            errorContent = document.createElement('div');
                            const p = document.createElement('p');
                            p.textContent = 'Input tidak valid:';
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
                            errorContent = 'Gagal memuat preview laporan: ' + ((err && err.message) ? err.message : 'Error tidak diketahui');
                        }
                        Swal.fire({ icon: 'error', title: 'Gagal Memuat Preview', html: errorContent });
                        reportPreviewContent.innerHTML = '<p class="text-center text-red-500">Gagal memuat preview.</p>';
                        reportPreviewArea.classList.remove('hidden');
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonHTML;
                        submitButton.disabled = false;
                    });
            });
        }

        if (generatePdfBtn && reportPreviewArea) {
            generatePdfBtn.addEventListener('click', function() {
                if (!currentReportParams) {
                    Swal.fire({ icon: 'warning', title: 'Aksi Belum Sesuai', text: 'Harap klik "View Laporan" terlebih dahulu.' });
                    return;
                }
                const submitButton = this;
                const originalButtonHTML = submitButton.innerHTML;
                submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Membuat PDF...`;
                submitButton.disabled = true;

                apiRequest(generateUrl, 'POST', currentReportParams)
                    .then(response => {
                        if (response.success || (response.message && response.message.includes('berhasil'))) {
                            Swal.fire({ icon: 'success', title: 'Berhasil Dibuat!', text: response.message || 'Laporan PDF berhasil dibuat!', timer: 1500, showConfirmButton: false });
                            loadReportHistory();
                            reportPreviewArea.classList.add('hidden');
                            submitButton.classList.add('hidden');
                            currentPreviewData = null;
                            currentReportParams = null;
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal Membuat PDF', text: response.message || 'Error tidak diketahui' });
                        }
                    })
                    .catch(err => {
                        let errorContent;
                        if (err && err.status === 422 && err.errors) {
                            errorContent = document.createElement('div');
                            const p = document.createElement('p');
                            p.textContent = 'Input tidak valid:';
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
                            errorContent = 'Terjadi kesalahan saat membuat PDF: ' + ((err && err.message) ? err.message : 'Error tidak diketahui');
                        }
                        Swal.fire({ icon: 'error', title: 'Error PDF', html: errorContent });
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonHTML;
                        submitButton.disabled = false;
                    });
            });
        }

        if (reportHistoryTable) {
            reportHistoryTable.addEventListener('click', function(event) {
            const button = event.target.closest('button.delete-report-btn');
            if (button) {
                const reportId = button.dataset.id;
                const fileName = button.closest('tr')?.querySelector('td:nth-child(4)')?.textContent || `ID ${reportId}`;
                deleteReport(reportId, fileName);
            }
            });
        }

        loadReportHistory();
    });
</script>
@endpush
