<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan semua data mahasiswa.
     * (Menggantikan method getAllMahasiswa)
     */
    public function index()
    {
        // Gunakan eager loading 'user' untuk mengambil data relasi secara efisien
        $mahasiswa = MahasiswaDetail::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json($mahasiswa);
    }

    /**
     * Menyimpan data mahasiswa baru.
     * (Menggantikan method registerMahasiswa)
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'npm' => 'required|string|unique:users,username|unique:mahasiswa_detail,npm',
            'program_studi' => 'required|string|max:100',
            'angkatan' => 'required|string|max:10',
            'semester_aktif' => 'required|integer|min:1|max:14',
        ]);

        // 2. Gunakan Database Transaction
        // Ini memastikan jika salah satu proses gagal, semua proses akan dibatalkan.
        // Mencegah adanya "user hantu" (user terbuat tapi detail mahasiswanya gagal).
        try {
            DB::beginTransaction();

            // Buat data di tabel 'users'
            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
                'username' => $validatedData['npm'], // Username = NPM
                'password' => Hash::make($validatedData['npm']), // Password default = NPM
                'role' => 'mahasiswa',
            ]);

            // Buat data di tabel 'mahasiswa_detail' yang berelasi dengan user di atas
            $user->mahasiswaDetail()->create([
                'npm' => $validatedData['npm'],
                'program_studi' => $validatedData['program_studi'],
                'angkatan' => $validatedData['angkatan'],
                'semester_aktif' => $validatedData['semester_aktif'],
                'status' => 'Aktif'
            ]);

            // Jika semua berhasil, simpan perubahan
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa berhasil didaftarkan.',
                'data' => $user->load('mahasiswaDetail') // Kirim kembali data yang baru dibuat
            ], 201);

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query
            DB::rollBack();

            // Kirim response error
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan mahasiswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan satu data mahasiswa spesifik.
     * (Menggantikan method getMahasiswaDetail)
     * Catatan: apiResource menggunakan ID, bukan NPM.
     */
    public function show($id)
    {
        // findOrFail akan otomatis return 404 Not Found jika ID tidak ada
        $mahasiswa = MahasiswaDetail::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }

    /**
     * Memperbarui data mahasiswa.
     * (Bisa kamu isi nanti jika butuh fitur update)
     */
    public function update(Request $request, $id)
    {
        // Logika untuk update data mahasiswa bisa ditambahkan di sini
        return response()->json(['message' => 'Fitur update belum tersedia.'], 501);
    }

    /**
     * Menghapus data mahasiswa.
     * (Bisa kamu isi nanti jika butuh fitur hapus)
     */
    public function destroy($id)
    {
        // Logika untuk menghapus data mahasiswa bisa ditambahkan di sini
        return response()->json(['message' => 'Fitur hapus belum tersedia.'], 501);
    }
}