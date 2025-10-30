@extends('layouts.app')

@section('title', 'Admin - Manajemen Mahasiswa')
@section('page-title', 'Manajemen Mahasiswa')

@section('content')

    {{--
      !! BLOK SESSION 'success' DAN 'error' (YANG LAMA) DIHAPUS DARI SINI !!
      Kita akan memindahkannya ke dalam @push('scripts') agar menjadi popup SweetAlert.
    --}}

    {{-- DEFINISI FUNGSI ALPINE.JS --}}
    <script>
        function mahasiswaPage() {
            return {
                allMahasiswa: [],
                filteredMahasiswa: [],
                searchQuery: '',
                loading: true,
                detailModalOpen: false,
                importModalOpen: false,
                modalLoading: false,
                selectedMhs: {},
                selectedAngkatan: '',
                angkatanList: [],

                init() {
                    this.loadMahasiswa();
                },

                async fetchData(url, options = {}) {
                    const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                    if (!apiToken) {
                        // !! GANTI ALERT !!
                        Swal.fire({
                            icon: 'error',
                            title: 'Sesi Tidak Valid',
                            text: 'Sesi Anda tidak ditemukan. Harap login kembali.',
                            confirmButtonText: 'Login'
                        }).then(() => { window.location.href = '/login'; });
                        return Promise.reject('Sesi tidak valid');
                    }
                    const headers = {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        ...options.headers,
                    };
                    const config = { ...options, headers: headers };
                    const response = await fetch(url, config);

                    if (response.status === 401) {
                        // !! GANTI ALERT !!
                        Swal.fire({
                            icon: 'error',
                            title: 'Sesi Berakhir',
                            text: 'Sesi Anda telah berakhir. Harap login kembali.',
                            confirmButtonText: 'Login'
                        }).then(() => { window.location.href = '/login'; });
                        return Promise.reject('Sesi berakhir');
                    }
                    if (response.status === 204) { return null; } // Handle DELETE success

                    const data = await response.json().catch(() => null); // Baca data
                    if (!response.ok) {
                        console.error('Fetch error:', data);
                        throw new Error(data?.message || `Gagal mengambil data. Status: ${response.status}`);
                    }
                    return data; // Kembalikan data (bukan response)
                },

                loadMahasiswa() {
                    this.loading = true;
                    const listUrl = "{{ url('api/admin/mahasiswa') }}";
                    this.fetchData(listUrl)
                        .then(data => {
                            this.allMahasiswa = data.data || []; // Handle jika data kosong
                            this.filteredMahasiswa = this.allMahasiswa;
                            const angkatanSet = new Set();
                            this.allMahasiswa.forEach(mhs => {
                                if (mhs.npm && mhs.npm.length >= 2) {
                                    const angkatan = mhs.npm.substring(0, 2);
                                    if (!isNaN(angkatan)) { angkatanSet.add(angkatan); }
                                }
                            });
                            this.angkatanList = Array.from(angkatanSet).sort().reverse();
                        })
                        .catch(error => {
                            console.error('Error fetching mahasiswa list:', error);
                            // !! GANTI ALERT !!
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memuat',
                                text: 'Gagal memuat daftar mahasiswa. Error: ' + error.message
                            });
                        })
                        .finally(() => { this.loading = false; });
                },

                filterMahasiswa() {
                    const query = this.searchQuery.toLowerCase();
                    const angkatan = this.selectedAngkatan;
                    this.filteredMahasiswa = this.allMahasiswa.filter(mhs => {
                        const nama = mhs.user?.nama_lengkap?.toLowerCase() || '';
                        const npm = mhs.npm?.toLowerCase() || '';
                        const searchMatch = nama.includes(query) || npm.includes(query);
                        const angkatanMatch = (angkatan === '') || (mhs.npm && mhs.npm.startsWith(angkatan));
                        return searchMatch && angkatanMatch;
                    });
                },

                showDetailModal(mahasiswaId) {
                    this.detailModalOpen = true;
                    this.modalLoading = true;
                    this.selectedMhs = {};
                    const detailUrl = `{{ url('api/admin/mahasiswa') }}/${mahasiswaId}`;
                    this.fetchData(detailUrl)
                        .then(response => {
                            this.selectedMhs = response.data; // Data ada di dlm properti 'data'
                        })
                        .catch(error => {
                            console.error('Error fetching detail:', error);
                            // !! GANTI ALERT !!
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memuat Detail',
                                text: 'Gagal memuat detail mahasiswa. Error: ' + error.message
                            });
                            this.detailModalOpen = false;
                        })
                        .finally(() => { this.modalLoading = false; });
                },

                deleteMahasiswa(mahasiswaId, nama) {
                    // !! GANTI CONFIRM & ALERT !!
                    Swal.fire({
                        title: 'Anda Yakin?',
                        text: `Anda akan menghapus mahasiswa "${nama || 'ini'}". Tindakan ini tidak dapat dibatalkan!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const deleteUrl = `{{ url('api/admin/mahasiswa') }}/${mahasiswaId}`;
                            this.fetchData(deleteUrl, { method: 'DELETE' })
                                .then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Dihapus!',
                                        text: 'Mahasiswa berhasil dihapus.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    this.allMahasiswa = this.allMahasiswa.filter(m => m.mahasiswa_id !== mahasiswaId);
                                    this.filterMahasiswa();
                                })
                                .catch(error => {
                                    console.error('Error deleting mahasiswa:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Menghapus',
                                        text: 'Gagal menghapus mahasiswa. Error: ' + error.message
                                    });
                                });
                        }
                    });
                }
            };
        }
    </script>

    {{-- DIV UTAMA ALPINE.JS (HTML Tetap Sama) --}}
    <div x-data="mahasiswaPage()">

        {{-- Filter, Tombol, Tabel, dan Modal (HTML tetap sama) --}}
        {{-- ... (Seluruh kode HTML dari <div class="flex flex-col sm:flex-row ..."> ... </div> sampai ... </div> </div>) ... --}}

        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                <div class="relative w-full sm:w-auto">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="filterMahasiswa" class="block w-full sm:w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari nama atau NPM...">
                </div>
                <div class="relative w-full sm:w-auto">
                    <select x-model="selectedAngkatan" @change="filterMahasiswa" class="block w-full sm:w-48 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm">
                        <option value="">Semua Angkatan</option>
                        <template x-for="angkatan in angkatanList" :key="angkatan"><option :value="angkatan" x-text="'20' + angkatan"></option></template>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></div>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button @click="importModalOpen = true" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700"><svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Import CSV</button>
                <a href="{{ route('admin.create-mahasiswa') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"><svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg> Tambah Mahasiswa</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th><th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th><th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm" x-ref="mahasiswaTableBody">
                        <template x-if="loading"><tr><td colspan="5" class="px-6 py-4"><div class="space-y-3"><div class="h-4 bg-gray-200 rounded w-full animate-pulse"></div><div class="h-4 bg-gray-200 rounded w-5/6 animate-pulse"></div></div></td></tr></template>
                        <template x-if="!loading && filteredMahasiswa.length === 0"><tr><td colspan="5" class="text-center py-10 text-gray-500"><div x-show="searchQuery || selectedAngkatan" x-cloak>Tidak ada mahasiswa yang cocok dengan filter.</div><div x-show="!searchQuery && !selectedAngkatan" x-cloak>Belum ada data mahasiswa.</div></td></tr></template>
                        <template x-for="mhs in filteredMahasiswa" :key="mhs.mahasiswa_id"><tr class="hover:bg-gray-50 transition-colors"><td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-gray-900" x-text="mhs.user?.nama_lengkap || 'N/A'"></div><div class="text-gray-500" x-text="mhs.npm || 'N/A'"></div></td><td class="px-6 py-4 whitespace-nowrap text-gray-700" x-text="mhs.program_studi || 'N/A'"></td><td class="px-6 py-4 whitespace-nowrap text-gray-700 text-center" x-text="mhs.semester_aktif || 'N/A'"></td><td class="px-6 py-4 whitespace-nowrap text-center"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="mhs.status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="mhs.status || 'N/A'"></span></td><td class="px-6 py-4 whitespace-nowrap text-right font-medium"><div class="flex justify-end items-center gap-2"><button @click="showDetailModal(mhs.mahasiswa_id)" class="text-gray-500 hover:text-blue-600" title="Detail"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button><a :href="`{{ url('admin/mahasiswa') }}/${mhs.mahasiswa_id}/edit`" class="text-gray-500 hover:text-yellow-600" title="Edit"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z"></path></svg></a><button @click="deleteMahasiswa(mhs.mahasiswa_id, mhs.user?.nama_lengkap)" class="text-gray-500 hover:text-red-600" title="Hapus"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></div></td></tr></template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DETAIL MAHASISWA (Layout Grid sudah diperbaiki) --}}
        <div x-show="detailModalOpen" @keydown.escape.window="detailModalOpen = false" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="detailModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Detail Mahasiswa</h3>
                                <div class="mt-4 text-base text-gray-700">
                                    <div x-show="modalLoading" class="text-center p-8">Memuat detail...</div>
                                    <div x-show="!modalLoading" class="grid grid-cols-[max-content,max-content,1fr] gap-y-2 gap-x-3">
                                        <span class="font-medium text-gray-900">Nama</span><span>:</span><span class="text-left" x-text="selectedMhs.user?.nama_lengkap || 'N/A'"></span>
                                        <span class="font-medium text-gray-900">Email</span><span>:</span><span class="text-left" x-text="selectedMhs.user?.email || 'N/A'"></span>
                                        <span class="font-medium text-gray-900">NPM</span><span>:</span><span class="text-left" x-text="selectedMhs.npm || 'N/A'"></span>
                                        <span class="font-medium text-gray-900">Program Studi</span><span>:</span><span class="text-left" x-text="selectedMhs.program_studi || 'N/A'"></span>
                                        <span class="font-medium text-gray-900">Angkatan</span><span>:</span><span class="text-left" x-text="selectedMhs.angkatan || 'N/A'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="detailModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL IMPORT CSV (Tidak berubah) --}}
        <div x-show="importModalOpen" @keydown.escape.window="importModalOpen = false" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            {{-- ... (HTML Modal Import tetap sama) ... --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div @click="importModalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white rounded-lg shadow-xl transform sm:max-w-lg w-full">
                    <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 pt-5 pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Import Mahasiswa dari CSV</h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Pastikan file CSV Anda memiliki urutan kolom sebagai berikut:</p>
                                <code class="text-xs bg-gray-100 p-2 rounded-md block">nama_lengkap, email, npm</code>
                                <p class="text-sm text-gray-600 mt-2">Program Studi, Angkatan, dan Semester akan diisi otomatis berdasarkan NPM.</p>
                                <div class="mt-4">
                                    <label for="file_csv" class="block text-sm font-medium text-gray-700">Pilih File CSV</label>
                                    <input type="file" name="file_csv" id="file_csv" accept=".csv" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                            <button @click="importModalOpen = false" type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">Upload & Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
{{-- !! SCRIPT BARU UNTUK MENANGANI NOTIFIKASI SESSION DENGAN SWEETALERT !! --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek jika ada session 'success' dari server (misal: setelah import/create/update)
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 2000, // Tutup otomatis setelah 2 detik
                showConfirmButton: false
            });
        @endif

        // Cek jika ada session 'error' dari server (misal: setelah import gagal)
        @if (session('error'))
    let mainErrorMessage = @json(Str::before(session('error'), "\n\nDetail Kesalahan"));

    // Buat elemen 'p' untuk pesan utama
    const mainMessageEl = document.createElement('p');
    mainMessageEl.textContent = mainErrorMessage; // Aman

    // Buat 'div' untuk menampung semua HTML
    const finalHtmlContent = document.createElement('div');
    finalHtmlContent.appendChild(mainMessageEl);

    @if (session('import_errors'))
                let errors = @json(session('import_errors'));

                // Buat 'ul' untuk list error
                const errorList = document.createElement('ul');
                errorList.className = 'list-disc list-inside text-left text-sm mt-2 max-h-40 overflow-y-auto';

                // Loop dan buat <li> menggunakan .textContent (INI BAGIAN AMANNYA)
                errors.forEach(errText => {
                    const li = document.createElement('li');
                    li.textContent = errText; // <--- Ini adalah perbaikan yang TEPAT
                    errorList.appendChild(li);
                });

                finalHtmlContent.appendChild(errorList); // Tambahkan list ke div utama
            @endif

            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: finalHtmlContent // Masukkan Node DOM yang sudah aman ke SweetAlert
            });
        @endif
    });
</script>
@endpush
