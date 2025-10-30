@extends('layouts.app')

@section('title', 'Admin - Registrasi Pengguna')
@section('page-title', 'Registrasi Pengguna Baru')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">👨‍🎓 Registrasi Mahasiswa</h3>
            <form id="mahasiswaForm" class="space-y-4">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="npm" class="block text-sm font-medium text-gray-700">NPM</label>
                    <input type="text" id="npm" name="npm" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="program_studi" class="block text-sm font-medium text-gray-700">Program Studi</label>
                    <select id="program_studi" name="program_studi" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="" disabled selected>Pilih Program Studi</option>
                        <option value="S1 Informatika">S1 Informatika</option>
                        <option value="S1 Teknik Sipil">S1 Teknik Sipil</option>
                        <option value="D3 Teknik Komputer">D3 Teknik Komputer</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan</label>
                        <select id="angkatan" name="angkatan" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="" disabled selected>Pilih</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                        </select>
                    </div>
                    <div>
                        <label for="semester_aktif" class="block text-sm font-medium text-gray-700">Semester</label>
                        <select id="semester_aktif" name="semester_aktif" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="" disabled selected>Pilih</option>
                            @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Daftarkan Mahasiswa
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">👔 Registrasi Kasir</h3>
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
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Daftarkan Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        async function apiRequest(url, method = 'POST', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) {
                alert('Sesi tidak valid.'); window.location.href = '/login'; return Promise.reject();
            }
            const options = {
                method: method,
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}` }
            };
            if (body) {
                options.body = body;
            }
            const response = await fetch(url, options);
            return response.json();
        }

        function handleFormSubmit(form, url, button, originalButtonText) {
            const formData = new FormData(form);

            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mendaftarkan...
            `;
            button.disabled = true;

            apiRequest(url, 'POST', formData).then(response => {
                if (response.success) {
                    alert(response.message);
                    form.reset();
                } else {
                    let errorMessages = response.message;
                    if (response.errors) {
                        errorMessages = Object.values(response.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert('Gagal mendaftarkan:\n' + errorMessages);
                }
            }).catch(err => alert('Terjadi kesalahan.'))
            .finally(() => {
                button.innerHTML = originalButtonText;
                button.disabled = false;
            });
        }

        // Handle form registrasi mahasiswa
        const mahasiswaForm = document.getElementById('mahasiswaForm');
        mahasiswaForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const url = "{{ route('admin.mahasiswa.store') }}";
            const button = this.querySelector('button[type="submit"]');
            handleFormSubmit(this, url, button, 'Daftarkan Mahasiswa');
        });

        // Handle form registrasi kasir
        const kasirForm = document.getElementById('kasirForm');
        kasirForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const url = "{{ route('admin.users.kasir.register') }}";
            const button = this.querySelector('button[type="submit"]');
            handleFormSubmit(this, url, button, 'Daftarkan Kasir');
        });
    });
</script>
@endpush
