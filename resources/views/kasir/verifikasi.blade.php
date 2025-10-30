@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')
@section('page-title', 'Verifikasi Pembayaran Transfer')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Menunggu Verifikasi</h2>
            <p class="text-sm text-gray-500 mt-1">Daftar pembayaran via transfer yang perlu diperiksa dan dikonfirmasi.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="verifikasi-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Upload</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Tagihan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse ($pendingVerifications as $item)
                        <tr id="row-{{ $item->konfirmasi_id }}" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMM YYYY, HH:mm') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ optional($item->tagihan->mahasiswa->user)->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-gray-500">{{ optional($item->tagihan->mahasiswa)->npm ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ optional($item->tagihan->tarif)->nama_pembayaran ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                                Rp {{ number_format(optional($item->tagihan)->jumlah_tagihan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ Storage::url($item->file_bukti_pembayaran) }}" target="_blank"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-medium">
                                <div class="flex justify-center items-center gap-2">
                                    <button class="action-btn-approve inline-flex items-center px-3 py-1 text-xs rounded-md bg-green-100 text-green-800 hover:bg-green-200 font-bold" data-id="{{ $item->konfirmasi_id }}">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Setujui
                                    </button>
                                    <button class="action-btn-reject inline-flex items-center px-3 py-1 text-xs rounded-md bg-red-100 text-red-800 hover:bg-red-200 font-bold" data-id="{{ $item->konfirmasi_id }}">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                ✅ Tidak ada pembayaran yang menunggu verifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pendingVerifications->hasPages())
            <div class="p-6 border-t">
                {{ $pendingVerifications->links() }}
            </div>
        @endif
    </div>
@endsection


@push('scripts')
{{-- SweetAlert sudah di-include dari layouts/app.blade.php, jadi tidak perlu lagi --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ==========================================================
        // FUNGSI apiRequest ANDA (Tidak Berubah)
        // ==========================================================
        async function apiRequest(url, method = 'POST', body = null) {
            const apiToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (!apiToken) {
                Swal.fire({ icon: 'error', title: 'Sesi Tidak Valid', text: 'Sesi Anda tidak ditemukan. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '{{ route("login") }}'; });
                return Promise.reject('Token not found');
            }
            const options = {
                method: method,
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${apiToken}`, 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
            };
            if (body) { options.body = JSON.stringify(body); } // Ini akan kita pakai

            try {
                const response = await fetch(url, options);
                if (response.status === 401) {
                    Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Sesi Anda telah berakhir. Harap login kembali.', confirmButtonText: 'Login' }).then(() => { window.location.href = '{{ route("login") }}'; });
                    throw new Error('Unauthorized');
                }
                if (response.status === 204) {
                    return { success: true, message: 'Operasi berhasil.' };
                }
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const responseBody = await response.text();
                    console.error("Non-JSON response received:", response.status, responseBody);
                    throw new Error(`Server (${response.status}): Respon tidak valid.`);
                }

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        throw { status: 422, errors: data.errors, message: data.message || 'Validation failed' };
                    }
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                return data;
            } catch (error) {
                console.error("Error in apiRequest:", error);
                if (error.status === 422) throw error;
                throw new Error(error.message || 'Gagal terhubung ke server.');
            }
        }

        // ==========================================================
        // Event Listener Tabel (Tidak Berubah)
        // ==========================================================
        const table = document.getElementById('verifikasi-table');
        if (table) {
            table.addEventListener('click', function(event) {
                const button = event.target.closest('button');
                if (!button) return;

                const konfirmasiId = button.dataset.id;
                if (button.classList.contains('action-btn-approve')) {
                    handleAction('approve', konfirmasiId, button);
                } else if (button.classList.contains('action-btn-reject')) {
                    handleAction('reject', konfirmasiId, button);
                }
            });
        }

        // ==========================================================
        // !! INI FUNGSI YANG DIPERBARUI !!
        // ==========================================================
        async function handleAction(action, id, button) {
            const isApprove = action === 'approve';
            const originalButtonHTML = button.innerHTML; // Simpan HTML asli

            // Helper function untuk loading
            const showLoading = () => {
                button.innerHTML = `<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                button.disabled = true;
            };

            // Helper function untuk handle hasil
            const handleApiResponse = (response, konfirmasiId) => {
                if (response.success) {
                    Swal.fire('Berhasil!', response.message || 'Aksi berhasil dilakukan.', 'success');
                    const row = document.getElementById(`row-${konfirmasiId}`);
                    if(row) {
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 500);
                    }
                } else {
                    Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    button.innerHTML = originalButtonHTML;
                    button.disabled = false;
                }
            };

            // Helper function untuk handle error
            const handleApiError = (error) => {
                 if (error.status === 422) {
                    // Tampilkan error validasi pertama untuk 'alasan_ditolak' jika ada
                    let errorMsg = error.message || 'Data tidak valid.';
                    if (error.errors && error.errors.alasan_ditolak) {
                        errorMsg = error.errors.alasan_ditolak[0];
                    }
                    Swal.fire('Validasi Gagal!', errorMsg, 'error');
                } else {
                    Swal.fire('Error!', error.message || 'Tidak dapat terhubung ke server.', 'error');
                }
                button.innerHTML = originalButtonHTML;
                button.disabled = false;
            };

            // --- Logika Utama (Approve vs Reject) ---

            if (isApprove) {
                // --- ALUR UNTUK SETUJUI (LAMA) ---
                Swal.fire({
                    title: 'Anda yakin?',
                    text: 'Pembayaran ini akan disetujui dan tagihan akan dilunaskan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, setujui!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        showLoading();
                        try {
                            const url = `{{ url('/api/kasir/verifikasi') }}/approve/${id}`;
                            const response = await apiRequest(url, 'POST'); // Approve tidak perlu body
                            handleApiResponse(response, id);
                        } catch (error) {
                            handleApiError(error);
                        }
                    }
                });

            } else {
                // --- ALUR UNTUK TOLAK (BARU) ---
                Swal.fire({
                    title: 'Tolak Verifikasi?',
                    text: 'Harap masukkan alasan penolakan:',
                    icon: 'warning',
                    input: 'textarea', // <-- Kunci utamanya
                    inputPlaceholder: 'Contoh: Bukti transfer tidak jelas, nominal tidak sesuai...',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Anda harus mengisi alasan penolakan!';
                        }
                        if (value.length < 10) {
                            return 'Alasan harus diisi minimal 10 karakter.';
                        }
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, tolak!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    // Cek jika dikonfirmasi DAN ada alasannya
                    if (result.isConfirmed && result.value) {
                        showLoading();
                        try {
                            const url = `{{ url('/api/kasir/verifikasi') }}/reject/${id}`;
                            // Kirim alasan di dalam body request
                            const body = { alasan_ditolak: result.value };
                            const response = await apiRequest(url, 'POST', body);
                            handleApiResponse(response, id);
                        } catch (error) {
                            handleApiError(error);
                        }
                    }
                });
            }
        }
    });
</script>
@endpush