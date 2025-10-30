@extends('layouts.app')

@section('title', 'Daftar Tagihan')
@section('page-title', 'Daftar Tagihan Pembayaran')

@section('content')
    {{-- Notifikasi Sukses/Error --}}
    @if (session('success'))
        <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            <div><span class="font-medium">Berhasil!</span> {{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            <div><span class="font-medium">Gagal!</span> {{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-xl font-semibold text-gray-800">Semua Tagihan Anda</h2>
            <div>
                <label for="status-filter" class="sr-only">Filter Status</label>
                {{-- Filter dropdown yang sudah diperbaiki --}}
                <select id="status-filter" class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Belum Lunas" {{ request('status') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="Menunggu Pembayaran Tunai" {{ request('status') == 'Menunggu Pembayaran Tunai' ? 'selected' : '' }}>Menunggu Tunai</option>
                    <option value="Menunggu Verifikasi Transfer" {{ request('status') == 'Menunggu Verifikasi Transfer' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse ($tagihan as $item)

                {{-- INI BLOK YANG HILANG (PENYEBAB ERROR) --}}
                @php
                    $borderColorClass = '';
                    switch ($item->status) {
                        case 'Lunas':
                            $borderColorClass = 'border-l-4 border-green-500'; break;
                        case 'Menunggu Verifikasi Transfer':
                            $borderColorClass = 'border-l-4 border-yellow-500'; break;
                        case 'Menunggu Pembayaran Tunai':
                            $borderColorClass = 'border-l-4 border-blue-500'; break;
                        case 'Ditolak':
                            $borderColorClass = 'border-l-4 border-orange-500'; break;
                        default: // 'Belum Lunas'
                            $borderColorClass = 'border-l-4 border-red-500'; break;
                    }
                @endphp

                {{-- Baris ini (sekitar baris 61) sekarang aman digunakan --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-4 items-center hover:bg-gray-50 transition-colors {{ $borderColorClass }}">

                    {{-- Detail Tagihan --}}
                    <div class="md:col-span-3 flex items-center gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ $item->tarif->nama_pembayaran }}</h4>
                            <p class="text-sm text-gray-600">Jatuh Tempo:
                                <span class="font-medium {{ $item->status != 'Lunas' && \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                    {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1 font-mono">Kode: {{ $item->kode_pembayaran }}</p>
                            {{-- ======================================================== --}}
                            {{-- !! KODE UNTUK MENAMPILKAN ALASAN PENOLAKAN !! --}}
                            {{-- ======================================================== --}}
                            @if ($item->status == 'Ditolak' && $item->konfirmasi && $item->konfirmasi->alasan_ditolak)
                                <div class="mt-3 p-3 bg-orange-100 border-l-4 border-orange-500 text-orange-800 rounded-lg">
                                    <p class="font-semibold text-sm">Alasan Ditolak:</p>
                                    <p class="text-sm">{{ $item->konfirmasi->alasan_ditolak }}</p>
                                </div>
                            @endif
                            {{-- ======================================================== --}}
                        </div>
                    </div>

                    {{-- Jumlah --}}
                    <div class="md:col-span-1 text-left md:text-right">
                        <p class="text-lg font-bold text-gray-800">Rp. {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</p>
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-1 text-left md:text-center">
                        @php
                            $statusClass = '';
                            switch ($item->status) {
                                case 'Lunas':
                                    $statusClass = 'bg-green-100 text-green-800'; break;
                                case 'Menunggu Verifikasi Transfer':
                                    $statusClass = 'bg-yellow-100 text-yellow-800'; break;
                                case 'Menunggu Pembayaran Tunai':
                                    $statusClass = 'bg-blue-100 text-blue-800'; break;
                                case 'Ditolak':
                                    $statusClass = 'bg-orange-100 text-orange-800'; break;
                                default: // 'Belum Lunas'
                                    $statusClass = 'bg-red-100 text-red-800'; break;
                            }
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClass }}">
                            {{ $item->status }}
                        </span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="md:col-span-1 text-left md:text-right">
                        @if ($item->status == 'Lunas')
                            @if ($item->pembayaran)
                                <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-100 text-indigo-700 text-sm font-semibold hover:bg-indigo-200">
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
                            <a href="{{ route('mahasiswa.pembayaran.pilih-metode', $item->tagihan_id) }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                                {{ $item->status == 'Ditolak' ? 'Bayar Ulang' : 'Bayar' }}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500">
                    <div class="inline-block bg-green-100 p-4 rounded-full mb-2">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="font-medium">Tidak ada tagihan untuk ditampilkan.</p>
                </div>
            @endforelse
        </div>
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