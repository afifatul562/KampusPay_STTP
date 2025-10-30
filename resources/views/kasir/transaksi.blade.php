@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6 border-b">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <h2 class="text-xl font-semibold text-gray-800">Filter Transaksi</h2>
                <a href="{{ route('kasir.transaksi.export', request()->query()) }}"
                   class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 w-full md:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Ekspor ke Excel
                </a>
            </div>

            <form action="{{ route('kasir.transaksi.index') }}" method="GET" class="mt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Filter Rentang Tanggal --}}
                    <div>
                        <label for="start_date" class="text-sm font-medium text-gray-700">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="text-sm font-medium text-gray-700">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- Filter Jenis Pembayaran --}}
                    <div>
                        <label for="jenis_filter" class="text-sm font-medium text-gray-700">Jenis Pembayaran</label>
                        <select name="jenis_filter" id="jenis_filter"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Jenis</option>
                            @foreach ($jenisTarif as $tarif)
                                <option value="{{ $tarif->nama_pembayaran }}" {{ request('jenis_filter') == $tarif->nama_pembayaran ? 'selected' : '' }}>
                                    {{ $tarif->nama_pembayaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            Filter
                        </button>
                        <a href="{{ route('kasir.transaksi.index') }}" class="w-full inline-flex justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50" title="Reset Filter">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse ($transaksi as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ \Carbon\Carbon::parse($item->tanggal_bayar)->isoFormat('D MMM YYYY, HH:mm') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $item->tagihan->mahasiswa->user->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-gray-500">{{ $item->tagihan->mahasiswa->npm ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $item->tagihan->tarif->nama_pembayaran ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                                Rp {{ number_format($item->tagihan->jumlah_tagihan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $item->metode_pembayaran }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">
                                Tidak ada riwayat transaksi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaksi->hasPages())
            <div class="p-6 border-t">
                {{ $transaksi->links() }}
            </div>
        @endif
    </div>
@endsection