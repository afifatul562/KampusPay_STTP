@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')
@section('page-title', 'Verifikasi Pembayaran Transfer')

@section('content')
    @include('layouts.partials.kasir-nav')

    <div class="bg-white p-6 rounded-lg shadow-md">
        <p class="text-gray-600 mb-4">Daftar pembayaran via transfer yang perlu diperiksa dan dikonfirmasi.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="verifikasi-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Upload</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pendingVerifications as $item)
                        <tr id="row-{{ $item->konfirmasi_id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-semibold text-gray-900">{{ optional($item->tagihan->mahasiswa->user)->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-gray-500">{{ optional($item->tagihan->mahasiswa)->npm ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ optional($item->tagihan->tarif)->nama_pembayaran ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp {{ number_format(optional($item->tagihan)->jumlah_tagihan ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{-- ▼▼▼ INI KODE YANG BENAR ▼▼▼ --}}
                                <a href="{{ Storage::url($item->file_bukti_pembayaran) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                    Lihat Bukti
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="action-btn-approve px-3 py-1 text-xs rounded-md bg-green-500 text-white hover:bg-green-600" data-id="{{ $item->konfirmasi_id }}">Setujui</button>
                                <button class="action-btn-reject px-3 py-1 text-xs rounded-md bg-red-500 text-white hover:bg-red-600" data-id="{{ $item->konfirmasi_id }}">Tolak</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">✅ Tidak ada pembayaran yang menunggu verifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pendingVerifications->links() }}
        </div>
    </div>
@endsection

@push('scripts')
{{-- JavaScript untuk SweetAlert tidak perlu diubah --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        async function apiRequest(url, method = 'POST', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) { Swal.fire('Error', 'Sesi tidak valid, harap login kembali.', 'error'); return Promise.reject('Token not found'); }
            const options = {
                method: method,
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'Content-Type': 'application/json' }
            };
            if (body) { options.body = JSON.stringify(body); }
            const response = await fetch(url, options);
            return response.json();
        }

        const table = document.getElementById('verifikasi-table');
        if (table) {
            table.addEventListener('click', function(event) {
                const target = event.target;
                const konfirmasiId = target.dataset.id;
                if (target.classList.contains('action-btn-approve')) {
                    handleAction('approve', konfirmasiId);
                } else if (target.classList.contains('action-btn-reject')) {
                    handleAction('reject', konfirmasiId);
                }
            });
        }

        async function handleAction(action, id) {
            const url = `{{ url('/api/kasir/verifikasi/') }}/${action}/${id}`;
            const confirmationText = action === 'approve'
                ? { title: 'Anda yakin?', text: 'Pembayaran ini akan disetujui dan tagihan akan dilunaskan.', confirmButtonText: 'Ya, setujui!' }
                : { title: 'Anda yakin?', text: 'Pembayaran ini akan ditolak.', confirmButtonText: 'Ya, tolak!' };

            Swal.fire(confirmationText).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await apiRequest(url, 'POST');
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            document.getElementById(`row-${id}`).remove();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error');
                    }
                }
            });
        }
    });
</script>
@endpush

