@extends('layouts.app')

@section('title', 'Daftar Wisuda')
@section('page-title', 'Daftar Wisuda')

@section('content')
@php
    $state = $status['state'] ?? 'not_registered';
    $eligible = (bool) ($status['eligible'] ?? false);
    $tagihan = $status['active_tagihan'] ?? null;
    $tarifPreview = $status['tarif_preview'] ?? null;
    $dueInDays = (int) ($status['due_in_days'] ?? 30);
    $skripsiLabel = $status['skripsi_label'] ?? 'Skripsi';
    $registration = $status['registration'] ?? null;
@endphp

<div class="space-y-4 sm:space-y-6">
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('mahasiswa.dashboard')],
        ['label' => 'Daftar Wisuda']
    ]" />

    @if (session('success'))
        <div class="flex items-center p-4 text-sm text-green-800 rounded-lg bg-green-100 border-l-4 border-green-500" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center p-4 text-sm text-red-800 rounded-lg bg-red-100 border-l-4 border-red-500" role="alert">
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <x-card title="Pendaftaran Wisuda" subtitle="Formulir pendaftaran wisuda dan pembuatan tagihan Uang Wisuda">
        <div class="space-y-4 text-sm text-gray-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">NPM</p>
                    <p class="font-medium">{{ $mahasiswa->npm }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Program Studi</p>
                    <p class="font-medium">{{ $mahasiswa->program_studi }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Semester Aktif</p>
                    <p class="font-medium">{{ $mahasiswa->semester_aktif }}</p>
                </div>
                @if ($status['min_semester'])
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Minimal Semester</p>
                        <p class="font-medium">{{ $status['min_semester'] }}</p>
                    </div>
                @endif
            </div>

            @if ($state === 'paid')
                <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                    <p class="font-semibold">Anda sudah terdaftar wisuda.</p>
                    <p class="mt-1">Pembayaran Uang Wisuda telah lunas
                        @if ($registration?->registered_at)
                            (terdaftar {{ $registration->registered_at->format('d M Y H:i') }}).
                        @else
                            .
                        @endif
                    </p>
                </div>
            @elseif ($state === 'registered_unpaid' && $tagihan)
                <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                    <p class="font-semibold">Anda sudah terdaftar wisuda.</p>
                    <p class="mt-1">Tagihan Uang Wisuda masih aktif. Jatuh tempo: {{ $tagihan->tanggal_jatuh_tempo?->format('d M Y') }}.</p>
                    <p class="mt-2">Nominal: <strong>Rp {{ number_format($tagihan->jumlah_tagihan, 0, ',', '.') }}</strong></p>
                    <a href="{{ route('mahasiswa.pembayaran.show', $tagihan->tagihan_id) }}"
                       class="inline-flex items-center mt-3 px-4 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700 shadow-sm">
                        Bayar Sekarang
                    </a>
                </div>
            @elseif (!$eligible)
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                    <p class="font-semibold">Belum memenuhi syarat</p>
                    <p class="mt-1">{{ $status['ineligible_reason'] }}</p>
                </div>
            @elseif (!$tarifPreview)
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                    <p class="font-semibold">Tarif belum tersedia</p>
                    <p class="mt-1">Tarif Uang Wisuda untuk angkatan/program studi Anda belum dikonfigurasi. Silakan hubungi admin keuangan.</p>
                </div>
            @else
                <div class="p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-900">
                    <p>Setelah mendaftar, sistem akan membuat tagihan <strong>{{ $tarifPreview['nama'] }}</strong>
                        sebesar <strong>Rp {{ number_format($tarifPreview['nominal'], 0, ',', '.') }}</strong>.</p>
                    <p class="mt-1">Jatuh tempo pembayaran: <strong>H+{{ $dueInDays }}</strong> setelah pendaftaran.</p>
                </div>

                <form action="{{ route('mahasiswa.wisuda.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                               name="confirmed_skripsi"
                               value="1"
                               class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                               {{ old('confirmed_skripsi') ? 'checked' : '' }}
                               required>
                        <span>
                            Saya menyatakan telah menyelesaikan <strong>{{ $skripsiLabel }}</strong> dan siap mengikuti wisuda.
                        </span>
                    </label>
                    @error('confirmed_skripsi')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-white bg-success-600 hover:bg-success-700 shadow-sm">
                        Daftar Wisuda
                    </button>
                </form>
            @endif

            <p class="text-xs text-gray-500">Pendaftaran wisuda tidak terikat semester aktivasi. Setelah submit, tagihan langsung muncul di menu Pembayaran.</p>
        </div>
    </x-card>
</div>
@endsection
