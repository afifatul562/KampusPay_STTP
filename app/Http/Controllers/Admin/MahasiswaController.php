<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\MahasiswaProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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
        ]);

        DB::beginTransaction();
        try {
            // Buat data di tabel 'users'
            // Password default dapat dikonfigurasi via env; fallback ke NPM agar non-breaking
            $defaultPassword = $this->defaultMahasiswaPassword($validatedData['npm']);
            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => null,
                'username' => $validatedData['npm'],
                'password' => $defaultPassword,
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

            $this->syncDefaultProgram($detail->mahasiswa_id);

            DB::commit();

            return redirect()->route('admin.mahasiswa')->with('success', 'Mahasiswa berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Store Mahasiswa: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal mendaftarkan mahasiswa: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            return $this->handleMahasiswaImport($request);
        } catch (\Throwable $e) {
            Log::error('Impor CSV fatal: '.$e->getMessage(), [
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.mahasiswa')->with(
                'error',
                'Impor gagal di server: '.$e->getMessage().' (cek storage/logs/laravel.log)'
            );
        }
    }

    /**
     * Impor mahasiswa dari CSV (web: redirect + flash; logging di channel default).
     */
    protected function handleMahasiswaImport(Request $request)
    {
        // Bcrypt per baris ~0,3–0,5 dtk; 100+ mahasiswa butuh waktu > 30 dtk (limit PHP hosting).
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');

        Log::info('Memulai proses impor CSV mahasiswa.');

        if (! Schema::hasTable('mahasiswa_program')) {
            Log::error('Impor CSV gagal: tabel mahasiswa_program belum ada (migration belum dijalankan).');

            return redirect()->route('admin.mahasiswa')->with(
                'error',
                'Tabel mahasiswa_program belum ada. Jalankan di server: php artisan migrate --force'
            );
        }

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

        // Baris pertama: header atau data
        $firstRow = fgetcsv($handle);
        $pendingRows = [];
        if (is_array($firstRow) && $this->isMahasiswaCsvHeaderRow($firstRow)) {
            Log::info('Header CSV: ' . implode(', ', array_map('strval', $firstRow)));
        } elseif (is_array($firstRow) && ! empty(array_filter($firstRow, 'strlen'))) {
            $pendingRows[] = $firstRow;
        }

        $berhasil = 0;
        $gagal = 0;
        $errors = [];
        $lineNumber = 1;
        $npmInFile = [];

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
            $existingUsernames = array_flip(User::query()->pluck('username')->all());
            $existingNpms = array_flip(MahasiswaDetail::query()->pluck('npm')->all());

            Log::info('Impor CSV: validasi duplikat — total username: ' . count($existingUsernames) . ', total NPM: ' . count($existingNpms));
            $sampleNpms = array_slice(array_keys($existingNpms), 0, 5);
            Log::info('Impor CSV: contoh NPM di DB: ' . implode(', ', $sampleNpms));
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data awal untuk validasi impor: ' . $e->getMessage());
            fclose($handle);

            return redirect()->route('admin.mahasiswa')->with('error', 'Gagal menyiapkan validasi: ' . $e->getMessage());
        }

        $processRow = function (array $row) use (
            &$berhasil,
            &$gagal,
            &$errors,
            &$lineNumber,
            &$existingUsernames,
            &$existingNpms,
            &$npmInFile,
            $prodiMap,
            $currentYear,
            $currentMonth,
        ): void {
            $lineNumber++;

            if (empty(array_filter($row, 'strlen'))) {
                Log::warning("Baris {$lineNumber} dilewati karena kosong.");

                return;
            }

            [$nama_lengkap, $npmRaw] = $this->parseMahasiswaCsvColumns($row);
            $npm = $this->normalizeNpm($npmRaw);
            $nama_lengkap = $nama_lengkap !== null ? trim($nama_lengkap) : null;

            $rowDataForLog = implode(', ', array_filter([$nama_lengkap, $npm], fn ($v) => $v !== null && $v !== ''));
            Log::info("Memproses baris {$lineNumber}: " . $rowDataForLog);

            try {
                if (empty($npm) || ! $this->looksLikeNpm($npm)) {
                    throw new \Exception("Format NPM tidak valid ('{$npm}'). NPM harus angka minimal 8 digit.");
                }
                if (empty($nama_lengkap)) {
                    throw new \Exception('Nama lengkap kosong.');
                }

                if (isset($npmInFile[$npm])) {
                    throw new \Exception("NPM '{$npm}' duplikat di file CSV (sudah ada di baris {$npmInFile[$npm]}).");
                }
                $npmInFile[$npm] = $lineNumber;

                if (isset($existingUsernames[$npm])) {
                    $existingUser = User::query()->where('username', $npm)->first();
                    $detailInfo = $existingUser ? " (User ID: {$existingUser->id}, Nama: {$existingUser->nama_lengkap})" : '';
                    Log::warning("Baris {$lineNumber}: NPM '{$npm}' sudah ada sebagai username di database{$detailInfo}");
                    throw new \Exception("NPM '{$npm}' sudah terdaftar sebagai username di database{$detailInfo}.");
                }
                if (isset($existingNpms[$npm])) {
                    $existingMhs = MahasiswaDetail::query()->where('npm', $npm)->with('user')->first();
                    $detailInfo = $existingMhs && $existingMhs->user ? " (Mahasiswa ID: {$existingMhs->mahasiswa_id}, Nama: {$existingMhs->user->nama_lengkap})" : '';
                    Log::warning("Baris {$lineNumber}: NPM '{$npm}' sudah ada di detail mahasiswa{$detailInfo}");
                    throw new \Exception("NPM '{$npm}' sudah terdaftar di detail mahasiswa{$detailInfo}.");
                }

                // Turunan dari NPM (angkatan, prodi, semester)
                $angkatanCode = substr($npm, 0, 2);
                $angkatan = '20' . $angkatanCode;
                $angkatanTahunInt = (int) $angkatan;
                $prodiCode = substr($npm, 4, 2);
                $program_studi = $prodiMap[$prodiCode] ?? null;
                if (! $program_studi) {
                    throw new \Exception("Kode prodi '{$prodiCode}' (digit 5-6 NPM) tidak dikenali (harus 11, 12, atau 13).");
                }
                $selisihTahun = $currentYear - $angkatanTahunInt;
                $semesterAktif = $selisihTahun * 2;
                if ($currentMonth >= 10) {
                    $semesterAktif += 1;
                }
                $semesterAktif = max(1, min($semesterAktif, 8));

                $defaultPassword = $this->defaultMahasiswaPassword($npm);
                $user = User::create([
                    'nama_lengkap' => $nama_lengkap,
                    'email' => null,
                    'username' => $npm,
                    'password' => $defaultPassword,
                    'role' => 'mahasiswa',
                ]);

                $user->mahasiswaDetail()->create([
                    'npm' => $npm,
                    'program_studi' => $program_studi,
                    'angkatan' => $angkatan,
                    'semester_aktif' => $semesterAktif,
                    'status' => 'Aktif',
                ]);

                $mhsDetail = MahasiswaDetail::query()->where('npm', $npm)->first();
                if ($mhsDetail) {
                    $this->syncDefaultProgram($mhsDetail->mahasiswa_id);
                }

                $existingUsernames[$npm] = true;
                $existingNpms[$npm] = true;

                $berhasil++;
                Log::info("Baris {$lineNumber} (NPM: {$npm}) berhasil disimpan.");
            } catch (\Throwable $e) {
                $gagal++;
                $errorMessage = "Baris {$lineNumber} (Data: {$rowDataForLog}): " . $e->getMessage();
                $errors[] = $errorMessage;
                Log::error($errorMessage);
            }
        };

        DB::beginTransaction();
        try {
            foreach ($pendingRows as $row) {
                $processRow($row);
            }

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $processRow($row);
            }

            if ($gagal > 0) {
                $detail = isset($errors[0]) ? ' '.$errors[0] : '';
                throw new \Exception("Terjadi {$gagal} kesalahan saat memproses baris CSV. Transaksi dibatalkan.{$detail}");
            }

            DB::commit();
            Log::info("Impor CSV berhasil. {$berhasil} data disimpan.");

            $message = "Impor selesai. Berhasil: {$berhasil} data.";
            return redirect()->route('admin.mahasiswa')->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Impor CSV Gagal Total: ' . $e->getMessage());
            Log::info('Impor CSV: jumlah baris error: ' . count($errors ?? []));

            $errorMessage = 'Gagal melakukan impor: ' . $e->getMessage();

            if (isset($errors) && ! empty($errors) && is_array($errors)) {
                return redirect()->route('admin.mahasiswa')
                    ->with('error', $errorMessage)
                    ->with('import_errors', array_slice($errors, 0, 30));
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
    public function show(int|string $id)
    {
        $mahasiswa = MahasiswaDetail::query()->with('user')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }

    public function edit(int|string $id)
    {
        $mahasiswaDetail = MahasiswaDetail::query()->with(['user', 'program'])->find($id);

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
    public function update(Request $request, int|string $id)
    {
        $mahasiswaDetail = MahasiswaDetail::query()->find($id);

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

            $this->syncDefaultProgram($mahasiswaDetail->mahasiswa_id);

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
    public function destroy(int|string $id)
    {
        $mahasiswaDetail = MahasiswaDetail::query()->find($id);

        if (!$mahasiswaDetail) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            $userId = $mahasiswaDetail->user_id;
            $mahasiswaId = $mahasiswaDetail->mahasiswa_id;

            MahasiswaDetail::query()->whereKey($mahasiswaId)->delete();

            if ($userId) {
                User::query()->whereKey($userId)->delete();
            }

            DB::commit();

            return response()->noContent();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Hapus Mahasiswa: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data. Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Semua mahasiswa memakai skema pembayaran yang sama (reguler, non-transfer).
     */
    private function defaultMahasiswaPassword(string $npm): string
    {
        $configured = config('accounts.mahasiswa_default_password');

        return ($configured !== null && $configured !== '') ? (string) $configured : $npm;
    }

    private function syncDefaultProgram(int $mahasiswaId): void
    {
        MahasiswaProgram::updateOrCreate(
            ['mahasiswa_id' => $mahasiswaId],
            [
                'program_type' => 'reguler',
                'kip_scheme' => null,
                'is_transfer' => false,
                'start_semester' => 1,
            ]
        );
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isMahasiswaCsvHeaderRow(array $row): bool
    {
        $first = mb_strtolower(trim((string) ($row[0] ?? '')));
        $firstNorm = str_replace([' ', '-'], '_', $first);
        $second = mb_strtolower(trim((string) ($row[1] ?? '')));

        if (in_array($first, ['npm', 'nama', 'name'], true)) {
            return true;
        }

        if (in_array($firstNorm, ['nama_lengkap', 'nama_mahasiswa'], true)) {
            return true;
        }

        // Excel: kolom "Nama Lengkap" + "NPM"
        if (str_contains($first, 'nama') && $second === 'npm') {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string|null>  $row
     * @return array{0: ?string, 1: ?string}
     */
    private function parseMahasiswaCsvColumns(array $row): array
    {
        $cols = array_values(array_map(fn ($c) => is_string($c) ? trim($c) : trim((string) $c), $row));

        if (count($cols) >= 3 && filter_var($cols[1], FILTER_VALIDATE_EMAIL)) {
            return [
                $cols[0] !== '' ? $cols[0] : null,
                $cols[2] !== '' ? $cols[2] : null,
            ];
        }

        if (count($cols) >= 2) {
            if ($this->looksLikeNpm($cols[0]) && ! $this->looksLikeNpm($cols[1])) {
                return [
                    $cols[1] !== '' ? $cols[1] : null,
                    $cols[0] !== '' ? $cols[0] : null,
                ];
            }

            return [
                $cols[0] !== '' ? $cols[0] : null,
                $cols[1] !== '' ? $cols[1] : null,
            ];
        }

        return [null, null];
    }

    private function looksLikeNpm(?string $value): bool
    {
        $value = $this->normalizeNpm($value);

        return $value !== '' && preg_match('/^\d{8,}$/', $value) === 1;
    }

    private function normalizeNpm(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = preg_replace('/\D+/', '', $value) ?? $value;

        return trim($value);
    }
}
