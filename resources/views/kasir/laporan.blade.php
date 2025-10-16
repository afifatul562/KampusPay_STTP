@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan')

@section('content')
    @include('layouts.partials.kasir-nav')

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form action="{{ route('kasir.laporan.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
            <div class="w-full sm:w-auto">
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 sm:text-sm">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" id="tahun" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 sm:text-sm">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="pt-0 sm:pt-6">
                <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded-md hover:bg-blue-600 font-semibold">Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- Kartu Statistik & Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-sm text-gray-500">Total Penerimaan</div>
                <div class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-sm text-gray-500">Jumlah Transaksi</div>
                <div class="text-3xl font-bold text-gray-900">{{ $jumlahTransaksi }}</div>
            </div>
        </div>
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
             <canvas id="laporanChart"></canvas>
        </div>
    </div>

    {{-- Tabel Rangkuman --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Rangkuman per Jenis Pembayaran</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nominal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($laporanPerJenis as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->nama_pembayaran }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->jumlah_transaksi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Library Chart.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('laporanChart');
    if (ctx) {
        const myChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Total Penerimaan (Rp)',
                    data: @json($chartData),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>
@endpush

