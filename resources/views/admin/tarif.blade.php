@extends('layouts.app')

@section('title', 'Admin - Manajemen Tarif')
@section('page-title', 'Tarif')

@section('content')
    <div class="tab-menu">
        <a href="{{ route('admin.dashboard') }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}" class="active">Tarif</a>
        <a href="{{ route('admin.laporan') }}">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}">Registrasi</a>
    </div>

    <div class="content-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="section-title" style="margin-bottom: 0;">💰 Master Tarif</h3>
            <button id="addTarifBtn" class="action-btn" style="background-color: #28a745;">+ Tambah Tarif</button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Nama Pembayaran</th>
                    <th>Nominal</th>
                    <th>Program Studi</th>
                    <th>Angkatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tarif-table-body">
                <tr><td colspan="5" style="text-align: center;">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- ▼▼▼ KODE HTML UNTUK MODAL TAMBAH/EDIT ▼▼▼ --}}
    <div id="tarifModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Tarif Baru</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body">
                <form id="tarifForm">
                    <input type="hidden" id="tarifId" name="tarif_id">

                    <div class="form-group">
                        <label for="nama_pembayaran">Nama Pembayaran</label>
                        <select id="nama_pembayaran" name="nama_pembayaran" required>
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

                    <div class="form-group">
                        <label for="nominal">Nominal (Rp)</label>
                        <input type="number" id="nominal" name="nominal" required>
                    </div>

                    <div class="form-group">
                        <label for="program_studi">Program Studi</label>
                        <select id="program_studi" name="program_studi">
                            <option value="">Berlaku untuk Semua</option>
                            <option value="S1 Teknik Sipil">S1 Teknik Sipil</option>
                            <option value="D3 Teknik Komputer">D3 Teknik Komputer</option>
                            <option value="S1 Informatika">S1 Informatika</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="angkatan">Angkatan</label>
                        <select id="angkatan" name="angkatan">
                            <option value="">Berlaku untuk Semua</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="action-btn" id="cancelBtn" style="background-color: #6c757d;">Batal</button>
                        <button type="submit" class="action-btn" style="background-color: #28a745;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
{{-- CSS untuk Modal & Form --}}
<style>
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
    .modal-content { background-color: #fefefe; margin: auto; padding: 0; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); width: 90%; max-width: 500px; animation: fadeIn 0.3s; }
    .modal-header { padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; background-color: #f1f1f1; border-bottom: 1px solid #ddd; border-radius: 10px 10px 0 0; }
    .modal-header h3 { margin: 0; }
    .modal-body { padding: 20px; }
    .close-button { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; background-color: white; }
    @keyframes fadeIn { from {opacity: 0; transform: translateY(-20px);} to {opacity: 1; transform: translateY(0);} }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        const tarifTableBody = document.getElementById('tarif-table-body');
        const tarifModal = document.getElementById('tarifModal');
        const modalTitle = document.getElementById('modalTitle');
        const tarifForm = document.getElementById('tarifForm');
        const tarifIdInput = document.getElementById('tarifId');

        async function apiRequest(url, method = 'GET', body = null) {
            if (!apiToken) {
                alert('Sesi tidak valid. Harap login kembali.');
                window.location.href = '/login';
                return Promise.reject('No API Token Found');
            }
            const options = {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`,
                    'Content-Type': 'application/json'
                }
            };
            if (body) {
                options.body = JSON.stringify(body);
            }
            const response = await fetch(url, options);
            if (response.status === 401) { window.location.href = '/login'; throw new Error('Unauthorized'); }
            return response.json();
        }

        function loadTarifs() {
            const listUrl = "{{ route('admin.tarif.index') }}";
            apiRequest(listUrl)
                .then(data => {
                    tarifTableBody.innerHTML = '';
                    if (!data || data.length === 0) {
                        tarifTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Belum ada data tarif.</td></tr>';
                        return;
                    }
                    data.forEach(tarif => {
                        const row = `
                            <tr>
                                <td>${tarif.nama_pembayaran}</td>
                                <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(tarif.nominal)}</td>
                                <td>${tarif.program_studi || 'Semua'}</td>
                                <td>${tarif.angkatan || 'Semua'}</td>
                                <td>
                                    <button class="action-btn edit-btn" data-id="${tarif.tarif_id}">Edit</button>
                                    <button class="action-btn delete-btn" data-id="${tarif.tarif_id}" style="background-color: #dc3545;">Hapus</button>
                                </td>
                            </tr>
                        `;
                        tarifTableBody.innerHTML += row;
                    });
                })
                .catch(error => console.error('Error fetching tarif list:', error));
        }

        function openModal(mode = 'add', tarifData = null) {
            tarifForm.reset();
            tarifIdInput.value = '';
            if (mode === 'add') {
                modalTitle.textContent = 'Tambah Tarif Baru';
            } else if (mode === 'edit' && tarifData) {
                modalTitle.textContent = 'Edit Tarif';
                tarifIdInput.value = tarifData.tarif_id;
                document.getElementById('nama_pembayaran').value = tarifData.nama_pembayaran;
                document.getElementById('nominal').value = tarifData.nominal;
                document.getElementById('program_studi').value = tarifData.program_studi || "";
                document.getElementById('angkatan').value = tarifData.angkatan || "";
            }
            tarifModal.style.display = 'flex';
        }

        function closeModal() {
            tarifModal.style.display = 'none';
        }

        // Event Listeners
        document.getElementById('addTarifBtn').addEventListener('click', () => openModal('add'));
        document.getElementById('cancelBtn').addEventListener('click', closeModal);
        document.querySelector('.close-button').addEventListener('click', closeModal);
        window.addEventListener('click', (event) => {
            if (event.target == tarifModal) closeModal();
        });

        document.querySelector('.content-section').addEventListener('click', function(event) {
            const target = event.target;
            const tarifId = target.dataset.id;

            if (target.classList.contains('edit-btn')) {
                const detailUrl = `{{ url('/api/admin/tarif') }}/${tarifId}`;
                apiRequest(detailUrl).then(response => openModal('edit', response.data));
            }
            if (target.classList.contains('delete-btn')) {
                if (confirm('Apakah Anda yakin ingin menghapus tarif ini?')) {
                    const deleteUrl = `{{ url('/api/admin/tarif') }}/${tarifId}`;
                    apiRequest(deleteUrl, 'DELETE').then((response) => {
                        if (response.success) {
                            alert(response.message);
                            loadTarifs();
                        } else {
                            alert(response.message);
                        }
                    }).catch(err => alert('Gagal menghapus tarif.'));
                }
            }
        });

        tarifForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const id = tarifIdInput.value;
            const isEdit = !!id;
            const url = isEdit ? `{{ url('/api/admin/tarif') }}/${id}` : "{{ route('admin.tarif.store') }}";
            const method = isEdit ? 'PUT' : 'POST';

            const formData = {
                nama_pembayaran: document.getElementById('nama_pembayaran').value,
                nominal: document.getElementById('nominal').value,
                program_studi: document.getElementById('program_studi').value,
                angkatan: document.getElementById('angkatan').value,
            };

            apiRequest(url, method, formData).then(response => {
                if (response.success) {
                    alert(response.message);
                    closeModal();
                    loadTarifs();
                } else {
                    let errorMessages = response.message;
                    if (response.errors) {
                        errorMessages = Object.values(response.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert('Gagal menyimpan:\n' + errorMessages);
                }
            }).catch(err => alert('Terjadi kesalahan.'));
        });

        // Initial Load
        loadTarifs();
    });
</script>
@endpush