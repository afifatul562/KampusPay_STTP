@extends('layouts.app')

@section('title', 'Admin - Manajemen Pembayaran')
@section('page-title', 'Manajemen Pembayaran')

@section('content')
    {{-- Tombol Buat Tagihan --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 self-start">💰 Data Pembayaran & Tagihan</h2>
        <button id="addTagihanBtn" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Buat Tagihan Baru
        </button>
    </div>

    {{-- Filter Dropdowns --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        {{-- Filter Status --}}
        <div class="flex-1">
            <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
            <select id="filterStatus" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                <option value="">Semua Status</option>
                <option value="Lunas">Lunas</option>
                <option value="Belum Dibayarkan">Belum Dibayarkan</option>
            </select>
        </div>
        {{-- Filter Jenis Tagihan --}}
        <div class="flex-1">
            <label for="filterJenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Tagihan</label>
            <select id="filterJenis" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                <option value="">Semua Jenis</option>
                {{-- Opsi akan diisi oleh JavaScript --}}
            </select>
        </div>
    </div>

    {{-- Tabel Pembayaran & Tagihan --}}
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Tagihan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Bayar</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="payment-table-body" class="bg-white divide-y divide-gray-200 text-sm">
                    <tr><td colspan="7" class="text-center py-10 text-gray-500">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

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
                            <select id="mahasiswa_id" name="mahasiswa_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"><option value="" disabled selected>Pilih angkatan dulu</option></select>
                        </div>
                        <div>
                            <label for="tarif_id" class="block text-sm font-medium text-gray-700">Pilih Jenis Tarif</label>
                            <select id="tarif_id" name="tarif_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500"><option value="" disabled selected>Pilih mahasiswa dulu</option></select>
                        </div>
                        <div>
                            <label for="jumlah_tagihan" class="block text-sm font-medium text-gray-700">Jumlah Tagihan (Rp)</label>
                            <input type="number" id="jumlah_tagihan" name="jumlah_tagihan" required placeholder="Akan terisi otomatis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500" readonly>
                        </div>
                        <div>
                            <label for="tanggal_jatuh_tempo" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo</label>
                            <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500">
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
         <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg w-full">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Detail Tagihan</h3>
                    <button type="button" class="detail-close-button text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="mt-4 text-base text-gray-700" id="detailTagihanContent">Memuat detail...</div>
            </div>
             <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="detail-close-button mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Bagian Umum ---
        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        const paymentTableBody = document.getElementById('payment-table-body');

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
        const filterStatusSelect = document.getElementById('filterStatus');
        const filterJenisSelect = document.getElementById('filterJenis');
        let currentFilters = { status: '', jenis: '' };

        // --- Atur Tanggal Minimum Jatuh Tempo ---
        const today = new Date();
        const yearNow = today.getFullYear();
        const monthNow = (today.getMonth() + 1).toString().padStart(2, '0');
        const dayNow = today.getDate().toString().padStart(2, '0');
        tglJatuhTempoInput.setAttribute('min', `${yearNow}-${monthNow}-${dayNow}`);

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

        // -------------------------------------
        // FUNGSI RENDER TABEL (Aksi Lengkap)
        // -------------------------------------
        function renderTable(dataToRender) {
            const tbody = document.getElementById('payment-table-body');
            tbody.innerHTML = '';
            if (!dataToRender || dataToRender.length === 0) {
                 const isFiltering = currentFilters.status || currentFilters.jenis;
                 const message = isFiltering ? 'Tidak ada data tagihan yang cocok dengan filter.' : 'Belum ada data tagihan.';
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-gray-500">${message}</td></tr>`;
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
                cellStatus.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isLunas ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${statusText}</span>`;
                tr.appendChild(cellStatus);

                // 6. Tgl Bayar
                tr.appendChild(createCell(pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-', ['text-gray-500']));

                // 7. Aksi (Pakai innerHTML tapi aman karena ID bukan input teks)
                const cellAksi = document.createElement('td');
            cellAksi.className = 'px-6 py-4 whitespace-nowrap text-right font-medium';
            // ==========================================================
            // !! MASUKKAN KEMBALI KODE SVG ASLI DI SINI !!
            // ==========================================================
            cellAksi.innerHTML = `
                <div class="flex justify-end items-center gap-2">
                    <button class="view-btn text-gray-500 hover:text-blue-600" data-id="${tagihan.tagihan_id}" title="Lihat Detail">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    ${!isLunas ? `
                    <button class="edit-btn text-gray-500 hover:text-yellow-600" data-id="${tagihan.tagihan_id}" title="Edit Tagihan">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z"></path></svg>
                    </button>` : '<span class="w-5 h-5 inline-block"></span>'}
                    ${!isLunas ? `
                    <button class="delete-btn text-gray-500 hover:text-red-600" data-id="${tagihan.tagihan_id}" title="Hapus Tagihan">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>` : '<span class="w-5 h-5 inline-block"></span>'}
                    ${isLunas && pembayaran ? `
                    <a href="{{ url('admin/pembayaran/print') }}/${pembayaran.pembayaran_id}" target="_blank" class="print-btn text-gray-500 hover:text-indigo-600" title="Cetak Bukti">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </a>` : '<span class="w-5 h-5 inline-block"></span>'}
                </div>`;
            tr.appendChild(cellAksi);

                tbody.appendChild(tr);
            });
        }

        // -------------------------------------
        // FUNGSI FILTER TABEL
        // -------------------------------------
         function applyFilters() {
             currentFilters.status = filterStatusSelect.value;
             currentFilters.jenis = filterJenisSelect.value;
             const filteredData = allTagihanData.filter(tagihan => {
                 let statusMatch = true;
                 if (currentFilters.status) {
                     let statusAsli = tagihan.status === 'Belum Lunas' ? 'Belum Dibayarkan' : tagihan.status;
                     statusMatch = statusAsli === currentFilters.status;
                 }
                 const jenisMatch = !currentFilters.jenis || (tagihan.tarif && tagihan.tarif.nama_pembayaran === currentFilters.jenis);
                 return statusMatch && jenisMatch;
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
                paymentTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500">Gagal memuat data. Silakan refresh.</td></tr>`;
            });
        }

        // --- Fungsi Modal & Form Handling (Tetap Sama) ---
        async function populateFormDropdowns() { /* ... Kode populateFormDropdowns ... */
             const mahasiswaUrl = "{{ route('admin.mahasiswa.index') }}"; const tarifUrl = "{{ route('admin.tarif.index') }}"; try { const [mahasiswaResponse, tarifResponse] = await Promise.all([apiRequest(mahasiswaUrl),apiRequest(tarifUrl)]); allMahasiswaData = mahasiswaResponse.data || mahasiswaResponse; allTarifsData = tarifResponse.data || tarifResponse; const angkatanUnik = [...new Set(allMahasiswaData.map(mhs => mhs.angkatan))].sort().reverse(); angkatanFilterSelect.innerHTML = '<option value="">Tampilkan Semua Angkatan</option>'; angkatanUnik.forEach(angkatan => { angkatanFilterSelect.innerHTML += `<option value="${angkatan}">${angkatan}</option>`; }); filterMahasiswaDropdown(); } catch (error) { console.error("Gagal memuat data form:", error); Swal.fire({ icon: 'error', title: 'Gagal Memuat Data Form', text: `Gagal memuat data mahasiswa/tarif: ${error.message}` }); mahasiswaSelect.innerHTML = '<option value="">Gagal memuat</option>'; tarifSelect.innerHTML = '<option value="">Gagal memuat</option>';}
        }
        function filterMahasiswaDropdown() { /* ... Kode filterMahasiswaDropdown ... */
            const selectedAngkatan = angkatanFilterSelect.value; const filteredMahasiswa = allMahasiswaData.filter(mhs => !selectedAngkatan || (mhs.angkatan == selectedAngkatan)); mahasiswaSelect.innerHTML = '<option value="" disabled selected>Pilih Mahasiswa</option>'; filteredMahasiswa.forEach(mhs => { mahasiswaSelect.innerHTML += `<option value="${mhs.mahasiswa_id}" data-prodi="${mhs.program_studi}" data-angkatan="${mhs.angkatan}">${mhs.npm} - ${mhs.user?.nama_lengkap ?? 'N/A'}</option>`; }); filterTarifDropdown();
        }
        function filterTarifDropdown() { /* ... Kode filterTarifDropdown ... */
            const selectedMahasiswaOption = mahasiswaSelect.options[mahasiswaSelect.selectedIndex]; if (!selectedMahasiswaOption || !selectedMahasiswaOption.value) { tarifSelect.innerHTML = '<option value="" disabled selected>Pilih mahasiswa dulu</option>'; jumlahInput.value = ''; kodeInput.value = ''; return; } const prodiMhs = selectedMahasiswaOption.dataset.prodi; const angkatanMhs = selectedMahasiswaOption.dataset.angkatan; const filteredTarifs = allTarifsData.filter(tarif => { const prodiCocok = !tarif.program_studi || tarif.program_studi === prodiMhs; const angkatanCocok = !tarif.angkatan || tarif.angkatan === angkatanMhs; return prodiCocok && angkatanCocok; }); tarifSelect.innerHTML = '<option value="" disabled selected>Pilih Jenis Tarif</option>'; filteredTarifs.forEach(tarif => { tarifSelect.innerHTML += `<option value="${tarif.tarif_id}">${tarif.nama_pembayaran} (${tarif.program_studi || 'Semua'}, ${tarif.angkatan || 'Semua'})</option>`; }); jumlahInput.value = ''; kodeInput.value = ''; tarifSelect.dispatchEvent(new Event('change'));
        }
        function openTagihanModal(mode = 'add', tagihanData = null) { /* ... Kode openTagihanModal ... */
             tagihanForm.reset(); tagihanIdInput.value = ''; jumlahInput.value = ''; kodeInput.value = ''; angkatanFilterSelect.value = ''; mahasiswaSelect.innerHTML = '<option value="" disabled selected>Pilih angkatan dulu</option>'; tarifSelect.innerHTML = '<option value="" disabled selected>Pilih mahasiswa dulu</option>'; if (mode === 'add') { modalTitle.textContent = 'Buat Tagihan Baru'; populateFormDropdowns(); } else if (mode === 'edit' && tagihanData) { modalTitle.textContent = 'Edit Tagihan'; tagihanIdInput.value = tagihanData.tagihan_id; populateFormDropdowns().then(() => { angkatanFilterSelect.value = tagihanData.mahasiswa?.angkatan ?? ''; angkatanFilterSelect.dispatchEvent(new Event('change')); setTimeout(() => { mahasiswaSelect.value = tagihanData.mahasiswa_id; mahasiswaSelect.dispatchEvent(new Event('change')); setTimeout(() => { tarifSelect.value = tagihanData.tarif_id; jumlahInput.value = tagihanData.jumlah_tagihan; tglJatuhTempoInput.value = tagihanData.tanggal_jatuh_tempo ? tagihanData.tanggal_jatuh_tempo.split('T')[0] : ''; kodeInput.value = tagihanData.kode_pembayaran; tarifSelect.dispatchEvent(new Event('change')); }, 150); }, 150); }); } tagihanModal.classList.remove('hidden'); tagihanModal.classList.add('flex');
        }
        function closeTagihanModal() { /* ... Kode closeTagihanModal ... */ tagihanModal.classList.add('hidden'); tagihanModal.classList.remove('flex'); }

        // --- Fungsi Modal Detail (Tetap Sama) ---
        function openDetailModal(tagihanData) {
            detailTagihanContent.innerHTML = ''; // Kosongkan dulu
            const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            let statusText = tagihanData.status === 'Belum Lunas' ? 'Belum Dibayarkan' : tagihanData.status;
            const isLunas = tagihanData.status === 'Lunas';
            const pembayaran = tagihanData.pembayaran;
            const tglJatuhTempo = tagihanData.tanggal_jatuh_tempo ? new Date(tagihanData.tanggal_jatuh_tempo).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';
            const tglBayar = pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric'}) : '-';

            const detailGrid = document.createElement('div');
            detailGrid.className = 'grid grid-cols-[max-content,max-content,1fr] gap-y-2 gap-x-3 text-base';

            // Helper untuk menambah baris detail (AMAN)
            function addDetailRow(label, value) {
                const labelSpan = document.createElement('span');
                labelSpan.className = 'font-medium text-gray-900';
                labelSpan.textContent = label;

                const colonSpan = document.createElement('span');
                colonSpan.textContent = ':';

                const valueSpan = document.createElement('span');
                valueSpan.textContent = value ?? '-'; // Default ke '-' jika null/undefined

                detailGrid.appendChild(labelSpan);
                detailGrid.appendChild(colonSpan);
                detailGrid.appendChild(valueSpan);
            }

            // Helper untuk menambah baris status (innerHTML aman)
            function addStatusRow(label, statusHtml) {
                const labelSpan = document.createElement('span');
                labelSpan.className = 'font-medium text-gray-900';
                labelSpan.textContent = label;
                const colonSpan = document.createElement('span'); colonSpan.textContent = ':';
                const valueSpan = document.createElement('span');
                valueSpan.innerHTML = statusHtml; // Aman karena HTML dari logic kita

                detailGrid.appendChild(labelSpan); detailGrid.appendChild(colonSpan); detailGrid.appendChild(valueSpan);
            }

            addDetailRow('Kode Pembayaran', tagihanData.kode_pembayaran);
            addDetailRow('Nama Mahasiswa', tagihanData.mahasiswa?.user?.nama_lengkap);
            addDetailRow('NPM', tagihanData.mahasiswa?.npm);
            addDetailRow('Jenis Tagihan', tagihanData.tarif?.nama_pembayaran);
            addDetailRow('Jumlah', rupiahFormat.format(tagihanData.jumlah_tagihan));

            const statusBadgeHtml = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isLunas ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${statusText}</span>`;
            addStatusRow('Status', statusBadgeHtml);

            addDetailRow('Tgl Jatuh Tempo', tglJatuhTempo);
            addDetailRow('Tgl Bayar', tglBayar);

            if (pembayaran) {
                addDetailRow('ID Pembayaran', pembayaran.pembayaran_id);
                addDetailRow('Metode Bayar', pembayaran.metode_pembayaran);
                addDetailRow('Diverifikasi Oleh', pembayaran.user_kasir?.nama_lengkap);
            }

            detailTagihanContent.appendChild(detailGrid);
            detailTagihanModal.classList.remove('hidden');
            detailTagihanModal.classList.add('flex');
        }
        function closeDetailModal() { /* ... Kode closeDetailModal ... */ detailTagihanModal.classList.add('hidden'); detailTagihanModal.classList.remove('flex'); detailTagihanContent.innerHTML = 'Memuat detail...'; }

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
        // Listener Tombol Tambah & Modal
        addTagihanBtn.addEventListener('click', () => openTagihanModal('add'));
        closeModalButton.addEventListener('click', closeTagihanModal);
        cancelBtn.addEventListener('click', closeTagihanModal);
        window.addEventListener('click', (event) => { if (event.target == tagihanModal) { closeTagihanModal(); } });
        // Listener Dropdown di Modal
        angkatanFilterSelect.addEventListener('change', filterMahasiswaDropdown);
        mahasiswaSelect.addEventListener('change', filterTarifDropdown);
        tarifSelect.addEventListener('change', function() { /* ... Kode listener tarifSelect (Kode Invoice) ... */
             const selectedTarifId = this.value; const selectedTarif = allTarifsData.find(t => t.tarif_id == selectedTarifId); if (selectedTarif) { jumlahInput.value = selectedTarif.nominal; let paymentCode = "INV"; const namaPembayaran = selectedTarif.nama_pembayaran; const today = new Date(); const year = today.getFullYear().toString().slice(-2); const month = (today.getMonth() + 1).toString().padStart(2, '0'); const day = today.getDate().toString().padStart(2, '0'); const datePart = `${year}${month}${day}`; switch (namaPembayaran) { case "Uang Semester": paymentCode = "SMT"; break; case "Uang Pembangunan": paymentCode = "PBN"; break; case "Uang Skripsi": paymentCode = "SKR"; break; case "Uang Wisuda": paymentCode = "WSD"; break; case "Uang KP": paymentCode = "KP"; break; case "Uang Kemahasiswaan": paymentCode = "KHS"; break; case "Uang Ujian Akhir": paymentCode = "UAS"; break; default: paymentCode = "OTH"; } kodeInput.value = `INV-${paymentCode}-${datePart}-${selectedTarifId}`; } else { jumlahInput.value = ''; kodeInput.value = ''; }
        });

        // Listener untuk Tombol Aksi di Tabel
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

        // Listener untuk tombol close di Modal Detail
         detailCloseButtons.forEach(button => { button.addEventListener('click', closeDetailModal); });
         window.addEventListener('click', (event) => { if (event.target == detailTagihanModal) { closeDetailModal(); } });

        // Listener Form Submit Modal (DENGAN SWEETALERT)
        tagihanForm.addEventListener('submit', function(event) {
             event.preventDefault();
             const id = tagihanIdInput.value; const isEdit = !!id;
             const url = isEdit ? `{{ url('api/admin/tagihan') }}/${id}` : "{{ route('admin.payments.tagihan.create') }}";
             const method = isEdit ? 'PUT' : 'POST';
             const formData = { mahasiswa_id: mahasiswaSelect.value, tarif_id: tarifSelect.value, jumlah_tagihan: jumlahInput.value, tanggal_jatuh_tempo: tglJatuhTempoInput.value, kode_pembayaran: kodeInput.value, };

             if (!formData.mahasiswa_id || !formData.tarif_id || !formData.tanggal_jatuh_tempo) {
                 Swal.fire({ icon: 'warning', title: 'Input Tidak Lengkap', text: 'Harap lengkapi semua field (Mahasiswa, Tarif, Tgl. Jatuh Tempo)!' });
                 return;
             }

             const submitButton = this.querySelector('button[type="submit"]'); const originalText = submitButton.textContent; submitButton.textContent = 'Menyimpan...'; submitButton.disabled = true;

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
                 submitButton.textContent = originalText; submitButton.disabled = false;
                 tagihanIdInput.value = '';
             });
        });

         // Listener untuk Filter Tabel
         filterStatusSelect.addEventListener('change', applyFilters);
         filterJenisSelect.addEventListener('change', applyFilters);

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
