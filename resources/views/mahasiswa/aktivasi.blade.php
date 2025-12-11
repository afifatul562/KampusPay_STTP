@extends('layouts.app')

@section('title', 'Aktivasi Mahasiswa')
@section('page-title', 'Aktivasi Status Semester')

@section('content')
@php
    $semesterLabel = config('academic.current_semester');
@endphp
<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('mahasiswa.dashboard')],
        ['label' => 'Aktivasi Semester']
    ]" />

    <x-card title="Pilih Status Semester ({{ $semesterLabel }})" subtitle="Tentukan apakah Anda aktif kuliah atau BSS (Berhenti Sementara)">
        <div class="flex flex-col gap-4">
            <div id="aktivasiStatusBox" class="p-4 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-700">
                Memuat status...
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="btnAktif" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-white bg-success-600 hover:bg-success-700 shadow-sm">Pilih Aktif</button>
                <button id="btnBss" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-white bg-amber-600 hover:bg-amber-700 shadow-sm">Pilih BSS</button>
            </div>
            <p class="text-xs text-gray-500">Catatan: pilihan berlaku untuk semester {{ $semesterLabel }}. Kasir akan mendapat notifikasi setelah Anda memilih.</p>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusBox = document.getElementById('aktivasiStatusBox');
    const btnAktif = document.getElementById('btnAktif');
    const btnBss = document.getElementById('btnBss');
    const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');

    if (!apiToken) {
        statusBox.textContent = 'Sesi tidak valid. Silakan login ulang.';
        return;
    }

    async function apiRequest(url, method = 'GET', body = null) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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

    function renderStatus(data) {
        if (!data || !data.data) {
            statusBox.textContent = 'Belum ada pilihan untuk semester ini.';
            // Jika belum ada status, tombol tetap aktif
            btnAktif.disabled = false;
            btnBss.disabled = false;
            btnAktif.classList.remove('opacity-50', 'cursor-not-allowed');
            btnBss.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }
        const s = data.data;
        const chosenBy = s.chosen_by_role === 'mahasiswa' ? 'Mahasiswa' : 'Kasir';
        
        statusBox.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${s.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">
                    ${s.status === 'aktif' ? 'Aktif' : 'BSS'}
                </span>
                <div class="text-sm text-gray-700">
                    Dipilih oleh: ${chosenBy} • ${s.updated_at ? new Date(s.updated_at).toLocaleString('id-ID') : ''}
                </div>
            </div>
            ${s.note ? `<p class="mt-2 text-xs text-gray-600">Catatan: ${s.note}</p>` : ''}
            <p class="mt-2 text-xs text-amber-600 font-medium">⚠️ Status sudah dipilih. Untuk mengubah, silakan hubungi kasir.</p>
        `;

        // Nonaktifkan tombol jika sudah ada status (tidak peduli siapa yang pilih)
        btnAktif.disabled = true;
        btnBss.disabled = true;
        btnAktif.classList.add('opacity-50', 'cursor-not-allowed');
        btnBss.classList.add('opacity-50', 'cursor-not-allowed');
    }

    async function loadStatus() {
        try {
            const data = await apiRequest("{{ route('mahasiswa.aktivasi.current') }}");
            renderStatus(data);
        } catch (e) {
            statusBox.textContent = e.message || 'Gagal memuat status.';
        }
    }

    async function setStatus(status) {
        try {
            // Cek dulu apakah tombol disabled
            if (btnAktif.disabled || btnBss.disabled) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Tidak Dapat Diubah', 
                    text: 'Status sudah dipilih. Untuk mengubah, silakan hubungi kasir.' 
                });
                return;
            }

            const note = status === 'bss' ? 'BSS dipilih oleh mahasiswa' : 'Aktif dipilih oleh mahasiswa';
            const response = await apiRequest("{{ route('mahasiswa.aktivasi.store') }}", 'POST', { status, note });
            
            if (response.success) {
                Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'Pilihan Anda sudah dikirim ke kasir.' });
                loadStatus();
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal menyimpan.' });
            // Reload status untuk update UI
            loadStatus();
        }
    }

    btnAktif?.addEventListener('click', () => setStatus('aktif'));
    btnBss?.addEventListener('click', () => setStatus('bss'));
    loadStatus();
});
</script>
@endpush

