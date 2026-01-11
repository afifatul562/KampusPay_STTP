@extends('layouts.app')

@section('title', 'Admin - Edit Mahasiswa')
@section('page-title', 'Edit Mahasiswa')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">
            Edit Data: <span class="font-bold">{{ $mahasiswa->user->nama_lengkap }}</span>
        </h3>

        {{-- Tampilkan error validasi jika ada --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.mahasiswa.update', $mahasiswa->mahasiswa_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap"
                           value="{{ old('nama_lengkap', $mahasiswa->user->nama_lengkap) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $mahasiswa->user->email) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="npm" class="block text-sm font-medium text-gray-700">NPM</label>
                    <input type="text" id="npm" name="npm"
                           value="{{ old('npm', $mahasiswa->npm) }}" required maxlength="9" inputmode="numeric" pattern="^[0-9]{9}$"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" aria-describedby="npm-help">
                    <p id="npm-help" class="mt-1 text-xs text-gray-500">Masukkan 9 digit angka.</p>
                </div>

                <div>
                    <label for="program_studi" class="block text-sm font-medium text-gray-700">Program Studi</label>
                    <select id="program_studi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled>
                        <option value="" disabled {{ old('program_studi', $mahasiswa->program_studi) ? '' : 'selected' }}></option>
                        <option value="S1 Informatika" {{ old('program_studi', $mahasiswa->program_studi) == 'S1 Informatika' ? 'selected' : '' }}>S1 Informatika</option>
                        <option value="S1 Teknik Sipil" {{ old('program_studi', $mahasiswa->program_studi) == 'S1 Teknik Sipil' ? 'selected' : '' }}>S1 Teknik Sipil</option>
                        <option value="D3 Teknik Komputer" {{ old('program_studi', $mahasiswa->program_studi) == 'D3 Teknik Komputer' ? 'selected' : '' }}>D3 Teknik Komputer</option>
                    </select>
                    <input type="hidden" name="program_studi" id="program_studi_hidden" value="{{ old('program_studi', $mahasiswa->program_studi) }}">
                </div>

                <div>
                    <label for="semester_aktif" class="block text-sm font-medium text-gray-700">Semester</label>
                    <select id="semester_aktif" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled>
                        <option value="" disabled {{ old('semester_aktif', $mahasiswa->semester_aktif) ? '' : 'selected' }}></option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ old('semester_aktif', $mahasiswa->semester_aktif) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="semester_aktif" id="semester_hidden" value="{{ old('semester_aktif', $mahasiswa->semester_aktif) }}">
                </div>

                <div>
                    <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan</label>
                    <select id="angkatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled>
                        @php $angkatanVal = old('angkatan', $mahasiswa->angkatan); @endphp
                        <option value="" disabled {{ $angkatanVal ? '' : 'selected' }}></option>
                        <option value="{{ $angkatanVal }}" selected>{{ $angkatanVal }}</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </select>
                    <input type="hidden" name="angkatan" id="angkatan_hidden" value="{{ old('angkatan', $mahasiswa->angkatan) }}">
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.mahasiswa') }}" class="px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const npmInput = document.getElementById('npm');
    const prodiSelect = document.getElementById('program_studi');
    const semesterSelect = document.getElementById('semester_aktif');
    const angkatanSelect = document.getElementById('angkatan');
    const prodiHidden = document.getElementById('program_studi_hidden');
    const semesterHidden = document.getElementById('semester_hidden');
    const angkatanHidden = document.getElementById('angkatan_hidden');

    const prodiMap = { '11': 'S1 Teknik Sipil', '12': 'D3 Teknik Komputer', '13': 'S1 Informatika' };

    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1;

    npmInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 9);
        const npmValue = this.value;

        if (npmValue.length >= 6) {
            const prodiCode = npmValue.substring(4, 6);
            const matchedProdi = prodiMap[prodiCode] || '';
            prodiSelect.value = matchedProdi;
            prodiHidden.value = matchedProdi;
        } else {
            prodiSelect.value = '';
            prodiHidden.value = '';
        }

        let angkatanTahun = null;
        if (npmValue.length >= 2) {
            const angkatanCode = npmValue.substring(0, 2);
            const angkatanTahunString = '20' + angkatanCode;
            angkatanSelect.value = angkatanTahunString;
            angkatanHidden.value = angkatanTahunString;
            angkatanTahun = parseInt(angkatanTahunString);
        } else {
            angkatanSelect.value = '';
            angkatanHidden.value = '';
        }

        if (angkatanTahun) {
            let selisihTahun = currentYear - angkatanTahun;
            let semesterAktif = selisihTahun * 2;
            if (currentMonth >= 10) { semesterAktif += 1; }
            semesterAktif = Math.max(1, Math.min(semesterAktif, 8));
            semesterSelect.value = semesterAktif.toString();
            semesterHidden.value = semesterAktif.toString();
        } else {
            semesterSelect.value = '';
            semesterHidden.value = '';
        }
    });
});
</script>
@endpush
