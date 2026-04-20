@extends('layouts.app')

@section('title', 'Admin - Import Data')
@section('page-title', 'Import Data')

@section('content')
@php
    $headerIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
    </svg>';
@endphp

<div class="space-y-4 sm:space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Import Data']
    ]" />

    <x-page-header
        title="Import Data"
        subtitle="Impor data dari sistem lama (CSV) agar histori tagihan & pembayaran tersinkron."
        :icon="$headerIcon">
    </x-page-header>

    <div class="text-xs text-gray-500">
        Template CSV ada di folder <code class="text-gray-700">docs/import-templates/</code>.
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <x-card title="Import Tagihan (CSV)" subtitle="Wajib: kode_pembayaran, npm, nama_pembayaran. Disarankan: tanggal_jatuh_tempo, status, semester_label.">
            <form action="{{ route('admin.import.tagihan') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="tagihan_csv">File CSV</label>
                    <input id="tagihan_csv" name="file_csv" type="file" accept=".csv,.txt" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    <p class="mt-2 text-xs text-gray-500">Gunakan template: <code class="text-gray-700">docs/import-templates/tagihan_template.csv</code></p>
                </div>
                <button type="submit" class="inline-flex items-center justify-center min-h-touch px-4 py-2.5 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 text-white text-sm font-semibold hover:from-primary-700 hover:to-primary-800 shadow-md hover:shadow-lg transition-all duration-200">
                    Upload & Import Tagihan
                </button>
            </form>
        </x-card>

        <x-card title="Import Pembayaran/Histori (CSV)" subtitle="Wajib: kode_pembayaran, tanggal_bayar, metode_pembayaran, jumlah_bayar. Cicilan boleh beberapa baris untuk 1 kode_pembayaran.">
            <form action="{{ route('admin.import.pembayaran') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="pembayaran_csv">File CSV</label>
                    <input id="pembayaran_csv" name="file_csv" type="file" accept=".csv,.txt" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    <p class="mt-2 text-xs text-gray-500">Gunakan template: <code class="text-gray-700">docs/import-templates/pembayaran_template.csv</code></p>
                </div>
                <button type="submit" class="inline-flex items-center justify-center min-h-touch px-4 py-2.5 rounded-lg bg-gradient-to-r from-success-600 to-success-700 text-white text-sm font-semibold hover:from-success-700 hover:to-success-800 shadow-md hover:shadow-lg transition-all duration-200">
                    Upload & Import Pembayaran
                </button>
            </form>
        </x-card>
    </div>

    <x-card title="Urutan yang disarankan" subtitle="Agar data saling terhubung dan histori terbaca.">
        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
            <li>Import Mahasiswa (menu Mahasiswa → Import CSV)</li>
            <li>Import Tagihan (di halaman ini)</li>
            <li>Import Pembayaran/Histori (di halaman ini)</li>
        </ol>
        <p class="mt-3 text-xs text-gray-500">Jika ada error per baris, sistem akan menampilkan detailnya dan transaksi dibatalkan agar data tidak setengah masuk.</p>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function showNotification(type, title, message, errors = null) {
        let attempts = 0;
        const maxAttempts = 10;

        function attemptShow() {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                if (errors && errors.length > 0) {
                    const mainMessageEl = document.createElement('p');
                    mainMessageEl.textContent = message;
                    const finalHtmlContent = document.createElement('div');
                    finalHtmlContent.appendChild(mainMessageEl);
                    const errorList = document.createElement('ul');
                    errorList.className = 'list-disc list-inside text-left text-sm mt-2 max-h-52 overflow-y-auto';
                    errors.forEach(errText => {
                        const li = document.createElement('li');
                        li.textContent = errText;
                        errorList.appendChild(li);
                    });
                    finalHtmlContent.appendChild(errorList);
                    Swal.fire({ icon: type, title: title, html: finalHtmlContent });
                } else {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        timer: type === 'success' ? 2200 : undefined,
                        showConfirmButton: type !== 'success'
                    });
                }
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(attemptShow, 100);
            } else {
                alert(title + "\n\n" + message);
            }
        }
        attemptShow();
    }

    @if (session('success'))
        showNotification('success', 'Berhasil!', @json(session('success')));
    @endif

    @if (session('error'))
        @php
            $mainErrorMessage = session('error');
            $importErrors = session('import_errors', []);
        @endphp
        @if (!empty($importErrors) && is_array($importErrors) && count($importErrors) > 0)
            showNotification('error', 'Gagal!', @json($mainErrorMessage), @json($importErrors));
        @else
            showNotification('error', 'Gagal!', @json($mainErrorMessage));
        @endif
    @endif
});
</script>
@endpush

