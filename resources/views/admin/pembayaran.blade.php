@extends('layouts.app')

@section('title', 'Admin - Manajemen Pembayaran')
@section('page-title', 'Manajemen Pembayaran')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Manajemen Pembayaran']
    ]" />

    <x-page-header
        title="Manajemen Pembayaran"
        subtitle="Kelola pembayaran dan tagihan mahasiswa"
        :icon="$headerIcon">
        <x-slot:actions>
            <x-gradient-button id="addTagihanBtn" variant="primary" size="md" aria-label="Buat Tagihan Baru">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Buat Tagihan Baru
            </x-gradient-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Dropdowns --}}
    <x-card title="Filter">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Filter Pencarian Nama Mahasiswa --}}
            <div class="flex-1">
                <label for="filterNamaMahasiswa" class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Mahasiswa</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="filterNamaMahasiswa" placeholder="Cari nama atau NPM..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                </div>
            </div>
            {{-- Filter Status --}}
            <div class="flex-1">
                <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                <select id="filterStatus" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="Lunas">Lunas</option>
                    <option value="Belum Dibayarkan">Belum Dibayarkan</option>
                    <option value="Menunggu Pembayaran Tunai">Menunggu Pembayaran Tunai</option>
                    <option value="Menunggu Verifikasi Transfer">Menunggu Verifikasi Transfer</option>
                    <option value="Ditolak">Ditolak</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            {{-- Filter Jenis Tagihan --}}
            <div class="flex-1">
                <label for="filterJenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Tagihan</label>
                <select id="filterJenis" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Jenis</option>
                    {{-- Opsi akan diisi oleh JavaScript --}}
                </select>
            </div>
        </div>
    </x-card>

    {{-- Tabel Pembayaran & Tagihan --}}
    <x-data-table
        :headers="['Kode Pembayaran', 'Mahasiswa', 'Jenis Tagihan', 'Jumlah', 'Status', 'Tgl Bayar', 'Aksi']"
        aria-label="Tabel pembayaran dan tagihan">
        <tr id="loading-row">
            <td colspan="7" class="text-center py-10 text-gray-500">Memuat data...</td>
        </tr>
    </x-data-table>

    {{-- Modal Buat/Edit Tagihan --}}
    <div id="tagihanModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900 bg-opacity-60">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg w-full">
            <form id="tagihanForm">
                 <input type="hidden" id="tagihanId">
                <div class="bg-white px-6 pt-5 pb-4">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Buat Tagihan Baru</h3>
                        <button type="button" class="close-button text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="angkatan_filter" class="block text-sm font-medium text-gray-700">Filter Berdasarkan Angkatan</label>
                            <select id="angkatan_filter" name="angkatan_filter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"><option value="">Tampilkan Semua Angkatan</option></select>
                        </div>
                        <div>
                            <label for="mahasiswa_id" class="block text-sm font-medium text-gray-700">Pilih Mahasiswa</label>
                            {{-- !! Ini akan di-target oleh Select2 !! --}}
                            <select id="mahasiswa_id" name="mahasiswa_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500" style="width: 100%"><option value="" disabled selected>Pilih angkatan dulu</option></select>
                        </div>
                        <div>
                            <label for="tarif_id" class="block text-sm font-medium text-gray-700">Pilih Jenis Tarif</label>
                            <select id="tarif_id" name="tarif_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"><option value="" disabled selected>Pilih mahasiswa dulu</option></select>
                        </div>
                        <div>
                            <label for="jumlah_tagihan" class="block text-sm font-medium text-gray-700">Jumlah Tagihan (Rp)</label>
                            {{-- !! Ganti type="text" agar Cleave.js berfungsi !! --}}
                            <input type="text" id="jumlah_tagihan" name="jumlah_tagihan" required placeholder="Akan terisi otomatis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500" readonly>
                        </div>
                        <div>
                            <label for="tanggal_jatuh_tempo" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo</label>
                            <input type="text" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required placeholder="dd/mm/yyyy" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="kode_pembayaran" class="block text-sm font-medium text-gray-700">Kode Pembayaran</label>
                            <input type="text" id="kode_pembayaran" name="kode_pembayaran" required placeholder="Akan ter-generate otomatis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500" readonly>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>

     {{-- Modal Detail Tagihan --}}
     <div id="detailTagihanModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900 bg-opacity-60">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-2xl w-full max-h-[90vh] flex flex-col">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Detail Tagihan</h3>
                    <button type="button" class="detail-close-button text-2xl text-gray-400 hover:text-gray-600 transition-colors">&times;</button>
                </div>
            </div>
            <div class="px-6 py-5 overflow-y-auto flex-1" id="detailTagihanContent">
                <div class="text-center text-gray-500 py-4">Memuat detail...</div>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" class="detail-close-button inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Bagian Umum ---
        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        // Find tbody by finding the table with aria-label and then its tbody
        const paymentTable = document.querySelector('table[aria-label="Tabel pembayaran dan tagihan"]');
        const paymentTableBody = paymentTable ? paymentTable.querySelector('tbody') : null;

        // --- Bagian Modal Tambah/Edit ---
        const tagihanModal = document.getElementById('tagihanModal');
        const modalTitle = document.getElementById('modalTitle');
        const tagihanForm = document.getElementById('tagihanForm');
        const tagihanIdInput = document.getElementById('tagihanId');
        const addTagihanBtn = document.getElementById('addTagihanBtn');
        const closeModalButton = tagihanModal.querySelector('.close-button');
        const cancelBtn = document.getElementById('cancelBtn');
        const angkatanFilterSelect = document.getElementById('angkatan_filter');
        const mahasiswaSelect = document.getElementById('mahasiswa_id');
        const tarifSelect = document.getElementById('tarif_id');
        const jumlahInput = document.getElementById('jumlah_tagihan');
        const kodeInput = document.getElementById('kode_pembayaran');
        const tglJatuhTempoInput = document.getElementById('tanggal_jatuh_tempo');

        // --- Bagian Modal Detail ---
        const detailTagihanModal = document.getElementById('detailTagihanModal');
        const detailTagihanContent = document.getElementById('detailTagihanContent');
        const detailCloseButtons = detailTagihanModal.querySelectorAll('.detail-close-button');

        // --- Variabel Data & Filter Tabel ---
        let allTagihanData = [];
        let allMahasiswaData = []; // Pindah ke scope atas
        let allTarifsData = []; // Pindah ke scope atas
        const filterStatusSelect = document.getElementById('filterStatus');
        const filterJenisSelect = document.getElementById('filterJenis');
        const filterNamaMahasiswaInput = document.getElementById('filterNamaMahasiswa');
        let currentFilters = { status: '', jenis: '', namaMahasiswa: '' };

        // ===========================================
        // !! Inisialisasi Cleave.js (Format Angka) !!
        // ===========================================
        // Gunakan fallback sederhana jika Cleave.js tidak tersedia agar tidak error
        const cleaveJumlah = (window.Cleave)
            ? new Cleave(jumlahInput, { numeral: true, numeralThousandsGroupStyle: 'thousand', delimiter: '.' })
            : {
                setRawValue: function(value) {
                    if (value === undefined || value === null || value === '') { jumlahInput.value = ''; return; }
                    const num = parseInt(String(value).replace(/\D/g, ''), 10);
                    if (isNaN(num)) { jumlahInput.value = ''; return; }
                    jumlahInput.value = new Intl.NumberFormat('id-ID').format(num);
                }
            };

        // ===========================================
        // !! Inisialisasi Flatpickr (Format Tanggal) !!
        // ===========================================
        // Gunakan fallback ringan jika Flatpickr tidak tersedia agar tidak error
        // Paksa placeholder Indonesia sebelum inisialisasi
        tglJatuhTempoInput.setAttribute('placeholder', 'dd/mm/yyyy');
        // Paksa locale ID secara global bila tersedia
        if (window.flatpickr && window.flatpickr.l10ns && window.flatpickr.l10ns.id) {
            window.flatpickr.localize(window.flatpickr.l10ns.id);
        }
        const flatpickrDueDate = (window.flatpickr)
            ? flatpickr(tglJatuhTempoInput, {
                dateFormat: "d/m/Y",
                minDate: "today",
                allowInput: false,
                defaultDate: null,
                static: false,
                disableMobile: true, // hindari native picker yang memaksa mm/dd/yyyy
                onReady: function(selectedDates, dateStr, instance) {
                    instance.input.placeholder = 'dd/mm/yyyy';
                    if (!instance.input.value) instance.input.value = '';
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.input.placeholder = 'dd/mm/yyyy';
                },
                onClose: function(selectedDates, dateStr, instance) {
                    if (!dateStr) instance.input.placeholder = 'dd/mm/yyyy';
                }
            })
            : {
                clear: function() { tglJatuhTempoInput.value = ''; },
                setDate: function(date) {
                    const d = new Date(date);
                    if (isNaN(d)) { return; }
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const yyyy = d.getFullYear();
                    tglJatuhTempoInput.value = `${dd}/${mm}/${yyyy}`;
                }
            };

        // Pastikan placeholder tetap "dd/mm/yyyy" setelah Flatpickr diinisialisasi
        // Gunakan setTimeout untuk memastikan placeholder di-set setelah Flatpickr selesai
        setTimeout(() => {
            if (!tglJatuhTempoInput.value) {
                tglJatuhTempoInput.placeholder = "dd/mm/yyyy";
            }
        }, 100);

        // Event listener untuk memastikan placeholder tetap benar saat input kosong
        tglJatuhTempoInput.addEventListener('focus', function() {
            if (!this.value) {
                this.placeholder = "dd/mm/yyyy";
            }
        });

        tglJatuhTempoInput.addEventListener('blur', function() {
            if (!this.value) {
                this.placeholder = "dd/mm/yyyy";
            }
        });

        // MutationObserver untuk memastikan placeholder selalu "dd/mm/yyyy"
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'placeholder') {
                    if (tglJatuhTempoInput.placeholder !== "dd/mm/yyyy" && !tglJatuhTempoInput.value) {
                        tglJatuhTempoInput.placeholder = "dd/mm/yyyy";
                    }
                }
            });
        });

        observer.observe(tglJatuhTempoInput, {
            attributes: true,
            attributeFilter: ['placeholder']
        });

        // ===========================================
        // !! Inisialisasi Select2 (Pencarian Mahasiswa) - Opsional !!
        // ===========================================
        const isjQueryAvailable = !!(window.$ && $.fn);
        const isSelect2Available = isjQueryAvailable && !!$.fn.select2;
        if (isSelect2Available) {
            $(mahasiswaSelect).select2({
                theme: "default",
                width: '100%',
                dropdownParent: $('#tagihanModal'),
                placeholder: 'Pilih Mahasiswa',
                allowClear: true
            });
            // Pastikan perubahan pilihan memicu pemfilteran tarif
            $(mahasiswaSelect).on('select2:select select2:clear', function(){
                mahasiswaSelect.dispatchEvent(new Event('change'));
            });
        }

        // -------------------------------------
        // FUNGSI API REQUEST (DENGAN SWEETALERT)
        // -------------------------------------
        async function apiRequest(url, method = 'GET', body = null) {
             if (!apiToken) {
                 Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
                 return Promise.reject('No API Token');
             }
             const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); const options = { method: method, headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'X-CSRF-TOKEN': csrfToken } };
             if (body) { options.body = JSON.stringify(body); options.headers['Content-Type'] = 'application/json'; }
             if (method === 'DELETE') { delete options.headers['Content-Type']; }
             try {
                 const response = await fetch(url, options);
                 if (response.status === 401) {
                     Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Sesi Anda telah berakhir. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
                     throw new Error('Unauthorized');
                 }
                 if (response.status === 204 && method === 'DELETE') { return { success: true, message: 'Data berhasil dihapus.' }; }
                 const contentType = response.headers.get("content-type"); const responseBody = await response.text();
                 if (!contentType || !contentType.includes("application/json")) { console.error("Non-JSON response:", response.status, responseBody); throw new Error(`Server error: ${response.status}. ${responseBody.substring(0,150)}`); }
                 const data = JSON.parse(responseBody);
                 if (!response.ok && response.status === 422 && data.errors) { console.error('Validation errors:', data.errors); throw { status: 422, errors: data.errors, message: data.message || 'Validation failed' }; }
                 if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); }
                 return data;
             } catch (error) {
                 console.error("Error in apiRequest:", error);
                 if (error.status === 422) throw error;
                 throw new Error(error.message || 'Gagal memproses permintaan.');
             }
        }
        // Util: konversi dd/mm/yyyy -> yyyy-mm-dd untuk backend
        function toYmdFromDdMmYyyy(input) {
            if (!input) return '';
            const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(input.trim());
            if (!match) return input; // biarkan backend menangani jika format lain
            const [_, dd, mm, yyyy] = match;
            return `${yyyy}-${mm}-${dd}`;
        }
        // Util: ubah '50.000' -> 50000 (angka)
        function parseRupiahToNumber(input) {
            if (input === undefined || input === null) return '';
            const onlyDigits = String(input).replace(/[^\d-]/g, '');
            return onlyDigits ? Number(onlyDigits) : '';
        }

        // -------------------------------------
        // FUNGSI RENDER TABEL (Aksi Lengkap)
        // -------------------------------------
        function renderTable(dataToRender) {
            if (!paymentTableBody) {
                console.error('paymentTableBody not found in renderTable');
                return;
            }
            paymentTableBody.innerHTML = '';
            if (!dataToRender || dataToRender.length === 0) {
                 const isFiltering = currentFilters.status || currentFilters.jenis || currentFilters.namaMahasiswa;
                 renderEmptyState(paymentTableBody, {
                     colspan: 7,
                     title: isFiltering ? 'Tidak ada data' : 'Belum ada data tagihan',
                     message: isFiltering ? 'Tidak ada data tagihan yang cocok dengan filter yang dipilih.' : 'Silakan buat tagihan baru untuk memulai.',
                     icon: `
                         <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                         </svg>
                     `
                 });
                return;
            }
            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

            // Fungsi helper untuk membuat sel (td) dengan textContent (AMAN)
            function createCell(text, classes = []) {
                const cell = document.createElement('td');
                cell.textContent = text ?? '-'; // Gunakan '-' jika null/undefined
                cell.className = 'px-6 py-4 whitespace-nowrap ' + classes.join(' ');
                return cell;
            }

            dataToRender.forEach(tagihan => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';

                const isLunas = tagihan.status === 'Lunas';
                const pembayaran = tagihan.pembayaran;
                let statusText = tagihan.status === 'Belum Lunas' ? 'Belum Dibayarkan' : tagihan.status;

                // 1. Kode Pembayaran
                tr.appendChild(createCell(tagihan.kode_pembayaran, ['text-gray-700']));

                // 2. Mahasiswa (Nama & NPM)
                const cellMahasiswa = document.createElement('td');
                cellMahasiswa.className = 'px-6 py-4 whitespace-nowrap';
                const divNama = document.createElement('div');
                divNama.className = 'font-medium text-gray-900';
                divNama.textContent = tagihan.mahasiswa?.user?.nama_lengkap ?? 'N/A'; // Aman
                const divNpm = document.createElement('div');
                divNpm.className = 'text-gray-500';
                divNpm.textContent = tagihan.mahasiswa?.npm ?? 'N/A'; // Aman
                cellMahasiswa.appendChild(divNama);
                cellMahasiswa.appendChild(divNpm);
                tr.appendChild(cellMahasiswa);

                // 3. Jenis Tagihan
                tr.appendChild(createCell(tagihan.tarif?.nama_pembayaran, ['text-gray-700']));

                // 4. Jumlah
                tr.appendChild(createCell(rupiahFormat.format(tagihan.jumlah_tagihan), ['text-right', 'font-medium', 'text-gray-800']));

                // 5. Status (Pakai innerHTML tapi aman karena dari logic kita)
                const cellStatus = document.createElement('td');
                cellStatus.className = 'px-6 py-4 whitespace-nowrap text-center';
                const isDibatalkan = pembayaran && pembayaran.status_dibatalkan;
                if (isDibatalkan) {
                    statusText = 'Dibatalkan';
                }
                cellStatus.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isDibatalkan ? 'bg-red-100 text-red-800' : (isLunas ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')}">${statusText}</span>`;
                tr.appendChild(cellStatus);

                // 6. Tgl Bayar
                tr.appendChild(createCell(pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-', ['text-gray-500']));

                // 7. Aksi (Pakai innerHTML tapi aman karena ID bukan input teks)
                const cellAksi = document.createElement('td');
                cellAksi.className = 'px-6 py-4 whitespace-nowrap text-right font-medium';
                cellAksi.innerHTML = `
                <div class="flex justify-end items-center gap-2">
                    <button class="view-btn text-gray-500 hover:text-blue-600" data-id="${tagihan.tagihan_id}" data-tooltip="Lihat detail tagihan" title="Lihat Detail">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    ${!isLunas ? `
                    <button class="edit-btn text-gray-500 hover:text-yellow-600" data-id="${tagihan.tagihan_id}" data-tooltip="Edit tagihan" title="Edit Tagihan">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z"></path></svg>
                    </button>` : '<span class="w-5 h-5 inline-block"></span>'}
                    ${!isLunas ? `
                    <button class="delete-btn text-gray-500 hover:text-red-600" data-id="${tagihan.tagihan_id}" data-tooltip="Hapus tagihan" title="Hapus Tagihan">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>` : '<span class="w-5 h-5 inline-block"></span>'}
                    ${isLunas && pembayaran ? `
                    <a href="{{ url('admin/pembayaran/print') }}/${pembayaran.pembayaran_id}" target="_blank" class="print-btn text-gray-500 hover:text-indigo-600" data-tooltip="Cetak bukti pembayaran" title="Cetak Bukti">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </a>` : '<span class="w-5 h-5 inline-block"></span>'}
                </div>
            `;
                tr.appendChild(cellAksi);

                paymentTableBody.appendChild(tr);
            });
        }

        // -------------------------------------
        // FUNGSI FILTER TABEL
        // -------------------------------------
         function applyFilters() {
             currentFilters.status = filterStatusSelect.value;
             currentFilters.jenis = filterJenisSelect.value;
             currentFilters.namaMahasiswa = filterNamaMahasiswaInput.value.toLowerCase().trim();
             const filteredData = allTagihanData.filter(tagihan => {
                 let statusMatch = true;
                 if (currentFilters.status) {
                     // Handle status khusus "Dibatalkan" yang berasal dari pembayaran.status_dibatalkan
                     if (currentFilters.status === 'Dibatalkan') {
                         const pembayaran = tagihan.pembayaran;
                         statusMatch = pembayaran && pembayaran.status_dibatalkan === true;
                     } else {
                         // Handle status normal dari tagihan.status
                         // Penting: Jika pembayaran dibatalkan, status yang ditampilkan adalah "Dibatalkan",
                         // jadi kita harus exclude tagihan yang dibatalkan ketika filter status bukan "Dibatalkan"
                         const pembayaran = tagihan.pembayaran;
                         const isDibatalkan = pembayaran && pembayaran.status_dibatalkan === true;

                         if (isDibatalkan) {
                             // Jika pembayaran dibatalkan, status yang ditampilkan adalah "Dibatalkan",
                             // jadi tidak cocok dengan filter status lainnya
                             statusMatch = false;
                         } else {
                             // Status normal dari tagihan.status
                             let statusAsli = tagihan.status === 'Belum Lunas' ? 'Belum Dibayarkan' : tagihan.status;
                             statusMatch = statusAsli === currentFilters.status;
                         }
                     }
                 }
                 const jenisMatch = !currentFilters.jenis || (tagihan.tarif && tagihan.tarif.nama_pembayaran === currentFilters.jenis);
                 const namaMatch = !currentFilters.namaMahasiswa ||
                     (tagihan.mahasiswa?.user?.nama_lengkap?.toLowerCase().includes(currentFilters.namaMahasiswa) ||
                      tagihan.mahasiswa?.npm?.toLowerCase().includes(currentFilters.namaMahasiswa));
                 return statusMatch && jenisMatch && namaMatch;
             });
             renderTable(filteredData);
         }

        // -------------------------------------
        // FUNGSI ISI DROPDOWN FILTER
        // -------------------------------------
         function populateTableFilters() {
             const uniqueJenis = new Set();
             allTagihanData.forEach(tagihan => { if (tagihan.tarif && tagihan.tarif.nama_pembayaran) { uniqueJenis.add(tagihan.tarif.nama_pembayaran); } });
             filterJenisSelect.innerHTML = '<option value="">Semua Jenis</option>';
             Array.from(uniqueJenis).sort().forEach(jenis => { const option = document.createElement('option'); option.value = jenis; option.textContent = jenis; filterJenisSelect.appendChild(option); });
         }

        // -------------------------------------
        // FUNGSI LOAD DATA AWAL
        // -------------------------------------
        function loadPayments() {
            renderTable([]);
            const url = "{{ route('admin.tagihan.index') }}";
            apiRequest(url).then(response => {
                allTagihanData = response.data || response;
                populateTableFilters();
                // !! PANGGIL applyFilters() DI SINI !!
                applyFilters();
            }).catch(error => {
                console.error('Error fetching tagihan:', error);
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Data', text: `Gagal memuat data tagihan: ${error.message}` });
                if (paymentTableBody) {
                    paymentTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500">Gagal memuat data. Silakan refresh.</td></tr>`;
                }
            });
        }

        // --- Fungsi Modal & Form Handling (Diperbarui) ---

        async function populateFormDropdowns() {
            const mahasiswaUrl = "{{ route('admin.mahasiswa.index') }}";
            const tarifUrl = "{{ route('admin.tarif.index') }}";
            try {
                const [mahasiswaResponse, tarifResponse] = await Promise.all([apiRequest(mahasiswaUrl), apiRequest(tarifUrl)]);
                allMahasiswaData = mahasiswaResponse.data || mahasiswaResponse;
                allTarifsData = tarifResponse.data || tarifResponse;

                const angkatanUnik = [...new Set(allMahasiswaData.map(mhs => mhs.angkatan))].sort().reverse();
                angkatanFilterSelect.innerHTML = '<option value="">Tampilkan Semua Angkatan</option>';
                angkatanUnik.forEach(angkatan => {
                    angkatanFilterSelect.innerHTML += `<option value="${angkatan}">${angkatan}</option>`;
                });

                // Panggil filterMahasiswaDropdown untuk populasi awal
                filterMahasiswaDropdown();

            } catch (error) {
                console.error("Gagal memuat data form:", error);
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Data Form', text: `Gagal memuat data mahasiswa/tarif: ${error.message}` });
                // Fallback tanpa jQuery
                mahasiswaSelect.innerHTML = '';
                mahasiswaSelect.appendChild(new Option('Gagal memuat', ''));
                mahasiswaSelect.dispatchEvent(new Event('change'));
                tarifSelect.innerHTML = '<option value="">Gagal memuat</option>';
            }
        }

        function filterMahasiswaDropdown() {
            const selectedAngkatan = angkatanFilterSelect.value;
            const filteredMahasiswa = allMahasiswaData.filter(mhs => !selectedAngkatan || (mhs.angkatan == selectedAngkatan));

            // Kosongkan dan tambah placeholder (native)
            mahasiswaSelect.innerHTML = '';
            const placeholderOpt = new Option('Pilih Mahasiswa', '', true, true);
            mahasiswaSelect.appendChild(placeholderOpt);

            filteredMahasiswa.forEach(mhs => {
                const option = new Option(`${mhs.npm} - ${mhs.user?.nama_lengkap ?? 'N/A'}`, mhs.mahasiswa_id, false, false);
                option.dataset.prodi = mhs.program_studi ?? '';
                option.dataset.angkatan = mhs.angkatan ?? '';
                mahasiswaSelect.appendChild(option);
            });

            // Trigger change agar dependent dropdown ter-update
            mahasiswaSelect.dispatchEvent(new Event('change'));
            filterTarifDropdown();
        }

        function filterTarifDropdown() {
            // Ambil option terpilih secara aman (kompatibel dengan Select2)
            const selectedValue = mahasiswaSelect.value;
            const selectedOption = mahasiswaSelect.querySelector(`option[value="${CSS.escape(selectedValue)}"]`);

            if (!selectedOption || !selectedOption.value) {
                tarifSelect.innerHTML = '<option value="" disabled selected>Pilih mahasiswa dulu</option>';
                cleaveJumlah.setRawValue(''); // Set ke kosong
                kodeInput.value = '';
                return;
            }

            // Ambil dataset dari <option> terpilih
            const prodiMhs = selectedOption.dataset.prodi;
            const angkatanMhs = selectedOption.dataset.angkatan;

            const filteredTarifs = allTarifsData.filter(tarif => {
                const prodiCocok = !tarif.program_studi || tarif.program_studi === prodiMhs;
                const angkatanCocok = !tarif.angkatan || tarif.angkatan === angkatanMhs;
                return prodiCocok && angkatanCocok;
            });

            tarifSelect.innerHTML = '<option value="" disabled selected>Pilih Jenis Tarif</option>';
            filteredTarifs.forEach(tarif => {
                tarifSelect.innerHTML += `<option value="${tarif.tarif_id}">${tarif.nama_pembayaran}</option>`;
            });

            cleaveJumlah.setRawValue(''); // Set ke kosong
            kodeInput.value = '';
            tarifSelect.dispatchEvent(new Event('change'));
        }

        // !! Perbarui fungsi openTagihanModal !!
        function openTagihanModal(mode = 'add', tagihanData = null) {
            tagihanForm.reset();
            tagihanIdInput.value = '';

            // Reset 3 library
            cleaveJumlah.setRawValue('');
            flatpickrDueDate.clear();
            tglJatuhTempoInput.value = ''; // Reset input field secara eksplisit
            tglJatuhTempoInput.placeholder = "dd/mm/yyyy"; // Pastikan placeholder benar
            mahasiswaSelect.innerHTML = '';
            mahasiswaSelect.appendChild(new Option('Pilih angkatan dulu', '', true, true));
            mahasiswaSelect.dispatchEvent(new Event('change'));

            angkatanFilterSelect.value = '';
            tarifSelect.innerHTML = '<option value="" disabled selected>Pilih mahasiswa dulu</option>';
            kodeInput.value = '';

            // Pastikan placeholder "dd/mm/yyyy" ditampilkan setelah modal dibuka
            setTimeout(() => {
                if (!tglJatuhTempoInput.value) {
                    tglJatuhTempoInput.placeholder = "dd/mm/yyyy";
                }
            }, 150);

            if (mode === 'add') {
                modalTitle.textContent = 'Buat Tagihan Baru';
                populateFormDropdowns();
            } else if (mode === 'edit' && tagihanData) {
                modalTitle.textContent = 'Edit Tagihan';
                tagihanIdInput.value = tagihanData.tagihan_id;

                populateFormDropdowns().then(() => {
                    // Isi data yang ada
                    angkatanFilterSelect.value = tagihanData.mahasiswa?.angkatan ?? '';
                    angkatanFilterSelect.dispatchEvent(new Event('change')); // Trigger filter mahasiswa

                    setTimeout(() => {
                        // Set value (support Select2 jika ada)
                        if (isSelect2Available) {
                            $(mahasiswaSelect).val(tagihanData.mahasiswa_id).trigger('change');
                        } else {
                            mahasiswaSelect.value = String(tagihanData.mahasiswa_id);
                            mahasiswaSelect.dispatchEvent(new Event('change'));
                        }

                        setTimeout(() => {
                            tarifSelect.value = tagihanData.tarif_id;

                            // Set Cleave.js value
                            cleaveJumlah.setRawValue(tagihanData.jumlah_tagihan);

                            // Set Flatpickr value (konversi YYYY-MM-DD ke Date object)
                            if(tagihanData.tanggal_jatuh_tempo) {
                                const dateStr = tagihanData.tanggal_jatuh_tempo.split('T')[0]; // Ambil bagian tanggal saja
                                const dateObj = new Date(dateStr + 'T00:00:00'); // Tambahkan time untuk menghindari timezone issue
                                if (!isNaN(dateObj.getTime())) {
                                    flatpickrDueDate.setDate(dateObj, false); // false = gunakan dateFormat dari config (d/m/Y)
                                }
                            }

                            kodeInput.value = tagihanData.kode_pembayaran;
                            tarifSelect.dispatchEvent(new Event('change'));
                        }, 200); // Delay kecil untuk Select2
                    }, 200); // Delay kecil untuk Select2
                });
            }
            tagihanModal.classList.remove('hidden');
            tagihanModal.classList.add('flex');
        }

        function closeTagihanModal() {
            tagihanModal.classList.add('hidden');
            tagihanModal.classList.remove('flex');
        }

        // --- Fungsi Modal Detail (Tetap Sama) ---
        function openDetailModal(tagihanData) {
            detailTagihanContent.innerHTML = ''; // Kosongkan dulu
            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            let statusText = tagihanData.status === 'Belum Lunas' ? 'Belum Dibayarkan' : tagihanData.status;
            const isLunas = tagihanData.status === 'Lunas';
            const pembayaran = tagihanData.pembayaran;
            const isDibatalkan = pembayaran && pembayaran.status_dibatalkan;

            if (isDibatalkan) {
                statusText = 'Dibatalkan';
            }

            const tglJatuhTempo = tagihanData.tanggal_jatuh_tempo ? new Date(tagihanData.tanggal_jatuh_tempo).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric'}) : '-';
            const tglBayar = pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric'}) : '-';
            let tglPembatalan = '-';
            if (pembayaran && pembayaran.tanggal_pembatalan) {
                const pembatalanDate = new Date(pembayaran.tanggal_pembatalan);
                const dateStr = pembatalanDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric'});
                const timeStr = pembatalanDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit'});
                tglPembatalan = `${dateStr} pukul ${timeStr}`;
            }

            // Container utama untuk detail
            const detailContainer = document.createElement('div');
            detailContainer.className = 'space-y-4';

            // Grid untuk informasi detail
            const detailGrid = document.createElement('div');
            detailGrid.className = 'grid grid-cols-[max-content,max-content,1fr] gap-y-2 gap-x-3 text-sm';

            function addDetailRow(label, value) {
                const labelSpan = document.createElement('span');
                labelSpan.className = 'font-medium text-gray-700';
                labelSpan.textContent = label;
                const colonSpan = document.createElement('span');
                colonSpan.textContent = ':';
                colonSpan.className = 'text-gray-500';
                const valueSpan = document.createElement('span');
                valueSpan.textContent = value ?? '-';
                valueSpan.className = 'text-gray-900';
                detailGrid.appendChild(labelSpan);
                detailGrid.appendChild(colonSpan);
                detailGrid.appendChild(valueSpan);
            }
            function addStatusRow(label, statusHtml) {
                const labelSpan = document.createElement('span');
                labelSpan.className = 'font-medium text-gray-700';
                labelSpan.textContent = label;
                const colonSpan = document.createElement('span');
                colonSpan.textContent = ':';
                colonSpan.className = 'text-gray-500';
                const valueSpan = document.createElement('span');
                valueSpan.innerHTML = statusHtml;
                detailGrid.appendChild(labelSpan);
                detailGrid.appendChild(colonSpan);
                detailGrid.appendChild(valueSpan);
            }

            // Informasi dasar tagihan
            addDetailRow('Kode Pembayaran', tagihanData.kode_pembayaran);
            addDetailRow('Nama Mahasiswa', tagihanData.mahasiswa?.user?.nama_lengkap);
            addDetailRow('NPM', tagihanData.mahasiswa?.npm);
            addDetailRow('Jenis Tagihan', tagihanData.tarif?.nama_pembayaran);
            addDetailRow('Jumlah', rupiahFormat.format(tagihanData.jumlah_tagihan));

            // Status badge
            let statusBadgeClass = 'bg-yellow-100 text-yellow-800';
            if (isDibatalkan) {
                statusBadgeClass = 'bg-red-100 text-red-800';
            } else if (isLunas) {
                statusBadgeClass = 'bg-green-100 text-green-800';
            }
            const statusBadgeHtml = `<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusBadgeClass}">${statusText}</span>`;
            addStatusRow('Status', statusBadgeHtml);

            addDetailRow('Tgl Jatuh Tempo', tglJatuhTempo);

            // Informasi pembayaran jika ada
            if (pembayaran) {
                addDetailRow('Tgl Bayar', tglBayar);
                addDetailRow('ID Pembayaran', pembayaran.pembayaran_id);
                addDetailRow('Metode Bayar', pembayaran.metode_pembayaran);
                addDetailRow('Diverifikasi Oleh', pembayaran.user_kasir?.nama_lengkap || '-');
            } else {
                addDetailRow('Tgl Bayar', '-');
            }

            detailContainer.appendChild(detailGrid);

            // Tampilkan informasi pembatalan jika ada (HANYA SEKALI, di luar grid)
            if (pembayaran && isDibatalkan && pembayaran.alasan_pembatalan) {
                const pembatalanDiv = document.createElement('div');
                pembatalanDiv.className = 'mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm';
                pembatalanDiv.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-sm text-red-900 mb-2">Pembayaran Dibatalkan</p>
                            <div class="mb-3">
                                <p class="text-xs text-red-700 font-medium mb-1">Tanggal Pembatalan:</p>
                                <p class="text-sm text-red-900">${tglPembatalan}</p>
                            </div>
                            <div class="mb-3">
                                <p class="text-xs text-red-700 font-medium mb-1.5">Alasan Pembatalan:</p>
                                <div class="bg-white p-3 rounded border border-red-200">
                                    <p class="text-sm text-red-900 leading-relaxed">${pembayaran.alasan_pembatalan}</p>
                                </div>
                            </div>
                            <p class="text-xs text-red-600 italic mt-2">Pembayaran ini dibatalkan oleh kasir karena ditemukan kesalahan pada saat verifikasi.</p>
                        </div>
                    </div>
                `;
                detailContainer.appendChild(pembatalanDiv);
            }

            detailTagihanContent.appendChild(detailContainer);
            detailTagihanModal.classList.remove('hidden');
            detailTagihanModal.classList.add('flex');
        }
        function closeDetailModal() {
            detailTagihanModal.classList.add('hidden');
            detailTagihanModal.classList.remove('flex');
            detailTagihanContent.innerHTML = '<div class="text-center text-gray-500 py-4">Memuat detail...</div>';
        }

        // --- Fungsi Hapus (DENGAN SWEETALERT) ---
        function deleteTagihan(tagihanId, namaTagihan = 'ini') {
            Swal.fire({
                title: 'Anda Yakin?',
                text: `Yakin ingin menghapus tagihan ${namaTagihan}? Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = `{{ url('api/admin/tagihan') }}/${tagihanId}`;
                    apiRequest(deleteUrl, 'DELETE').then(response => {
                        Swal.fire({ icon: 'success', title: 'Dihapus!', text: response.message || 'Tagihan berhasil dihapus.', timer: 1500, showConfirmButton: false });
                        loadPayments();
                    }).catch(err => {
                         Swal.fire({ icon: 'error', title: 'Gagal Hapus', text: 'Gagal menghapus tagihan: ' + err.message });
                    });
                }
            });
        }

        // -------------------------------------
        // EVENT LISTENERS
        // -------------------------------------
        // Add null checks before adding event listeners
        if (!paymentTableBody) {
            console.error('paymentTableBody not found');
        }
        if (!tagihanModal || !modalTitle || !tagihanForm || !tagihanIdInput) {
            console.error('Modal elements not found');
        }
        if (!filterStatusSelect || !filterJenisSelect || !filterNamaMahasiswaInput) {
            console.error('Filter elements not found');
        }

        // Listener Tombol Tambah & Modal
        if (addTagihanBtn) {
            addTagihanBtn.addEventListener('click', () => openTagihanModal('add'));
        }
        if (closeModalButton) {
            closeModalButton.addEventListener('click', closeTagihanModal);
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeTagihanModal);
        }
        if (tagihanModal) {
            window.addEventListener('click', (event) => { if (event.target == tagihanModal) { closeTagihanModal(); } });
        }
        // Listener Dropdown di Modal
        if (angkatanFilterSelect) {
            angkatanFilterSelect.addEventListener('change', filterMahasiswaDropdown);
        }
        // Dengarkan event change native (Select2 juga mem-bubble event ini)
        if (mahasiswaSelect) {
            mahasiswaSelect.addEventListener('change', filterTarifDropdown);
        }

        // !! Perbarui Jumlah Tagihan !!
        if (tarifSelect) {
            tarifSelect.addEventListener('change', function() {
                const selectedTarifId = this.value;
                const selectedTarif = allTarifsData.find(t => t.tarif_id == selectedTarifId);

                if (selectedTarif) {
                    // Set nilai Cleave.js
                    cleaveJumlah.setRawValue(selectedTarif.nominal);
                    // Kode pembayaran akan di-generate oleh backend saat simpan
                } else {
                    cleaveJumlah.setRawValue(''); // Set ke kosong
                }
            });
        }

        // Listener untuk Tombol Aksi di Tabel
        if (paymentTableBody) {
            paymentTableBody.addEventListener('click', function(event) {
            const button = event.target.closest('button, a'); // Tangkap <a> (print) atau <button> (view, edit, delete)
            if (!button) return;

            // Cek jika tombol print (tag <a>), biarkan browser menangani
            if (button.classList.contains('print-btn')) {
                return;
            }

            // Untuk <button> (view, edit, delete)
            event.preventDefault(); // Cegah aksi default jika ada
            const tagihanId = button.dataset.id;
            const namaTagihan = button.closest('tr')?.querySelector('td:nth-child(3)')?.textContent.trim() || `ID ${tagihanId}`;

            if (button.classList.contains('view-btn')) {
                 const detailUrl = `{{ url('api/admin/tagihan') }}/${tagihanId}`;
                 apiRequest(detailUrl).then(response => openDetailModal(response.data || response))
                 .catch(err => { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat detail: ' + err.message }); });
            } else if (button.classList.contains('edit-btn')) {
                 const detailUrl = `{{ url('api/admin/tagihan') }}/${tagihanId}`;
                 apiRequest(detailUrl).then(response => openTagihanModal('edit', response.data || response))
                 .catch(err => { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data untuk edit: ' + err.message }); });
            } else if (button.classList.contains('delete-btn')) {
                deleteTagihan(tagihanId, namaTagihan);
            }
            });
        }

        // Listener untuk tombol close di Modal Detail
        if (detailCloseButtons && detailCloseButtons.length > 0) {
            detailCloseButtons.forEach(button => { button.addEventListener('click', closeDetailModal); });
        }
        if (detailTagihanModal) {
            window.addEventListener('click', (event) => { if (event.target == detailTagihanModal) { closeDetailModal(); } });
        }

        // Listener Form Submit Modal (DENGAN SWEETALERT)
        if (tagihanForm) {
            tagihanForm.addEventListener('submit', function(event) {
             event.preventDefault();
             const id = tagihanIdInput.value; const isEdit = !!id;
             const url = isEdit ? `{{ url('api/admin/tagihan') }}/${id}` : "{{ route('admin.payments.tagihan.create') }}";
             const method = isEdit ? 'PUT' : 'POST';
            const formData = { mahasiswa_id: mahasiswaSelect.value, tarif_id: tarifSelect.value, jumlah_tagihan: parseRupiahToNumber(jumlahInput.value), tanggal_jatuh_tempo: toYmdFromDdMmYyyy(tglJatuhTempoInput.value), kode_pembayaran: kodeInput.value, };

             if (!formData.mahasiswa_id || !formData.tarif_id || !formData.tanggal_jatuh_tempo) {
                 Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Harap lengkapi semua field (Mahasiswa, Tarif, Tgl. Jatuh Tempo)!' });
                 return;
             }

             const submitButton = this.querySelector('button[type="submit"]');
             setButtonLoading(submitButton, true, 'Menyimpan...');

             apiRequest(url, method, formData).then(response => {
                 Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Data berhasil disimpan.', timer: 1500, showConfirmButton: false });
                 closeTagihanModal();
                 loadPayments();
             }).catch(err => {
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
                        li.textContent = e.join(', '); // AMAN
                        ul.appendChild(li);
                    });
                    errorContent.appendChild(ul);
                } else {
                    errorContent = err.message || 'Terjadi kesalahan.';
                }
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: errorContent });
             }).finally(() => {
                 setButtonLoading(submitButton, false);
                 tagihanIdInput.value = '';
             });
        });
        }

         // Listener untuk Filter Tabel
         if (filterStatusSelect) {
             filterStatusSelect.addEventListener('change', applyFilters);
         }
         if (filterJenisSelect) {
             filterJenisSelect.addEventListener('change', applyFilters);
         }
         if (filterNamaMahasiswaInput) {
             // Gunakan debounce untuk performa yang lebih baik
             let debounceTimer;
             filterNamaMahasiswaInput.addEventListener('input', function() {
                 clearTimeout(debounceTimer);
                 debounceTimer = setTimeout(() => {
                     applyFilters();
                 }, 300);
             });
         }

        // -------------------------------------
        // MUAT DATA AWAL (DENGAN LOGIKA URL PARAM)
        // -------------------------------------
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const statusFilter = urlParams.get('status');
            if (statusFilter === 'pending') {
                filterStatusSelect.value = 'Belum Dibayarkan';
            } else if (statusFilter === 'lunas') {
                filterStatusSelect.value = 'Lunas';
            }
        } catch (e) {
            console.warn("Gagal membaca URL parameter.", e);
        }

        loadPayments();
    });
</script>
@endpush
