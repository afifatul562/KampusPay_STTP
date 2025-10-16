@extends('layouts.app')

@section('title', 'Admin - Manajemen Mahasiswa')
@section('page-title', 'Mahasiswa')

@section('content')
    <div class="tab-menu">
        <a href="{{ route('admin.dashboard') }}">Overview</a>
        <a href="{{ route('admin.mahasiswa') }}" class="active">Mahasiswa</a>
        <a href="{{ route('admin.pembayaran') }}">Pembayaran</a>
        <a href="{{ route('admin.tarif') }}">Tarif</a>
        <a href="{{ route('admin.laporan') }}">Laporan</a>
        <a href="{{ route('admin.pengaturan') }}">Pengaturan</a>
        <a href="{{ route('admin.registrasi') }}">Registrasi</a>
    </div>

    <div class="info-cards">
        <div class="card">
            <div>
                <div class="title">Total Mahasiswa</div>
                <div class="value" id="total-mahasiswa">Memuat...</div>
            </div>
            <div>👥</div>
        </div>
    </div>

    <div class="content-section">
        <h3 class="section-title">👨‍🎓 Daftar Mahasiswa</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>NPM</th>
                    <th>Nama</th>
                    <th>Program Studi</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="mahasiswa-table-body">
                <tr>
                    <td colspan="6" style="text-align: center;">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ▼▼▼ KODE HTML UNTUK MODAL DITAMBAHKAN DI SINI ▼▼▼ --}}
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Mahasiswa</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body" id="detailModalBody">
                <p>Memuat detail...</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
{{-- Menambahkan CSS untuk Modal agar terlihat bagus --}}
<style>
    .modal {
        display: none; /* Disembunyikan secara default */
        position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; overflow: auto;
        background-color: rgba(0,0,0,0.6);
        align-items: center; justify-content: center;
    }
    .modal-content {
        background-color: #fefefe; margin: auto; padding: 0;
        border: 1px solid #888; width: 80%; max-width: 600px;
        border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        animation: fadeIn 0.3s;
    }
    .modal-header {
        padding: 15px 20px; display: flex; justify-content: space-between;
        align-items: center; background-color: #f1f1f1;
        border-bottom: 1px solid #ddd; border-radius: 10px 10px 0 0;
    }
    .modal-header h3 { margin: 0; font-size: 1.25rem; }
    .modal-body { padding: 20px; line-height: 1.6; }
    .modal-body h4 { margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px;}
    .modal-body p { margin-bottom: 5px; }
    .close-button {
        color: #aaa; float: right; font-size: 28px;
        font-weight: bold; cursor: pointer;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        async function fetchData(url) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) {
                alert('Sesi tidak valid atau telah berakhir. Harap login kembali.');
                window.location.href = '/login';
                return Promise.reject('No API Token Found');
            }

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`
                }
            });

            if (response.status === 401) { window.location.href = '/login'; throw new Error('Unauthorized'); }
            if (!response.ok) throw new Error('Failed to fetch data.');

            return response.json();
        }

        const mahasiswaTableBody = document.getElementById('mahasiswa-table-body');
        const totalMahasiswaCard = document.getElementById('total-mahasiswa');
        const detailModal = document.getElementById('detailModal');
        const detailModalBody = document.getElementById('detailModalBody');
        const closeModalButton = document.querySelector('.close-button');

        function loadMahasiswa() {
            const listUrl = "{{ route('admin.mahasiswa.index') }}";
            fetchData(listUrl)
                .then(data => {
                    mahasiswaTableBody.innerHTML = '';
                    totalMahasiswaCard.textContent = data.length;

                    if (data.length === 0) {
                        mahasiswaTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Tidak ada data mahasiswa.</td></tr>';
                        return;
                    }

                    data.forEach(mhs => {
                        // ▼▼▼ PERBAIKAN KUNCI ADA DI SINI ▼▼▼
                        // Gunakan 'mahasiswa_id' sesuai nama primary key di model/database
                        const row = `
                            <tr>
                                <td>${mhs.npm ?? 'N/A'}</td>
                                <td>${mhs.user?.nama_lengkap ?? 'N/A'}</td>
                                <td>${mhs.program_studi ?? 'N/A'}</td>
                                <td>${mhs.semester_aktif ?? 'N/A'}</td>
                                <td><span class="status-lunas">${mhs.status ?? 'N/A'}</span></td>
                                <td><button class="action-btn detail-btn" data-id="${mhs.mahasiswa_id}">Detail</button></td>
                            </tr>
                        `;
                        mahasiswaTableBody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error fetching mahasiswa list:', error);
                    mahasiswaTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Gagal memuat data.</td></tr>';
                });
        }

        function showDetailModal(mahasiswaId) {
            const detailUrl = `{{ url('api/admin/mahasiswa') }}/${mahasiswaId}`;
            detailModal.style.display = 'flex';
            detailModalBody.innerHTML = '<p>Memuat detail...</p>';

            fetchData(detailUrl)
                .then(response => {
                    const mhs = response.data; // Data dibungkus dalam properti 'data'
                    detailModalBody.innerHTML = `
                        <h4>Informasi Pribadi</h4>
                        <p><strong>Nama:</strong> ${mhs.user?.nama_lengkap ?? 'N/A'}</p>
                        <p><strong>Email:</strong> ${mhs.user?.email ?? 'N/A'}</p>
                        <h4>Informasi Akademik</h4>
                        <p><strong>NPM:</strong> ${mhs.npm ?? 'N/A'}</p>
                        <p><strong>Program Studi:</strong> ${mhs.program_studi ?? 'N/A'}</p>
                        <p><strong>Angkatan:</strong> ${mhs.angkatan ?? 'N/A'}</p>
                        <p><strong>Semester:</strong> ${mhs.semester_aktif ?? 'N/A'}</p>
                        <p><strong>Status:</strong> ${mhs.status ?? 'N/A'}</p>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching detail:', error);
                    detailModalBody.innerHTML = '<p>Gagal memuat detail mahasiswa.</p>';
                });
        }

        // Event listener untuk menangani semua klik di dalam tabel
        mahasiswaTableBody.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('detail-btn')) {
                const mahasiswaId = event.target.getAttribute('data-id');
                showDetailModal(mahasiswaId);
            }
        });

        // Logika untuk menutup modal
        closeModalButton.onclick = () => detailModal.style.display = 'none';
        window.onclick = (event) => {
            if (event.target == detailModal) {
                detailModal.style.display = 'none';
            }
        }

        // Jalankan fungsi utama
        loadMahasiswa();
    });
</script>
@endpush