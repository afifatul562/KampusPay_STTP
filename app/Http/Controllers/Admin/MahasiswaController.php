<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail; // Kita akan pakai ini
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan semua data mahasiswa. (Untuk API)
     */
    public function index()
    {
        $mahasiswa = MahasiswaDetail::with('user')->orderBy('npm', 'asc')->get();
        // SELALU bungkus respon
        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }

    /**
     * Menampilkan form untuk membuat mahasiswa baru. (Untuk Web)
     */
    public function create()
    {
        return view('admin.create-mahasiswa');
    }

    /**
     * Menyimpan data mahasiswa baru. (Untuk Web)
     * !! INI YANG DIPERBAIKI (return-nya) !!
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

        DB::beginTransaction();
        try {
            // Buat data di tabel 'users'
            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
                'username' => $validatedData['npm'],
                'password' => Hash::make($validatedData['npm']),
                'role' => 'mahasiswa',
            ]);

            // Buat data di tabel 'mahasiswa_detail'
            $user->mahasiswaDetail()->create([
                'npm' => $validatedData['npm'],
                'program_studi' => $validatedData['program_studi'],
                'angkatan' => $validatedData['angkatan'],
                'semester_aktif' => $validatedData['semester_aktif'],
                'status' => 'Aktif'
            ]);

            DB::commit();

            // !! PERBAIKAN: Ganti dari JSON ke Redirect !!
            return redirect()->route('admin.mahasiswa')->with('success', 'Mahasiswa berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Mahasiswa: ' . $e->getMessage());

            // !! PERBAIKAN: Ganti dari JSON ke Redirect !!
            return redirect()->back()->withInput()->with('error', 'Gagal mendaftarkan mahasiswa: ' . $e->getMessage());
        }
    }

 /**
     * Mengimpor mahasiswa dari CSV. (Untuk Web)
     * !! DENGAN LOGGING & VALIDASI LEBIH DETAIL !!
     */
    public function import(Request $request)
    {
        Log::info('Memulai proses impor CSV mahasiswa.');

        $request->validate([
            'file_csv' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file_csv');
        $path = $file->getRealPath();
        Log::info('File CSV diterima: ' . $file->getClientOriginalName() . ' di path: ' . $path);

        $handle = fopen($path, "r");
        if ($handle === FALSE) {
            Log::error('Gagal membuka file CSV.');
            return redirect()->route('admin.mahasiswa')->with('error', 'Gagal membuka file CSV.');
        }

        // Lewati baris header (jika ada)
        $header = fgetcsv($handle);
        Log::info('Header CSV: ' . implode(', ', $header ?: []));

        $berhasil = 0;
        $gagal = 0;
        $errors = [];
        $lineNumber = 1; // Untuk melacak nomor baris

        $prodiMap = [
            '11' => 'S1 Teknik Sipil',
            '12' => 'D3 Teknik Komputer',
            '13' => 'S1 Informatika'
        ];
        $today = new \DateTime();
        $currentYear = (int)$today->format('Y');
        $currentMonth = (int)$today->format('m');

        // ===========================================
        // !! PERBAIKAN PERFORMA DIMULAI DARI SINI !!
        // ===========================================
        try {
            // 1. Ambil semua data yang ada ke memori SEBELUM loop
            // Kita pakai array_flip untuk pencarian O(1) yang super cepat
            $existingEmails = array_flip(User::pluck('email')->toArray());
            $existingUsernames = array_flip(User::pluck('username')->toArray());
            $existingNpms = array_flip(MahasiswaDetail::pluck('npm')->toArray());
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data awal untuk validasi impor: ' . $e->getMessage());
            fclose($handle);
            return redirect()->route('admin.mahasiswa')->with('error', 'Gagal menyiapkan validasi: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $lineNumber++;

                // Cek jika baris kosong (logika ini bisa di-skip jika tidak perlu)
                if (empty(array_filter($row, 'strlen'))) {
                    Log::warning("Baris {$lineNumber} dilewati karena kosong.");
                    continue;
                }

                // Ambil data 3 kolom dan trim spasi ekstra
                $nama_lengkap = isset($row[0]) ? trim($row[0]) : null;
                $email = isset($row[1]) ? trim($row[1]) : null;
                $npm = isset($row[2]) ? trim($row[2]) : null;

                // Log baris data mentah (lebih baik sebelum 'try' agar selalu tercatat)
                $rowDataForLog = implode(', ', [$nama_lengkap, $email, $npm]);
                Log::info("Memproses baris {$lineNumber}: " . $rowDataForLog);

                try {
                    // --- Validasi Data Baris ---
                    if (empty($npm) || strlen($npm) < 6) { throw new \Exception("Format NPM tidak valid ('{$npm}')."); }
                    if (empty($nama_lengkap)) { throw new \Exception("Nama lengkap kosong."); }
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new \Exception("Format Email tidak valid ('{$email}')."); }

                    // --- Kalkulasi (seperti sebelumnya) ---
                    $angkatanCode = substr($npm, 0, 2);
                    $angkatan = '20' . $angkatanCode;
                    $angkatanTahunInt = (int)$angkatan;
                    $prodiCode = substr($npm, 4, 2);
                    $program_studi = $prodiMap[$prodiCode] ?? null;
                    if (!$program_studi) { throw new \Exception("Kode prodi '{$prodiCode}' (digit 5-6 NPM) tidak dikenali."); }
                    $selisihTahun = $currentYear - $angkatanTahunInt;
                    $semesterAktif = $selisihTahun * 2;
                    if ($currentMonth >= 10) { $semesterAktif += 1; } // Asumsi Oktober = Ganjil
                    $semesterAktif = max(1, min($semesterAktif, 8)); // Batasi semester 1-8

                    // ==================================================
                    // !! PERBAIKAN: Cek duplikat di memori (bukan DB) !!
                    // ==================================================
                    if (isset($existingEmails[$email])) {
                        throw new \Exception("Email '{$email}' sudah terdaftar.");
                    }
                    if (isset($existingUsernames[$npm])) {
                        throw new \Exception("NPM '{$npm}' sudah terdaftar sebagai username.");
                    }
                    if (isset($existingNpms[$npm])) {
                        throw new \Exception("NPM '{$npm}' sudah terdaftar di detail mahasiswa.");
                    }

                    // --- Simpan ke Database ---
                    $user = User::create([
                        'nama_lengkap' => $nama_lengkap,
                        'email' => $email,
                        'username' => $npm,
                        'password' => Hash::make($npm), // Password default = NPM
                        'role' => 'mahasiswa',
                    ]);

                    $user->mahasiswaDetail()->create([
                        'npm' => $npm,
                        'program_studi' => $program_studi,
                        'angkatan' => $angkatan,
                        'semester_aktif' => $semesterAktif,
                        'status' => 'Aktif'
                    ]);

                    // !! PENTING: Tambahkan data baru ke array memori !!
                    // Ini untuk mencegah duplikat di dalam file CSV yang sama
                    $existingEmails[$email] = true;
                    $existingUsernames[$npm] = true;
                    $existingNpms[$npm] = true;

                    $berhasil++;
                    Log::info("Baris {$lineNumber} (NPM: {$npm}) berhasil disimpan."); // Log Sukses Baris

                } catch (\Exception $e) {
                    $gagal++;
                    // Gunakan data mentah yang sudah di-trim untuk log error
                    $errorMessage = "Baris {$lineNumber} (Data: {$rowDataForLog}): " . $e->getMessage();
                    $errors[] = $errorMessage;
                    Log::error($errorMessage); // Log Error Baris
                    // Jangan 'throw $e' di sini agar loop berlanjut mengumpulkan semua error
                }
            } // End While

            // Jika ada error di salah satu baris, batalkan semua
            if ($gagal > 0) {
                // Buat pesan error utama yang akan dilempar ke 'catch' di luar
                throw new \Exception("Terjadi {$gagal} kesalahan saat memproses baris CSV. Transaksi dibatalkan.");
            }

            DB::commit();
            Log::info("Impor CSV berhasil. {$berhasil} data disimpan."); // Log Sukses Global

            $message = "Impor selesai. Berhasil: {$berhasil} data.";
            return redirect()->route('admin.mahasiswa')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Impor CSV Gagal Total: ' . $e->getMessage()); // Log Error Global

            // Siapkan pesan error untuk ditampilkan
            $errorMessage = 'Gagal melakukan impor: ' . $e->getMessage();

            // Siapkan session khusus untuk detail error per baris
            if (!empty($errors)) {
                // Kita tidak tambahkan $errors ke $errorMessage utama agar tidak terlalu panjang
                // Kita kirim via session terpisah
                return redirect()->route('admin.mahasiswa')
                                 ->with('error', $errorMessage)
                                 ->with('import_errors', $errors);
            }

            return redirect()->route('admin.mahasiswa')->with('error', $errorMessage);

        } finally {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
        }
    }
    /**
     * Menampilkan satu data mahasiswa spesifik. (Untuk API)
     */
    public function show($id)
    {
        $mahasiswa = MahasiswaDetail::with('user')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }

    public function edit($id)
    {
        // Cari data mahasiswa beserta relasi user-nya
        // Kita gunakan find() agar bisa dicek manual, sama seperti method update() Anda
        $mahasiswaDetail = MahasiswaDetail::with('user')->find($id);

        // Jika data tidak ditemukan, kembalikan ke halaman index dengan error
        if (!$mahasiswaDetail) {
            // Asumsi Anda punya route 'admin.mahasiswa' untuk halaman index web
            return redirect()->route('admin.mahasiswa')->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Tampilkan view 'edit' dan kirim data mahasiswa tersebut
        // Pastikan Anda membuat file view di:
        // resources/views/admin/mahasiswa/edit.blade.php
        return view('admin.mahasiswa_edit', [
            'mahasiswa' => $mahasiswaDetail
        ]);
    }

    /**
     * Memperbarui data mahasiswa. (Untuk Web)
     * (Fungsi ini sudah benar dari sebelumnya)
     */
    public function update(Request $request, $id)
    {
        $mahasiswaDetail = MahasiswaDetail::find($id);

        if (!$mahasiswaDetail) {
            return redirect()->route('admin.mahasiswa')->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $user = $mahasiswaDetail->user;
        if (!$user) {
             return redirect()->route('admin.mahasiswa')->with('error', 'Data user terkait tidak ditemukan.');
        }

        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'program_studi' => 'required|string|max:100',
            'semester_aktif' => 'required|integer|min:1|max:14',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
            ]);

            $mahasiswaDetail->update([
                'program_studi' => $validatedData['program_studi'],
                'semester_aktif' => $validatedData['semester_aktif'],
            ]);

            DB::commit();

            return redirect()->route('admin.mahasiswa')->with('success', 'Data mahasiswa berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Update Mahasiswa: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data mahasiswa. (Untuk API)
     */
    public function destroy($id)
    {
        $mahasiswaDetail = MahasiswaDetail::find($id);

        if (!$mahasiswaDetail) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            $userId = $mahasiswaDetail->user_id;

            $mahasiswaDetail->delete();

            $user = User::find($userId);
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return response()->noContent();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Hapus Mahasiswa: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data. Terjadi kesalahan server.'], 500);
        }
    }
}
