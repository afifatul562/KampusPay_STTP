@extends('layouts.app')

@section('title', 'Admin - Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')
    @include('layouts.partials.admin-nav')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Settings -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">⚙️ Pengaturan Sistem</h3>
            <form id="systemSettingsForm" class="space-y-4">
                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                    <input type="text" id="app_name" name="app_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="academic_year" class="block text-sm font-medium text-gray-700">Tahun Akademik</label>
                    <input type="text" id="academic_year" name="academic_year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700">Semester Aktif</label>
                    <input type="text" id="semester" name="semester" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <hr class="my-6">
                <h4 class="text-lg font-semibold">Informasi Rekening Pembayaran</h4>

                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Nama Bank</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Contoh: Bank Nagari" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="account_holder" class="block text-sm font-medium text-gray-700">Atas Nama</label>
                    <input type="text" id="account_holder" name="account_holder" placeholder="Contoh: Yayasan Kampus STTP" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="account_number" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                    <input type="text" id="account_number" name="account_number" placeholder="Contoh: 1234567890" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 mt-6">
                    Simpan Pengaturan
                </button>
            </form>
        </div>

        <!-- System Information -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">ℹ️ Informasi Sistem</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span>Versi PHP:</span> <strong id="php-version" class="font-mono">...</strong></div>
                <div class="flex justify-between"><span>Versi Laravel:</span> <strong id="laravel-version" class="font-mono">...</strong></div>
                <div class="flex justify-between"><span>Database Driver:</span> <strong id="database-driver" class="font-mono">...</strong></div>
                <div class="flex justify-between"><span>Waktu Server:</span> <strong id="server-time" class="font-mono">...</strong></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        async function apiRequest(url, method = 'GET', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) {
                alert('Sesi tidak valid. Harap login kembali.');
                window.location.href = '/login';
                return Promise.reject('No API Token Found');
            }
            const options = {
                method: method,
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}` }
            };
            if (body) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }
            const response = await fetch(url, options);
            if (response.status === 401) { window.location.href = '/login'; throw new Error('Unauthorized'); }
            return response.json();
        }

        function loadSystemInfo() {
            const url = "{{ route('admin.system-info') }}";
            apiRequest(url).then(data => {
                document.getElementById('php-version').textContent = data.php_version;
                document.getElementById('laravel-version').textContent = data.laravel_version;
                document.getElementById('database-driver').textContent = data.database;
                document.getElementById('server-time').textContent = data.server_time;
            }).catch(error => console.error('Error fetching system info:', error));
        }

        // ▼▼▼ PERBAIKAN DI SINI ▼▼▼
        function loadSettings() {
            const url = "{{ route('admin.settings.system.show') }}";
            apiRequest(url).then(data => {
                document.getElementById('app_name').value = data.app_name || '';
                document.getElementById('academic_year').value = data.academic_year || '';
                document.getElementById('semester').value = data.semester || '';
                // Memuat data rekening
                document.getElementById('bank_name').value = data.bank_name || '';
                document.getElementById('account_holder').value = data.account_holder || '';
                document.getElementById('account_number').value = data.account_number || '';
            }).catch(error => console.error('Error fetching settings:', error));
        }

        document.getElementById('systemSettingsForm').addEventListener('submit', function(e){
            e.preventDefault();
            const url = "{{ route('admin.settings.system.update') }}";
            // ▼▼▼ PERBAIKAN DI SINI ▼▼▼
            const data = {
                app_name: document.getElementById('app_name').value,
                academic_year: document.getElementById('academic_year').value,
                semester: document.getElementById('semester').value,
                // Mengirim data rekening
                bank_name: document.getElementById('bank_name').value,
                account_holder: document.getElementById('account_holder').value,
                account_number: document.getElementById('account_number').value,
            };

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.textContent = 'Menyimpan...';
            submitButton.disabled = true;

            apiRequest(url, 'POST', data)
                .then(response => {
                    if (response.success) {
                        alert(response.message);
                    } else {
                        alert('Gagal menyimpan pengaturan.');
                    }
                })
                .catch(err => alert('Terjadi kesalahan.'))
                .finally(() => {
                    submitButton.textContent = 'Simpan Pengaturan';
                    submitButton.disabled = false;
                });
        });

        // Initial Load
        loadSystemInfo();
        loadSettings();
    });
</script>
@endpush

