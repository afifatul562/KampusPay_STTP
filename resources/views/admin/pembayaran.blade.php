@extends('layouts.app')

@section('title', 'Admin - Manajemen Pembayaran')
@section('page-title', 'Pembayaran')

@section('content')
    <div class="tab-menu">
        <a href="{{ route('admin.dashboard') }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}" class="active">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}">Tarif</a>
        <a href="{{ route('admin.laporan') }}">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}">Registrasi</a>
    </div>

    <div class="content-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="section-title" style="margin-bottom: 0;">💰 Data Pembayaran & Tagihan</h3>
            <button id="addTagihanBtn" class="action-btn" style="background-color: #28a745;">+ Buat Tagihan Baru</button>
        </div>

        <div class="info-cards" style="margin-bottom: 30px;">
             {{-- Kartu statistik bisa ditambahkan di sini jika perlu --}}
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID Pembayaran</th>
                    <th>NPM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Jenis Pembayaran</th>
                    <th>Jumlah</th>
                    <th>Status Tagihan</th>
                    <th>Tanggal Bayar</th>
                </tr>
            </thead>
            <tbody id="payment-table-body">
                <tr><td colspan="7" style="text-align: center;">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- MODAL FORM UNTUK MEMBUAT TAGIHAN BARU --}}
    <div id="tagihanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Buat Tagihan Baru</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body">
                <form id="tagihanForm">
                    <div class="form-group">
                        <label for="angkatan_filter">Filter Berdasarkan Angkatan</label>
                        <select id="angkatan_filter" name="angkatan_filter">
                            <option value="">Tampilkan Semua Angkatan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mahasiswa_id">Pilih Mahasiswa</label>
                        <select id="mahasiswa_id" name="mahasiswa_id" required>
                            <option value="" disabled selected>Pilih angkatan terlebih dahulu...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tarif_id">Pilih Jenis Tarif</label>
                        <select id="tarif_id" name="tarif_id" required>
                            <option value="" disabled selected>Pilih mahasiswa terlebih dahulu...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlah_tagihan">Jumlah Tagihan (Rp)</label>
                        <input type="number" id="jumlah_tagihan" name="jumlah_tagihan" required placeholder="Akan terisi otomatis">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
                        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required>
                    </div>
                    <div class="form-group">
                        <label for="kode_pembayaran">Kode Pembayaran</label>
                        <input type="text" id="kode_pembayaran" name="kode_pembayaran" required placeholder="Akan ter-generate otomatis">
                    </div>
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="action-btn" id="cancelBtn" style="background-color: #6c757d;">Batal</button>
                        <button type="submit" class="action-btn" style="background-color: #28a745;">Simpan Tagihan</button>
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

        // Elemen Tabel
        const paymentTableBody = document.getElementById('payment-table-body');

        // Elemen Modal
        const tagihanModal = document.getElementById('tagihanModal');
        const addTagihanBtn = document.getElementById('addTagihanBtn');
        const closeModalButton = tagihanModal.querySelector('.close-button');
        const cancelBtn = document.getElementById('cancelBtn');
        const tagihanForm = document.getElementById('tagihanForm');

        // Elemen Form
        const angkatanFilterSelect = document.getElementById('angkatan_filter');
        const mahasiswaSelect = document.getElementById('mahasiswa_id');
        const tarifSelect = document.getElementById('tarif_id');
        const jumlahInput = document.getElementById('jumlah_tagihan');
        const kodeInput = document.getElementById('kode_pembayaran');

        let allMahasiswaData = [];
        let allTarifsData = [];

        async function apiRequest(url, method = 'GET', body = null) {
            if (!apiToken) {
                alert('Sesi tidak valid.'); window.location.href = '/login'; return Promise.reject();
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

        function loadPayments() {
            const url = "{{ route('admin.tagihan.index') }}";
            apiRequest(url).then(data => {
                const tbody = document.getElementById('payment-table-body');
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Tidak ada data tagihan.</td></tr>';
                    return;
                }
                data.forEach(tagihan => {
                    const isLunas = tagihan.status === 'Lunas';
                    const pembayaran = tagihan.pembayaran;
                    const row = `
                        <tr>
                            <td>${pembayaran ? pembayaran.pembayaran_id : '-'}</td>
                            <td>${tagihan.mahasiswa?.npm ?? 'N/A'}</td>
                            <td>${tagihan.mahasiswa?.user?.nama_lengkap ?? 'N/A'}</td>
                            <td>${tagihan.tarif?.nama_pembayaran ?? 'N/A'}</td>
                            <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(tagihan.jumlah_tagihan)}</td>
                            <td><span style="font-weight: bold; color: ${isLunas ? 'green' : 'orange'};">${tagihan.status}</span></td>
                            <td>${pembayaran ? new Date(pembayaran.tanggal_bayar).toLocaleDateString('id-ID') : '-'}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }).catch(error => {
                console.error('Error fetching tagihan:', error);
                paymentTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Gagal memuat data.</td></tr>';
            });
        }

        async function populateFormDropdowns() {
            const mahasiswaUrl = "{{ route('admin.mahasiswa.index') }}";
            const tarifUrl = "{{ route('admin.tarif.index') }}";
            try {
                const [mahasiswaResponse, tarifResponse] = await Promise.all([
                    apiRequest(mahasiswaUrl),
                    apiRequest(tarifUrl)
                ]);
                allMahasiswaData = mahasiswaResponse;
                allTarifsData = tarifResponse;
                const angkatanUnik = [...new Set(allMahasiswaData.map(mhs => mhs.angkatan))].sort();
                angkatanFilterSelect.innerHTML = '<option value="">Tampilkan Semua Angkatan</option>';
                angkatanUnik.forEach(angkatan => {
                    angkatanFilterSelect.innerHTML += `<option value="${angkatan}">${angkatan}</option>`;
                });
                filterMahasiswaDropdown();
            } catch (error) {
                console.error("Gagal memuat data untuk form:", error);
            }
        }

        function filterMahasiswaDropdown() {
            const selectedAngkatan = angkatanFilterSelect.value;
            const filteredMahasiswa = allMahasiswaData.filter(mhs => {
                const npmPrefix = selectedAngkatan ? selectedAngkatan.toString().slice(-2) : '';
                return !selectedAngkatan || (mhs.angkatan == selectedAngkatan && mhs.npm.startsWith(npmPrefix));
            });
            mahasiswaSelect.innerHTML = '<option value="" disabled selected>Pilih Mahasiswa</option>';
            filteredMahasiswa.forEach(mhs => {
                mahasiswaSelect.innerHTML += `<option value="${mhs.mahasiswa_id}">${mhs.npm} - ${mhs.user.nama_lengkap}</option>`;
            });
            filterTarifDropdown();
        }

        function filterTarifDropdown() {
            const selectedMahasiswaId = mahasiswaSelect.value;
            if (!selectedMahasiswaId) {
                tarifSelect.innerHTML = '<option value="" disabled selected>Pilih mahasiswa terlebih dahulu...</option>';
                jumlahInput.value = '';
                return;
            }
            const selectedMahasiswa = allMahasiswaData.find(mhs => mhs.mahasiswa_id == selectedMahasiswaId);
            const prodiMhs = selectedMahasiswa.program_studi;
            
            // Ekstrak angkatan dari NPM (2 digit pertama)
            const npmPrefix = selectedMahasiswa.npm.substring(0, 2);
            const angkatanFromNpm = '20' + npmPrefix;
            
            const filteredTarifs = allTarifsData.filter(tarif => {
                // Filter berdasarkan angkatan dari NPM atau 'Semua Angkatan'
                const angkatanCocok = (tarif.angkatan == angkatanFromNpm) || (tarif.angkatan === 'Semua Angkatan');
                const prodiCocok = (tarif.program_studi == prodiMhs) || (tarif.program_studi === 'Semua Jurusan');
                return angkatanCocok && prodiCocok;
            });
            
            tarifSelect.innerHTML = '<option value="" disabled selected>Pilih Jenis Tarif</option>';
            filteredTarifs.forEach(tarif => {
                tarifSelect.innerHTML += `<option value="${tarif.tarif_id}">${tarif.nama_pembayaran} (${tarif.angkatan} - ${tarif.program_studi})</option>`;
            });
            jumlahInput.value = '';
        }

        addTagihanBtn.addEventListener('click', () => {
            tagihanForm.reset();
            mahasiswaSelect.innerHTML = '<option value="" disabled selected>Memuat...</option>';
            tagihanModal.style.display = 'flex';
            populateFormDropdowns();
        });

        angkatanFilterSelect.addEventListener('change', filterMahasiswaDropdown);
        mahasiswaSelect.addEventListener('change', filterTarifDropdown);

        closeModalButton.addEventListener('click', () => tagihanModal.style.display = 'none');
        cancelBtn.addEventListener('click', () => tagihanModal.style.display = 'none');
        window.addEventListener('click', (event) => {
            if (event.target == tagihanModal) tagihanModal.style.display = 'none';
        });

        tarifSelect.addEventListener('change', function() {
            const selectedTarifId = this.value;
            const selectedTarif = allTarifsData.find(t => t.tarif_id == selectedTarifId);
            if (selectedTarif) {
                jumlahInput.value = selectedTarif.nominal;
                kodeInput.value = `INV-${Date.now()}-${selectedTarifId}`;
            }
        });

        tagihanForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const url = "{{ route('admin.payments.tagihan.create') }}";
            const formData = {
                mahasiswa_id: mahasiswaSelect.value,
                tarif_id: tarifSelect.value,
                jumlah_tagihan: jumlahInput.value,
                tanggal_jatuh_tempo: document.getElementById('tanggal_jatuh_tempo').value,
                kode_pembayaran: kodeInput.value,
            };
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.textContent = 'Menyimpan...';
            submitButton.disabled = true;
            apiRequest(url, 'POST', formData).then(response => {
                if (response.success) {
                    alert(response.message);
                    tagihanModal.style.display = 'none';
                    loadPayments();
                } else {
                    let errorMessages = response.message;
                    if (response.errors) {
                        errorMessages = Object.values(response.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert('Gagal membuat tagihan:\n' + errorMessages);
                }
            }).catch(err => alert('Terjadi kesalahan koneksi.')).finally(() => {
                submitButton.textContent = 'Simpan Tagihan';
                submitButton.disabled = false;
            });
        });

        loadPayments();
    });
</script>
@endpush

