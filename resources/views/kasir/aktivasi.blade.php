@extends('layouts.app')

@section('title', 'Kasir - Aktivasi Mahasiswa')
@section('page-title', 'Aktivasi Status Semester Mahasiswa')

@section('content')
@php
    $semesterLabel = config('academic.current_semester');
@endphp
<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('kasir.dashboard')],
        ['label' => 'Aktivasi Mahasiswa']
    ]" />

    <x-card title="Kelola Status Aktivasi Mahasiswa" subtitle="Ubah status aktivasi mahasiswa untuk semester {{ $semesterLabel }}">
        <div class="space-y-4">
            {{-- Search Mahasiswa --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Mahasiswa</label>
                <div class="flex gap-2">
                    <input type="text" id="npm-search" placeholder="Masukkan NPM mahasiswa..."
                           class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <button id="btn-search" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Cari
                    </button>
                </div>
            </div>

            {{-- Status Mahasiswa --}}
            <div id="mahasiswa-status-container" class="hidden">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Status Mahasiswa</h3>
                    <div id="mahasiswa-info" class="mb-4"></div>
                    <div id="current-status" class="mb-4"></div>

                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-md font-semibold mb-3 text-gray-700">Ubah Status</h4>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button id="btn-set-aktif" class="flex-1 px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700 transition-colors">
                                Set Aktif
                            </button>
                            <button id="btn-set-bss" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                                Set BSS
                            </button>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                            <textarea id="status-note" rows="2" placeholder="Tambahkan catatan..."
                                      class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daftar Aktivasi Terbaru --}}
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Aktivasi Terbaru ({{ $semesterLabel }})</h3>
                <div id="aktivasi-list" class="space-y-2">
                    <div class="text-center text-gray-500 py-4">Memuat data...</div>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const npmSearch = document.getElementById('npm-search');
    const btnSearch = document.getElementById('btn-search');
    const mahasiswaContainer = document.getElementById('mahasiswa-status-container');
    const mahasiswaInfo = document.getElementById('mahasiswa-info');
    const currentStatus = document.getElementById('current-status');
    const btnSetAktif = document.getElementById('btn-set-aktif');
    const btnSetBss = document.getElementById('btn-set-bss');
    const statusNote = document.getElementById('status-note');
    const aktivasiList = document.getElementById('aktivasi-list');

    let currentMahasiswaId = null;
    let currentAktivasiId = null;

    if (!apiToken) {
        console.error('API token tidak ditemukan');
        return;
    }

    async function apiRequest(url, method = 'GET', body = null) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${apiToken}`,
                'X-CSRF-TOKEN': csrfToken,
            }
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const resp = await fetch(url, opts);
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok) {
            throw new Error(data.message || 'Permintaan gagal');
        }
        return data;
    }

    async function searchMahasiswa() {
        const npm = npmSearch.value.trim();
        if (!npm) {
            Swal.fire({ icon: 'warning', title: 'NPM Kosong', text: 'Silakan masukkan NPM mahasiswa.' });
            return;
        }

        try {
            const response = await apiRequest('/api/kasir/search-mahasiswa', 'POST', { npm });
            if (response.success && response.data) {
                const mhs = response.data;
                currentMahasiswaId = mhs.mahasiswa_id;
                displayMahasiswaInfo(mhs);
                await loadMahasiswaStatus(mhs.mahasiswa_id);
            } else {
                Swal.fire({ icon: 'error', title: 'Tidak Ditemukan', text: 'Mahasiswa dengan NPM tersebut tidak ditemukan.' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal mencari mahasiswa.' });
        }
    }

    function displayMahasiswaInfo(mhs) {
        mahasiswaInfo.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-xl">
                    ${mhs.user?.nama_lengkap?.substring(0, 2).toUpperCase() || 'M'}
                </div>
                <div>
                    <div class="font-semibold text-gray-900">${mhs.user?.nama_lengkap || '-'}</div>
                    <div class="text-sm text-gray-600">NPM: ${mhs.npm || '-'}</div>
                    <div class="text-sm text-gray-600">Program Studi: ${mhs.program_studi || '-'}</div>
                </div>
            </div>
        `;
        mahasiswaContainer.classList.remove('hidden');
    }

    async function loadMahasiswaStatus(mahasiswaId) {
        try {
            const response = await apiRequest(`{{ route('kasir.aktivasi.notifications') }}`);
            const aktivasi = response.data?.find(a => a.mahasiswa_id === mahasiswaId);

            if (aktivasi) {
                currentAktivasiId = aktivasi.id;
                displayCurrentStatus(aktivasi);
            } else {
                currentAktivasiId = null;
                currentStatus.innerHTML = `
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">Belum ada status aktivasi untuk semester ini.</p>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Gagal memuat status:', e);
        }
    }

    function displayCurrentStatus(aktivasi) {
        const statusLabel = aktivasi.status === 'aktif' ? 'Aktif' : 'BSS';
        const statusColor = aktivasi.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
        const chosenBy = aktivasi.chosen_by_role === 'mahasiswa' ? 'Mahasiswa' : 'Kasir';
        const updatedAt = aktivasi.updated_at ? new Date(aktivasi.updated_at).toLocaleString('id-ID') : '';

        currentStatus.innerHTML = `
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">${statusLabel}</span>
                    <span class="text-sm text-gray-600">Dipilih oleh: ${chosenBy}</span>
                </div>
                <div class="text-xs text-gray-500">${updatedAt}</div>
                ${aktivasi.note ? `<div class="mt-2 text-sm text-gray-600">Catatan: ${aktivasi.note}</div>` : ''}
            </div>
        `;
    }

    async function setStatus(status) {
        if (!currentMahasiswaId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Silakan cari mahasiswa terlebih dahulu.' });
            return;
        }

        try {
            const note = statusNote.value.trim() || null;
            let response;

            if (currentAktivasiId) {
                response = await apiRequest(`/api/kasir/aktivasi/${currentAktivasiId}/override`, 'POST', {
                    status,
                    note: note || `Status diubah menjadi ${status} oleh kasir`
                });
            } else {
                response = await apiRequest(`/api/kasir/aktivasi/mahasiswa/${currentMahasiswaId}`, 'POST', {
                    status,
                    note: note || `Status ditetapkan sebagai ${status} oleh kasir`
                });
            }

            if (response.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Status aktivasi berhasil diubah.' });
                statusNote.value = '';
                await loadMahasiswaStatus(currentMahasiswaId);
                await loadAktivasiList();
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal mengubah status.' });
        }
    }

    async function loadAktivasiList() {
        try {
            const response = await apiRequest(`{{ route('kasir.aktivasi.notifications') }}`);
            const data = response.data || [];

            if (data.length === 0) {
                aktivasiList.innerHTML = '<div class="text-center text-gray-500 py-4">Belum ada aktivasi untuk semester ini.</div>';
                return;
            }

            aktivasiList.innerHTML = data.map(item => {
                const statusLabel = item.status === 'aktif' ? 'Aktif' : 'BSS';
                const statusColor = item.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                const chosenBy = item.chosen_by_role === 'mahasiswa' ? 'Mahasiswa' : 'Kasir';
                const nama = item.mahasiswa?.user?.nama_lengkap || 'Mahasiswa';
                const npm = item.mahasiswa?.npm || '-';
                const updatedAt = item.updated_at ? new Date(item.updated_at).toLocaleString('id-ID') : '';

                return `
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">${nama}</div>
                                <div class="text-sm text-gray-600">NPM: ${npm}</div>
                                <div class="text-xs text-gray-500 mt-1">${updatedAt}</div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">${statusLabel}</span>
                                <div class="text-xs text-gray-500 mt-1">Oleh: ${chosenBy}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            aktivasiList.innerHTML = `<div class="text-center text-red-500 py-4">Gagal memuat data: ${e.message}</div>`;
        }
    }

    btnSearch.addEventListener('click', searchMahasiswa);
    npmSearch.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') searchMahasiswa();
    });
    btnSetAktif.addEventListener('click', () => setStatus('aktif'));
    btnSetBss.addEventListener('click', () => setStatus('bss'));

    loadAktivasiList();
});
</script>
@endpush

