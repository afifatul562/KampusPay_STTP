@extends('layouts.app')

@section('title', 'Admin - Laporan')
@section('page-title', 'Laporan')

@section('content')
    <div class="tab-menu">
        <a href="{{ route('admin.dashboard') }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}">Tarif</a>
        <a href="{{ route('admin.laporan') }}" class="active">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}">Registrasi</a>
    </div>

    <div class="content-section">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="card" style="padding: 20px;">
                <h3 style="margin-top: 0; color: #333;">📊 Generate Laporan</h3>
                <form id="reportForm">
                    <div class="form-group"><label for="jenis_laporan">Jenis Laporan:</label>
                        <select id="jenis_laporan" name="jenis_laporan" required>
                            <option value="" disabled selected>Pilih Jenis Laporan</option>
                            <option value="mahasiswa">Laporan Mahasiswa</option>
                            <option value="pembayaran">Laporan Pembayaran</option>
                        </select>
                    </div>
                    <div class="form-group"><label for="periode">Periode (Bulan dan Tahun):</label>
                        <input type="month" id="periode" name="periode" required style="font-family: inherit; font-size: inherit;">
                    </div>
                    <button type="submit" class="action-btn" style="width: 100%; background-color: #4CAF50;">Generate Laporan</button>
                </form>
            </div>
             <div class="card" style="padding: 20px;">
                <h3 style="margin-top: 0; color: #333;">ℹ️ Informasi</h3>
                <p>Silakan pilih jenis laporan dan periode yang diinginkan. Laporan akan dibuat dalam format PDF dan dapat diunduh dari tabel riwayat di bawah.</p>
            </div>
        </div>

        <div class="content-section" style="margin-top: 30px; padding:0; box-shadow: none;">
            <h3 class="section-title">📋 Riwayat Laporan</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal Dibuat</th>
                        <th>Jenis Laporan</th>
                        <th>Periode</th>
                        <th>Nama File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="reportHistory">
                    <tr><td colspan="5" style="text-align: center;">Memuat riwayat...</td></tr>
                </tbody>
            </table>
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

        async function apiRequest(url, method = 'GET', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) { alert('Sesi tidak valid.'); window.location.href = '/login'; return Promise.reject(); }

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

            // Kita tidak perlu lagi penanganan khusus blob di sini
            return response.json();
        }

        const reportHistoryTable = document.getElementById('reportHistory');

        function loadReportHistory() {
            // PERBAIKAN NAMA RUTE #1
            const historyUrl = "{{ route('admin.reports.index') }}";
            apiRequest(historyUrl)
                .then(data => {
                    reportHistoryTable.innerHTML = '';
                    if (!data || data.length === 0) {
                        reportHistoryTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">Belum ada laporan yang dibuat.</td></tr>';
                        return;
                    }
                    data.forEach(report => {
                        // PERBAIKAN NAMA RUTE #2 & CARA DOWNLOAD
                        const downloadUrl = "{{ route('admin.reports.download', ['report' => ':id']) }}".replace(':id', report.id);

                        const row = `
                            <tr>
                                <td>${new Date(report.created_at).toLocaleString('id-ID')}</td>
                                <td>${report.jenis_laporan}</td>
                                <td>${report.periode}</td>
                                <td>${report.file_name}</td>
                                <td>
                                    <!-- MENGUBAH BUTTON MENJADI LINK (<a>) ADALAH SOLUSINYA -->
                                    <a href="${downloadUrl}" class="action-btn" target="_blank">Download</a>
                                </td>
                            </tr>
                        `;
                        reportHistoryTable.innerHTML += row;
                    });
                })
                .catch(error => console.error('Error fetching report history:', error));
        }

        document.getElementById('reportForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // PERBAIKAN NAMA RUTE #3
            const generateUrl = "{{ route('admin.reports.store') }}";
            const formData = {
                jenis_laporan: this.elements.jenis_laporan.value,
                periode: this.elements.periode.value,
            };

            if (!formData.jenis_laporan || !formData.periode) { alert('Harap pilih jenis laporan dan periode.'); return; }

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.textContent = 'Membuat...';
            submitButton.disabled = true;

            apiRequest(generateUrl, 'POST', formData)
                .then(response => {
                    if (response.success) {
                        alert('Laporan berhasil dibuat!');
                        loadReportHistory();
                    } else {
                        alert('Gagal membuat laporan:\n' + (response.message || 'Error tidak diketahui'));
                    }
                })
                .catch(err => alert('Terjadi kesalahan.'))
                .finally(() => {
                    submitButton.textContent = 'Generate Laporan';
                    submitButton.disabled = false;
                });
        });

        // KODE JAVASCRIPT UNTUK KLIK TOMBOL DOWNLOAD SUDAH TIDAK DIPERLUKAN LAGI
        // KITA BISA MENGHAPUSNYA KARENA LINK <a> SUDAH OTOMATIS BEKERJA

        loadReportHistory();
    });
</script>
@endpush

