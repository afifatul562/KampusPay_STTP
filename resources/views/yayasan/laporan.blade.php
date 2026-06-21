@extends('layouts.app')

@section('title', 'Laporan — Yayasan')
@section('page-title', 'Laporan Keuangan')

@section('content')
@php
    $fmt = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $jenis = $filters['jenis_laporan'] ?? 'pembayaran';
    $periodeMode = $filters['periode_mode'] ?? 'akademik';
    $isKalender = $periodeMode === 'kalender';
@endphp

<div class="space-y-4 sm:space-y-6">
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 px-4 py-3 text-fluid-sm text-indigo-800">
        Lihat dan unduh laporan pembayaran atau tunggakan. Data tidak dapat diubah dari halaman ini.
    </div>

    <x-card title="Filter Laporan">
        <form method="POST" action="{{ route('yayasan.laporan.index') }}" class="space-y-4" id="yayasanReportForm">
            @csrf
            <div>
                <label for="jenis_laporan" class="block text-fluid-sm font-medium text-gray-700">Jenis Laporan</label>
                <select id="jenis_laporan" name="jenis_laporan" required class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="pembayaran" @selected($jenis === 'pembayaran')>Laporan Pembayaran</option>
                    <option value="tunggakan" @selected($jenis === 'tunggakan')>Laporan Tunggakan</option>
                </select>
                <p id="jenis_laporan_hint" class="text-fluid-xs text-gray-500 mt-1.5">
                    @if ($isKalender && $jenis === 'pembayaran')
                        Daftar transaksi uang masuk Jan–Des tahun kalender (berdasarkan tanggal bayar).
                    @elseif ($isKalender && $jenis === 'tunggakan')
                        Tagihan diterbitkan di tahun kalender tersebut yang masih menunggak sampai hari ini.
                    @elseif ($jenis === 'tunggakan')
                        Hanya tagihan yang masih menunggak; kolom nominal = <strong>sisa</strong> yang belum dibayar.
                    @else
                        Semua tagihan periode akademik (lunas + belum lunas); kolom nominal = total tagihan.
                    @endif
                </p>
            </div>

            <div>
                <span class="block text-fluid-sm font-medium text-gray-700 mb-2">Mode Periode</span>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="periode_mode" value="akademik" class="text-primary-600 focus:ring-primary-500" @checked($periodeMode === 'akademik')>
                        <span class="text-fluid-sm text-gray-700">Tahun Akademik</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="periode_mode" value="kalender" class="text-primary-600 focus:ring-primary-500" @checked($periodeMode === 'kalender')>
                        <span class="text-fluid-sm text-gray-700">Tahun Kalender (Jan–Des)</span>
                    </label>
                </div>
            </div>

            <div id="wrap_semester" @class(['hidden' => $isKalender])>
                <label for="semester" class="block text-fluid-sm font-medium text-gray-700">Semester <span class="text-gray-500 font-normal">(opsional)</span></label>
                <select id="semester" name="semester" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" @selected(($filters['semester'] ?? '') === '')>Semua semester</option>
                    <option value="Ganjil" @selected(($filters['semester'] ?? '') === 'Ganjil')>Ganjil</option>
                    <option value="Genap" @selected(($filters['semester'] ?? '') === 'Genap')>Genap</option>
                </select>
            </div>

            <div id="wrap_tahun_akademik" @class(['hidden' => $isKalender])>
                <label for="tahun_akademik" class="block text-fluid-sm font-medium text-gray-700">Tahun Akademik</label>
                <select id="tahun_akademik" name="tahun_akademik" @disabled($isKalender) class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay }}" @selected(($filters['tahun_akademik'] ?? $defaultTahunAkademik) === $ay)>{{ $ay }}</option>
                    @endforeach
                </select>
            </div>

            <div id="wrap_tahun_kalender" @class(['hidden' => ! $isKalender])>
                <label for="tahun_kalender" class="block text-fluid-sm font-medium text-gray-700">Tahun Kalender</label>
                <select id="tahun_kalender" name="tahun_kalender" @disabled(! $isKalender) class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    @foreach ($calendarYears as $y)
                        <option value="{{ $y }}" @selected((string) ($filters['tahun_kalender'] ?? $defaultTahunKalender) === (string) $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="angkatan" class="block text-fluid-sm font-medium text-gray-700">Angkatan <span class="text-gray-500 font-normal">(opsional)</span></label>
                <select id="angkatan" name="angkatan" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" @selected(($filters['angkatan'] ?? '') === '')>Semua angkatan</option>
                    @foreach ($masterAngkatan as $a)
                        <option value="{{ $a }}" @selected(($filters['angkatan'] ?? '') == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="program_studi" class="block text-fluid-sm font-medium text-gray-700">Program Studi <span class="text-gray-500 font-normal">(opsional)</span></label>
                <select id="program_studi" name="program_studi" class="mt-1 block w-full min-h-touch border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base sm:text-sm">
                    <option value="" @selected(($filters['program_studi'] ?? '') === '')>Semua program studi</option>
                    @foreach ($masterProdi as $p)
                        <option value="{{ $p }}" @selected(($filters['program_studi'] ?? '') == $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <x-gradient-button type="submit" variant="primary" size="md" class="w-full sm:w-auto">
                    Tampilkan Preview
                </x-gradient-button>
                @if ($previewData && $previewData->isNotEmpty())
                    <a href="{{ route('yayasan.laporan.pdf', array_filter([
                        'jenis_laporan' => $filters['jenis_laporan'] ?? null,
                        'periode_mode' => $filters['periode_mode'] ?? null,
                        'tahun_akademik' => $filters['tahun_akademik'] ?? null,
                        'tahun_kalender' => $filters['tahun_kalender'] ?? null,
                        'semester' => $filters['semester'] ?? null,
                        'angkatan' => $filters['angkatan'] ?? null,
                        'program_studi' => $filters['program_studi'] ?? null,
                    ])) }}"
                       class="inline-flex items-center justify-center min-h-touch px-4 py-2.5 rounded-lg bg-gradient-to-r from-success-600 to-success-700 text-white text-fluid-sm font-semibold hover:from-success-700 hover:to-success-800 shadow-md w-full sm:w-auto">
                        Unduh PDF
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    @if ($isKalender && $jenis === 'pembayaran' && !empty($summaryPembayaran))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Jumlah Transaksi</div>
                <div class="text-fluid-xl font-bold text-gray-900 mt-1">{{ number_format($summaryPembayaran['jumlah_transaksi']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Total Uang Masuk</div>
                <div class="text-fluid-lg font-bold text-green-700 mt-1">{{ $fmt($summaryPembayaran['total_uang_masuk']) }}</div>
            </div>
        </div>
    @endif

    @if ($jenis === 'tunggakan' && $summaryTunggakan)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Jumlah Tagihan Tunggak</div>
                <div class="text-fluid-xl font-bold text-gray-900 mt-1">{{ number_format($summaryTunggakan['jumlah_tagihan']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Total Sisa Tunggakan</div>
                <div class="text-fluid-lg font-bold text-red-700 mt-1">{{ $fmt($summaryTunggakan['total_sisa']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Belum Dibayarkan</div>
                <div class="text-fluid-xl font-bold text-amber-700 mt-1">{{ number_format($summaryTunggakan['belum_dibayar']) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="text-fluid-xs text-gray-500">Belum Lunas (cicilan)</div>
                <div class="text-fluid-xl font-bold text-orange-700 mt-1">{{ number_format($summaryTunggakan['belum_lunas']) }}</div>
            </div>
        </div>
    @endif

    @if ($previewData !== null)
        <x-card title="Preview Laporan">
            @if ($previewData->isEmpty())
                <p class="text-gray-500 text-center py-8">Tidak ada data untuk filter yang dipilih.</p>
            @elseif ($isKalender && $jenis === 'pembayaran')
                <div class="table-responsive rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 text-fluid-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tgl Bayar</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Tagihan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Bayar</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($previewData as $bayar)
                                @php $tagihan = $bayar->tagihan; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-600">{{ $bayar->tanggal_bayar?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ $tagihan?->mahasiswa?->user?->nama_lengkap ?? '—' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $tagihan?->mahasiswa?->npm ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $tagihan?->jenis_tampilan ?? $tagihan?->tarif?->nama_pembayaran ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-500 text-xs">{{ $tagihan?->semester_label ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right font-medium">{{ $fmt($bayar->jumlah_bayar ?? 0) }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ $bayar->metode_pembayaran ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($jenis === 'pembayaran')
                <div class="table-responsive rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 text-fluid-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Tagihan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tgl Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($previewData as $item)
                                @php
                                    $statusText = $item->displayStatusLabel();
                                    $badgeClass = $item->displayStatusBadgeTailwindClasses();
                                    $paid = $item->totalTerverifikasi();
                                    $sisa = max(0.0, (float) $item->nominalTunggakan());
                                    $pembayaranTerakhir = ($item->pembayaranAll ?? collect())
                                        ->where('status_dibatalkan', false)
                                        ->sortByDesc('tanggal_bayar')
                                        ->first();
                                    $tglBayar = $pembayaranTerakhir?->tanggal_bayar ?? $item->pembayaran?->tanggal_bayar;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $item->kode_pembayaran ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ $item->mahasiswa?->user?->nama_lengkap ?? '—' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $item->mahasiswa?->npm ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $item->jenis_tampilan ?? $item->tarif?->nama_pembayaran ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <div>{{ $fmt($item->jumlah_tagihan ?? 0) }}</div>
                                        @if ($paid > 0 && $statusText !== 'Lunas')
                                            <div class="text-xs text-gray-500">Sisa: {{ $fmt($sisa) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">{{ $tglBayar ? $tglBayar->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="table-responsive rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 text-fluid-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Tagihan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sisa</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($previewData as $item)
                                @php
                                    $sisa = max(0.0, (float) $item->nominalTunggakan());
                                    $statusText = $item->displayStatusLabel();
                                    $badgeClass = $item->displayStatusBadgeTailwindClasses();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ $item->mahasiswa?->user?->nama_lengkap ?? '—' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $item->mahasiswa?->npm ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $item->jenis_tampilan ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-red-700">{{ $fmt($sisa) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('jenis_laporan');
    const hint = document.getElementById('jenis_laporan_hint');
    const wrapSemester = document.getElementById('wrap_semester');
    const wrapAkademik = document.getElementById('wrap_tahun_akademik');
    const wrapKalender = document.getElementById('wrap_tahun_kalender');
    const selectAkademik = document.getElementById('tahun_akademik');
    const selectKalender = document.getElementById('tahun_kalender');
    const modeInputs = document.querySelectorAll('input[name="periode_mode"]');

    function isKalenderMode() {
        const checked = document.querySelector('input[name="periode_mode"]:checked');
        return checked && checked.value === 'kalender';
    }

    function updateHint() {
        if (!sel || !hint) return;
        const kalender = isKalenderMode();
        if (kalender && sel.value === 'pembayaran') {
            hint.textContent = 'Daftar transaksi uang masuk Jan–Des tahun kalender (berdasarkan tanggal bayar).';
        } else if (kalender && sel.value === 'tunggakan') {
            hint.textContent = 'Tagihan diterbitkan di tahun kalender tersebut yang masih menunggak sampai hari ini.';
        } else if (sel.value === 'tunggakan') {
            hint.innerHTML = 'Hanya tagihan yang masih menunggak; kolom nominal = <strong>sisa</strong> yang belum dibayar.';
        } else {
            hint.textContent = 'Semua tagihan periode akademik (lunas + belum lunas); kolom nominal = total tagihan.';
        }
    }

    function syncPeriodeFields() {
        const kalender = isKalenderMode();
        wrapSemester?.classList.toggle('hidden', kalender);
        wrapAkademik?.classList.toggle('hidden', kalender);
        wrapKalender?.classList.toggle('hidden', !kalender);
        if (selectAkademik) selectAkademik.disabled = kalender;
        if (selectKalender) selectKalender.disabled = !kalender;
        updateHint();
    }

    sel?.addEventListener('change', updateHint);
    modeInputs.forEach(el => el.addEventListener('change', syncPeriodeFields));
    syncPeriodeFields();
});
</script>
@endpush
