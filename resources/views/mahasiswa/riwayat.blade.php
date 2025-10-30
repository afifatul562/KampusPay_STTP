@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg">
        <div class="p-6 border-b">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Riwayat Transaksi Lunas</h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar semua pembayaran yang telah berhasil diselesaikan.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pembayaran</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diverifikasi Oleh</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse ($riwayat as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ optional($item->pembayaran)->created_at->isoFormat('D MMM YYYY, HH:mm') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $item->tarif->nama_pembayaran ?? 'N/A' }}
                                <p class="text-xs text-gray-400 mt-1 font-mono">Kode: {{ $item->kode_pembayaran }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                                Rp {{ number_format($item->jumlah_tagihan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ optional($item->pembayaran)->metode_pembayaran ?? 'N/A' }}
                                </span>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ optional(optional($item->pembayaran)->verifier)->nama_lengkap ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($item->pembayaran)
                                    <a href="{{ route('mahasiswa.kwitansi.download', ['pembayaran' => $item->pembayaran->pembayaran_id]) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                       <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Kwitansi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                Belum ada riwayat pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
            <div class="p-6 border-t">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>
@endsection
