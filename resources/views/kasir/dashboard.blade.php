@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Proses Pembayaran')

@section('content')
    {{-- Memanggil menu navigasi terpusat yang sudah benar --}}
    @include('layouts.partials.kasir-nav')

    {{-- Kartu Info Konsisten --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Transaksi Hari Ini</div>
            <div class="text-3xl font-bold text-gray-900" id="transaksi-hari-ini">...</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Total Penerimaan Hari Ini</div>
            <div class="text-3xl font-bold text-gray-900" id="total-penerimaan">...</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Pending Verifikasi</div>
            <div class="text-3xl font-bold text-gray-900" id="pending-verifikasi-count">...</div>
        </div>
    </div>

    {{-- Panel Proses Pembayaran --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Panel 1: Pencarian --}}
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
            <h3 class="text-lg font-semibold mb-1">1. Cari Mahasiswa</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan NPM untuk memulai.</p>
            <div class="flex gap-2">
                <input type="text" id="npm-search-input" placeholder="Masukkan NPM..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <button id="search-btn" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm font-semibold">Cari</button>
            </div>
            <div id="mahasiswa-info" class="mt-4"></div>
        </div>

        {{-- Panel 2 & 3 --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Panel 2: Tagihan --}}
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-1">2. Pilih Tagihan</h3>
                <p class="text-sm text-gray-500 mb-4">Centang tagihan yang akan dibayar.</p>
                <div id="tagihan-list" class="text-gray-400 text-center py-5">
                    Cari mahasiswa terlebih dahulu.
                </div>
            </div>

            {{-- Panel 3: Pembayaran --}}
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-1">3. Proses Pembayaran</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih metode dan proses pembayaran.</p>
                <div id="payment-form-container" class="text-gray-400 text-center py-5">
                    Pilih tagihan terlebih dahulu.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JavaScript lama Anda tidak perlu diubah, cukup salin dan tempel di sini --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Salin semua JavaScript dari file kasir/dashboard.blade.php yang lama ke sini)
            const searchBtn = document.getElementById('search-btn');
            const npmInput = document.getElementById('npm-search-input');
            const mahasiswaInfoDiv = document.getElementById('mahasiswa-info');
            const tagihanListDiv = document.getElementById('tagihan-list');
            const paymentFormContainer = document.getElementById('payment-form-container');

            async function apiRequest(url, method = 'POST', body = null) {
                const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                if (!apiToken) {
                    alert('Sesi tidak valid atau token tidak ditemukan. Harap login kembali.');
                    window.location.href = '{{ route("login") }}';
                    return Promise.reject('Token tidak ditemukan');
                }
                const options = {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`,
                        'Content-Type': 'application/json'
                    }
                };
                if (body) { options.body = JSON.stringify(body); }
                const response = await fetch(url, options);
                if (response.status === 401) { window.location.href = '{{ route("login") }}'; throw new Error('Unauthorized'); }
                return response.json();
            }

            async function updateDashboardStats() {
                try {
                    const url = "{{ route('kasir.dashboard-stats') }}";
                    const response = await apiRequest(url, 'GET');
                    if (response.success && response.data) {
                        const { transaksi_count, total_penerimaan, pending_verifikasi_count } = response.data;
                        document.getElementById('transaksi-hari-ini').textContent = transaksi_count;
                        document.getElementById('total-penerimaan').textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total_penerimaan)}`;
                        document.getElementById('pending-verifikasi-count').textContent = pending_verifikasi_count;
                    }
                } catch (error) {
                    console.error("Gagal memuat statistik dashboard:", error);
                }
            }

            async function searchMahasiswa() {
                const npm = npmInput.value.trim();
                if (!npm) {
                    alert('Harap masukkan NPM mahasiswa.');
                    return;
                }
                mahasiswaInfoDiv.innerHTML = '<p class="text-blue-500">Mencari...</p>';
                tagihanListDiv.innerHTML = '<p class="text-gray-400 text-center py-5">Cari mahasiswa terlebih dahulu.</p>';
                paymentFormContainer.innerHTML = '<p class="text-gray-400 text-center py-5">Pilih tagihan terlebih dahulu.</p>';
                try {
                    const url = "{{ route('kasir.search-mahasiswa') }}";
                    const response = await apiRequest(url, 'POST', { npm: npm });
                    if (response.success && response.data) {
                        displayMahasiswaInfo(response.data);
                        displayTagihan(response.data.tagihan);
                    } else {
                        mahasiswaInfoDiv.innerHTML = `<p class="text-red-500 font-semibold">${response.message || 'Mahasiswa tidak ditemukan.'}</p>`;
                    }
                } catch (error) {
                    console.error('Error saat mencari mahasiswa:', error);
                    mahasiswaInfoDiv.innerHTML = '<p class="text-red-500 font-semibold">Gagal terhubung ke server.</p>';
                }
            }

            function displayMahasiswaInfo(mahasiswa) {
                mahasiswaInfoDiv.innerHTML = `
                    <div class="mt-4 border-t pt-4 space-y-3">
                        <div><p class="text-xs text-gray-500">Nama</p><p class="font-semibold">${mahasiswa.user.nama_lengkap}</p></div>
                        <div><p class="text-xs text-gray-500">NPM</p><p class="font-semibold">${mahasiswa.npm}</p></div>
                        <div><p class="text-xs text-gray-500">Program Studi</p><p class="font-semibold">${mahasiswa.program_studi}</p></div>
                    </div>`;
            }

            function displayTagihan(tagihan) {
                if (!tagihan || tagihan.length === 0) {
                    tagihanListDiv.innerHTML = '<p class="text-center text-green-600 py-5 font-semibold">✅ Tidak ada tagihan yang belum lunas.</p>';
                    return;
                }
                let tagihanHtml = '<div class="space-y-2">';
                tagihan.forEach(item => {
                    tagihanHtml += `
                        <label class="flex items-center p-3 border rounded-md hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" name="tagihan" value="${item.tagihan_id}" data-amount="${item.jumlah_tagihan}">
                            <div class="ml-3 text-sm flex-grow">
                                <p class="font-medium text-gray-800">${item.tarif.nama_pembayaran}</p>
                                <p class="text-gray-600">Rp ${new Intl.NumberFormat('id-ID').format(item.jumlah_tagihan)}</p>
                            </div>
                        </label>`;
                });
                tagihanHtml += '</div>';
                tagihanListDiv.innerHTML = tagihanHtml;
            }

            function updatePaymentForm() {
                const selectedCheckboxes = tagihanListDiv.querySelectorAll('input[name="tagihan"]:checked');
                let totalAmount = 0;
                selectedCheckboxes.forEach(checkbox => {
                    totalAmount += parseFloat(checkbox.dataset.amount);
                });
                if (totalAmount > 0) {
                    paymentFormContainer.innerHTML = `
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Total Pembayaran</p>
                                <p class="text-2xl font-bold text-blue-600">Rp ${new Intl.NumberFormat('id-ID').format(totalAmount)}</p>
                            </div>
                            <form id="process-payment-form">
                                <div class="space-y-3">
                                    <div>
                                        <label for="metode_pembayaran" class="block text-sm font-medium text-gray-700 text-left">Metode Pembayaran</label>
                                        <select id="metode_pembayaran" name="metode_pembayaran" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                            <option>Tunai</option>
                                            <option>Transfer Bank Nagari</option>
                                        </select>
                                    </div>
                               </div>
                               <button type="submit" class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold transition-colors">
                                   Proses Pembayaran
                               </button>
                            </form>
                        </div>`;
                } else {
                    paymentFormContainer.innerHTML = '<p class="text-gray-400 text-center py-5">Pilih tagihan terlebih dahulu.</p>';
                }
            }

            async function handlePayment(event) {
                event.preventDefault();
                const selectedCheckboxes = tagihanListDiv.querySelectorAll('input[name="tagihan"]:checked');
                const tagihanIds = Array.from(selectedCheckboxes).map(cb => cb.value);
                const metodePembayaran = document.getElementById('metode_pembayaran').value;
                if (tagihanIds.length === 0) {
                    alert('Tidak ada tagihan yang dipilih.');
                    return;
                }
                const submitButton = event.target.querySelector('button[type="submit"]');
                submitButton.textContent = 'Memproses...';
                submitButton.disabled = true;
                try {
                    const url = "{{ route('kasir.process-payment') }}";
                    const response = await apiRequest(url, 'POST', {
                        tagihan_ids: tagihanIds,
                        metode_pembayaran: metodePembayaran
                    });
                    if (response.success) {
                        alert('Pembayaran berhasil diproses!');
                        updateDashboardStats();
                        searchMahasiswa();
                    } else {
                        alert('Gagal memproses pembayaran: ' + (response.message || 'Error tidak diketahui.'));
                        submitButton.textContent = 'Proses Pembayaran';
                        submitButton.disabled = false;
                    }
                } catch(error) {
                    console.error("Error saat proses pembayaran:", error);
                    alert('Terjadi kesalahan saat menghubungkan ke server.');
                    submitButton.textContent = 'Proses Pembayaran';
                    submitButton.disabled = false;
                }
            }

            updateDashboardStats();
            searchBtn.addEventListener('click', searchMahasiswa);
            npmInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchMahasiswa(); });
            tagihanListDiv.addEventListener('change', e => { if (e.target.name === 'tagihan') updatePaymentForm(); });
            paymentFormContainer.addEventListener('submit', e => { if (e.target.id === 'process-payment-form') handlePayment(e); });
        });
    </script>
@endpush

