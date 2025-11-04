@extends('layouts.app')

@section('title', 'Admin - Pengaturan')
@section('page-title', 'Pengaturan Aplikasi')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: Form Pengaturan Sistem --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">⚙️ Pengaturan Sistem</h3>
            <form id="systemSettingsForm" class="space-y-4" aria-label="Form pengaturan sistem">
                {{-- Pengaturan Dasar Aplikasi --}}
                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                    <input type="text" id="app_name" name="app_name" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="academic_year" class="block text-sm font-medium text-gray-700">Tahun Akademik</label>
                        <input type="text" id="academic_year" name="academic_year" placeholder="Contoh: 2025/2026" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly disabled aria-readonly="true">
                        <p class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700">Semester Aktif</label>
                        <input type="text" id="semester" name="semester" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly disabled aria-readonly="true">
                        <p class="text-xs text-gray-500 mt-1"></p>
                    </div>
                </div>
                <hr class="my-6">
                {{-- Pengaturan Rekening --}}
                <h4 class="text-lg font-semibold text-gray-800">Informasi Rekening Pembayaran</h4>
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Nama Bank</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Contoh: Bank Nagari" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="account_holder" class="block text-sm font-medium text-gray-700">Atas Nama</label>
                    <input type="text" id="account_holder" name="account_holder" placeholder="Contoh: Yayasan Kampus STTP" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="account_number" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                    <input type="text" id="account_number" name="account_number" placeholder="Contoh: 1234567890" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-md hover:shadow-lg transition-all duration-200">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan: Informasi & Registrasi Kasir --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">ℹ️ Informasi Sistem</h3>
                <div class="space-y-4 text-sm bg-gray-50 p-4 rounded-lg border" aria-live="polite">
                    <div class="flex justify-between"><span class="text-gray-600">Versi PHP:</span><strong id="php-version" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded">...</strong></div>
                    <div class="flex justify-between"><span class="text-gray-600">Versi Laravel:</span><strong id="laravel-version" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded">...</strong></div>
                    <div class="flex justify-between"><span class="text-gray-600">Database Driver:</span><strong id="database-driver" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded">...</strong></div>
                    <div class="flex justify-between"><span class="text-gray-600">Waktu Server:</span><strong id="server-time" class="font-mono text-gray-800 bg-gray-200 px-2 py-0.5 rounded">...</strong></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">👔 Registrasi Kasir Baru</h3>
                <form id="kasirForm" class="space-y-4">
                    <div>
                        <label for="kasir_nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="kasir_nama" name="nama_lengkap" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="kasir_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="kasir_email" name="email" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="kasir_username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="kasir_username" name="username" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-success-600 to-success-700 hover:from-success-700 hover:to-success-800 shadow-md hover:shadow-lg transition-all duration-200">
                            Daftarkan Kasir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        // Pakai util global apiRequest
        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) { console.error('apiRequest util tidak tersedia'); }

        // Helper untuk menampilkan error 422 di SweetAlert (AMAN)
        function displayValidationErrors(errors) {
            let errorContent = document.createElement('div');
            const p = document.createElement('p'); p.textContent = "Input tidak valid:"; errorContent.appendChild(p);
            const ul = document.createElement('ul'); ul.className = 'list-disc list-inside text-left mt-2';
            Object.values(errors).forEach(e => { const li = document.createElement('li'); li.textContent = e.join(', '); ul.appendChild(li); }); // AMAN
            errorContent.appendChild(ul);
            Swal.fire({ icon: 'error', title: 'Gagal', html: errorContent }); // Pakai html
        }


        // --- LOGIKA UNTUK PENGATURAN SISTEM ---
        function loadSystemInfo() {
            apiRequest("{{ route('admin.system-info') }}").then(response => {
                // Akses via response.data (sesuai controller baru)
                const data = response.data;
                document.getElementById('php-version').textContent = data.php_version;
                document.getElementById('laravel-version').textContent = data.laravel_version;
                document.getElementById('database-driver').textContent = data.database;
                document.getElementById('server-time').textContent = data.server_time;
            }).catch(error => {
                console.error('Error fetching system info:', error);
                // Tampilkan error di UI jika perlu
                document.getElementById('php-version').textContent = 'Error';
                document.getElementById('laravel-version').textContent = 'Error';
                document.getElementById('database-driver').textContent = 'Error';
                document.getElementById('server-time').textContent = 'Error';
            });
        }

        function loadSettings() {
            apiRequest("{{ route('admin.settings.system.show') }}").then(response => {
                // Akses via response.data (sesuai controller baru)
                const data = response.data;
                document.getElementById('app_name').value = data.app_name || '';
                document.getElementById('academic_year').value = data.academic_year || '';
                document.getElementById('semester').value = data.semester || '';
                document.getElementById('bank_name').value = data.bank_name || '';
                document.getElementById('account_holder').value = data.account_holder || '';
                document.getElementById('account_number').value = data.account_number || '';
            }).catch(error => {
                 console.error('Error fetching settings:', error);
                 Swal.fire({ icon: 'error', title: 'Gagal Muat', text: 'Gagal memuat pengaturan awal.' });
            });
        }

        // Submit Pengaturan Sistem
        document.getElementById('systemSettingsForm').addEventListener('submit', function(e){
            e.preventDefault();
            const url = "{{ route('admin.settings.system.update') }}";
            const data = {
                app_name: document.getElementById('app_name').value,
                academic_year: document.getElementById('academic_year').value,
                semester: document.getElementById('semester').value,
                bank_name: document.getElementById('bank_name').value,
                account_holder: document.getElementById('account_holder').value,
                account_number: document.getElementById('account_number').value,
            };
            const submitButton = this.querySelector('button[type="submit"]'); const originalButtonText = "Simpan Pengaturan"; submitButton.innerHTML = `Menyimpan...`; submitButton.disabled = true;

            apiRequest(url, 'POST', data)
                .then(response => {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message || 'Pengaturan berhasil disimpan.', timer: 1500, showConfirmButton: false });
                    // Update nama aplikasi di sidebar secara live
                    try {
                        const newName = document.getElementById('app_name').value || 'Aplikasi';
                        document.querySelectorAll('[data-app-name]')?.forEach(el => { el.textContent = newName; });
                    } catch (e) { /* ignore */ }
                })
                .catch(err => {
                    if (err.status === 422 && err.errors) {
                         displayValidationErrors(err.errors); // Panggil helper
                    } else {
                         Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: 'Terjadi kesalahan: ' + (err.message || 'Error tidak diketahui') });
                    }
                })
                .finally(() => {
                    submitButton.innerHTML = originalButtonText; submitButton.disabled = false;
                });
        });

        // --- LOGIKA UNTUK REGISTRASI KASIR ---
        const kasirForm = document.getElementById('kasirForm');
        if(kasirForm) {
            kasirForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const url = "{{ route('admin.users.kasir.register') }}";
                // Kirim sebagai JSON, bukan FormData, karena controller tidak menangani file
                const data = {
                    nama_lengkap: document.getElementById('kasir_nama').value,
                    email: document.getElementById('kasir_email').value,
                    username: document.getElementById('kasir_username').value,
                };

                const button = this.querySelector('button[type="submit"]'); const originalButtonText = "Daftarkan Kasir"; button.innerHTML = `Mendaftarkan...`; button.disabled = true;

                apiRequest(url, 'POST', data).then(response => { // Kirim 'data' (JSON)
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Kasir Terdaftar!', text: response.message || 'Kasir baru berhasil didaftarkan.', timer: 2000, showConfirmButton: false });
                        this.reset(); // Reset form
                    } else {
                        // Jika success false tapi bukan 422 (jarang terjadi jika backend benar)
                        Swal.fire({ icon: 'error', title: 'Gagal Mendaftarkan', text: response.message || 'Terjadi kesalahan.' });
                    }
                }).catch(err => {
                     if (err.status === 422 && err.errors) {
                         displayValidationErrors(err.errors); // Panggil helper
                    } else {
                         Swal.fire({ icon: 'error', title: 'Oops!', text: err.message || 'Terjadi kesalahan koneksi atau server.' });
                    }
                }).finally(() => {
                    button.innerHTML = originalButtonText; button.disabled = false;
                });
            });
        }

        // --- PANGGIL FUNGSI SAAT HALAMAN DIMUAT ---
        loadSystemInfo();
        loadSettings();
    });
</script>
@endpush
