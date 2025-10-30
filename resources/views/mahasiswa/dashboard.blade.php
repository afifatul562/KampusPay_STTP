@extends('layouts.app')

@section('title', 'Portal Mahasiswa')
@section('page-title', 'Overview Dashboard')

@section('content')
    {{-- Notifikasi Jatuh Tempo --}}
    @if($tagihanJatuhTempo > 0)
    <div class="flex items-center p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-100" role="alert">
        <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 5a1 1 0 011 1v3a1 1 0 01-2 0V6a1 1 0 011-1zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
        <div>
            <span class="font-medium">Perhatian!</span> Anda memiliki {{ $tagihanJatuhTempo }} pembayaran yang sudah melewati jatuh tempo.
        </div>
    </div>
    @endif

    <!-- Kartu Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Total Tunggakan</div>
                <div class="text-2xl font-bold text-red-600 mt-1 truncate">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $jumlahTunggakan }} tagihan aktif</div>
            </div>
            <div class="bg-red-100 p-3 rounded-full"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Total Pembayaran</div>
                <div class="text-2xl font-bold text-green-600 mt-1 truncate">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
                 <div class="text-xs text-gray-400 mt-1">Seluruh riwayat</div>
            </div>
            <div class="bg-green-100 p-3 rounded-full"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-500">Semester Aktif</div>
                <div class="text-2xl font-bold text-gray-900 mt-1 truncate">{{ $mahasiswa->semester_aktif }}</div>
                 <div class="text-xs text-gray-400 mt-1">{{ $mahasiswa->program_studi }}</div>
            </div>
            <div class="bg-blue-100 p-3 rounded-full"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
        </div>
    </div>

    <!-- Daftar Tagihan Aktif -->
    <div class="bg-white rounded-2xl shadow-lg">
         <div class="p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-800">Tagihan Aktif</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse ($tagihanAktif as $tagihan)
            <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-4 items-center hover:bg-gray-50 transition-colors">
                <div class="md:col-span-3 flex items-center gap-4">
                    <div class="hidden sm:block">
                        <div class="bg-gray-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $tagihan->tarif->nama_pembayaran }}</h4>
                        <p class="text-sm text-gray-600">Jatuh Tempo:
                            <span class="font-medium {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="md:col-span-3 text-left md:text-right">
                    <p class="text-sm text-gray-500">Jumlah Tagihan</p>
                    <p class="text-lg font-bold text-orange-500">Rp. {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</p>
                </div>
                {{-- <div class="md:col-span-1 text-left md:text-right">
                     <a href="{{ route('mahasiswa.pembayaran.show', $tagihan->tagihan_id) }}" class="inline-block w-full md:w-auto px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 text-center">
                        Bayar
                    </a>
                </div> --}}
            </div>
            @empty
            <div class="text-center py-10 text-gray-500">
                <div class="inline-block bg-green-100 p-4 rounded-full mb-2">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="font-medium">Selamat! Tidak ada tagihan aktif yang perlu dibayar.</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection
