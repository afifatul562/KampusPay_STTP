{{-- Menggunakan layout app.blade.php Anda --}}
@extends('layouts.app')

@section('title', 'Manajemen User')

{{-- 1. PASTIKAN SEMUA LINK CSS MENGGUNAKAN "https://" --}}
@push('styles')
{{-- Tidak perlu CSS tambahan: gunakan Tailwind bawaan agar konsisten --}}
@endpush


@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Manajemen User</h1>
        <button id="openAddKasirBtn" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-success-600 to-success-700 text-white text-sm font-semibold hover:from-success-700 hover:to-success-800 shadow-md hover:shadow-lg transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kasir
        </button>
    </div>

    {{-- Flash banner dihilangkan karena sudah digantikan SweetAlert global --}}

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h6 class="font-semibold text-lg text-gray-800">Daftar Staff (Kasir / Admin)</h6>
        </div>
        <div class="p-4 sm:p-6">
            <div class="mb-3">
                <input id="searchStaff" type="text" placeholder="Cari nama atau email..." class="w-full sm:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div class="overflow-x-auto">
                <table id="tableStaff" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($staff as $index => $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->nama_lengkap }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($user->role == 'admin')
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $user->role }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $user->role }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center px-3 py-1 rounded-md bg-gradient-to-r from-primary-100 to-primary-200 text-primary-700 text-sm font-semibold hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200" data-tooltip="Edit user" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 000-2.828l-1.172-1.172a2 2 0 00-2.828 0L5.293 15.707A1 1 0 005 16.414V20z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline js-delete-user" data-user-name="{{ $user->nama_lengkap }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-md bg-gradient-to-r from-danger-600 to-danger-700 text-white text-sm font-semibold hover:from-danger-700 hover:to-danger-800 shadow-md hover:shadow-lg transition-all duration-200" data-tooltip="Hapus user" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 011-1h4a1 1 0 011 1m-7 0h8"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            @php
                                $emptyIcon = '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
                            @endphp
                            <tr>
                                <td colspan="5" class="px-6 py-12">
                                    <x-empty-state
                                        title="Belum ada data staff"
                                        message="Belum ada data staff (Kasir/Admin). Silakan daftarkan staff baru."
                                        :icon="$emptyIcon" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h6 class="font-semibold text-lg text-gray-800">Daftar Mahasiswa</h6>
        </div>
        <div class="p-4 sm:p-6">
            <div class="mb-3">
                <input id="searchMhs" type="text" placeholder="Cari nama atau email..." class="w-full sm:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div class="overflow-x-auto">
                <table id="tableMhs" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($mahasiswa as $index => $user)
                             <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->nama_lengkap }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center px-3 py-1 rounded-md bg-gradient-to-r from-primary-100 to-primary-200 text-primary-700 text-sm font-semibold hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200" data-tooltip="Edit user" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 000-2.828l-1.172-1.172a2 2 0 00-2.828 0L5.293 15.707A1 1 0 005 16.414V20z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline js-delete-user" data-user-name="{{ $user->nama_lengkap }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-md bg-gradient-to-r from-danger-600 to-danger-700 text-white text-sm font-semibold hover:from-danger-700 hover:to-danger-800 shadow-md hover:shadow-lg transition-all duration-200" data-tooltip="Hapus user" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 011-1h4a1 1 0 011 1m-7 0h8"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            @php
                                $emptyIconMahasiswa = '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';
                            @endphp
                            <tr>
                                <td colspan="5" class="px-6 py-12">
                                    <x-empty-state
                                        title="Belum ada data mahasiswa"
                                        message="Belum ada data mahasiswa. Silakan import atau tambahkan mahasiswa baru."
                                        :icon="$emptyIconMahasiswa" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Kasir Baru --}}
