@extends('layouts.app')

@section('title', 'Admin - Laporan')
@section('page-title', 'Laporan')

@section('content')
    <div class="mb-6">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">📊 Laporan</h3>
            <form id="reportForm" class="space-y-4">
                {{-- Form fields --}}
                <div><label for="jenis_laporan" class="block text-sm font-medium text-gray-700">Jenis Laporan</label><select id="jenis_laporan" name="jenis_laporan" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"><option value="" disabled selected>Pilih Jenis Laporan</option><option value="mahasiswa">Laporan Mahasiswa</option><option value="pembayaran">Laporan Pembayaran</option></select></div>
                <div><label for="periode" class="block text-sm font-medium text-gray-700">Periode (Bulan dan Tahun)</label><input type="month" id="periode" name="periode" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"></div>
                {{-- Tombol View --}}
                <button type="submit" id="viewReportBtn" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> View Laporan</button>
                {{-- Tombol Generate PDF --}}
                <button type="button" id="generatePdfBtn" class="hidden w-full mt-2 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Generate PDF & Simpan Riwayat</button>
            </form>
        </div>
    </div>

    {{-- Area Preview --}}
    <div id="reportPreviewArea" class="hidden mb-6 bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">🔍 Preview Laporan</h3>
        <div id="reportPreviewContent" class="overflow-x-auto"><p class="text-gray-500">Data preview akan muncul di sini...</p></div>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6"><h3 class="text-xl font-semibold text-gray-800">📋 Riwayat Laporan</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Laporan</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th></tr></thead>
                <tbody id="reportHistory" class="bg-white divide-y divide-gray-200 text-sm"><tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td></tr></tbody>
            </table>
        </div>
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
        const reportHistoryTable = document.getElementById('reportHistory');

        // ============================================
        // !! PASTIKAN VARIABEL INI PAKAI 'let' !!
        // ============================================
        let currentPreviewData = null;
        let currentReportParams = null;

        // -------------------------------------
        // FUNGSI API REQUEST (DENGAN SWEETALERT)
        // -------------------------------------
        async function apiRequest(url, method = 'GET', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) {
                Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
                return Promise.reject('No API Token');
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const options = { method: method, headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'X-CSRF-TOKEN': csrfToken } };
            if (body) { options.headers['Content-Type'] = 'application/json'; options.body = JSON.stringify(body); }

            try {
                const response = await fetch(url, options);
                if (response.status === 401) {
                    Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Sesi Anda telah berakhir. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
                    throw new Error('Unauthorized');
                }
                if (response.status === 204 && method === 'DELETE') { return { success: true, message: 'Data berhasil dihapus.' }; }

                const contentType = response.headers.get("content-type");
                const responseBody = await response.text();

                if (!contentType || !contentType.includes("application/json")) {
                    console.error("Non-JSON response:", response.status, responseBody);
                    throw new Error(`Server error: ${response.status}. ${responseBody.substring(0,150)}`);
                }

                const data = JSON.parse(responseBody);

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        console.error('Validation errors:', data.errors);
                        throw { status: 422, errors: data.errors, message: data.message || 'Validation failed' };
                    }
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                return data;
            } catch (error) {
                console.error("Error in apiRequest:", error);
                if (error.status === 422) throw error;
                throw new Error(error.message || 'Gagal memproses permintaan.');
            }
        }

        // -------------------------------------
        // FUNGSI LOAD RIWAYAT (AMAN DARI XSS)
        // -------------------------------------
        function loadReportHistory() {
            const historyUrl = "{{ route('admin.reports.index') }}";
            reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat riwayat...</td></tr>';

            function createHistoryCell(text, classes = []) { /* ... (kode helper cell) ... */
                 const td = document.createElement('td'); td.className = 'px-6 py-4 whitespace-nowrap ' + classes.join(' '); td.textContent = text ?? '-'; return td;
            }

            apiRequest(historyUrl).then(response => {
                const data = response.data || response;
                reportHistoryTable.innerHTML = '';
                if (!data || data.length === 0) { /* ... pesan kosong ... */ reportHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada laporan yang dibuat.</td></tr>'; return; }

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

        // ===================================================
        // !! FUNGSI INI DITULIS ULANG AGAR AMAN DARI XSS !!
        // ===================================================
        function displayReportPreview(data, type) {
            reportPreviewContent.innerHTML = ''; // Kosongkan
            if (!data || (Array.isArray(data) && data.length === 0) || (typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length === 0) ) {
                reportPreviewContent.innerHTML = '<p class="text-center text-gray-500">Tidak ada data untuk laporan ini.</p>';
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

            const headerRow = document.createElement('tr');
            if (type === 'mahasiswa') {
                headers = ['NPM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Semester', 'Status'];
                headerRow.appendChild(createHeaderCell('NPM'));
                headerRow.appendChild(createHeaderCell('Nama Lengkap'));
                headerRow.appendChild(createHeaderCell('Program Studi'));
                headerRow.appendChild(createHeaderCell('Angkatan'));
                headerRow.appendChild(createHeaderCell('Semester'));
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

            if (type === 'mahasiswa' && typeof data === 'object' && !Array.isArray(data)) {
                // Data Mahasiswa (Grouped by Semester)
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
                        dataRow.appendChild(createDataCell(item.program_studi));
                        dataRow.appendChild(createDataCell(item.angkatan));
                        dataRow.appendChild(createDataCell(item.semester_aktif, false, ['text-center']));
                        dataRow.appendChild(createStatusCell(item.status || '-', item.status === 'Aktif'));
                        tbody.appendChild(dataRow);
                    });
                });
            } else if (Array.isArray(data)) {
                // Data Pembayaran (Flat Array)
                data.forEach(item => {
                    const dataRow = document.createElement('tr');
                    if (type === 'pembayaran') {
                        // Gunakan 'let' untuk variabel yang nilainya bisa berubah
                        let statusText = item.status === 'Belum Lunas' ? 'Belum Dibayarkan' : item.status;
                        const isLunas = item.status === 'Lunas';
                        const tglBayar = item.pembayaran ? new Date(item.pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-';

                        dataRow.appendChild(createDataCell(item.kode_pembayaran));
                        dataRow.appendChild(createMahasiswaCell(item.mahasiswa?.user?.nama_lengkap, item.mahasiswa?.npm));
                        dataRow.appendChild(createDataCell(item.tarif?.nama_pembayaran));
                        dataRow.appendChild(createDataCell(rupiahFormat.format(item.jumlah_tagihan || 0), true));
                        dataRow.appendChild(createStatusCell(statusText, isLunas));
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

        // -------------------------------------
        // FUNGSI HAPUS LAPORAN (DENGAN SWEETALERT)
        // -------------------------------------
        function deleteReport(reportId, fileName) { /* ... (kode deleteReport) ... */ Swal.fire({ title: 'Anda Yakin?', text: `Yakin hapus riwayat & file "${fileName}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { const deleteUrl = `{{ url('api/admin/reports') }}/${reportId}`; apiRequest(deleteUrl, 'DELETE').then(response => { Swal.fire({ icon: 'success', title: 'Dihapus!', text: response.message || 'Laporan berhasil dihapus.', timer: 1500, showConfirmButton: false }); loadReportHistory(); }).catch(err => { Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: 'Gagal menghapus laporan: ' + err.message }); }); } }); }

        // -------------------------------------
        // EVENT LISTENERS (DENGAN SWEETALERT)
        // -------------------------------------
        reportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const previewUrl = "{{ route('admin.reports.preview') }}";
            const formData = { jenis_laporan: this.elements.jenis_laporan.value, periode: this.elements.periode.value, };
            if (!formData.jenis_laporan || !formData.periode) { Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Harap pilih jenis laporan dan periode.' }); return; }
            currentReportParams = formData;
            const submitButton = viewReportBtn; const originalButtonHTML = submitButton.innerHTML; submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memuat Preview...`; submitButton.disabled = true; generatePdfBtn.classList.add('hidden'); reportPreviewArea.classList.add('hidden');

            apiRequest(previewUrl, 'POST', formData).then(response => {
                currentPreviewData = response.data || response;
                if (currentPreviewData && ( (Array.isArray(currentPreviewData) && currentPreviewData.length > 0) || (typeof currentPreviewData === 'object' && !Array.isArray(currentPreviewData) && Object.keys(currentPreviewData).length > 0) )) {
                    displayReportPreview(currentPreviewData, formData.jenis_laporan); reportPreviewArea.classList.remove('hidden'); generatePdfBtn.classList.remove('hidden');
                } else {
                    reportPreviewContent.innerHTML = '<p class="text-center text-gray-500">Tidak ada data preview untuk periode ini.</p>'; reportPreviewArea.classList.remove('hidden'); generatePdfBtn.classList.add('hidden');
                }
            })
            // ==========================================================
            // !! PERBAIKAN TAMPILAN ERROR VALIDASI (422) DI SINI !!
            // ==========================================================
            .catch(err => {
                let errorContent;
                if (err.status === 422 && err.errors) {
                    errorContent = document.createElement('div'); const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p); const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2'; Object.values(err.errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); }); errorContent.appendChild(ul); // AMAN
                } else {
                    errorContent = 'Gagal memuat preview laporan: ' + (err.message || 'Error tidak diketahui');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Preview', html: errorContent }); // Pakai html
                reportPreviewContent.innerHTML = `<p class="text-center text-red-500">Gagal memuat preview.</p>`; reportPreviewArea.classList.remove('hidden');
            }).finally(() => {
                submitButton.innerHTML = originalButtonHTML; submitButton.disabled = false;
            });
        });

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
            // ==========================================================
            // !! PERBAIKAN TAMPILAN ERROR VALIDASI (422) DI SINI !!
            // ==========================================================
            .catch(err => {
                let errorContent;
                 if (err.status === 422 && err.errors) {
                    errorContent = document.createElement('div'); const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p); const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2'; Object.values(err.errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); }); errorContent.appendChild(ul); // AMAN
                } else {
                     errorContent = 'Terjadi kesalahan saat membuat PDF: ' + (err.message || 'Error tidak diketahui');
                }
                Swal.fire({ icon: 'error', title: 'Error PDF', html: errorContent }); // Pakai html
            }).finally(() => {
                submitButton.innerHTML = originalButtonHTML; submitButton.disabled = false;
            });
        });

        reportHistoryTable.addEventListener('click', function(event) {
            const button = event.target.closest('button.delete-report-btn');
            if (button) {
                const reportId = button.dataset.id;
                const fileName = button.closest('tr')?.querySelector('td:nth-child(4)')?.textContent || `ID ${reportId}`;
                deleteReport(reportId, fileName);
            }
        });

        loadReportHistory();
    });
</script>
@endpush
