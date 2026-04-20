@extends('layouts.app')

@section('title', 'Admin - Registrasi')
@section('page-title', 'Registrasi pengguna')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-card title="Pintu registrasi" subtitle="Mahasiswa dan kasir didaftarkan lewat alur resmi di bawah (bukan form terpisah di halaman ini).">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.create-mahasiswa') }}" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-white to-primary-50/40 p-5 shadow-sm hover:shadow-md hover:border-primary-200 transition">
                <h3 class="text-lg font-semibold text-gray-900">Mahasiswa</h3>
                <p class="mt-2 text-sm text-gray-600">Form lengkap, validasi, dan penyimpanan ke database.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-primary-700">Buka form tambah mahasiswa →</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="block rounded-xl border border-gray-200 bg-gradient-to-br from-white to-success-50/40 p-5 shadow-sm hover:shadow-md hover:border-success-200 transition">
                <h3 class="text-lg font-semibold text-gray-900">Kasir</h3>
                <p class="mt-2 text-sm text-gray-600">Daftar pengguna; dari situ Anda dapat menambah kasir lewat API yang sama dengan aplikasi.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-success-700">Buka manajemen user →</span>
            </a>
        </div>
        <p class="mt-6 text-xs text-gray-500">URL halaman ini: <code class="text-gray-700">{{ url('/admin/registrasi') }}</code> — tidak ditampilkan di sidebar; gunakan tautan di atas.</p>
    </x-card>
</div>
@endsection