<div id="addKasirModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="addKasirBackdrop" class="absolute inset-0 bg-gray-900/60"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Registrasi Kasir Baru</h3>
                <button type="button" id="closeAddKasirModal" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="addKasirForm" class="px-6 py-5 space-y-4">
                <div>
                    <label for="kasir_nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="kasir_nama" name="nama_lengkap" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="kasir_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="kasir_email" name="email" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="kasir_username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="kasir_username" name="username" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" id="cancelAddKasirBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Batal</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-success-600 to-success-700 text-white text-sm font-semibold hover:from-success-700 hover:to-success-800 shadow-md hover:shadow-lg transition-all duration-200">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const apiRequest = (window.App && window.App.apiRequest) ? window.App.apiRequest : null;
        if (!apiRequest) {
            console.error('apiRequest util tidak tersedia. Pastikan utils/api.js telah dimuat.');
        }

        // Helper untuk menampilkan error validasi
        function displayValidationErrors(errors) {
            let errorContent = document.createElement('div');
            const p = document.createElement('p');
            p.textContent = 'Input tidak valid:';
            errorContent.appendChild(p);
            const ul = document.createElement('ul');
            ul.className = 'list-disc list-inside text-left mt-2';
            Object.values(errors || {}).forEach(errArr => {
                const li = document.createElement('li');
                li.textContent = Array.isArray(errArr) ? errArr.join(', ') : errArr;
                ul.appendChild(li);
            });
            errorContent.appendChild(ul);
            Swal.fire({ icon: 'error', title: 'Gagal', html: errorContent });
        }

        // --- Modal Tambah Kasir ---
        const addKasirModal = document.getElementById('addKasirModal');
        const addKasirBackdrop = document.getElementById('addKasirBackdrop');
        const openAddKasirBtn = document.getElementById('openAddKasirBtn');
        const closeAddKasirModal = document.getElementById('closeAddKasirModal');
        const cancelAddKasirBtn = document.getElementById('cancelAddKasirBtn');
        const addKasirForm = document.getElementById('addKasirForm');

        function openModal() {
            if (!addKasirModal) return;
            addKasirModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            if (!addKasirModal) return;
            addKasirModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (addKasirForm) addKasirForm.reset();
        }

        openAddKasirBtn?.addEventListener('click', openModal);
        closeAddKasirModal?.addEventListener('click', closeModal);
        cancelAddKasirBtn?.addEventListener('click', closeModal);
        addKasirBackdrop?.addEventListener('click', closeModal);

        if (addKasirForm && apiRequest) {
            addKasirForm.addEventListener('submit', function(event){
                event.preventDefault();
                const submitButton = addKasirForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.innerHTML = 'Menyimpan...';
                submitButton.disabled = true;

                const data = {
                    nama_lengkap: document.getElementById('kasir_nama')?.value,
                    email: document.getElementById('kasir_email')?.value,
                    username: document.getElementById('kasir_username')?.value,
                };

                apiRequest("{{ route('admin.users.kasir.register') }}", 'POST', data)
                    .then(response => {
                        if (response.success) {
                            closeModal();
                            Swal.fire({ icon: 'success', title: 'Kasir Terdaftar!', text: response.message || 'Kasir baru berhasil didaftarkan.', timer: 1800, showConfirmButton: false });
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: response.message || 'Terjadi kesalahan.' });
                        }
                    })
                    .catch(err => {
                        if (err.status === 422 && err.errors) {
                            displayValidationErrors(err.errors);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops!', text: err.message || 'Terjadi kesalahan koneksi atau server.' });
                        }
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    });
            });
        }

        function attachFilter(inputId, tableSelector){
            const input = document.getElementById(inputId);
            const table = document.querySelector(tableSelector);
            if (!input || !table) return;
            const tbody = table.querySelector('tbody');
            input.addEventListener('input', function(){
                const q = this.value.toLowerCase().trim();
                Array.from(tbody.rows).forEach(function(row){
                    // gabungkan teks nama + email
                    const nameCell = row.cells[1]?.textContent.toLowerCase() || '';
                    const emailCell = row.cells[2]?.textContent.toLowerCase() || '';
                    row.style.display = (nameCell.includes(q) || emailCell.includes(q)) ? '' : 'none';
                });
            });
        }
        attachFilter('searchStaff', '#tableStaff');
        attachFilter('searchMhs', '#tableMhs');

        // Uniform delete confirmation using SweetAlert
        (function(){
            const forms = document.querySelectorAll('form.js-delete-user');
            forms.forEach(function(f){
                f.addEventListener('submit', async function(e){
                    e.preventDefault();
                    const nama = this.getAttribute('data-user-name') || 'user ini';
                    if (window.App && App.alert && App.alert.confirm) {
                        const ok = await App.alert.confirm('Anda Yakin?', `Yakin ingin menghapus ${nama}? Tindakan ini tidak dapat dibatalkan.`, 'Ya, Hapus');
                        if (ok) this.submit();
                    } else {
                        if (confirm(`Yakin ingin menghapus ${nama}?`)) this.submit();
                    }
                });
            });
        })();
    });
</script>
@endpush
