@extends('layouts.app')

@section('title', 'Laporan Mahasiswa')
@section('page-title', 'Laporan Mahasiswa')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>';
@endphp

<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('mahasiswa.dashboard')],
        ['label' => 'Laporan']
    ]" />
    
    <x-page-header
        title="Laporan"
        subtitle="Download laporan pembayaran dalam format PDF"
        :icon="$headerIcon">
    </x-page-header>

    {{-- Notifikasi jika ada error dari backend --}}
    @if (session('report_error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 border-l-4 border-red-500" role="alert">
            <svg class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div><span class="font-medium">Gagal!</span> {{ session('report_error') }}</div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Laporan Histori Pembayaran" subtitle="Generate laporan lengkap riwayat pembayaran Anda dalam format PDF" withHeader>

            <form action="{{ route('mahasiswa.laporan.histori.download') }}" method="GET" class="flex-grow flex flex-col" id="historiForm">
                <div class="space-y-4 flex-grow">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="text" id="start_date_display" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="dd/mm/yyyy" autocomplete="off" required>
                        <input type="hidden" name="start_date" id="start_date">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="text" id="end_date_display" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="dd/mm/yyyy" autocomplete="off" required>
                        <input type="hidden" name="end_date" id="end_date">
                    </div>
                </div>
                <div class="mt-6">
                    <x-gradient-button type="submit" variant="primary" size="md" class="w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Histori (PDF)
                    </x-gradient-button>
                </div>
            </form>
        </x-card>

        <x-card title="Laporan Data Tunggakan" subtitle="Unduh laporan berisi semua tunggakan pembayaran yang masih aktif" withHeader>

            <div class="flex-grow">
                @if($tunggakan->isEmpty())
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg text-center h-full flex flex-col justify-center items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="font-semibold">Selamat!</p>
                        <p class="text-sm">Anda tidak memiliki tunggakan aktif.</p>
                    </div>
                @else
                    @php
                        $totalTunggakan = $tunggakan->sum(function($tagihan) {
                            return $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;
                        });
                    @endphp
                    <div class="bg-red-100 text-red-800 p-4 rounded-lg">
                        <p>Anda memiliki <strong>{{ $tunggakan->count() }} tunggakan</strong> dengan total <strong>Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong>.</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                 @if($tunggakan->isEmpty())
                    <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed" disabled>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Tunggakan (PDF)
                    </button>
                 @else
                    <a href="{{ route('mahasiswa.laporan.tunggakan.download') }}" class="w-full inline-flex justify-center items-center no-underline">
                        <x-gradient-button variant="primary" size="md" class="w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Tunggakan (PDF)
                        </x-gradient-button>
                    </a>
                @endif
            </div>
        </x-card>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const startDisp = document.getElementById('start_date_display');
    const endDisp = document.getElementById('end_date_display');
    const startHidden = document.getElementById('start_date');
    const endHidden = document.getElementById('end_date');
    const form = document.getElementById('historiForm');

    function bindFlatpickr(inputEl, onChangeCb){
        if (window.flatpickr) {
            const localeId = (window.flatpickr.l10ns && window.flatpickr.l10ns.id) ? window.flatpickr.l10ns.id : undefined;
            flatpickr(inputEl, {
                dateFormat: 'd/m/Y',
                allowInput: false,
                locale: localeId,
                disableMobile: true,
                onChange: function(selectedDates, dateStr){
                    onChangeCb(selectedDates, dateStr);
                }
            });
        } else {
            // fallback manual
            inputEl.placeholder = 'dd/mm/yyyy';
            inputEl.addEventListener('blur', function(){ onChangeCb([], inputEl.value); });
        }
    }

    function toYmd(ddmmyyyy){
        const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(ddmmyyyy || '');
        if (!m) return '';
        return `${m[3]}-${m[2]}-${m[1]}`;
    }

    bindFlatpickr(startDisp, (_, str) => { startHidden.value = toYmd(str); });
    bindFlatpickr(endDisp,   (_, str) => { endHidden.value   = toYmd(str); });

    form.addEventListener('submit', function(e){
        if (!startHidden.value || !endHidden.value) {
            e.preventDefault();
            alert('Mohon pilih tanggal dengan benar (dd/mm/yyyy).');
            return;
        }
        
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            setButtonLoading(submitButton, true, 'Mengunduh PDF...');
        }
    });
});
</script>
@endpush
