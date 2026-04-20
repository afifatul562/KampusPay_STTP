<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\MahasiswaProgram;
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
     * Menyimpan data mahasiswa baru (web: redirect + flash).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'npm' => 'required|string|unique:users,username|unique:mahasiswa_detail,npm',
            'program_studi' => 'required|string|max:100',
            'angkatan' => 'required|string|max:10',
            'semester_aktif' => 'required|integer|min:1|max:14',
            'program_type' => 'nullable|string|in:kip,reguler,eksklusif',
            'kip_scheme' => 'nullable|integer|in:1,2',
            'is_transfer' => 'nullable|boolean',
            'start_semester' => 'nullable|integer|min:1|max:14',
        ]);

        DB::beginTransaction();
        try {
            // Buat data di tabel 'users'
            // Password default dapat dikonfigurasi via env; fallback ke NPM agar non-breaking
            $defaultPassword = env('MAHASISWA_DEFAULT_PASSWORD', $validatedData['npm']);
            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => null,
                'username' => $validatedData['npm'],
                'password' => Hash::make($defaultPassword),
                'role' => 'mahasiswa',
            ]);

            // Buat data di tabel 'mahasiswa_detail'
            $detail = $user->mahasiswaDetail()->create([
                'npm' => $validatedData['npm'],
                'program_studi' => $validatedData['program_studi'],
                'angkatan' => $validatedData['angkatan'],
                'semester_aktif' => $validatedData['semester_aktif'],
                'status' => 'Aktif'
            ]);

            $programType = $validatedData['program_type'] ?? 'reguler';
            $kipScheme = $programType === 'kip' ? ($validatedData['kip_scheme'] ?? null) : null;
            $isTransfer = (bool) ($validatedData['is_transfer'] ?? false);
            $startSemester = $isTransfer ? (int) ($validatedData['start_semester'] ?? $detail->semester_aktif ?? 1) : 1;

            MahasiswaProgram::updateOrCreate(
                ['mahasiswa_id' => $detail->mahasiswa_id],
                [
                    'program_type' => $programType,
                    'kip_scheme' => $kipScheme,
                    'is_transfer' => $isTransfer,
                    'start_semester' => $startSemester,
                ]
            );

            DB::commit();

            return redirect()->route('admin.mahasiswa')->with('success', 'Mahasiswa berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Mahasiswa: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal mendaftarkan mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Impor mahasiswa dari CSV (web: redirect + flash; logging di channel default).
     */
    public function import(Request $request)
    {
        Log::info('Memulai proses impor CSV mahasiswa.');

        // Validasi file
        $request->validate([
            'file_csv' => 'required|file|max:10240' // max 10MB
        ], [
            'file_csv.required' => 'File CSV harus dipilih.',
            'file_csv.file' => 'File yang diunggah tidak valid.',
            'file_csv.max' => 'Ukuran file maksimal 10MB.'
        ]);

        $file = $request->file('file_csv');

        // Validasi ekstensi file secara manual (lebih fleksibel)
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt'])) {
            Log::error('Format file tidak valid: ' . $extension);
            return redirect()->route('admin.mahasiswa')->with('error', 'File harus berformat CSV (.csv) atau TXT (.txt).');
        }
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

        try {
            // Cache username & NPM yang sudah ada (lookup O(1) per baris CSV).
            $existingUsernames = array_flip(User::pluck('username')->toArray());
            $existingNpms = array_flip(MahasiswaDetail::pluck('npm')->toArray());

            Log::info('Impor CSV: validasi duplikat — total username: ' . count($existingUsernames) . ', total NPM: ' . count($existingNpms));
            $sampleNpms = array_slice(array_keys($existingNpms), 0, 5);
            Log::info('Impor CSV: contoh NPM di DB: ' . implode(', ', $sampleNpms));
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data awal untuk validasi impor: ' . $e->getMessage());
            fclose($handle);
            return redirect()->route('admin.mahasiswa')->with('error', 'Gagal menyiapkan validasi: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $lineNumber++;

                if (empty(array_filter($row, 'strlen'))) {
                    Log::warning("Baris {$lineNumber} dilewati karena kosong.");
                    continue;
                }

                // Format: nama_lengkap, npm — baris lama nama_lengkap, email, npm tetap didukung (email diabaikan).
                $cols = array_values(array_map(fn ($c) => is_string($c) ? trim($c) : '', $row));
                if (count($cols) >= 3 && filter_var($cols[1], FILTER_VALIDATE_EMAIL)) {
                    $nama_lengkap = $cols[0] !== '' ? $cols[0] : null;
                    $npm = $cols[2] !== '' ? $cols[2] : null;
                } elseif (count($cols) >= 2) {
                    $nama_lengkap = $cols[0] !== '' ? $cols[0] : null;
                    $npm = $cols[1] !== '' ? $cols[1] : null;
                } else {
                    $nama_lengkap = null;
                    $npm = null;
                }

                $rowDataForLog = implode(', ', array_filter([$nama_lengkap, $npm], fn ($v) => $v !== null && $v !== ''));
                Log::info("Memproses baris {$lineNumber}: " . $rowDataForLog);

                try {
                    // Validasi baris
                    if (empty($npm) || strlen($npm) < 6) { throw new \Exception("Format NPM tidak valid ('{$npm}')."); }
                    if (empty($nama_lengkap)) { throw new \Exception("Nama lengkap kosong."); }

                    // Turunan dari NPM (angkatan, prodi, semester)
                    $angkatanCode = substr($npm, 0, 2);
                    $angkatan = '20' . $angkatanCode;
                    $angkatanTahunInt = (int)$angkatan;
                    $prodiCode = substr($npm, 4, 2);
                    $program_studi = $prodiMap[$prodiCode] ?? null;
                    if (!$program_studi) { throw new \Exception("Kode prodi '{$prodiCode}' (digit 5-6 NPM) tidak dikenali."); }
                    $selisihTahun = $currentYear - $angkatanTahunInt;
                    $semesterAktif = $selisihTahun * 2;
                    if ($currentMonth >= 10) {
                        $semesterAktif += 1;
                    }
                    $semesterAktif = max(1, min($semesterAktif, 8));

                    if (isset($existingUsernames[$npm])) {
                        $existingUser = User::where('username', $npm)->first();
                        $detailInfo = $existingUser ? " (User ID: {$existingUser->id}, Nama: {$existingUser->nama_lengkap})" : "";
                        Log::warning("Baris {$lineNumber}: NPM '{$npm}' sudah ada sebagai username di database{$detailInfo}");
                        throw new \Exception("NPM '{$npm}' sudah terdaftar sebagai username di database{$detailInfo}.");
                    }
                    if (isset($existingNpms[$npm])) {
                        $existingMhs = MahasiswaDetail::where('npm', $npm)->with('user')->first();
                        $detailInfo = $existingMhs && $existingMhs->user ? " (Mahasiswa ID: {$existingMhs->mahasiswa_id}, Nama: {$existingMhs->user->nama_lengkap})" : "";
                        Log::warning("Baris {$lineNumber}: NPM '{$npm}' sudah ada di detail mahasiswa{$detailInfo}");
                        throw new \Exception("NPM '{$npm}' sudah terdaftar di detail mahasiswa{$detailInfo}.");
                    }

                    // Simpan user + detail
                    $defaultPassword = env('MAHASISWA_DEFAULT_PASSWORD', $npm);
                    $user = User::create([
                        'nama_lengkap' => $nama_lengkap,
                        'email' => null,
                        'username' => $npm,
                        'password' => Hash::make($defaultPassword), // Default bisa diubah via env
                        'role' => 'mahasiswa',
                    ]);

                    $user->mahasiswaDetail()->create([
                        'npm' => $npm,
                        'program_studi' => $program_studi,
                        'angkatan' => $angkatan,
                        'semester_aktif' => $semesterAktif,
                        'status' => 'Aktif'
                    ]);

                    // Default program untuk import: reguler, non-transfer.
                    // Untuk mahasiswa transfer/KIP biasanya akan di-set via edit setelah import.
                    $mhsDetail = MahasiswaDetail::where('npm', $npm)->first();
                    if ($mhsDetail) {
                        \App\Models\MahasiswaProgram::updateOrCreate(
                            ['mahasiswa_id' => $mhsDetail->mahasiswa_id],
                            [
                                'program_type' => 'reguler',
                                'kip_scheme' => null,
                                'is_transfer' => false,
                                'start_semester' => 1,
                            ]
                        );
                    }

                    // Perbarui cache agar baris berikutnya dalam file yang sama tidak bentrok.
                    $existingUsernames[$npm] = true;
                    $existingNpms[$npm] = true;

                    $berhasil++;
                    Log::info("Baris {$lineNumber} (NPM: {$npm}) berhasil disimpan.");

                } catch (\Exception $e) {
                    $gagal++;
                    $errorMessage = "Baris {$lineNumber} (Data: {$rowDataForLog}): " . $e->getMessage();
                    $errors[] = $errorMessage;
                    Log::error($errorMessage);
                }
            }

            if ($gagal > 0) {
                throw new \Exception("Terjadi {$gagal} kesalahan saat memproses baris CSV. Transaksi dibatalkan.");
            }

            DB::commit();
            Log::info("Impor CSV berhasil. {$berhasil} data disimpan.");

            $message = "Impor selesai. Berhasil: {$berhasil} data.";
            return redirect()->route('admin.mahasiswa')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Impor CSV Gagal Total: ' . $e->getMessage());
            Log::info('Impor CSV: jumlah baris error: ' . count($errors ?? []));

            $errorMessage = 'Gagal melakukan impor: ' . $e->getMessage();

            if (isset($errors) && !empty($errors) && is_array($errors)) {
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
     * Ekspor daftar mahasiswa ke CSV (untuk Web)
     */
    public function export()
    {
        $filename = 'mahasiswa_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 untuk Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Header kolom
            fputcsv($handle, ['Nama Lengkap', 'Email', 'NPM', 'Program Studi', 'Angkatan', 'Semester', 'Status']);

            $rows = MahasiswaDetail::with('user')->orderBy('npm')->get();
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->user)->nama_lengkap,
                    optional($row->user)->email,
                    $row->npm,
                    $row->program_studi,
                    $row->angkatan,
                    $row->semester_aktif,
                    $row->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
        $mahasiswaDetail = MahasiswaDetail::with(['user', 'program'])->find($id);

        if (!$mahasiswaDetail) {
            return redirect()->route('admin.mahasiswa')->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        return view('admin.mahasiswa_edit', [
            'mahasiswa' => $mahasiswaDetail
        ]);
    }

    /**
     * Memperbarui data mahasiswa (web: redirect + flash).
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
                'nullable',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'npm' => [
                'required',
                'regex:/^\\d{9}$/',
                Rule::unique('users', 'username')->ignore($user->id),
                Rule::unique('mahasiswa_detail', 'npm')->ignore($mahasiswaDetail->mahasiswa_id, 'mahasiswa_id'),
            ],
            'program_type' => 'nullable|string|in:kip,reguler,eksklusif',
            'kip_scheme' => 'nullable|integer|in:1,2',
            'is_transfer' => 'nullable|boolean',
            'start_semester' => 'nullable|integer|min:1|max:14',
        ]);

        DB::beginTransaction();
        try {
            // Hitung nilai turunan dari NPM
            $npm = $validatedData['npm'];
            $prodiMap = [ '11' => 'S1 Teknik Sipil', '12' => 'D3 Teknik Komputer', '13' => 'S1 Informatika' ];
            $angkatan = '20' . substr($npm, 0, 2);
            $prodiCode = substr($npm, 4, 2);
            $programStudi = $prodiMap[$prodiCode] ?? $mahasiswaDetail->program_studi;

            $today = now();
            $currentYear = (int)$today->format('Y');
            $currentMonth = (int)$today->format('m');
            $selisihTahun = $currentYear - (int)$angkatan;
            $semesterAktif = $selisihTahun * 2;
            if ($currentMonth >= 10) { $semesterAktif += 1; }
            $semesterAktif = max(1, min($semesterAktif, 8));

            // Update user (termasuk username = npm)
            $email = isset($validatedData['email']) && $validatedData['email'] !== ''
                ? $validatedData['email']
                : null;

            $user->update([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $email,
                'username' => $npm,
            ]);

            // Update detail mahasiswa
            $mahasiswaDetail->update([
                'npm' => $npm,
                'program_studi' => $programStudi,
                'angkatan' => $angkatan,
                'semester_aktif' => $semesterAktif,
            ]);

            $programType = $validatedData['program_type'] ?? ($mahasiswaDetail->program?->program_type ?? 'reguler');
            $kipScheme = $programType === 'kip' ? ($validatedData['kip_scheme'] ?? ($mahasiswaDetail->program?->kip_scheme)) : null;
            $isTransfer = (bool) ($validatedData['is_transfer'] ?? ($mahasiswaDetail->program?->is_transfer ?? false));
            $startSemester = $isTransfer
                ? (int) ($validatedData['start_semester'] ?? ($mahasiswaDetail->program?->start_semester ?? $semesterAktif))
                : 1;

            MahasiswaProgram::updateOrCreate(
                ['mahasiswa_id' => $mahasiswaDetail->mahasiswa_id],
                [
                    'program_type' => $programType,
                    'kip_scheme' => $kipScheme,
                    'is_transfer' => $isTransfer,
                    'start_semester' => $startSemester,
                ]
            );

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
