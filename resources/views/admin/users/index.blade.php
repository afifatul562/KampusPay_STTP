{{-- Menggunakan layout app.blade.php Anda --}}
@extends('layouts.app')

@section('title', 'Manajemen User')

{{-- 1. PASTIKAN SEMUA LINK CSS MENGGUNAKAN "https://" --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
@endpush


@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Manajemen User</h1>
    </div>

    {{-- Pesan Sukses (Versi Tailwind) --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Pesan Error (Versi Tailwind) --}}
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h6 class="font-semibold text-lg text-gray-800">Daftar Staff (Kasir / Admin)</h6>
        </div>
        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="dataTableStaff">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($staff as $index => $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->nama_lengkap }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($user->role == 'admin')
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $user->role }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $user->role }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-block px-3 py-1 text-sm font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition duration-150" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Anda yakin ingin menghapus user ini ({{ $user->nama_lengkap }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-block px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition duration-150" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Belum ada data staff (Kasir/Admin).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h6 class="font-semibold text-lg text-gray-800">Daftar Mahasiswa</h6>
        </div>
        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="dataTableMahasiswa">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($mahasiswa as $index => $user)
                             <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->nama_lengkap }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-block px-3 py-1 text-sm font-medium text-white bg-yellow-500 rounded-md hover:bg-yellow-600 transition duration-150" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Anda yakin ingin menghapus user ini ({{ $user->nama_lengkap }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-block px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition duration-150" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                             <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Belum ada data mahasiswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
{{-- PENTING: DataTables butuh jQuery, tambahkan jika layout Anda belum punya --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.min.js"></script>

<script>
    $(document).ready(function() {

        // Opsi Bahasa Indonesia
        // 2. PASTIKAN URL INI MENGGUNAKAN "https://"
        var indonesianLanguage = {
            "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json"
        };

        // Inisialisasi Tabel Staff dengan style Tailwind
        $('#dataTableStaff').DataTable({
            "language": indonesianLanguage
        });

        // Inisialisasi Tabel Mahasiswa dengan style Tailwind
        $('#dataTableMahasiswa').DataTable({
            "language": indonesianLanguage
        });
    });
</script>
@endpush
