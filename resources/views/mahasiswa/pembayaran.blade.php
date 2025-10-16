@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-title', 'Daftar Tagihan Pembayaran')

@section('content')
    @include('layouts.partials.mahasiswa-nav')

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="flex justify-end mb-4">
        <select id="status-filter" class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
            <option value="Belum Lunas" {{ request('status') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
        </select>
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($tagihan as $item)
            <div class="bg-white rounded-lg shadow p-4 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">

                <div class="flex items-center gap-4">
                    <span class="text-4xl opacity-75">📄</span>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h4 class="font-bold text-lg">{{ $item->tarif->nama_pembayaran }}</h4>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $item->status == 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $item->status }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">Jatuh Tempo:
                            <span class="{{ $item->status != 'Lunas' && \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isPast() ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d F Y') }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col md:items-end">
                    <p class="text-sm text-gray-500">Jumlah:
                        <span class="text-xl font-bold text-orange-500">Rp. {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Kode Pembayaran: {{ $item->kode_pembayaran }}</p>

                    <div class="mt-3">
                        @if ($item->status == 'Belum Lunas')
                            @php
                                $menungguVerifikasi = $item->konfirmasiPembayaran->where('status_verifikasi', 'Menunggu Verifikasi')->isNotEmpty();
                            @endphp

                            @if($menungguVerifikasi)
                                <button class="px-4 py-2 rounded-md bg-yellow-400 text-yellow-800 text-sm font-semibold cursor-wait" disabled>Menunggu Verifikasi</button>
                            @else
                                <a href="{{ route('mahasiswa.pembayaran.show', $item->tagihan_id) }}" class="inline-block px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600">Bayar Sekarang</a>
                            @endif
                        @else
                            @if ($item->pembayaran)
                                <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}"
                                   class="inline-block px-4 py-2 rounded-md bg-purple-500 text-white text-sm font-semibold hover:bg-purple-600">
                                   Download Kwitansi
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p>✅ Tidak ada tagihan untuk ditampilkan.</p>
            </div>
        @endforelse
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

