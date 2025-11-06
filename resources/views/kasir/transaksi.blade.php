@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('kasir.dashboard')],
        ['label' => 'Riwayat Transaksi']
    ]" />

    {{-- Header Section menggunakan komponen reusable --}}
    <x-page-header
        title="Riwayat Transaksi"
        subtitle="Kelola dan filter transaksi pembayaran"
        :icon="$headerIcon">
        <x-slot:actions>
            <a href="{{ route('kasir.transaksi.export', request()->query()) }}">
                <x-gradient-button variant="success" size="md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Ekspor ke CSV
                </x-gradient-button>
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Section menggunakan komponen Card --}}
    <x-card title="Filter Transaksi">
        <form action="{{ route('kasir.transaksi.index') }}" method="GET" aria-label="Form filter transaksi" id="kasirFilterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

                {{-- Filter Nama Mahasiswa --}}
                <div>
                    <label for="nama_mahasiswa" class="text-sm font-medium text-gray-700">Nama Mahasiswa</label>
                    <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" value="{{ request('nama_mahasiswa') }}" placeholder="Cari nama mahasiswa"
                           class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                {{-- Filter Rentang Tanggal --}}
                <div>
                    <label for="start_date_display" class="text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="text" id="start_date_display" value="" placeholder="dd/mm/yyyy"
                           class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500" autocomplete="off">
                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                </div>
                <div>
                    <label for="end_date_display" class="text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="text" id="end_date_display" value="" placeholder="dd/mm/yyyy"
                           class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500" autocomplete="off">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>

                {{-- Filter Jenis Pembayaran --}}
                <div>
                    <label for="jenis_filter" class="text-sm font-medium text-gray-700">Jenis Pembayaran</label>
                    <select name="jenis_filter" id="jenis_filter"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Jenis</option>
                        @foreach ($jenisTarif as $tarif)
                            <option value="{{ $tarif->nama_pembayaran }}" {{ request('jenis_filter') == $tarif->nama_pembayaran ? 'selected' : '' }}>
                                {{ $tarif->nama_pembayaran }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Metode Pembayaran --}}
                <div>
                    <label for="metode_pembayaran" class="text-sm font-medium text-gray-700">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Metode</option>
                        @foreach ($metodePembayaranList as $metode)
                            @php $selected = strtolower(request('metode_pembayaran')) === strtolower($metode); @endphp
                            <option value="{{ $metode }}" {{ $selected ? 'selected' : '' }}>{{ ucfirst($metode) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter menggunakan komponen reusable --}}
                <div class="flex items-end gap-2">
                    <x-gradient-button type="submit" variant="primary" size="md" class="w-full">
                        Filter
                    </x-gradient-button>
                    <a href="{{ route('kasir.transaksi.index') }}" class="w-full inline-flex justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 shadow-sm hover:shadow-md transition-all duration-200" title="Reset Filter">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </x-card>

    {{-- Tabel menggunakan komponen reusable --}}
    @if($transaksi->count() > 0)
        <x-data-table
            :headers="['Tanggal', 'Mahasiswa', 'Jenis Pembayaran', 'Jumlah', 'Metode', 'Bukti', 'Aksi']"
            aria-label="Tabel riwayat transaksi kasir">
            @foreach ($transaksi as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                        {{ \Carbon\Carbon::parse($item->tanggal_bayar)->isoFormat('D MMM YYYY, HH:mm') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-gray-900">{{ data_get($item, 'tagihan.mahasiswa.user.nama_lengkap', 'N/A') }}</div>
                        <div class="text-gray-500">{{ data_get($item, 'tagihan.mahasiswa.npm', 'N/A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ data_get($item, 'tagihan.tarif.nama_pembayaran', 'N/A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                        Rp {{ number_format(data_get($item, 'tagihan.jumlah_tagihan', 0), 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $isCancelled = (bool) ($item->status_dibatalkan ?? false);
                            $metodeLower = \Illuminate\Support\Str::lower($item->metode_pembayaran ?? '');
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $isCancelled ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $isCancelled ? 'Dibatalkan' : ($item->metode_pembayaran ?? '-') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if ($metodeLower === 'transfer' && $item->konfirmasi && $item->konfirmasi->file_bukti_pembayaran)
                            <a href="{{ asset('storage/' . $item->konfirmasi->file_bukti_pembayaran) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200">Lihat Bukti</a>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if (!$item->status_dibatalkan && isset($item->pembayaran_id))
                                <a href="{{ route('kasir.kwitansi.download', ['pembayaran' => $item->pembayaran_id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-primary-100 to-primary-200 text-primary-700 hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200">Kwitansi</a>
                            @endif
                            @if (!$item->status_dibatalkan && $metodeLower === 'transfer')
                                <button type="button" data-cancel-url="{{ route('kasir.transaksi.cancel', $item->pembayaran_id) }}" class="btn-cancel inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-danger-100 to-danger-200 text-danger-700 hover:from-danger-200 hover:to-danger-300 shadow-sm hover:shadow-md transition-all duration-200">Batalkan</button>
                            @endif
                        </div>
                        @if ($item->status_dibatalkan && $item->alasan_pembatalan)
                            <div class="text-xs text-red-700 mt-2">Alasan: {{ $item->alasan_pembatalan }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if ($transaksi->hasPages())
                <x-slot:pagination>
                    {{ $transaksi->links() }}
                </x-slot:pagination>
            @endif
        </x-data-table>
    @else
        @php
            $emptyIcon = '<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>';
        @endphp
        <x-empty-state
            title="Tidak ada transaksi"
            message="Tidak ada riwayat transaksi yang cocok dengan filter yang Anda pilih."
            :icon="$emptyIcon" />
    @endif
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const startDisp = document.getElementById('start_date_display');
    const endDisp = document.getElementById('end_date_display');
    const startHidden = document.getElementById('start_date');
    const endHidden = document.getElementById('end_date');
    const form = document.getElementById('kasirFilterForm');

    function toDdMmYyyy(ymd){
        if(!ymd) return '';
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd);
        if(!m) return '';
        return `${m[3]}/${m[2]}/${m[1]}`;
    }
    function toYmd(ddmmyyyy){
        const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(ddmmyyyy || '');
        if (!m) return '';
        return `${m[3]}-${m[2]}-${m[1]}`;
    }

    // Prefill tampilan jika ada query sebelumnya
    if (startHidden.value) startDisp.value = toDdMmYyyy(startHidden.value);
    if (endHidden.value) endDisp.value = toDdMmYyyy(endHidden.value);

    function bindFlatpickr(inputEl, onChangeCb){
        if (window.flatpickr) {
            const localeId = (window.flatpickr.l10ns && window.flatpickr.l10ns.id) ? window.flatpickr.l10ns.id : undefined;
            flatpickr(inputEl, {
                dateFormat: 'd/m/Y',
                allowInput: false,
                locale: localeId,
                disableMobile: true,
                onChange: function(selectedDates, dateStr){ onChangeCb(dateStr); }
            });
        } else {
            inputEl.placeholder = 'dd/mm/yyyy';
            inputEl.addEventListener('blur', function(){ onChangeCb(inputEl.value); });
        }
    }

    bindFlatpickr(startDisp, (str)=>{ startHidden.value = toYmd(str); });
    bindFlatpickr(endDisp,   (str)=>{ endHidden.value   = toYmd(str); });

    form.addEventListener('submit', function(e){
        // validasi sederhana
        if ((startDisp.value && !startHidden.value) || (endDisp.value && !endHidden.value)) {
            e.preventDefault();
            alert('Format tanggal harus dd/mm/yyyy.');
        }
    });

    // Batalkan pembayaran (SweetAlert jika tersedia, fallback prompt)
    document.querySelectorAll('.btn-cancel').forEach(function(btn){
        btn.addEventListener('click', async function(){
            const url = this.getAttribute('data-cancel-url');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let alasan = '';
            if (window.Swal) {
                const { value } = await Swal.fire({
                    title: 'Batalkan Pembayaran',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan pembatalan (min. 10 karakter)...',
                    inputAttributes: { 'aria-label': 'Alasan pembatalan' },
                    showCancelButton: true,
                    confirmButtonText: 'Batalkan',
                    cancelButtonText: 'Batal',
                    inputValidator: (val) => {
                        if (!val || String(val).trim().length < 10) return 'Minimal 10 karakter.';
                    }
                });
                if (!value) return; alasan = value;
            } else {
                alasan = prompt('Masukkan alasan pembatalan (min. 10 karakter):');
                if (!alasan || alasan.trim().length < 10) return;
            }
            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'text/html' },
                    body: new URLSearchParams({ alasan_pembatalan: alasan })
                });
                if (window.Swal) {
                    Swal.fire({ icon: resp.ok ? 'success' : 'error', title: resp.ok ? 'Berhasil' : 'Gagal', text: resp.ok ? 'Pembayaran dibatalkan.' : 'Gagal membatalkan.' }).then(()=> window.location.reload());
                } else {
                    if (resp.ok) { alert('Pembayaran dibatalkan.'); location.reload(); } else { alert('Gagal membatalkan.'); }
                }
            } catch (e) {
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Terjadi kesalahan' }); else alert('Terjadi kesalahan.');
            }
        });
    });
});
</script>
@endpush
