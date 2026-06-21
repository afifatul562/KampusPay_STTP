@extends('layouts.app')

@section('title', 'Portal Mahasiswa')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-4 sm:space-y-6">
        {{-- Notifikasi Jatuh Tempo --}}
        @if ($tagihanJatuhTempo > 0)
            <div class="flex items-start sm:items-center gap-3 p-4 mb-2 sm:mb-4 text-fluid-sm text-yellow-800 rounded-lg bg-yellow-100 border-l-4 border-yellow-500"
                role="alert">
                <svg class="flex-shrink-0 w-5 h-5 sm:w-6 sm:h-6 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 5a1 1 0 011 1v3a1 1 0 01-2 0V6a1 1 0 011-1zm0 8a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <span class="font-medium">Perhatian!</span> Anda memiliki {{ $tagihanJatuhTempo }} pembayaran yang sudah
                    melewati jatuh tempo.
                </div>
            </div>
        @endif

        {{-- Tagihan mendekati jatuh tempo (≤ 7 hari) --}}
        @if (($jumlahMendekati ?? 0) > 0)
            <div class="flex items-start sm:items-center gap-3 p-4 mb-2 sm:mb-4 text-fluid-sm text-amber-900 rounded-lg bg-amber-50 border-l-4 border-amber-500"
                role="alert">
                <svg class="flex-shrink-0 w-5 h-5 sm:w-6 sm:h-6 mt-0.5 sm:mt-0 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 5a1 1 0 011 1v3a1 1 0 01-2 0V6a1 1 0 011-1zm0 8a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <span class="font-medium">Perhatian!</span>
                    Anda memiliki {{ $jumlahMendekati }} tagihan yang jatuh tempo dalam 7 hari ke depan
                    (total Rp {{ number_format($totalMendekati ?? 0, 0, ',', '.') }}).
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" class="font-semibold underline hover:text-amber-950">Lihat tagihan →</a>
                </div>
            </div>
        @endif

        <!-- Kartu Statistik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Kartu Total Tunggakan (Dijadikan Link) --}}
            <a href="{{ route('mahasiswa.pembayaran.index') }}"
                class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl active:scale-[0.99] sm:hover:scale-[1.02] transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-danger-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
                <div class="flex-1 min-w-0 relative z-10">
                    <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total Tunggakan</div>
                    <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-red-600 mt-1 truncate">Rp
                        {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
                    <div class="text-fluid-xs text-gray-400 mt-1">{{ $jumlahTunggakan }} tagihan aktif</div>
                </div>
                <div
                    class="relative z-10 bg-gradient-to-br from-danger-400 to-danger-600 p-3 sm:p-4 rounded-xl shadow-lg group-hover:shadow-xl sm:group-hover:scale-110 transition-all duration-300 shrink-0">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                </div>
            </a>

            {{-- Kartu Total Pembayaran (Dijadikan Link) --}}
            <a href="{{ route('mahasiswa.riwayat.index') }}"
                class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl active:scale-[0.99] sm:hover:scale-[1.02] transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-success-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
                <div class="flex-1 min-w-0 relative z-10">
                    <div class="text-fluid-sm font-medium text-gray-500 mb-1">Total Pembayaran</div>
                    <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-green-600 mt-1 truncate">Rp
                        {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                    <div class="text-fluid-xs text-gray-400 mt-1">Seluruh riwayat</div>
                </div>
                <div
                    class="relative z-10 bg-gradient-to-br from-success-400 to-success-600 p-3 sm:p-4 rounded-xl shadow-lg group-hover:shadow-xl sm:group-hover:scale-110 transition-all duration-300 shrink-0">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </a>

            <div
                class="group relative bg-gradient-to-br from-white to-gray-50 p-4 sm:p-5 md:p-6 rounded-xl shadow-md border border-gray-200 flex items-start gap-4 sm:gap-5 hover:shadow-xl transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
                <div class="flex-1 min-w-0 relative z-10">
                    <div class="text-fluid-sm font-medium text-gray-500 mb-1">Semester Aktif</div>
                    <div class="text-fluid-2xl lg:text-fluid-3xl font-bold text-gray-900 mt-1 truncate">
                        {{ $mahasiswa->semester_aktif }}</div>
                    <div class="text-fluid-xs text-gray-400 mt-1">{{ $mahasiswa->program_studi }}</div>
                </div>
                <div
                    class="relative z-10 bg-gradient-to-br from-primary-400 to-primary-600 p-3 sm:p-4 rounded-xl shadow-lg sm:group-hover:scale-110 transition-all duration-300 shrink-0">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Daftar Tagihan Aktif -->
        <div class="mt-4 sm:mt-6 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            {{-- Header Section --}}
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="bg-white p-2.5 sm:p-3 rounded-lg shadow-sm shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-fluid-xl sm:text-fluid-2xl font-bold text-gray-900">Tagihan Aktif</h2>
                        <p class="text-fluid-sm text-gray-500 mt-1">Daftar tagihan yang perlu segera dibayar</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($tagihanAktif as $tagihan)
                    @php
                        $borderClass = match ($tagihan->status) {
                            'Ditolak' => 'border-l-4 border-orange-500',
                            'Menunggu Pembayaran Tunai' => 'border-l-4 border-blue-500 bg-blue-50/30',
                            'Menunggu Verifikasi Transfer' => 'border-l-4 border-yellow-500',
                            default => '',
                        };
                    @endphp
                    <div
                        class="p-4 sm:p-5 md:p-6 grid grid-cols-1 md:grid-cols-6 gap-4 items-stretch md:items-center hover:bg-gray-50 transition-colors {{ $borderClass }}">
                        <div class="md:col-span-3 flex items-center gap-4">
                            <div class="hidden sm:block">
                                <div class="bg-gray-100 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-gray-800">{{ $tagihan->jenis_tampilan }}</h4>
                                    @if ($tagihan->status == 'Ditolak')
                                        <span
                                            class="text-xs font-semibold px-2 py-1 rounded-full bg-orange-100 text-orange-800">
                                            Ditolak
                                        </span>
                                    @elseif ($tagihan->status == 'Menunggu Pembayaran Tunai')
                                        <span
                                            class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                                </path>
                                            </svg>
                                            Bayar di Kasir
                                        </span>
                                    @endif
                                </div>
                                <p class="text-fluid-sm text-gray-600">Jatuh Tempo:
                                    <span
                                        class="font-medium {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1 font-mono">Kode: {{ $tagihan->kode_pembayaran }}</p>
                                @if ($tagihan->status == 'Ditolak' && $tagihan->konfirmasi && $tagihan->konfirmasi->alasan_ditolak)
                                    <div
                                        class="mt-2 p-2 bg-orange-50 border-l-4 border-orange-400 text-orange-700 rounded text-xs">
                                        <p class="font-semibold">Alasan Ditolak:</p>
                                        <p>{{ $tagihan->konfirmasi->alasan_ditolak }}</p>
                                    </div>
                                @elseif ($tagihan->status == 'Menunggu Pembayaran Tunai')
                                    <div
                                        class="mt-2 p-2 bg-blue-50 border-l-4 border-blue-400 text-blue-800 rounded text-xs">
                                        <p class="font-semibold">Pembayaran Tunai di Kasir</p>
                                        <p>Anda sudah memilih bayar lunas tunai. Silakan datang ke kasir untuk melunasi tagihan ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="md:col-span-2 text-left md:text-right">
                            @php
                                $sisaTagihan = $tagihan->nominalTunggakan();
                            @endphp
                            <p class="text-fluid-sm text-gray-500">Jumlah Tagihan</p>
                            <p class="text-fluid-lg font-bold text-orange-500">Rp.
                                {{ number_format($sisaTagihan, 0, ',', '.') }}</p>
                        </div>
                        <div class="md:col-span-1 text-left md:text-right">
                            @if ($tagihan->status == 'Menunggu Pembayaran Tunai')
                                <span
                                    class="inline-flex items-center justify-center min-h-touch w-full md:w-auto px-4 py-2.5 rounded-lg bg-blue-100 text-blue-700 text-fluid-sm font-semibold cursor-default">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                    Menunggu Tunai
                                </span>
                            @else
                                <a href="{{ route('mahasiswa.pembayaran.pilih-metode', $tagihan->tagihan_id) }}"
                                    class="inline-flex items-center justify-center min-h-touch w-full md:w-auto px-4 py-2.5 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 text-white text-fluid-sm font-semibold hover:from-primary-700 hover:to-primary-800 text-center shadow-md hover:shadow-lg transition-all duration-200 active:scale-[0.98]">
                                    {{ $tagihan->status == 'Ditolak' ? 'Bayar Ulang' : 'Bayar' }}
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    @php
                        $emptyIcon =
                            '<svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    @endphp
                    <x-empty-state title="Tidak ada tagihan aktif"
                        message="Selamat! Tidak ada tagihan aktif yang perlu dibayar." :icon="$emptyIcon" />
                @endforelse
            </div>
        </div>
    </div>
@endsection
