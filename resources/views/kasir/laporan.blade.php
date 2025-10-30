@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan')

@section('content')
    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Pilih Periode Laporan</h2>
            <button onclick="window.print()" class="w-full sm:w-auto mt-2 sm:mt-0 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-14a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Cetak Laporan
            </button>
        </div>
        <form action="{{ route('kasir.laporan.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-auto">
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" id="tahun" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 font-semibold">Tampilkan</button>
            </div>
        </form>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Total Penerimaan</div>
                <div class="text-2xl font-bold text-gray-900 mt-1 truncate">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</div>
            </div>
            <div class="bg-green-100 p-3 rounded-full"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
        </div>
         <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Jumlah Transaksi</div>
                <div class="text-2xl font-bold text-gray-900 mt-1 truncate">{{ $jumlahTransaksi }}</div>
            </div>
            <div class="bg-blue-100 p-3 rounded-full"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M12 7h.01M15 7h.01"></path></svg></div>
        </div>
    </div>

    <!-- Tabel Rangkuman -->
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-800">Rangkuman per Jenis Pembayaran</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nominal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse ($laporanPerJenis as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->nama_pembayaran }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700">{{ $item->jumlah_transaksi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-800 font-medium">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">Tidak ada data untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
