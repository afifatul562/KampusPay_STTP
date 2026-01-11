@extends('layouts.app')

@section('title', 'Admin - Laporan')
@section('page-title', 'Laporan')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-page-header
        title="Laporan"
        subtitle="Generate dan kelola laporan pembayaran"
        :icon="$headerIcon">
    </x-page-header>

    <x-card title="Generate Laporan">
        <form id="reportForm" class="space-y-4">
            <div>
                <label for="jenis_laporan" class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                <select id="jenis_laporan" name="jenis_laporan" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="" disabled selected>Pilih Jenis Laporan</option>
                    <option value="mahasiswa">Data Mahasiswa</option>
                    <option value="pembayaran">Laporan Pembayaran</option>
                </select>
            </div>
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select id="tahun" name="tahun" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></select>
            </div>
            <div id="semesterFilterContainer" class="hidden">
                <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="semester" name="semester" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
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

    <x-card id="reportPreviewArea" class="hidden" title="Preview Laporan" aria-live="polite">
        <div id="reportPreviewContent" class="overflow-x-auto">
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
        // --- Elemen ---
        const reportForm = document.getElementById('reportForm');
        const viewReportBtn = document.getElementById('viewReportBtn');
        const generatePdfBtn = document.getElementById('generatePdfBtn');
        const reportPreviewArea = document.getElementById('reportPreviewArea');
        const reportPreviewContent = document.getElementById('reportPreviewContent');
        // Cari tabel berdasarkan aria-label karena menggunakan komponen x-data-table
        const reportHistoryTableElement = document.querySelector('table[aria-label="Tabel riwayat laporan"]');
        const reportHistoryTable = reportHistoryTableElement ? reportHistoryTableElement.querySelector('tbody') : null;
        const tahunSelect = document.getElementById('tahun');
        const jenisLaporanSelect = document.getElementById('jenis_laporan');
        const semesterFilterContainer = document.getElementById('semesterFilterContainer');
        const semesterSelect = document.getElementById('semester');

        let currentPreviewData = null;
        let currentReportParams = null;
        // Isi pilihan tahun (10 tahun terakhir)
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

        // Gunakan util global dari resources/js/utils/api.js
        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) { console.error('apiRequest util tidak tersedia'); }

        // Fungsi untuk memuat riwayat laporan
        function loadReportHistory() {
            const historyUrl = "{{ route('admin.reports.index') }}";
            reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td></tr>';

            function createHistoryCell(text, classes = []) {
                 const td = document.createElement('td'); td.className = 'px-6 py-4 whitespace-nowrap ' + classes.join(' '); td.textContent = text ?? '-'; return td;
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

                    const cellAksi = document.createElement('td'); cellAksi.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
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

        // Fungsi helper untuk parse label semester
        function parseSemesterLabel(semesterLabel) {
            if (!semesterLabel) {
                return { tahunAkademik: '-', semester: '-', semesterNumber: null };
            }

            // Format: "2025/2026 Ganjil" atau "2025/2026 Genap"
            const parts = semesterLabel.trim().split(/\s+/);
            if (parts.length >= 2) {
                const tahunAkademik = parts[0]; // "2025/2026"
                const semester = parts[1]; // "Ganjil" atau "Genap"
                return { tahunAkademik, semester, semesterNumber: null };
            }

            // Fallback jika format tidak sesuai
            return { tahunAkademik: semesterLabel, semester: '-', semesterNumber: null };
        }

        // Fungsi helper untuk menghitung nomor semester dari tahun akademik dan angkatan
        function calculateSemesterNumber(tahunAkademik, angkatan, semesterType) {
            if (!tahunAkademik || !angkatan || !semesterType || tahunAkademik === '-' || angkatan === null) {
                return null;
            }

            try {
                // Extract tahun pertama dari tahun akademik (misal: "2025/2026" -> 2025)
                const tahunParts = tahunAkademik.split('/');
                if (tahunParts.length < 1) return null;

                const tahunAkademikAwal = parseInt(tahunParts[0]);
                const angkatanInt = parseInt(String(angkatan));

                if (isNaN(tahunAkademikAwal) || isNaN(angkatanInt)) {
                    return null;
                }

                // Hitung selisih tahun
                const selisihTahun = tahunAkademikAwal - angkatanInt;

                // Semester ganjil = semester 1, 3, 5, 7... (selisih*2 + 1)
                // Semester genap = semester 2, 4, 6, 8... (selisih*2 + 2)
                const semesterTypeLower = semesterType.toLowerCase();
                if (semesterTypeLower === 'ganjil') {
                    return selisihTahun * 2 + 1;
                } else if (semesterTypeLower === 'genap') {
                    return selisihTahun * 2 + 2;
                }

                return null;
            } catch (e) {
                console.warn('Error calculating semester number:', e);
                return null;
            }
        }

        // Fungsi untuk menampilkan preview laporan
        function displayReportPreview(data, type) {
            reportPreviewContent.innerHTML = ''; // Kosongkan
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
            let headers = []; // Pakai 'let' karena isinya bisa berubah

            // Helper untuk membuat TH
            function createHeaderCell(text, alignRight = false) {
                const th = document.createElement('th');
                th.scope = 'col';
                th.className = 'px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
                if (alignRight) th.classList.add('text-right');
                th.textContent = text;
                return th;
            }
            // Helper untuk membuat TD (AMAN)
            function createDataCell(text, alignRight = false, extraClasses = []) {
                const td = document.createElement('td');
                td.className = 'px-4 py-2 ' + extraClasses.join(' ');
                if (alignRight) td.classList.add('text-right');
                td.textContent = text ?? '-'; // Default ke '-'
                return td;
            }
            // Helper untuk membuat TD Status (innerHTML aman)
            function createStatusCell(statusText, isLunasOrAktif) {
                const td = document.createElement('td');
                td.className = 'px-4 py-2 text-center';
                // Pakai 'let' untuk variabel yang akan diubah
                let bgColor = isLunasOrAktif ? 'bg-green-100' : 'bg-red-100';
                let textColor = isLunasOrAktif ? 'text-green-800' : 'text-red-800';
                 if (type === 'pembayaran' && !isLunasOrAktif) {
                    bgColor = 'bg-yellow-100';
                    textColor = 'text-yellow-800';
                }
                td.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${bgColor} ${textColor}">${statusText}</span>`;
                return td;
            }
            // Helper untuk membuat TD Mahasiswa (2 baris)
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
            // Helper untuk membuat TD Semester dengan badge
            function createSemesterCell(semesterInfo) {
                const td = document.createElement('td');
                td.className = 'px-4 py-2 whitespace-nowrap';
                if (semesterInfo && semesterInfo.semester && semesterInfo.semester !== '-') {
                    const badgeClass = semesterInfo.semester.toLowerCase() === 'ganjil'
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-purple-100 text-purple-800';
                    td.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}">${semesterInfo.semester}</span>`;
                } else {
                    td.textContent = '-';
                }
                return td;
            }
            // Helper untuk membuat TD Jenis Tagihan dengan detail semester dan program studi
            function createJenisTagihanCell(tagihan, semesterInfo) {
                const td = document.createElement('td');
                td.className = 'px-4 py-2';

                const jenisTagihanDiv = document.createElement('div');
                jenisTagihanDiv.className = 'space-y-1';

                // Nama tagihan (baris pertama - bold)
                const namaTagihanDiv = document.createElement('div');
                namaTagihanDiv.className = 'font-medium text-gray-900';
                namaTagihanDiv.textContent = tagihan.tarif?.nama_pembayaran ?? 'N/A';
                jenisTagihanDiv.appendChild(namaTagihanDiv);

                // Detail semester dan program studi (baris kedua - text kecil)
                const detailDiv = document.createElement('div');
                detailDiv.className = 'text-xs text-gray-500';

                // Hitung semester dari semester_label dan angkatan
                const angkatan = tagihan.mahasiswa?.angkatan ?? null;
                let semesterNumber = calculateSemesterNumber(semesterInfo.tahunAkademik, angkatan, semesterInfo.semester);

                // Fallback ke semester_aktif jika perhitungan gagal
                if (!semesterNumber) {
                    semesterNumber = tagihan.mahasiswa?.semester_aktif ?? null;
                }

                const programStudi = tagihan.mahasiswa?.program_studi ?? null;

                const detailParts = [];
                if (semesterNumber) {
                    detailParts.push(`Semester ${semesterNumber}`);
                }
                if (programStudi) {
                    detailParts.push(programStudi);
                }

                if (detailParts.length > 0) {
                    detailDiv.textContent = detailParts.join(' • ');
                } else {
                    detailDiv.textContent = '-';
                }

                jenisTagihanDiv.appendChild(detailDiv);
                td.appendChild(jenisTagihanDiv);
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
                headers = ['Kode Pembayaran', 'Mahasiswa', 'Tahun Akademik', 'Semester', 'Jenis Tagihan', 'Jumlah', 'Status', 'Tgl Bayar'];
                headerRow.appendChild(createHeaderCell('Kode Pembayaran'));
                headerRow.appendChild(createHeaderCell('Mahasiswa'));
                headerRow.appendChild(createHeaderCell('Tahun Akademik'));
                headerRow.appendChild(createHeaderCell('Semester'));
                headerRow.appendChild(createHeaderCell('Jenis Tagihan'));
                headerRow.appendChild(createHeaderCell('Jumlah', true));
                headerRow.appendChild(createHeaderCell('Status'));
                headerRow.appendChild(createHeaderCell('Tgl Bayar'));
            }
            thead.appendChild(headerRow);

            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

            if (type === 'mahasiswa' && Array.isArray(data)) {
                // Data Mahasiswa (Flat Array - tidak perlu grouping berdasarkan semester)
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
                // Fallback untuk format lama (jika masih ada data grouped by semester)
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
                // Data Pembayaran (Flat Array)
                data.forEach(item => {
                    const dataRow = document.createElement('tr');
                    if (type === 'pembayaran') {
                        // Parse semester label untuk mendapatkan tahun akademik dan semester
                        const semesterInfo = parseSemesterLabel(item.semester_label);

                        // Handle status sama seperti di list tagihan
                        const pembayaranAll = item.pembayaran_all || item.pembayaranAll || [];
                        const pembayaran = item.pembayaran;
                        const sudahAdaPembayaran = pembayaranAll.length > 0 || (pembayaran && !pembayaran.status_dibatalkan);

                        let statusText = item.status === 'Belum Lunas'
                            ? (sudahAdaPembayaran ? 'Belum Lunas' : 'Belum Dibayarkan')
                            : item.status;

                        const isLunas = item.status === 'Lunas';
                        const isDibatalkan = pembayaran && pembayaran.status_dibatalkan;

                        if (isDibatalkan) {
                            statusText = 'Dibatalkan';
                        }

                        const tglBayar = pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-';

                        dataRow.appendChild(createDataCell(item.kode_pembayaran));
                        dataRow.appendChild(createMahasiswaCell(item.mahasiswa?.user?.nama_lengkap, item.mahasiswa?.npm));
                        dataRow.appendChild(createDataCell(semesterInfo.tahunAkademik));
                        dataRow.appendChild(createSemesterCell(semesterInfo));
                        dataRow.appendChild(createJenisTagihanCell(item, semesterInfo));
                        dataRow.appendChild(createDataCell(rupiahFormat.format(item.jumlah_tagihan || 0), true));

                        // Status cell dengan handling khusus untuk Dibatalkan
                        const statusCell = document.createElement('td');
                        statusCell.className = 'px-4 py-2 text-center';
                        let bgColor, textColor;
                        if (isDibatalkan) {
                            bgColor = 'bg-red-100';
                            textColor = 'text-red-800';
                        } else if (isLunas) {
                            bgColor = 'bg-green-100';
                            textColor = 'text-green-800';
                        } else {
                            bgColor = 'bg-yellow-100';
                            textColor = 'text-yellow-800';
                        }
                        statusCell.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${bgColor} ${textColor}">${statusText}</span>`;
                        dataRow.appendChild(statusCell);

                        dataRow.appendChild(createDataCell(tglBayar));
                    }
                    // Jika ada jenis laporan lain (selain mahasiswa & pembayaran) yang datanya array
                    // tambahkan logikanya di sini
                    tbody.appendChild(dataRow);
                });
            }

            table.appendChild(thead);
            table.appendChild(tbody);
            reportPreviewContent.appendChild(table);
        }

        // Fungsi untuk menghapus laporan
        function deleteReport(reportId, fileName) {
            Swal.fire({ title: 'Anda Yakin?', text: `Yakin hapus riwayat & file "${fileName}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { const deleteUrl = `{{ url('api/admin/reports') }}/${reportId}`; apiRequest(deleteUrl, 'DELETE').then(response => { Swal.fire({ icon: 'success', title: 'Dihapus!', text: response.message || 'Laporan berhasil dihapus.', timer: 1500, showConfirmButton: false }); loadReportHistory(); }).catch(err => { Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: 'Gagal menghapus laporan: ' + err.message }); }); } }); }

        // Event listeners
        // Tampilkan/sembunyikan filter semester berdasarkan jenis laporan
        if (jenisLaporanSelect && semesterFilterContainer) {
            function toggleSemesterFilter() {
                if (jenisLaporanSelect.value === 'pembayaran') {
                    semesterFilterContainer.classList.remove('hidden');
                } else {
                    semesterFilterContainer.classList.add('hidden');
                    if (semesterSelect) {
                        semesterSelect.value = ''; // Reset semester jika bukan pembayaran
                    }
                }
            }

            jenisLaporanSelect.addEventListener('change', toggleSemesterFilter);
            // Periksa state awal
            toggleSemesterFilter();
        }

        // Periksa null sebelum menambahkan event listener
        if (!reportForm || !viewReportBtn || !generatePdfBtn || !reportPreviewArea || !reportPreviewContent || !tahunSelect) {
            console.error('Required elements not found');
        }

        if (reportForm) {
            reportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const previewUrl = "{{ route('admin.reports.preview') }}";
            const formData = {
                jenis_laporan: this.elements.jenis_laporan.value,
                tahun: this.elements.tahun.value,
                semester: this.elements.semester ? this.elements.semester.value : ''
            };
            if (!formData.jenis_laporan || !formData.tahun) { Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Harap pilih jenis laporan dan tahun.' }); return; }
            currentReportParams = formData;
            const submitButton = viewReportBtn; const originalButtonHTML = submitButton.innerHTML; submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memuat Preview...`; submitButton.disabled = true; generatePdfBtn.classList.add('hidden'); reportPreviewArea.classList.add('hidden');

            apiRequest(previewUrl, 'POST', formData).then(response => {
                currentPreviewData = response.data || response;
                if (currentPreviewData && ( (Array.isArray(currentPreviewData) && currentPreviewData.length > 0) || (typeof currentPreviewData === 'object' && !Array.isArray(currentPreviewData) && Object.keys(currentPreviewData).length > 0) )) {
                    displayReportPreview(currentPreviewData, formData.jenis_laporan); reportPreviewArea.classList.remove('hidden'); generatePdfBtn.classList.remove('hidden'); generatePdfBtn.classList.add('flex', 'justify-center', 'items-center');
                } else {
                    renderEmptyState(reportPreviewContent, {
                        title: 'Tidak ada data preview',
                        message: 'Tidak ada data preview untuk periode ini.',
                        icon: `
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        `
                    });
                    reportPreviewArea.classList.remove('hidden');
                    generatePdfBtn.classList.add('hidden');
                }
            })
            .catch(err => {
                let errorContent;
                if (err.status === 422 && err.errors) {
                    errorContent = document.createElement('div'); const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p); const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2'; Object.values(err.errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); });                     errorContent.appendChild(ul);
                } else {
                    errorContent = 'Gagal memuat preview laporan: ' + (err.message || 'Error tidak diketahui');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Preview', html: errorContent });
                reportPreviewContent.innerHTML = `<p class="text-center text-red-500">Gagal memuat preview.</p>`; reportPreviewArea.classList.remove('hidden');
            }).finally(() => {
                submitButton.innerHTML = originalButtonHTML; submitButton.disabled = false;
            });
            });
        }

        if (generatePdfBtn) {
            generatePdfBtn.addEventListener('click', function() {
            if (!currentReportParams) { Swal.fire({ icon: 'warning', title: 'Aksi Belum Sesuai', text: 'Harap klik "View Laporan" terlebih dahulu.' }); return; }
            const generateUrl = "{{ route('admin.reports.store') }}";
            const submitButton = this; const originalButtonHTML = submitButton.innerHTML;
            submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Membuat PDF...`;
            submitButton.disabled = true;

            apiRequest(generateUrl, 'POST', currentReportParams).then(response => {
                if (response.success || response.message?.includes('berhasil')) {
                    Swal.fire({ icon: 'success', title: 'Berhasil Dibuat!', text: response.message || 'Laporan PDF berhasil dibuat!', timer: 1500, showConfirmButton: false });
                    loadReportHistory(); reportPreviewArea.classList.add('hidden'); submitButton.classList.add('hidden'); currentPreviewData = null; currentReportParams = null;
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal Membuat PDF', text: response.message || 'Error tidak diketahui' });
                }
            })
            .catch(err => {
                let errorContent;
                 if (err.status === 422 && err.errors) {
                    errorContent = document.createElement('div'); const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p); const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2'; Object.values(err.errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); });                     errorContent.appendChild(ul);
                } else {
                     errorContent = 'Terjadi kesalahan saat membuat PDF: ' + (err.message || 'Error tidak diketahui');
                }
                Swal.fire({ icon: 'error', title: 'Error PDF', html: errorContent });
            }).finally(() => {
                submitButton.innerHTML = originalButtonHTML; submitButton.disabled = false;
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
