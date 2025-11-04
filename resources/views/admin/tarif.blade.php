@extends('layouts.app')

@section('title', 'Admin - Manajemen Tarif')
@section('page-title', 'Manajemen Tarif')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Master Data Tarif']
    ]" />

    <x-page-header
        title="Master Data Tarif"
        subtitle="Kelola tarif pembayaran untuk mahasiswa"
        :icon="$headerIcon">
        <x-slot:actions>
            <x-gradient-button id="addTarifBtn" variant="primary" size="md" aria-label="Tambah Tarif">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Tarif
            </x-gradient-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Dropdowns --}}
    <x-card title="Filter">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Filter Nama Pembayaran --}}
            <div class="relative flex-grow">
                <select id="filterNama" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Nama Pembayaran</option>
                    {{-- Opsi akan diisi oleh JavaScript --}}
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </div>
            </div>
            {{-- Filter Program Studi --}}
            <div class="relative">
                <select id="filterProdi" class="block w-full sm:w-48 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Prodi</option>
                    {{-- Opsi akan diisi oleh JavaScript --}}
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </div>
            </div>
            {{-- Filter Angkatan --}}
            <div class="relative">
                <select id="filterAngkatan" class="block w-full sm:w-48 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 sm:text-sm">
                    <option value="">Semua Angkatan</option>
                    {{-- Opsi akan diisi oleh JavaScript --}}
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Tabel Tarif --}}
    <x-data-table
        :headers="['Nama Pembayaran', 'Nominal', 'Program Studi', 'Angkatan', 'Aksi']"
        aria-label="Tabel master tarif">
        <tr id="loading-row">
            <td colspan="5" class="text-center py-10 text-gray-500">Memuat data...</td>
        </tr>
    </x-data-table>

    {{-- Modal Tambah/Edit Tarif --}}
    <div id="tarifModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900 bg-opacity-60">
        <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg w-full">
            <form id="tarifForm">
                <input type="hidden" id="tarifId" name="tarif_id">
                <div class="bg-white px-6 pt-5 pb-4">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Tambah Tarif Baru</h3>
                        <button type="button" class="close-button text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="nama_pembayaran" class="block text-sm font-medium text-gray-700">Nama Pembayaran</label>
                            <select id="nama_pembayaran" name="nama_pembayaran" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="" disabled selected>Pilih Jenis Pembayaran</option>
                                <option value="Uang Semester">Uang Semester</option>
                                <option value="Uang Ujian Akhir">Uang Ujian Akhir</option>
                                <option value="Uang Pembangunan">Uang Pembangunan</option>
                                <option value="Uang Kemahasiswaan">Uang Kemahasiswaan</option>
                                <option value="Uang KP">Uang KP</option>
                                <option value="Uang Skripsi">Uang Skripsi</option>
                                <option value="Uang Wisuda">Uang Wisuda</option>
                            </select>
                        </div>
                        <div>
                            <label for="nominal" class="block text-sm font-medium text-gray-700">Nominal (Rp)</label>
                            <input type="text" id="nominal" name="nominal" inputmode="numeric" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="program_studi" class="block text-sm font-medium text-gray-700">Program Studi</label>
                            <select id="program_studi" name="program_studi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Semua Jurusan">Berlaku untuk Semua Jurusan</option>
                                <option value="S1 Teknik Sipil">S1 Teknik Sipil</option>
                                <option value="D3 Teknik Komputer">D3 Teknik Komputer</option>
                                <option value="S1 Informatika">S1 Informatika</option>
                            </select>
                        </div>
                        <div>
                            <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan</label>
                            <select id="angkatan" name="angkatan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="Semua Angkatan">Berlaku untuk Semua Angkatan</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                            <span id="angkatanWarning" class="text-xs text-yellow-600 mt-1 hidden"></span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        // --- Bagian Umum ---
        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        // Find tbody by finding the table with aria-label and then its tbody
        const tarifTable = document.querySelector('table[aria-label="Tabel master tarif"]');
        const tarifTableBody = tarifTable ? tarifTable.querySelector('tbody') : null;

        // --- Bagian Filter Tabel ---
        const filterNamaSelect = document.getElementById('filterNama');
        const filterProdiSelect = document.getElementById('filterProdi');
        const filterAngkatanSelect = document.getElementById('filterAngkatan');
        let allTarifs = [];
        let currentFilters = { nama: '', prodi: '', angkatan: '' };

        // --- Bagian Modal Tambah/Edit ---
        const tarifModal = document.getElementById('tarifModal');
        const modalTitle = document.getElementById('modalTitle');
        const tarifForm = document.getElementById('tarifForm');
        const tarifIdInput = document.getElementById('tarifId');
        let modalNamaPembayaranSelect, modalAngkatanSelect, modalAngkatanWarning, modalNominalInput;

        function initializeModalElements() {
            modalNamaPembayaranSelect = tarifModal.querySelector('#nama_pembayaran');
            modalAngkatanSelect = tarifModal.querySelector('#angkatan');
            modalAngkatanWarning = tarifModal.querySelector('#angkatanWarning');
            modalNominalInput = tarifModal.querySelector('#nominal');
            attachModalListeners();
        }

        // Pakai util global apiRequest
        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) { console.error('apiRequest util tidak tersedia'); }

        // -------------------------------------
        // FUNGSI FORMAT NOMINAL (Tetap Sama)
        // -------------------------------------
        const formatter = new Intl.NumberFormat('id-ID');
        function formatNumberInput(inputElement) { let value = inputElement.value.replace(/[^,\d]/g,'').toString(); if(value.length>1&&value.startsWith('0')&&!value.includes(',')){value=value.substring(1);} const number=parseInt(value.replace(/\./g,''),10); inputElement.value=isNaN(number)?'':formatter.format(number); }
        function unformatNumber(formattedValue) { return formattedValue.replace(/\./g, ''); }

        // -------------------------------------
        // FUNGSI LOGIKA TABEL & FILTER
        // -------------------------------------

        // ===================================================
        // !! PERBAIKAN KEAMANAN XSS (Stored) DI SINI !!
        // ===================================================
        function renderTable(dataToRender) {
            if (!tarifTableBody) {
                console.error('tarifTableBody not found in renderTable');
                return;
            }
            tarifTableBody.innerHTML = '';
            if (!dataToRender || dataToRender.length === 0) {
                const isFiltering = (currentFilters.nama || currentFilters.prodi || currentFilters.angkatan);
                renderEmptyState(tarifTableBody, {
                    colspan: 5,
                    title: isFiltering ? 'Tidak ada data' : 'Belum ada data tarif',
                    message: isFiltering ? 'Tidak ada data tarif yang cocok dengan filter yang dipilih.' : 'Silakan tambahkan tarif baru untuk memulai.',
                    icon: `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    `
                });
                return;
            }

            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

            dataToRender.forEach(tarif => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';

                // 1. Sel Nama Pembayaran (Aman)
                const cellNama = document.createElement('td');
                cellNama.className = 'px-6 py-4 whitespace-nowrap font-medium text-gray-900';
                cellNama.textContent = tarif.nama_pembayaran; // AMAN
                tr.appendChild(cellNama);

                // 2. Sel Nominal (Aman)
                const cellNominal = document.createElement('td');
                cellNominal.className = 'px-6 py-4 whitespace-nowrap text-right text-gray-700';
                cellNominal.textContent = rupiahFormat.format(tarif.nominal); // AMAN
                tr.appendChild(cellNominal);

                // 3. Sel Program Studi (Aman)
                const cellProdi = document.createElement('td');
                cellProdi.className = 'px-6 py-4 whitespace-nowrap text-gray-500';
                // Logika ini sudah benar, mengandalkan controller mengirim NULL
                cellProdi.textContent = tarif.program_studi || 'Semua Jurusan'; // AMAN
                tr.appendChild(cellProdi);

                // 4. Sel Angkatan (Aman)
                const cellAngkatan = document.createElement('td');
                cellAngkatan.className = 'px-6 py-4 whitespace-nowrap text-center text-gray-500';
                cellAngkatan.textContent = tarif.angkatan || 'Semua Angkatan'; // AMAN
                tr.appendChild(cellAngkatan);

                // 5. Sel Aksi (Aman, karena ID bukan input teks bebas)
                const cellAksi = document.createElement('td');
                cellAksi.className = 'px-6 py-4 whitespace-nowrap text-right font-medium';
                cellAksi.innerHTML = `
                    <div class="flex justify-end items-center gap-2">
                        <button class="edit-btn text-gray-500 hover:text-yellow-600" data-id="${tarif.tarif_id}" data-tooltip="Edit tarif" title="Edit">
                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z"></path></svg>
                        </button>
                        <button class="delete-btn text-gray-500 hover:text-red-600" data-id="${tarif.tarif_id}" data-tooltip="Hapus tarif" title="Hapus">
                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>`;
                tr.appendChild(cellAksi);

                tarifTableBody.appendChild(tr);
            });
        }

        function applyFilters() {
            currentFilters.nama = filterNamaSelect.value; currentFilters.prodi = filterProdiSelect.value; currentFilters.angkatan = filterAngkatanSelect.value;
            const filteredData = allTarifs.filter(tarif => {
                const namaMatch = !currentFilters.nama || tarif.nama_pembayaran === currentFilters.nama;
                const prodiMatch = currentFilters.prodi === "" || tarif.program_studi === currentFilters.prodi;
                const angkatanMatch = currentFilters.angkatan === "" || tarif.angkatan === currentFilters.angkatan;
                return namaMatch && prodiMatch && angkatanMatch;
            });
            renderTable(filteredData);
        }

        function populateFilterDropdowns() {
            const uniqueNama = new Set(); const uniqueProdi = new Set(); const uniqueAngkatan = new Set();
            allTarifs.forEach(tarif => {
                uniqueNama.add(tarif.nama_pembayaran);
                // Logika ini sudah benar, hanya menambah prodi/angkatan yg 'tidak null'
                if(tarif.program_studi) { uniqueProdi.add(tarif.program_studi); }
                if(tarif.angkatan) { uniqueAngkatan.add(tarif.angkatan); }
            });
            filterNamaSelect.innerHTML = '<option value="">Semua Nama Pembayaran</option>'; Array.from(uniqueNama).sort().forEach(nama => { const option = document.createElement('option'); option.value = nama; option.textContent = nama; filterNamaSelect.appendChild(option); });
            filterProdiSelect.innerHTML = '<option value="">Semua Prodi</option>'; Array.from(uniqueProdi).sort().forEach(prodi => { const option = document.createElement('option'); option.value = prodi; option.textContent = prodi; filterProdiSelect.appendChild(option); });
            filterAngkatanSelect.innerHTML = '<option value="">Semua Angkatan</option>'; Array.from(uniqueAngkatan).sort((a,b)=>b.localeCompare(a,undefined,{numeric:true})).forEach(angkatan => { const option = document.createElement('option'); option.value = angkatan; option.textContent = angkatan; filterAngkatanSelect.appendChild(option); });
        }

        // -------------------------------------
        // FUNGSI WARNING MODAL (Tetap Sama)
        // -------------------------------------
        const lateStagePayments = { "Uang KP": 2, "Uang Skripsi": 3, "Uang Wisuda": 3 };
        function checkAngkatanLogic() {
            if (!modalNamaPembayaranSelect || !modalAngkatanSelect || !modalAngkatanWarning) { return; }
            const selectedPayment = modalNamaPembayaranSelect.value; const selectedAngkatan = modalAngkatanSelect.value; const currentYear = new Date().getFullYear();
            modalAngkatanWarning.classList.add('hidden'); modalAngkatanWarning.textContent = '';
            if (selectedAngkatan && selectedAngkatan !== "Semua Angkatan" && lateStagePayments.hasOwnProperty(selectedPayment)) {
                const minimumYearsRequired = lateStagePayments[selectedPayment]; const angkatanYear = parseInt(selectedAngkatan);
                if (!isNaN(angkatanYear)) { const yearsPassed = currentYear - angkatanYear; if (yearsPassed < minimumYearsRequired) {
                    modalAngkatanWarning.textContent = `Peringatan: ${selectedPayment} biasanya untuk angkatan ${minimumYearsRequired}+ tahun.`;
                    modalAngkatanWarning.classList.remove('hidden');
                } }
            }
        }

        // -------------------------------------
        // FUNGSI MODAL (Dengan SweetAlert)
        // -------------------------------------
        function openModal(mode = 'add', tarifData = null) {
            tarifForm.reset(); tarifIdInput.value = ''; modalAngkatanWarning.classList.add('hidden');
            if (mode === 'add') {
                modalTitle.textContent = 'Tambah Tarif Baru';
                modalNominalInput.value = '';
            } else if (mode === 'edit' && tarifData) {
                modalTitle.textContent = 'Edit Tarif';
                tarifIdInput.value = tarifData.tarif_id;
                modalNamaPembayaranSelect.value = tarifData.nama_pembayaran;
                if(tarifData.nominal !== null && tarifData.nominal !== undefined) { modalNominalInput.value = formatter.format(tarifData.nominal); } else { modalNominalInput.value = ''; }
                // Logika ini sudah benar, mengubah NULL dari DB menjadi 'Semua Jurusan' di form
                tarifModal.querySelector('#program_studi').value = tarifData.program_studi || "Semua Jurusan";
                modalAngkatanSelect.value = tarifData.angkatan || "Semua Angkatan";
                setTimeout(checkAngkatanLogic, 50);
            }
            tarifModal.classList.remove('hidden'); tarifModal.classList.add('flex');
        }

        function closeModal() {
            tarifModal.classList.add('hidden'); tarifModal.classList.remove('flex');
        }

        // -------------------------------------
        // EVENT LISTENERS (Dengan SweetAlert)
        // -------------------------------------
        // Add null checks before adding event listeners
        if (!tarifTableBody) {
            console.error('tarifTableBody not found');
            return;
        }
        if (!filterNamaSelect || !filterProdiSelect || !filterAngkatanSelect) {
            console.error('Filter elements not found');
            return;
        }
        if (!tarifModal || !modalTitle || !tarifForm || !tarifIdInput) {
            console.error('Modal elements not found');
            return;
        }

        filterNamaSelect.addEventListener('change', applyFilters);
        filterProdiSelect.addEventListener('change', applyFilters);
        filterAngkatanSelect.addEventListener('change', applyFilters);

        const addTarifBtn = document.getElementById('addTarifBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const closeButton = tarifModal.querySelector('.close-button');

        if (addTarifBtn) {
            addTarifBtn.addEventListener('click', () => openModal('add'));
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }
        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }
        window.addEventListener('click', (event) => { if (event.target == tarifModal) closeModal(); });

        function attachModalListeners() {
            if (modalNamaPembayaranSelect && modalAngkatanSelect) {
                modalNamaPembayaranSelect.addEventListener('change', checkAngkatanLogic);
                modalAngkatanSelect.addEventListener('change', checkAngkatanLogic);
            }
            if (modalNominalInput) {
                modalNominalInput.addEventListener('input', formatNominalOnInput);
                modalNominalInput.addEventListener('blur', formatNominalOnBlur);
            }
        }
        function formatNominalOnInput() { formatNumberInput(this); }
        function formatNominalOnBlur() { formatNumberInput(this); }

        // Listener Tabel (Edit & Delete) - !! GANTI KE SWEETALERT !!
        if (tarifTableBody) {
            tarifTableBody.addEventListener('click', function(event) {
            const button = event.target.closest('button'); if (!button) return;
            const tarifId = button.dataset.id;

            if (button.classList.contains('edit-btn')) {
                const detailUrl = `{{ url('/api/admin/tarif') }}/${tarifId}`;
                apiRequest(detailUrl).then(response => openModal('edit', response.data)) // 'data' akan selalu ada
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengambil detail tarif: ' + err.message });
                });
            }

            if (button.classList.contains('delete-btn')) {
                // !! GANTI CONFIRM !!
                Swal.fire({
                    title: 'Anda Yakin?',
                    text: "Tarif yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const deleteUrl = `{{ url('/api/admin/tarif') }}/${tarifId}`;
                        apiRequest(deleteUrl, 'DELETE').then(response => {
                            // !! GANTI ALERT SUKSES !!
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: response.message || 'Tarif berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadTarifs();
                        }).catch(err => {
                            // !! GANTI ALERT ERROR !!
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal menghapus tarif: ' + err.message
                            });
                        });
                    }
                });
            }
        });
        }

        // Listener Form Submit Modal - !! GANTI KE SWEETALERT !!
        tarifForm.addEventListener('submit', function(event) {
            event.preventDefault(); const id = tarifIdInput.value; const isEdit = !!id;
            const url = isEdit ? `{{ url('/api/admin/tarif') }}/${id}` : "{{ route('admin.tarif.store') }}";
            const method = isEdit ? 'PUT' : 'POST';
            let prodiValue = tarifModal.querySelector('#program_studi').value; let angkatanValue = modalAngkatanSelect.value;
            const nominalValue = unformatNumber(modalNominalInput.value);

            // Logika ini sudah benar, mengirim NULL ke controller
            const formData = {
                nama_pembayaran: modalNamaPembayaranSelect.value,
                nominal: nominalValue,
                program_studi: prodiValue === "Semua Jurusan" ? null : prodiValue,
                angkatan: angkatanValue === "Semua Angkatan" ? null : angkatanValue,
            };

            // Validasi frontend dengan SweetAlert
            if (!formData.nama_pembayaran) {
                Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Nama Pembayaran harus dipilih.' });
                return;
            }
            if (!formData.nominal || isNaN(parseInt(formData.nominal, 10)) || parseInt(formData.nominal, 10) <= 0) {
                Swal.fire({ icon: 'warning', title: 'Input Tidak Valid', text: 'Nominal harus berupa angka positif yang valid.' });
                return;
            }

            const submitButton = this.querySelector('button[type="submit"]');
            setButtonLoading(submitButton, true, 'Menyimpan...');

            apiRequest(url, method, formData).then(response => {
                // !! GANTI ALERT SUKSES !!
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Data berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                });
                closeModal();
                loadTarifs();
            })
            // ===================================================
            // !! PERBAIKAN KEAMANAN XSS (Validasi) DI SINI !!
            // ===================================================
            .catch(err => {
                let errorContent; // Ganti nama dari errorMessages

                if (err.status === 422 && err.errors) {
                    // Jika error validasi, buat list yg aman
                    errorContent = document.createElement('div');

                    const p = document.createElement('p');
                    p.textContent = "Input tidak valid:"; // Pesan utama
                    errorContent.appendChild(p);

                    const ul = document.createElement('ul');
                    ul.className = 'list-disc list-inside text-left mt-2';
                    Object.values(err.errors).forEach(e => {
                        const li = document.createElement('li');
                        li.textContent = e.join(', '); // AMAN dari XSS
                        ul.appendChild(li);
                    });
                    errorContent.appendChild(ul);
                } else {
                    // Jika error biasa, pakai text
                    errorContent = err.message || 'Terjadi kesalahan.';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    html: errorContent // 'html' properti bisa menerima Node Element
                });
            })
            .finally(() => {
                setButtonLoading(submitButton, false);
            });
        });

        // -------------------------------------
        // MUAT DATA AWAL (Dengan SweetAlert Error)
        // -------------------------------------
        function loadTarifs() {
            if (!tarifTableBody) {
                console.error('tarifTableBody not found in loadTarifs');
                return;
            }
            tarifTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat data...</td></tr>';
            const listUrl = "{{ route('admin.tarif.index') }}";
            apiRequest(listUrl).then(response => {
                // !! PERBAIKAN KONSISTENSI: 'data' akan selalu ada
                allTarifs = response.data; // Tidak perlu '|| response' lagi
                populateFilterDropdowns();

                // ============================================
                // !! PASTIKAN BARIS INI BENAR SEKARANG !!
                // ============================================
                applyFilters(); // <--- Tidak ada 's' di awal, ada 's' di akhir

            }).catch(error => {
                console.error('Error fetching tarif list:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: `Gagal memuat daftar tarif: ${error.message}`
                });
                if (tarifTableBody) {
                    tarifTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat data. Silakan refresh halaman.</td></tr>`;
                }
            }); // <--- Kurung kurawal penutup untuk .catch
        } // <--- Kurung kurawal penutup untuk function loadTarifs

        // Panggil inisialisasi elemen modal SETELAH DOM ready
        initializeModalElements();
        // Baru muat data
        loadTarifs();
    });
</script>
@endpush
