@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Proses Pembayaran Tunai')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <a href="{{ route('kasir.transaksi.index', ['filter' => 'hari_ini']) }}" class="block rounded-2xl transition-all hover:scale-[1.02] hover:shadow-xl">
            <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5 h-full">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-500">Transaksi Hari Ini</div>
                    <div id="transaksi-hari-ini" class="text-2xl font-bold text-gray-900 mt-1 truncate">...</div>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path></svg>
                </div>
            </div>
        </a>
        <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Penerimaan Hari Ini</div>
                <div id="total-penerimaan" class="text-2xl font-bold text-gray-900 mt-1 truncate">...</div>
            </div>
            <div class="bg-green-100 p-3 rounded-full"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
        </div>
        <a href="{{ route('kasir.verifikasi.index') }}" class="block rounded-2xl transition-all hover:scale-[1.02] hover:shadow-xl">
            <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5 h-full">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-500">Pending Verifikasi</div>
                    <div id="pending-verifikasi-count" class="text-2xl font-bold text-gray-900 mt-1 truncate">...</div>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-lg lg:col-span-1">
            <h3 class="text-lg font-semibold mb-1 text-gray-800">1. Cari Mahasiswa</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan NPM untuk memulai transaksi.</p>
            <div class="flex gap-2">
                <input type="text" id="npm-search-input" placeholder="Masukkan NPM..." class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button id="search-btn" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">Cari</button>
            </div>
            <div id="mahasiswa-info" class="mt-4 text-sm"></div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-lg font-semibold mb-1 text-gray-800">2. Pilih Tagihan</h3>
                <p class="text-sm text-gray-500 mb-4">Centang tagihan yang akan dibayar.</p>
                <div id="tagihan-list" class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">
                    Cari mahasiswa terlebih dahulu
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-lg font-semibold mb-1 text-gray-800">3. Proses Pembayaran</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih metode dan konfirmasi pembayaran.</p>
                <div id="payment-form-container" class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">
                    Pilih tagihan terlebih dahulu
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('search-btn');
    const npmInput = document.getElementById('npm-search-input');
    const mahasiswaInfoDiv = document.getElementById('mahasiswa-info');
    const tagihanListDiv = document.getElementById('tagihan-list');
    const paymentFormContainer = document.getElementById('payment-form-container');

    // ==========================================================
    // !! FUNGSI API REQUEST DENGAN SWEETALERT UNTUK ERROR SESI !!
    // ==========================================================
    async function apiRequest(url, method = 'POST', body = null) {
        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        if (!apiToken) {
            Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '{{ route("login") }}'; });
            return Promise.reject('Token tidak ditemukan');
        }
        const options = {
            method: method,
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
        };
        if (body) { options.body = JSON.stringify(body); }

        try {
            const response = await fetch(url, options);
            if (response.status === 401) {
                Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Sesi Anda telah berakhir. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '{{ route("login") }}'; });
                throw new Error('Unauthorized');
            }
             // Handle No Content response (misal: DELETE sukses)
            if (response.status === 204) {
                 return { success: true, message: 'Operasi berhasil.' };
             }
            // Handle other non-JSON responses potentially
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                 const responseBody = await response.text();
                console.error("Non-JSON response:", response.status, responseBody);
                throw new Error(`Server (${response.status}): Respon tidak valid.`);
            }

            const data = await response.json(); // Sekarang aman parse JSON

            if (!response.ok) {
                if (response.status === 422 && data.errors) { // Handle Validation Errors
                     throw { status: 422, errors: data.errors, message: data.message || 'Validation failed' };
                }
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            return data;
        } catch (error) {
             console.error("Error in apiRequest:", error);
             if (error.status === 422) throw error; // Re-throw validation errors
             throw new Error(error.message || 'Gagal terhubung ke server.'); // Throw general error
        }
    }

     // Helper untuk menampilkan error 422 di SweetAlert (AMAN)
    function displayValidationErrors(errors) {
        let errorContent = document.createElement('div');
        const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p);
        const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2';
        Object.values(errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); }); // AMAN
        errorContent.appendChild(ul);
        Swal.fire({ icon: 'error', title: 'Gagal', html: errorContent }); // Pakai html
    }

    async function updateDashboardStats() {
        try {
            const url = "{{ route('kasir.dashboard-stats') }}";
            const response = await apiRequest(url, 'GET'); // Method GET
            const data = response.data || response; // Sesuaikan jika API belum konsisten
            if (response.success && data) {
                document.getElementById('transaksi-hari-ini').textContent = data.transaksi_count;
                document.getElementById('total-penerimaan').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data.total_penerimaan);
                document.getElementById('pending-verifikasi-count').textContent = data.pending_verifikasi_count;
            }
        } catch (error) {
            console.error("Gagal memuat statistik dashboard:", error);
             // Opsional: Tampilkan error ke user
            // Swal.fire({ icon: 'warning', title: 'Info', text: 'Gagal memuat statistik dashboard.' });
         }
    }

    async function searchMahasiswa() {
        const npm = npmInput.value.trim();
        if (!npm) {
            // !! GANTI ALERT !!
            Swal.fire({ icon: 'warning', title: 'Input Kosong', text: 'Harap masukkan NPM mahasiswa.' });
            return;
        }

        const originalButtonHTML = searchBtn.innerHTML; // Simpan HTML asli (termasuk SVG)
        searchBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
        searchBtn.disabled = true;

        mahasiswaInfoDiv.innerHTML = '<p class="text-blue-500">Mencari...</p>';
        tagihanListDiv.innerHTML = '<div class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">Cari mahasiswa terlebih dahulu</div>';
        paymentFormContainer.innerHTML = '<div class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">Pilih tagihan terlebih dahulu</div>';

        try {
            const url = "{{ route('kasir.search-mahasiswa') }}";
            const response = await apiRequest(url, 'POST', { npm: npm });
            const data = response.data; // Asumsi controller selalu return { success: true, data: ... }
            if (response.success && data) {
                displayMahasiswaInfo(data); // Aman
                displayTagihan(data.tagihan); // Aman
            } else {
                // Should not happen if controller always returns success true on find
                 const errorP = document.createElement('p');
                 errorP.className = 'text-red-500 font-semibold';
                 errorP.textContent = response.message || 'Mahasiswa tidak ditemukan.'; // Aman
                 mahasiswaInfoDiv.innerHTML = '';
                 mahasiswaInfoDiv.appendChild(errorP);
                 tagihanListDiv.innerHTML = '<div class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">Data tagihan tidak ditemukan</div>';
            }
        // Handle Not Found (404) specifically if needed
        } catch (error) {
             console.error('Error saat mencari mahasiswa:', error);
             const errorP = document.createElement('p');
             errorP.className = 'text-red-500 font-semibold';
             if (error.message && error.message.includes('404')) { // Simple check for 404
                  errorP.textContent = 'Mahasiswa dengan NPM tersebut tidak ditemukan.';
             } else if (error.status === 422) { // Handle validation error from controller (if exists)
                 errorP.textContent = 'NPM tidak valid.'; // Or use displayValidationErrors
             }
             else {
                 errorP.textContent = 'Gagal terhubung ke server: ' + error.message;
             }
             mahasiswaInfoDiv.innerHTML = '';
             mahasiswaInfoDiv.appendChild(errorP);
        } finally {
            searchBtn.innerHTML = originalButtonHTML; // Kembalikan HTML asli
            searchBtn.disabled = false;
        }
    }

    // ============================================
    // !! FUNGSI INI DITULIS ULANG AGAR AMAN DARI XSS !!
    // ============================================
    function displayMahasiswaInfo(mahasiswa) {
        mahasiswaInfoDiv.innerHTML = ''; // Kosongkan dulu

        const container = document.createElement('div');
        container.className = 'mt-4 border-t pt-4 space-y-3';

        // Helper untuk membuat baris info (AMAN)
        function createInfoRow(label, value) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'flex justify-between items-baseline';
            const labelSpan = document.createElement('span');
            labelSpan.className = 'text-gray-500';
            labelSpan.textContent = label;
            const valueSpan = document.createElement('span');
            valueSpan.className = 'font-semibold text-right text-gray-800';
            valueSpan.textContent = value ?? '-'; // Default ke '-' jika null
            rowDiv.appendChild(labelSpan);
            rowDiv.appendChild(valueSpan);
            return rowDiv;
        }

        container.appendChild(createInfoRow('Nama', mahasiswa.user?.nama_lengkap));
        container.appendChild(createInfoRow('NPM', mahasiswa.npm));
        container.appendChild(createInfoRow('Prodi', mahasiswa.program_studi));

        mahasiswaInfoDiv.appendChild(container);
    }

    // ============================================
    // !! FUNGSI INI DITULIS ULANG AGAR AMAN DARI XSS !!
    // ============================================
    function displayTagihan(tagihan) {
        tagihanListDiv.innerHTML = ''; // Kosongkan

        if (!tagihan || tagihan.length === 0) {
            tagihanListDiv.innerHTML = '<div class="text-center text-green-600 py-5 font-semibold border-2 border-dashed rounded-lg">✅ Tidak ada tagihan yang belum lunas.</div>';
            return;
        }

        const container = document.createElement('div');
        container.className = 'space-y-3';
        const rupiahFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

        tagihan.forEach(item => {
            const label = document.createElement('label');
            label.className = 'flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500';
            checkbox.name = 'tagihan';
            checkbox.value = item.tagihan_id;
            checkbox.dataset.amount = item.jumlah_tagihan;

            const textDiv = document.createElement('div');
            textDiv.className = 'ml-3 text-sm flex-grow';

            const namaP = document.createElement('p');
            namaP.className = 'font-medium text-gray-800';
            namaP.textContent = item.tarif?.nama_pembayaran ?? 'N/A'; // AMAN

            const jumlahP = document.createElement('p');
            jumlahP.className = 'text-gray-600';
            jumlahP.textContent = rupiahFormat.format(item.jumlah_tagihan);

            textDiv.appendChild(namaP);
            textDiv.appendChild(jumlahP);
            label.appendChild(checkbox);
            label.appendChild(textDiv);
            container.appendChild(label);
        });

        tagihanListDiv.appendChild(container);
    }

    function updatePaymentForm() {
        const selectedCheckboxes = tagihanListDiv.querySelectorAll('input[name="tagihan"]:checked');
        let totalAmount = 0;
        selectedCheckboxes.forEach(checkbox => totalAmount += parseFloat(checkbox.dataset.amount));
        if (totalAmount > 0) {
            paymentFormContainer.innerHTML = `
                <div class="space-y-4 text-left">
                    <div>
                        <p class="text-sm text-gray-600">Total Pembayaran</p>
                        <p class="text-2xl font-bold text-blue-600">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalAmount)}</p>
                    </div>
                    <form id="process-payment-form">
                        <div>
                            <label for="metode_pembayaran" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                            <select id="metode_pembayaran" name="metode_pembayaran" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option>Tunai</option>
                                {{-- <option>Transfer Bank Nagari</option> --}}
                                {{-- <option>Transfer</option> --}}
                                {{-- Opsi Transfer disembunyikan sementara sesuai logika controller --}}
                            </select>
                        </div>
                       <button type="submit" class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition-colors">
                           Proses Pembayaran
                       </button>
                    </form>
                </div>`;
        } else {
            paymentFormContainer.innerHTML = '<div class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">Pilih tagihan terlebih dahulu</div>';
        }
    }

    async function handlePayment(event) {
        event.preventDefault();
        const selectedCheckboxes = tagihanListDiv.querySelectorAll('input[name="tagihan"]:checked');
        const tagihanIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        if (tagihanIds.length === 0) {
            // !! GANTI ALERT !!
            Swal.fire({ icon: 'warning', title: 'Belum Dipilih', text: 'Tidak ada tagihan yang dipilih untuk dibayar.' });
            return;
        }

        const submitButton = event.target.querySelector('button[type="submit"]');
        const originalButtonHTML = submitButton.innerHTML; // Simpan HTML asli
        submitButton.innerHTML = `<svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
        submitButton.disabled = true;

        try {
            const response = await apiRequest("{{ route('kasir.process-payment') }}", 'POST', {
                tagihan_ids: tagihanIds,
                metode_pembayaran: document.getElementById('metode_pembayaran').value
            });
            if (response.success) {
                // !! GANTI ALERT !!
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Pembayaran berhasil diproses.', timer: 1500, showConfirmButton: false });
                updateDashboardStats(); // Update statistik
                searchMahasiswa(); // Refresh data mahasiswa & tagihan
            } else {
                // !! GANTI ALERT !!
                 Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memproses pembayaran: ' + (response.message || 'Error tidak diketahui.') });
                 submitButton.innerHTML = originalButtonHTML;
                 submitButton.disabled = false;
            }
        } catch(error) {
            console.error("Error saat proses pembayaran:", error);
             if (error.status === 422) {
                 displayValidationErrors(error.errors); // Tampilkan error validasi
             } else {
                 // !! GANTI ALERT !!
                 Swal.fire({ icon: 'error', title: 'Error Koneksi', text: error.message || 'Terjadi kesalahan saat menghubungkan ke server.' });
             }
            submitButton.innerHTML = originalButtonHTML;
            submitButton.disabled = false;
        }
    }

    // --- EVENT LISTENERS ---
    updateDashboardStats(); // Muat statistik awal
    searchBtn.addEventListener('click', searchMahasiswa);
    npmInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchMahasiswa(); });
    // Gunakan event delegation untuk checkbox tagihan
    tagihanListDiv.addEventListener('change', e => { if (e.target.matches('input[name="tagihan"]')) { updatePaymentForm(); } });
    // Gunakan event delegation untuk form pembayaran
    paymentFormContainer.addEventListener('submit', e => { if (e.target.id === 'process-payment-form') { handlePayment(e); } });
});
</script>
@endpush
