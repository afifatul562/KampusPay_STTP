@extends('layouts.app')

@section('title', 'Daftar Tagihan')
@section('page-title', 'Daftar Tagihan Pembayaran')

@section('content')
<div class="space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('mahasiswa.dashboard')],
        ['label' => 'Daftar Tagihan Pembayaran']
    ]" />

    {{-- Notifikasi Sukses/Error --}}
    @if (session('success'))
        <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border-l-4 border-green-500" role="alert">
            <svg class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div><span class="font-medium">Berhasil!</span> {{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 border-l-4 border-red-500" role="alert">
            <svg class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div><span class="font-medium">Gagal!</span> {{ session('error') }}</div>
        </div>
    @endif

    @php
        $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>';
    @endphp

    <x-page-header
        title="Semua Tagihan Anda"
        subtitle="Kelola pembayaran tagihan Anda"
        :icon="$headerIcon">
        <x-slot:actions>
            <label for="status-filter" class="sr-only">Filter Status</label>
            <select id="status-filter" aria-label="Filter status tagihan" class="border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                <option value="">Semua Status</option>
                <option value="Belum Lunas" {{ request('status') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                <option value="Menunggu Pembayaran Tunai" {{ request('status') == 'Menunggu Pembayaran Tunai' ? 'selected' : '' }}>Menunggu Tunai</option>
                <option value="Menunggu Verifikasi Transfer" {{ request('status') == 'Menunggu Verifikasi Transfer' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    <x-card>

        <div class="divide-y divide-gray-200">
            @forelse ($tagihan as $item)

                @php
                    $isDibatalkan = $item->pembayaran && $item->pembayaran->status_dibatalkan;
                    $statusForBorder = $item->status;

                    if ($isDibatalkan) {
                        $statusForBorder = 'Dibatalkan';
                    }

                    $borderColorClass = '';
                    switch ($statusForBorder) {
                        case 'Lunas':
                            $borderColorClass = 'border-l-4 border-green-500'; break;
                        case 'Menunggu Verifikasi Transfer':
                            $borderColorClass = 'border-l-4 border-yellow-500'; break;
                        case 'Menunggu Pembayaran Tunai':
                            $borderColorClass = 'border-l-4 border-blue-500'; break;
                        case 'Ditolak':
                            $borderColorClass = 'border-l-4 border-orange-500'; break;
                        case 'Dibatalkan':
                            $borderColorClass = 'border-l-4 border-red-500'; break;
                        default: // 'Belum Lunas'
                            $borderColorClass = 'border-l-4 border-red-500'; break;
                    }
                @endphp

                <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-4 items-center hover:bg-gray-50 transition-colors {{ $borderColorClass }}">

                    {{-- Detail Tagihan --}}
                    <div class="md:col-span-3 flex items-center gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ $item->tarif->nama_pembayaran }}</h4>
                            @php
                                $semesterLabel = $item->semester_label ?? null;
                                $tahunAkademik = '-';
                                $semesterType = '-';
                                if ($semesterLabel) {
                                    $parts = explode(' ', trim($semesterLabel));
                                    if (count($parts) >= 2) {
                                        $tahunAkademik = $parts[0];
                                        $semesterType = $parts[1];
                                    }
                                }

                                $semesterNumber = null;
                                $angkatan = $item->mahasiswa->angkatan ?? null;
                                if ($tahunAkademik !== '-' && $angkatan && $semesterType !== '-') {
                                    try {
                                        $tahunParts = explode('/', $tahunAkademik);
                                        if (count($tahunParts) >= 1) {
                                            $tahunAkademikAwal = (int) $tahunParts[0];
                                            $angkatanInt = (int) $angkatan;
                                            if ($tahunAkademikAwal > 0 && $angkatanInt > 0) {
                                                $selisihTahun = $tahunAkademikAwal - $angkatanInt;
                                                if (strtolower($semesterType) === 'ganjil') {
                                                    $semesterNumber = $selisihTahun * 2 + 1;
                                                } else if (strtolower($semesterType) === 'genap') {
                                                    $semesterNumber = $selisihTahun * 2 + 2;
                                                }
                                            }
                                    }
                                } catch (Exception $e) {
                                }
                            }

                            if (!$semesterNumber && isset($item->mahasiswa->semester_aktif)) {
                                    $semesterNumber = $item->mahasiswa->semester_aktif;
                                }
                            @endphp
                            <div class="text-sm text-gray-600 mt-1">
                                @if($tahunAkademik !== '-')
                                    <span class="font-medium">Tahun Akademik: {{ $tahunAkademik }}</span>
                                    @if($semesterType !== '-')
                                        <span class="mx-2">•</span>
                                        <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full {{ strtolower($semesterType) === 'ganjil' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ $semesterType }}</span>
                                    @endif
                                    @if($semesterNumber)
                                        <span class="mx-2">•</span>
                                        <span class="font-medium">Semester {{ $semesterNumber }}</span>
                                    @endif
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Jatuh Tempo:
                                <span class="font-medium {{ $item->status != 'Lunas' && \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                    {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1 font-mono">Kode: {{ $item->kode_pembayaran }}</p>
                            @if ($item->status == 'Ditolak' && $item->konfirmasi && $item->konfirmasi->alasan_ditolak)
                                <div class="mt-3 p-3 bg-orange-100 border-l-4 border-orange-500 text-orange-800 rounded-lg">
                                    <p class="font-semibold text-sm">Alasan Ditolak:</p>
                                    <p class="text-sm">{{ $item->konfirmasi->alasan_ditolak }}</p>
                                </div>
                            @endif

                            {{-- Tampilkan alasan pembatalan jika pembayaran dibatalkan --}}
                            @if ($item->pembayaran && $item->pembayaran->status_dibatalkan && $item->pembayaran->alasan_pembatalan)
                                <div class="mt-3 p-3 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-lg shadow-sm">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-bold text-sm mb-1">Pembayaran Dibatalkan</p>
                                            <p class="text-xs text-red-700 mb-1">Alasan pembatalan:</p>
                                            <p class="text-sm font-medium bg-white p-2 rounded border border-red-200">{{ $item->pembayaran->alasan_pembatalan }}</p>
                                            <p class="text-xs mt-2 text-red-700">Pembayaran ini telah dibatalkan oleh kasir. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Jumlah --}}
                    <div class="md:col-span-1 text-left md:text-right">
                        <p class="text-lg font-bold text-gray-800">Rp. {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</p>
                        @if($item->total_angsuran > 0)
                            @php
                                $sisaPokok = $item->sisa_pokok ?? $item->jumlah_tagihan;
                            @endphp
                            <p class="text-xs text-green-600 mt-1">Dibayar: Rp. {{ number_format($item->total_angsuran, 0, ',', '.') }}</p>
                            <p class="text-xs text-orange-600 font-semibold mt-1">Sisa: Rp. {{ number_format($sisaPokok, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-1 text-left md:text-center">
                        @php
                            $isDibatalkan = $item->pembayaran && $item->pembayaran->status_dibatalkan;
                            $statusDisplay = $item->status;

                            if ($isDibatalkan) {
                                $statusDisplay = 'Dibatalkan';
                            }

                            $statusClass = '';
                            switch ($statusDisplay) {
                                case 'Lunas':
                                    $statusClass = 'bg-green-100 text-green-800'; break;
                                case 'Menunggu Verifikasi Transfer':
                                    $statusClass = 'bg-yellow-100 text-yellow-800'; break;
                                case 'Menunggu Pembayaran Tunai':
                                    $statusClass = 'bg-blue-100 text-blue-800'; break;
                                case 'Ditolak':
                                    $statusClass = 'bg-orange-100 text-orange-800'; break;
                                case 'Dibatalkan':
                                    $statusClass = 'bg-red-100 text-red-800'; break;
                                default: // 'Belum Lunas'
                                    $statusClass = 'bg-red-100 text-red-800'; break;
                            }
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClass }}">
                            {{ $statusDisplay }}
                        </span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="md:col-span-1 text-left md:text-right">
                        @php
                            $isDibatalkan = $item->pembayaran && $item->pembayaran->status_dibatalkan;
                        @endphp

                        @if ($isDibatalkan)
                            {{-- Jika pembayaran dibatalkan, tidak tampilkan tombol aksi --}}
                            <span class="text-xs text-gray-400 italic">Tidak tersedia</span>
                        @elseif ($item->status == 'Lunas')
                            @if ($item->pembayaran && !$item->pembayaran->status_dibatalkan)
                                <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-primary-100 to-primary-200 text-primary-700 text-sm font-semibold hover:from-primary-200 hover:to-primary-300 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Kwitansi
                                </a>
                            @endif

                        @elseif ($item->status == 'Menunggu Verifikasi Transfer')
                            <button class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-200 text-gray-500 text-sm font-semibold cursor-wait" disabled>
                                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Diproses
                            </button>

                        @elseif ($item->status == 'Menunggu Pembayaran Tunai')
                             <button class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-100 text-blue-700 text-sm font-semibold cursor-default" disabled>
                                Menunggu Tunai
                            </button>

                        @else
                            <a href="{{ route('mahasiswa.pembayaran.pilih-metode', $item->tagihan_id) }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 text-white text-sm font-semibold hover:from-primary-700 hover:to-primary-800 shadow-md hover:shadow-lg transition-all duration-200">
                                {{ $item->status == 'Ditolak' ? 'Bayar Ulang' : 'Bayar' }}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                @php
                    $emptyIcon = '<svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                @endphp
                <x-empty-state
                    title="Tidak ada tagihan"
                    message="Selamat! Tidak ada tagihan untuk ditampilkan."
                    :icon="$emptyIcon" />
            @endforelse
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const selectedStatus = this.value;
                const currentUrl = new URL(window.location.href);
                if (selectedStatus) {
                    currentUrl.searchParams.set('status', selectedStatus);
                } else {
                    currentUrl.searchParams.delete('status');
                }
                window.location.href = currentUrl.toString();
            });
        }
    });
</script>
@endpush
