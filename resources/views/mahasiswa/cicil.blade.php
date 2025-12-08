@extends('layouts.app')

@section('title', 'Bayar Cicilan')
@section('page-title', 'Bayar Cicilan')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Kolom Kiri: Instruksi & Detail --}}
        <div class="space-y-6">
            {{-- Instruksi Pembayaran --}}
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h3 class="text-lg font-semibold mb-4 border-b pb-3 flex items-center gap-2 text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    1. Pilih Metode Pembayaran
                </h3>
                <p class="text-sm text-gray-600 mb-4">Anda dapat membayar cicilan sesuai kemampuan. Pilih metode pembayaran di kolom kanan.</p>

                @php
                    $settings = \App\Models\Setting::getCachedMap();
                @endphp

                <div x-data="{ accountNumber: '{{ $settings['account_number'] ?? 'N/A' }}' }" class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Bank</span>
                        <span class="font-semibold text-gray-800">{{ $settings['bank_name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Atas Nama</span>
                        <span class="font-semibold text-gray-800">{{ $settings['account_holder'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">No. Rekening</span>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800" x-text="accountNumber"></span>
                            <button @click="navigator.clipboard.writeText(accountNumber); $el.innerHTML = 'Tersalin!'"
                                    class="text-blue-600 hover:text-blue-800" title="Salin nomor rekening">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Tagihan --}}
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                 <h3 class="text-lg font-semibold mb-4 border-b pb-3 flex items-center gap-2 text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Detail Tagihan
                </h3>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis Pembayaran</span>
                        <span class="font-semibold text-gray-800">{{ $tagihan->tarif->nama_pembayaran }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jatuh Tempo</span>
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->isoFormat('D MMMM YYYY') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kode Pembayaran</span>
                        <span class="font-mono text-gray-800 bg-gray-100 px-2 py-1 rounded">{{ $tagihan->kode_pembayaran }}</span>
                    </div>
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Tagihan</span>
                            <span class="font-semibold text-gray-800">Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</span>
                        </div>
                        @if($tagihan->total_angsuran > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sudah Dibayar</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($tagihan->total_angsuran, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="text-gray-500 font-semibold">Sisa Pokok</span>
                            <span class="font-bold text-2xl text-orange-500">Rp {{ number_format($sisaPokok, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Cicilan --}}
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold mb-4 border-b pb-3 flex items-center gap-2 text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                2. Form Pembayaran Cicilan
            </h3>

            <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p class="text-sm text-purple-800">
                    <strong>Info:</strong> Anda dapat membayar cicilan sesuai kemampuan. Minimal pembayaran adalah Rp 50.000 (kecuali sisa pokok di bawah Rp 50.000).
                </p>
            </div>

            <form action="{{ route('mahasiswa.pembayaran.cicil.transfer', $tagihan->tagihan_id) }}" method="POST" enctype="multipart/form-data" aria-label="Form cicilan pembayaran">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Error</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Input Jumlah Bayar --}}
                <div class="mb-6">
                    <label for="jumlah_bayar" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                        @php $minBayar = min(50000, $sisaPokok); @endphp
                        <input type="number"
                               name="jumlah_bayar"
                               id="jumlah_bayar"
                               value="{{ old('jumlah_bayar') }}"
                               min="{{ $minBayar }}"
                               max="{{ $sisaPokok }}"
                               step="1000"
                               required
                               class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Masukkan jumlah pembayaran"
                               oninput="formatCurrency(this)">
                        <div class="mt-2 text-xs text-gray-500">
                            Minimal: Rp {{ number_format($minBayar, 0, ',', '.') }} | Maksimal: Rp {{ number_format($sisaPokok, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- Area Upload File Interaktif --}}
                <div x-data="fileUpload()" class="mb-6">
                    <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Bukti Transfer <span class="text-red-500">*</span>
                    </label>
                    <div @dragover.prevent @drop.prevent="dropHandler"
                         class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                         :class="{ 'border-blue-500 bg-blue-50': dragging }">
                        <div class="space-y-1 text-center">
                            <template x-if="filePreview">
                                <div class="relative">
                                    <img :src="filePreview" class="mx-auto max-h-40 rounded-md">
                                    <button @click="removeFile" type="button" aria-label="Hapus file" class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 leading-none">&times;</button>
                                </div>
                            </template>
                            <template x-if="!filePreview">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </template>
                            <div class="flex text-sm text-gray-600">
                                <label for="bukti_pembayaran" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                    <span>Upload file</span>
                                    <input @change="fileChosen" type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="sr-only" required accept="image/jpeg, image/jpg, image/png" aria-describedby="upload-help">
                                </label>
                                <p class="pl-1" x-show="!fileName"></p>
                                <p class="pl-1" x-show="fileName" x-text="fileName"></p>
                            </div>
                            <p id="upload-help" class="text-xs text-gray-500" x-show="!fileName">PNG, JPG hingga 2MB</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 shadow-md hover:shadow-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Kirim Konfirmasi Cicilan
                    </button>
                    <a href="{{ route('mahasiswa.pembayaran.index') }}" class="block text-center mt-3 text-sm text-gray-600 hover:text-purple-600 transition-colors duration-200">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function fileUpload() {
        return {
            dragging: false,
            fileName: '',
            filePreview: null,
            fileChosen(event) {
                this.fileToDataUrl(event.target.files[0]);
            },
            dropHandler(event) {
                this.dragging = false;
                this.fileToDataUrl(event.dataTransfer.files[0]);
            },
            fileToDataUrl(file) {
                if (!file || !file.type.startsWith('image/')) return;
                let reader = new FileReader();
                reader.onload = (e) => {
                    this.filePreview = e.target.result;
                    this.fileName = file.name;
                };
                reader.readAsDataURL(file);
            },
            removeFile() {
                this.fileName = '';
                this.filePreview = null;
                document.getElementById('bukti_pembayaran').value = '';
            }
        }
    }

    function formatCurrency(input) {
        // Format angka dengan pemisah ribuan (opsional, hanya untuk display)
        let value = input.value.replace(/[^\d]/g, '');
        if (value) {
            input.value = parseInt(value);
        }
    }
</script>
@endpush

