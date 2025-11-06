@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Proses Pembayaran Tunai')

@section('content')
    <div class="space-y-6">
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('kasir.dashboard')],
            ['label' => 'Proses Pembayaran Tunai']
        ]" />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <a href="{{ route('kasir.transaksi.index', ['filter' => 'hari_ini']) }}" aria-label="Lihat transaksi hari ini"
           class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Transaksi Hari Ini</div>
                <div id="transaksi-hari-ini" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">...</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-4 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path></svg>
            </div>
        </a>
        <div class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Penerimaan Hari Ini</div>
                <div id="total-penerimaan" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">...</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-4 rounded-xl shadow-lg group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <a href="{{ route('kasir.verifikasi.index') }}" aria-label="Buka halaman pending verifikasi"
           class="group relative bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-5 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-warning-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex-1 min-w-0 relative z-10">
                <div class="text-sm font-medium text-gray-500 mb-1">Pending Verifikasi</div>
                <div id="pending-verifikasi-count" class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">...</div>
            </div>
            <div class="relative z-10 bg-gradient-to-br from-warning-400 to-warning-600 p-4 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 lg:col-span-1">
            <h3 class="text-lg font-semibold mb-1 text-gray-800">1. Cari Mahasiswa</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan NPM untuk memulai transaksi.</p>
            <div class="flex gap-2">
                <input type="text" id="npm-search-input" placeholder="Masukkan NPM..." aria-label="Input NPM mahasiswa" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                <button id="search-btn" aria-label="Cari mahasiswa" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-md hover:shadow-lg transition-all duration-200">Cari</button>
            </div>
            <div id="mahasiswa-info" class="mt-4 text-sm"></div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-lg font-semibold mb-1 text-gray-800">2. Pilih Tagihan</h3>
                <p class="text-sm text-gray-500 mb-4">Centang tagihan yang akan dibayar.</p>
                <div id="tagihan-list" class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">
                    Cari mahasiswa terlebih dahulu
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-lg font-semibold mb-1 text-gray-800">3. Proses Pembayaran</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih metode dan konfirmasi pembayaran.</p>
                <div id="payment-form-container" class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">
                    Pilih tagihan terlebih dahulu
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-lg font-semibold mb-1 text-gray-800">4. Kwitansi Pembayaran</h3>
                <p class="text-sm text-gray-500 mb-4">Cetak kwitansi setelah pembayaran tunai berhasil diproses.</p>
                <div id="receipt-container" class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">
                    Belum ada pembayaran tunai yang diproses.
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
    const receiptContainer = document.getElementById('receipt-container');

    const currencyFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
    const receiptDefaultHtml = '<div class="text-gray-400 text-center py-5 border-2 border-dashed rounded-lg">Belum ada pembayaran tunai yang diproses.</div>';

    function resetReceiptContainer() {
        if (receiptContainer) {
            receiptContainer.innerHTML = receiptDefaultHtml;
        }
    }

    resetReceiptContainer();

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

    async function searchMahasiswa(options = {}) {
        const { resetReceipt = true } = options;
        const npm = npmInput.value.trim();
        if (!npm) {
            // !! GANTI ALERT !!
            Swal.fire({ icon: 'warning', title: 'Input Kosong', text: 'Harap masukkan NPM mahasiswa.' });
            return;
        }

        if (resetReceipt) {
            resetReceiptContainer();
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
            renderEmptyState(tagihanListDiv, {
                title: 'Tidak ada tagihan',
                message: '✅ Tidak ada tagihan yang belum lunas.',
                icon: `
                    <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                `
            });
            return;
        }

        const container = document.createElement('div');
        container.className = 'space-y-3';

        tagihan.forEach(item => {
            const label = document.createElement('label');
            // Tambahkan border biru jika status "Menunggu Pembayaran Tunai"
            const isWaitingCash = item.status === 'Menunggu Pembayaran Tunai';
            label.className = `flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors ${isWaitingCash ? 'border-blue-300 bg-blue-50' : ''}`;

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

            const detailDiv = document.createElement('div');
            detailDiv.className = 'flex items-center gap-2 mt-1';

            const jumlahP = document.createElement('p');
            jumlahP.className = 'text-gray-600';
            jumlahP.textContent = currencyFormatter.format(item.jumlah_tagihan);

            // Tambahkan badge jika status "Menunggu Pembayaran Tunai"
            if (isWaitingCash) {
                const badge = document.createElement('span');
                badge.className = 'px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 border border-blue-300';
                badge.textContent = '✓ Menunggu Bayar Tunai';
                detailDiv.appendChild(badge);
            }

            detailDiv.appendChild(jumlahP);
            textDiv.appendChild(namaP);
            textDiv.appendChild(detailDiv);
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
                        <p class="text-2xl font-bold text-blue-600">${currencyFormatter.format(totalAmount)}</p>
                    </div>
                    <form id="process-payment-form">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                            <div class="mt-2">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">Tunai</span>
                            </div>
                            <input type="hidden" id="metode_pembayaran" name="metode_pembayaran" value="Tunai">
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
        setButtonLoading(submitButton, true, 'Memproses Pembayaran...');

        try {
            const response = await apiRequest("{{ route('kasir.process-payment') }}", 'POST', {
                tagihan_ids: tagihanIds,
                metode_pembayaran: 'Tunai'
            });
            if (response.success) {
                // !! GANTI ALERT !!
                displayReceipt(response.data || null);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Pembayaran berhasil diproses.', timer: 1500, showConfirmButton: false });
                updateDashboardStats(); // Update statistik
                await searchMahasiswa({ resetReceipt: false }); // Refresh data tanpa menghapus kwitansi
            } else {
                // !! GANTI ALERT !!
                 Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memproses pembayaran: ' + (response.message || 'Error tidak diketahui.') });
            }
        } catch(error) {
            console.error("Error saat proses pembayaran:", error);
             if (error.status === 422) {
                 displayValidationErrors(error.errors); // Tampilkan error validasi
             } else {
                 // !! GANTI ALERT !!
                 Swal.fire({ icon: 'error', title: 'Error Koneksi', text: error.message || 'Terjadi kesalahan saat menghubungkan ke server.' });
             }
        } finally {
            setButtonLoading(submitButton, false);
        }
    }

    function displayReceipt(data) {
        if (!receiptContainer) {
            return;
        }

        if (!data || !Array.isArray(data.pembayaran) || data.pembayaran.length === 0) {
            resetReceiptContainer();
            return;
        }

        const mahasiswa = data.mahasiswa || {};
        const kasir = data.kasir || {};
        const pembayaranList = data.pembayaran;
        const totalBayar = data.total_bayar || pembayaranList.reduce((sum, item) => sum + (item.jumlah || 0), 0);
        const tanggalBayar = data.tanggal_bayar || '-';

        let itemsHtml = '';
        pembayaranList.forEach((item, index) => {
            const kode = item.kode_pembayaran || '-';
            const namaTagihan = item.nama_tagihan || `Tagihan ${index + 1}`;
            const jumlah = currencyFormatter.format(item.jumlah || 0);
            const kwitansiUrl = item.kwitansi_url || '#';

            itemsHtml += `
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border border-gray-200 rounded-lg p-3">
                    <div>
                        <p class="font-medium text-gray-800">${namaTagihan}</p>
                        <p class="text-xs text-gray-500">Kode: ${kode}</p>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span class="font-semibold text-gray-700">${jumlah}</span>
                        <a href="${kwitansiUrl}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gradient-to-r from-primary-100 to-primary-200 text-primary-700 text-xs font-semibold hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200">
                            Cetak Kwitansi
                        </a>
                    </div>
                </div>`;
        });

        const totalFormatted = currencyFormatter.format(totalBayar || 0);

        const multiple = pembayaranList.length > 1;

        receiptContainer.innerHTML = `
            <div class="space-y-4 text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nama Mahasiswa</p>
                        <p class="text-sm font-semibold text-gray-800">${mahasiswa.nama || '-'}</p>
                        <p class="text-xs text-gray-500 mt-2">NPM: <span class="font-medium text-gray-700">${mahasiswa.npm || '-'}</span></p>
                        <p class="text-xs text-gray-500">Program Studi: <span class="font-medium text-gray-700">${mahasiswa.prodi || '-'}</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Kasir</p>
                        <p class="text-sm font-semibold text-gray-800">${kasir.nama || '-'}</p>
                        <p class="text-xs text-gray-500 mt-2">Tanggal Bayar</p>
                        <p class="text-sm font-medium text-gray-700">${tanggalBayar}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    ${itemsHtml}
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-200 pt-4">
                    <span class="text-sm font-semibold text-gray-700">Total Pembayaran Tunai</span>
                    <span class="text-xl font-bold text-blue-600">${totalFormatted}</span>
                </div>
                ${multiple ? '<button id="printAllReceipts" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 text-white text-sm font-semibold hover:from-primary-700 hover:to-primary-800 shadow-md hover:shadow-lg transition-all duration-200">Cetak Semua Kwitansi</button>' : ''}
            </div>`;

        const printAllBtn = receiptContainer.querySelector('#printAllReceipts');
        if (printAllBtn) {
            printAllBtn.addEventListener('click', () => {
                pembayaranList.forEach(item => {
                    if (item.kwitansi_url) {
                        window.open(item.kwitansi_url, '_blank', 'noopener');
                    }
                });
            });
        }
    }

    // --- EVENT LISTENERS ---
    updateDashboardStats(); // Muat statistik awal
    searchBtn.addEventListener('click', () => searchMahasiswa());
    npmInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchMahasiswa(); });
    // Gunakan event delegation untuk checkbox tagihan
    tagihanListDiv.addEventListener('change', e => { if (e.target.matches('input[name="tagihan"]')) { updatePaymentForm(); } });
    // Gunakan event delegation untuk form pembayaran
    paymentFormContainer.addEventListener('submit', e => { if (e.target.id === 'process-payment-form') { handlePayment(e); } });
});
</script>
@endpush
