@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
    @include('layouts.partials.kasir-nav')

    <div class="bg-white p-6 rounded-lg shadow-md">
        {{-- Filter & Ekspor --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
            <h3 class="text-xl font-semibold text-gray-700">Filter Transaksi</h3>
            <div class="flex items-center gap-3">
                <form action="{{ route('kasir.transaksi.index') }}" method="GET" class="flex items-center gap-2">
                    <select name="jenis_filter" class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Jenis</option>
                        @foreach ($jenisTarif as $tarif)
                            <option value="{{ $tarif->nama_pembayaran }}" {{ request('jenis_filter') == $tarif->nama_pembayaran ? 'selected' : '' }}>
                                {{ $tarif->nama_pembayaran }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm font-semibold">Filter</button>
                    <a href="{{ route('kasir.transaksi.index') }}" class="text-gray-600 hover:text-gray-800 text-sm" title="Reset Filter">Reset</a>
                </form>

                <a href="{{ route('kasir.transaksi.export', request()->query()) }}"
                   class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center gap-2 text-sm font-semibold">
                   Ekspor
                </a>
            </div>
        </div>

        {{-- Tabel Riwayat --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NPM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transaksi as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->tagihan->mahasiswa->npm ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp {{ number_format($item->tagihan->jumlah_tagihan ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $item->metode_pembayaran }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $transaksi->links() }}
        </div>
    </div>
@endsection

