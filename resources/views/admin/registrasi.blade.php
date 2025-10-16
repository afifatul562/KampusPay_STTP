@extends('layouts.app')

@section('title', 'Admin - Registrasi')
@section('page-title', 'Registrasi')

@section('content')
    <div class="tab-menu">
        <a href="{{ route('admin.dashboard') }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}">Tarif</a>
        <a href="{{ route('admin.laporan') }}">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}" class="active">Registrasi</a>
    </div>

    <div class="content-section">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Mahasiswa Registration -->
            <div class="card" style="padding: 20px;">
                <h3 style="margin-top: 0; color: #333;">👨‍🎓 Registrasi Mahasiswa</h3>
                <form id="mahasiswaForm">
                    {{-- Form Mahasiswa (tidak perlu diubah) --}}
                    <div class="form-group"><label for="nama_lengkap">Nama Lengkap:</label><input type="text" id="nama_lengkap" name="nama_lengkap" required></div>
                    <div class="form-group"><label for="email">Email:</label><input type="email" id="email" name="email" required></div>
                    <div class="form-group"><label for="npm">NPM:</label><input type="text" id="npm" name="npm" required></div>
                    <div class="form-group"><label for="program_studi">Program Studi:</label>
                        <select id="program_studi" name="program_studi" required>
                            <option value="" disabled selected>Pilih Program Studi</option>
                            <option value="S1 Informatika">S1 Informatika</option>
                            <option value="S1 Teknik Sipil">S1 Teknik Sipil</option>
                            <option value="D3 Teknik Komputer">D3 Teknik Komputer</option>
                        </select>
                    </div>
                    <div class="form-group"><label for="angkatan">Angkatan:</label>
                        <select id="angkatan" name="angkatan" required>
                            <option value="" disabled selected>Pilih Angkatan</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                        </select>
                    </div>
                    <div class="form-group"><label for="semester_aktif">Semester Aktif:</label>
                        <select id="semester_aktif" name="semester_aktif" required>
                            <option value="" disabled selected>Pilih Semester</option>
                            @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                    <button type="submit" class="action-btn" style="width: 100%; background-color: #4CAF50;">Daftarkan Mahasiswa</button>
                </form>
            </div>

            <!-- Kasir Registration -->
            <div class="card" style="padding: 20px;">
                <h3 style="margin-top: 0; color: #333;">👔 Registrasi Kasir</h3>
                <form id="kasirForm">
                    {{-- Form Kasir (tidak perlu diubah) --}}
                    <div class="form-group"><label for="kasir_nama">Nama Lengkap:</label><input type="text" id="kasir_nama" name="nama_lengkap" required></div>
                    <div class="form-group"><label for="kasir_email">Email:</label><input type="email" id="kasir_email" name="email" required></div>
                    <div class="form-group"><label for="kasir_username">Username:</label><input type="text" id="kasir_username" name="username" required></div>
                    <button type="submit" class="action-btn" style="width: 100%; background-color: #2196F3; margin-top: 20px;">Daftarkan Kasir</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; background-color: white; }
</style>
@endpush

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
                options.body = body; // Mengirim sebagai FormData, bukan JSON
            }
            const response = await fetch(url, options);
            return response.json();
        }

        // Handle form registrasi mahasiswa
        document.getElementById('mahasiswaForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = "{{ route('admin.mahasiswa.store') }}";

            apiRequest(url, 'POST', formData).then(response => {
                if (response.success) {
                    alert(response.message);
                    this.reset();
                } else {
                    let errorMessages = response.message;
                    if (response.errors) {
                        errorMessages = Object.values(response.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert('Gagal mendaftarkan:\n' + errorMessages);
                }
            }).catch(err => alert('Terjadi kesalahan.'));
        });

        // Handle form registrasi kasir
        document.getElementById('kasirForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = "{{ route('admin.users.kasir.register') }}";

            apiRequest(url, 'POST', formData).then(response => {
                if (response.success) {
                    alert(response.message);
                    this.reset();
                } else {
                    let errorMessages = response.message;
                    if (response.errors) {
                        errorMessages = Object.values(response.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert('Gagal mendaftarkan:\n' + errorMessages);
                }
            }).catch(err => alert('Terjadi kesalahan.'));
        });
    });
</script>
@endpush