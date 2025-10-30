@extends('layouts.app')

@section('title', 'Admin - Manajemen Tarif')
@section('page-title', 'Manajemen Tarif')

@section('content')
    {{-- Tombol Tambah Tarif --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 self-start">💰 Master Data Tarif</h2>
        <button id="addTarifBtn" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Tarif
        </button>
    </div>

    {{-- Filter Dropdowns --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        {{-- Filter Nama Pembayaran --}}
        <div class="relative flex-grow">
            <select id="filterNama" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                <option value="">Semua Nama Pembayaran</option>
                {{-- Opsi akan diisi oleh JavaScript --}}
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </div>
        </div>
        {{-- Filter Program Studi --}}
        <div class="relative">
            <select id="filterProdi" class="block w-full sm:w-48 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                <option value="">Semua Prodi</option>
                {{-- Opsi akan diisi oleh JavaScript --}}
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </div>
        </div>
        {{-- Filter Angkatan --}}
        <div class="relative">
            <select id="filterAngkatan" class="block w-full sm:w-48 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                <option value="">Semua Angkatan</option>
                {{-- Opsi akan diisi oleh JavaScript --}}
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </div>
        </div>
    </div>

    {{-- Tabel Tarif --}}
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pembayaran</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Angkatan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tarif-table-body" class="bg-white divide-y divide-gray-200 text-sm">
                    <tr><td colspan="5" class="text-center py-10 text-gray-500">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

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
        const tarifTableBody = document.getElementById('tarif-table-body');

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

        // -------------------------------------
        // FUNGSI API REQUEST (Dengan SweetAlert Error)
        // -------------------------------------
        async function apiRequest(url, method = 'GET', body = null) {
            if (!apiToken) {
                Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '/login'; });
                return Promise.reject('No API Token');
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const options = { method: method, headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'X-CSRF-TOKEN': csrfToken } };
            if (body) { options.body = JSON.stringify(body); options.headers['Content-Type'] = 'application/json'; }
            if (method === 'DELETE') { delete options.headers['Content-Type']; }

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

                if (!response.ok && response.status === 422 && data.errors) {
                    console.error('Validation errors:', data.errors);
                    throw { status: 422, errors: data.errors, message: data.message || 'Validation failed' };
                }
                if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); }
                return data;
            } catch (error) {
                console.error("Error in apiRequest:", error);
                if (error.status === 422) throw error;
                throw new Error(error.message || 'Gagal memproses permintaan.');
            }
        }

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
            tarifTableBody.innerHTML = '';
            if (!dataToRender || dataToRender.length === 0) {
                const message = (currentFilters.nama || currentFilters.prodi || currentFilters.angkatan) ? 'Tidak ada data tarif yang cocok dengan filter.' : 'Belum ada data tarif.';
                tarifTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-gray-500">${message}</td></tr>`;
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
                        <button class="edit-btn text-gray-500 hover:text-yellow-600" data-id="${tarif.tarif_id}" title="Edit">
                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z"></path></svg>
                        </button>
                        <button class="delete-btn text-gray-500 hover:text-red-600" data-id="${tarif.tarif_id}" title="Hapus">
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
                // Logika ini sudah benar, 'Semua Jurusan' akan memfilter 'null' di database
                const namaMatch = !currentFilters.nama || tarif.nama_pembayaran === currentFilters.nama;
                const prodiMatch = currentFilters.prodi === "" || (tarif.program_studi === currentFilters.prodi) || (!tarif.program_studi && currentFilters.prodi === "Semua Jurusan");
                const angkatanMatch = currentFilters.angkatan === "" || (tarif.angkatan === currentFilters.angkatan) || (!tarif.angkatan && currentFilters.angkatan === "Semua Angkatan");
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
            // Logika ini sudah benar, menambah 'Semua Jurusan' jika ada data 'null'
            if(allTarifs.some(t => !t.program_studi)){ const o=document.createElement('option'); o.value="Semua Jurusan"; o.textContent="Semua Jurusan"; filterProdiSelect.appendChild(o); }
            filterAngkatanSelect.innerHTML = '<option value="">Semua Angkatan</option>'; Array.from(uniqueAngkatan).sort((a,b)=>b.localeCompare(a,undefined,{numeric:true})).forEach(angkatan => { const option = document.createElement('option'); option.value = angkatan; option.textContent = angkatan; filterAngkatanSelect.appendChild(option); });
            if(allTarifs.some(t => !t.angkatan)){ const o=document.createElement('option'); o.value="Semua Angkatan"; o.textContent="Semua Angkatan"; filterAngkatanSelect.appendChild(o); }
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
        filterNamaSelect.addEventListener('change', applyFilters);
        filterProdiSelect.addEventListener('change', applyFilters);
        filterAngkatanSelect.addEventListener('change', applyFilters);

        document.getElementById('addTarifBtn').addEventListener('click', () => openModal('add'));
        document.getElementById('cancelBtn').addEventListener('click', closeModal);
        tarifModal.querySelector('.close-button').addEventListener('click', closeModal);
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

            const submitButton = this.querySelector('button[type="submit"]'); const originalText = submitButton.textContent; submitButton.textContent = 'Menyimpan...'; submitButton.disabled = true;

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
                submitButton.textContent = originalText; submitButton.disabled = false;
            });
        });

        // -------------------------------------
        // MUAT DATA AWAL (Dengan SweetAlert Error)
        // -------------------------------------
        function loadTarifs() {
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
                tarifTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat data. Silakan refresh halaman.</td></tr>`;
            }); // <--- Kurung kurawal penutup untuk .catch
        } // <--- Kurung kurawal penutup untuk function loadTarifs

        // Panggil inisialisasi elemen modal SETELAH DOM ready
        initializeModalElements();
        // Baru muat data
        loadTarifs();
    });
</script>
@endpush
