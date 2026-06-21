@extends('layouts.app')

@section('title', 'Monitoring Yayasan')
@section('page-title', 'Panel Monitoring Yayasan')

@section('content')
@php
    $fmt = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

<div class="space-y-4 sm:space-y-6">
    <div class="rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-white px-4 py-3 sm:px-5 sm:py-4 flex items-start gap-3">
        <div class="shrink-0 mt-0.5 rounded-lg bg-indigo-100 p-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-fluid-sm font-semibold text-indigo-900">Akses hanya baca (read-only)</p>
            <p class="text-fluid-xs sm:text-fluid-sm text-indigo-700/90 mt-0.5">Anda dapat memantau kondisi keuangan kampus tanpa mengubah data. Untuk operasional, hubungi admin atau kasir.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 overflow-hidden" aria-label="Total mahasiswa">
            <div class="flex-1 min-w-0">
                <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total Mahasiswa</div>
                <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_mahasiswa']) }}</div>
            </div>
            <div class="bg-gradient-to-br from-primary-400 to-primary-600 p-3 sm:p-4 rounded-xl shadow-lg shrink-0">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <div class="relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 overflow-hidden" aria-label="Total uang masuk">
            <div class="flex-1 min-w-0">
                <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total Uang Masuk</div>
                <div class="font-bold text-gray-900 mt-1 text-fluid-lg lg:text-fluid-xl whitespace-nowrap tabular-nums leading-tight">{{ $fmt($stats['total_pembayaran']) }}</div>
                <p class="text-fluid-xs text-gray-400 mt-1">Tahun kalender {{ $stats['tahun_kalender'] ?? now()->year }}</p>
            </div>
            <div class="bg-gradient-to-br from-success-400 to-success-600 p-3 sm:p-4 rounded-xl shadow-lg shrink-0">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <div class="relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 overflow-hidden" aria-label="Tingkat pembayaran">
            <div class="flex-1 min-w-0">
                <div class="text-fluid-sm font-medium text-gray-500 mb-1">Tingkat Pembayaran</div>
                <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">{{ $stats['tingkat_pembayaran'] }}%</div>
                <p class="text-fluid-xs text-gray-400 mt-1">Tahun kalender {{ $stats['tahun_kalender'] ?? now()->year }}</p>
            </div>
            <div class="bg-gradient-to-br from-warning-400 to-warning-600 p-3 sm:p-4 rounded-xl shadow-lg shrink-0">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zm3.707 6.293a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l6-6z" clip-rule="evenodd"/></svg>
            </div>
        </div>

        <div class="relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 overflow-hidden" aria-label="Tagihan belum lunas">
            <div class="flex-1 min-w-0">
                <div class="text-fluid-sm font-medium text-gray-500 mb-1">Tagihan Belum Lunas</div>
                <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['pending_payment']) }}</div>
            </div>
            <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-3 sm:p-4 rounded-xl shadow-lg shrink-0">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm">
            <div class="text-fluid-xs font-medium text-gray-500 uppercase tracking-wide">Menunggu Verifikasi Kasir</div>
            <div class="text-fluid-2xl font-bold mt-1 {{ $operasional['menunggu_verifikasi'] > 0 ? 'text-amber-700' : 'text-gray-900' }}">
                {{ number_format($operasional['menunggu_verifikasi']) }}
            </div>
            <p class="text-fluid-xs text-gray-500 mt-2">Transfer mahasiswa yang belum diverifikasi</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm">
            <div class="text-fluid-xs font-medium text-gray-500 uppercase tracking-wide">Aktivasi Semester</div>
            <div class="text-fluid-sm font-semibold text-indigo-800 mt-1">{{ $operasional['semester_label'] }}</div>
            <div class="mt-2 grid grid-cols-3 gap-2 text-center text-fluid-xs">
                <div class="rounded-lg bg-green-50 py-2">
                    <div class="font-bold text-green-800">{{ number_format($operasional['aktivasi_aktif']) }}</div>
                    <div class="text-green-700">Aktif</div>
                </div>
                <div class="rounded-lg bg-amber-50 py-2">
                    <div class="font-bold text-amber-800">{{ number_format($operasional['aktivasi_bss']) }}</div>
                    <div class="text-amber-700">BSS</div>
                </div>
                <div class="rounded-lg bg-gray-50 py-2">
                    <div class="font-bold text-gray-800">{{ number_format($operasional['belum_aktivasi']) }}</div>
                    <div class="text-gray-600">Belum</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-fluid-xs font-medium text-gray-500 uppercase tracking-wide">Laporan Cepat</div>
                <p class="text-fluid-sm text-gray-600 mt-2">Unduh laporan pembayaran atau tunggakan per tahun akademik.</p>
            </div>
            <a href="{{ route('yayasan.laporan.index') }}" class="mt-3 inline-flex items-center justify-center min-h-touch px-4 py-2 rounded-lg bg-indigo-600 text-white text-fluid-sm font-semibold hover:bg-indigo-700">
                Buka Laporan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-5 md:p-6">
            <h2 class="text-fluid-lg font-bold text-gray-900">Tingkat Koleksi per Semester</h2>
            <p class="text-fluid-xs text-gray-500 mt-0.5">Tahun akademik {{ $semesterCompare['tahun_akademik'] }}</p>
            <div class="mt-4 space-y-4">
                @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $key => $label)
                    @php $s = $semesterCompare[$key]; @endphp
                    <div>
                        <div class="flex justify-between text-fluid-sm mb-1">
                            <span class="font-medium text-gray-800">Semester {{ $label }}</span>
                            <span class="text-gray-600">{{ $s['tingkat'] }}% · {{ number_format($s['total_lunas']) }}/{{ number_format($s['total_tagihan']) }} lunas</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ min(100, $s['tingkat']) }}%"></div>
                        </div>
                        <div class="text-fluid-xs text-gray-500 mt-1">Terbayar: {{ $fmt($s['terbayar']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-5 md:p-6">
            <h2 class="text-fluid-lg font-bold text-gray-900">Penerimaan 6 Bulan Terakhir</h2>
            <p class="text-fluid-xs text-gray-500 mt-0.5">Total uang masuk per bulan</p>
            <div class="mt-4 h-56 sm:h-64">
                <canvas id="chartPenerimaan" aria-label="Grafik penerimaan bulanan"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="bg-white p-2.5 sm:p-3 rounded-lg shadow-sm shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-fluid-xl sm:text-fluid-2xl font-bold text-gray-900">Pembayaran Terbaru</h2>
                    <p class="text-fluid-xs text-gray-500 mt-0.5">10 transaksi terakhir yang tercatat di sistem</p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5 md:p-6">
            <div class="table-responsive rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200" aria-label="Tabel pembayaran terbaru">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Mahasiswa</th>
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis Tagihan</th>
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jumlah Bayar</th>
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-center text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-fluid-sm sm:text-sm">
                        @forelse ($recentPayments as $p)
                            @php
                                $tagihan = $p->tagihan;
                                $mhs = $tagihan?->mahasiswa;
                                $user = $mhs?->user;
                                $jenisLabel = $tagihan?->jenis_tampilan ?? ($tagihan?->tarif?->nama_pembayaran ?? '—');
                                $statusText = $tagihan ? $tagihan->displayStatusLabel() : '—';
                                $badgeClass = $tagihan ? $tagihan->displayStatusBadgeTailwindClasses() : 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $user?->nama_lengkap ?? '—' }}</div>
                                    <div class="text-gray-500">{{ $mhs?->npm ?? '—' }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-700">{{ $jenisLabel }}</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right">
                                    <div class="font-medium text-gray-800">{{ $fmt($p->jumlah_bayar) }}</div>
                                    @if ($tagihan && (int) round($tagihan->jumlah_tagihan) !== (int) round($p->jumlah_bayar))
                                        <div class="text-fluid-xs text-gray-400 mt-0.5">Tagihan: {{ $fmt($tagihan->jumlah_tagihan) }}</div>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-500">
                                    {{ $p->tanggal_bayar ? $p->tanggal_bayar->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada riwayat transaksi pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('chartPenerimaan');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = @json(collect($monthlyPenerimaan)->pluck('label'));
    const values = @json(collect($monthlyPenerimaan)->pluck('total'));

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Penerimaan (Rp)',
                data: values,
                backgroundColor: 'rgba(79, 70, 229, 0.75)',
                borderColor: 'rgb(79, 70, 229)',
                borderWidth: 1,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' Rp ' + Number(ctx.raw || 0).toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID'),
                    },
                },
            },
        },
    });
});
</script>
@endpush
