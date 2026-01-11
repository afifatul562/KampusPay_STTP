@extends('layouts.app')

@section('title', 'Tambah Mahasiswa Baru')
@section('page-title', 'Tambah Mahasiswa Baru')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">👨‍🎓 Formulir Registrasi Mahasiswa</h3>

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

            <form action="{{ route('admin.mahasiswa.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="npm" class="block text-sm font-medium text-gray-700">NPM</label>
                    <input type="text" id="npm" name="npm" value="{{ old('npm') }}" required maxlength="9" inputmode="numeric" pattern="^[0-9]{9}$" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" aria-describedby="npm-help">
                    <p id="npm-help" class="mt-1 text-xs text-gray-500">Masukkan 9 digit angka.</p>
                </div>
                <div>
                    <label for="program_studi" class="block text-sm font-medium text-gray-700">Program Studi</label>
                    <select id="program_studi" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled aria-describedby="prodi-help">
                        <option value="" disabled {{ old('program_studi') ? '' : 'selected' }}></option>
                        <option value="S1 Informatika" {{ old('program_studi') == 'S1 Informatika' ? 'selected' : '' }}>S1 Informatika</option>
                        <option value="S1 Teknik Sipil" {{ old('program_studi') == 'S1 Teknik Sipil' ? 'selected' : '' }}>S1 Teknik Sipil</option>
                        <option value="D3 Teknik Komputer" {{ old('program_studi') == 'D3 Teknik Komputer' ? 'selected' : '' }}>D3 Teknik Komputer</option>
                    </select>
                    <input type="hidden" name="program_studi" id="program_studi_hidden" value="{{ old('program_studi') }}">
                    <p id="prodi-help" class="mt-1 text-xs text-gray-500"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan</label>
                        <select id="angkatan" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled aria-describedby="angkatan-help">
                            <option value="" disabled {{ old('angkatan') ? '' : 'selected' }}></option>
                            <option value="2022" {{ old('angkatan') == '2022' ? 'selected' : '' }}>2022</option>
                            <option value="2023" {{ old('angkatan') == '2023' ? 'selected' : '' }}>2023</option>
                            <option value="2024" {{ old('angkatan') == '2024' ? 'selected' : '' }}>2024</option>
                            <option value="2025" {{ old('angkatan') == '2025' ? 'selected' : '' }}>2025</option>
                        </select>
                        <input type="hidden" name="angkatan" id="angkatan_hidden" value="{{ old('angkatan') }}">
                        <p id="angkatan-help" class="mt-1 text-xs text-gray-500"></p>
                    </div>
                    <div>
                        <label for="semester_aktif" class="block text-sm font-medium text-gray-700">Semester</label>
                        <select id="semester_aktif" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 appearance-none no-native-arrow bg-gray-100 cursor-not-allowed" disabled aria-describedby="semester-help">
                            <option value="" disabled {{ old('semester_aktif') ? '' : 'selected' }}></option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ old('semester_aktif') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <input type="hidden" name="semester_aktif" id="semester_hidden" value="{{ old('semester_aktif') }}">
                        <p id="semester-help" class="mt-1 text-xs text-gray-500"></p>
                    </div>
                </div>
                <div class="pt-2 flex justify-end gap-3">
                    <a href="{{ route('admin.mahasiswa') }}" class="px-4 py-2 bg-gray-200 border border-transparent rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        Daftarkan Mahasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua elemen yang diperlukan
    const npmInput = document.getElementById('npm');
    const prodiSelect = document.getElementById('program_studi');
    const angkatanSelect = document.getElementById('angkatan');
    const semesterSelect = document.getElementById('semester_aktif');
    const prodiHidden = document.getElementById('program_studi_hidden');
    const angkatanHidden = document.getElementById('angkatan_hidden');
    const semesterHidden = document.getElementById('semester_hidden');

    // Kamus Program Studi
    const prodiMap = {
        '11': 'S1 Teknik Sipil',
        '12': 'D3 Teknik Komputer',
        '13': 'S1 Informatika'
    };

    // Dapatkan tanggal hari ini
    const today = new Date();
    const currentYear = today.getFullYear(); // Tahun sekarang (misal: 2025)
    const currentMonth = today.getMonth() + 1; // Bulan sekarang (misal: 10 untuk Oktober)

    // Tambahkan event listener ke input NPM
    npmInput.addEventListener('input', function() {
        // Paksa hanya angka dan batasi 9 digit
        this.value = this.value.replace(/\D/g, '').slice(0, 9);
        const npmValue = this.value;

        // Logika untuk Program Studi
        if (npmValue.length >= 6) {
            const prodiCode = npmValue.substring(4, 6);
            const matchedProdi = prodiMap[prodiCode];
            prodiSelect.value = matchedProdi ? matchedProdi : '';
            prodiHidden.value = matchedProdi ? matchedProdi : '';
        } else {
            prodiSelect.value = '';
            prodiHidden.value = '';
        }

        // Logika untuk Angkatan
        let angkatanTahun = null;
        if (npmValue.length >= 2) {
            const angkatanCode = npmValue.substring(0, 2);
            const angkatanTahunString = '20' + angkatanCode;

            const optionExists = Array.from(angkatanSelect.options).some(opt => opt.value === angkatanTahunString);

            if (optionExists) {
                angkatanSelect.value = angkatanTahunString;
                angkatanHidden.value = angkatanTahunString;
                angkatanTahun = parseInt(angkatanTahunString);
            } else {
                angkatanSelect.value = '';
                angkatanHidden.value = '';
            }
        } else {
            angkatanSelect.value = '';
            angkatanHidden.value = '';
        }

        // Logika semester (sesuai aturan kampus)
        if (angkatanTahun) {
            let selisihTahun = currentYear - angkatanTahun;
            let semesterAktif;

            // Aturan:
            // Oktober (10) s/d Februari (2) = Ganjil
            // Maret (3) s/d Juli (7) = Genap

            if (currentMonth >= 10) {
                // Jika sudah masuk Oktober, mahasiswa angkatan baru mulai semester 1, 3, 5, dst.
                semesterAktif = (selisihTahun * 2) + 1;
            } else if (currentMonth <= 2) {
                // Januari & Februari adalah sisa dari semester ganjil tahun ajaran sebelumnya
                semesterAktif = (selisihTahun * 2) - 1;
            } else if (currentMonth >= 3 && currentMonth <= 7) {
                // Maret - Juli adalah semester genap
                semesterAktif = selisihTahun * 2;
            } else {
                // Agustus - September (Masa Libur)
                // Biasanya tetap ditampilkan sebagai semester genap yang baru selesai
                semesterAktif = selisihTahun * 2;
            }

            // Batasi minimal semester 1 dan maksimal semester 8
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
