@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
    @include('layouts.partials.mahasiswa-nav')

    <div class="bg-white p-6 rounded-lg shadow-md">
        <p class="text-gray-600 mb-4">Daftar semua pembayaran yang telah berhasil diselesaikan.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diverifikasi Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($riwayat as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ optional($item->pembayaran)->created_at->format('d M Y, H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item->tarif->nama_pembayaran ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                Rp {{ number_format($item->jumlah_tagihan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ optional($item->pembayaran)->metode_pembayaran ?? 'N/A' }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ optional(optional($item->pembayaran)->verifier)->nama_lengkap ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($item->pembayaran)
                                    <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}"
                                       class="inline-block px-3 py-1 rounded-md bg-purple-500 text-white text-xs font-semibold hover:bg-purple-600 no-underline">
                                       Download Kwitansi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Belum ada riwayat pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $riwayat->links() }}
        </div>
    </div>
@endsection

