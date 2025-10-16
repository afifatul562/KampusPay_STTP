@extends('layouts.app')

@section('title', 'Portal Mahasiswa')
@section('page-title', 'Overview')

@section('content')
    {{-- Panggil menu navigasi di sini, di bawah judul utama --}}
    @include('layouts.partials.mahasiswa-nav')

    {{-- Baru kemudian sisa kontennya --}}
    @if($tagihanJatuhTempo > 0)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
        <p class="font-bold">Perhatian</p>
        <p>Anda memiliki {{ $tagihanJatuhTempo }} pembayaran yang sudah melewati jatuh tempo!</p>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Total Tunggakan</div>
            <div class="text-3xl font-bold text-red-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $jumlahTunggakan }} tagihan aktif</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Total Terbayar</div>
            <div class="text-3xl font-bold text-green-600">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">Semester ini dan sebelumnya</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-sm text-gray-500">Semester Aktif</div>
            <div class="text-3xl font-bold text-gray-900">{{ $mahasiswa->semester_aktif }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $mahasiswa->program_studi }}</div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Tagihan Aktif</h3>
        <div class="space-y-4">
            @forelse ($tagihanAktif as $tagihan)
            <div class="border rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="md:col-span-2 flex items-center gap-4">
                    <span class="text-4xl opacity-75">📄</span>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                             <h4 class="font-bold text-lg">{{ $tagihan->tarif->nama_pembayaran }}</h4>
                             <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-800">Belum Lunas</span>
                        </div>
                        <p class="text-sm text-gray-600">Jatuh Tempo:
                            <span class="font-semibold {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d F Y') }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-col md:items-end">
                    <p class="text-sm text-gray-500">Jumlah:</p>
                    <p class="text-xl font-bold text-orange-500 mb-3">Rp. {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</p>
                    <a href="{{ route('mahasiswa.pembayaran.show', $tagihan->tagihan_id) }}" class="inline-block px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600 text-center">
                        Bayar Sekarang
                    </a>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-gray-500">
                <p>✅ Selamat! Tidak ada tagihan aktif yang perlu dibayar.</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection

