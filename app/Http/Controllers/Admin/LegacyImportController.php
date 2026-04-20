<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LegacyImportController extends Controller
{
    public function index()
    {
        return view('admin.import-data');
    }

    public function importTagihan(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|max:10240',
        ], [
            'file_csv.required' => 'File CSV harus dipilih.',
            'file_csv.file' => 'File yang diunggah tidak valid.',
            'file_csv.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $rows = $this->readCsvAssoc($request->file('file_csv')->getRealPath());
        if (empty($rows)) {
            return back()->with('error', 'File CSV tagihan kosong.');
        }

        $created = 0;
        $updated = 0;
        $tarifCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $line = (int) ($r['__line'] ?? 0);
                $kode = trim((string) ($r['kode_pembayaran'] ?? ''));
                $npm = trim((string) ($r['npm'] ?? ''));
                $namaPembayaran = trim((string) ($r['nama_pembayaran'] ?? ''));
                if ($kode === '' || $npm === '' || $namaPembayaran === '') {
                    $errors[] = "Baris {$line}: wajib isi kode_pembayaran, npm, nama_pembayaran.";
                    continue;
                }

                $mhs = MahasiswaDetail::where('npm', $npm)->first();
                if (!$mhs) {
                    $errors[] = "Baris {$line}: mahasiswa dengan NPM {$npm} tidak ditemukan (import mahasiswa dulu).";
                    continue;
                }

                $jumlah = $this->parseInt($r['jumlah_tagihan'] ?? '');
                $jatuhTempo = $this->parseDate($r['tanggal_jatuh_tempo'] ?? '');
                $statusDb = $this->normalizeStatus($r['status'] ?? '');
                $semesterLabel = trim((string) ($r['semester_label'] ?? '')) ?: null;

                $tarif = TarifMaster::whereRaw('LOWER(nama_pembayaran) = ?', [strtolower($namaPembayaran)])->first();
                if (!$tarif) {
                    $tarif = TarifMaster::create([
                        'nama_pembayaran' => $namaPembayaran,
                        'nominal' => $jumlah > 0 ? $jumlah : 0,
                        'program_studi' => null,
                        'angkatan' => null,
                        'billing_code' => null,
                        'rule_json' => null,
                    ]);
                    $tarifCreated++;
                }

                $payload = [
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'tarif_id' => $tarif->tarif_id,
                    'kode_pembayaran' => $kode,
                    'jumlah_tagihan' => $jumlah,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'semester_label' => $semesterLabel,
                    'status' => $statusDb,
                ];

                if ($statusDb === 'Lunas') {
                    $payload['total_angsuran'] = $jumlah;
                    $payload['sisa_pokok'] = 0;
                } else {
                    $payload['total_angsuran'] = 0;
                    $payload['sisa_pokok'] = $jumlah;
                }

                $existing = Tagihan::where('kode_pembayaran', $kode)->first();
                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Tagihan::create($payload);
                    $created++;
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()
                    ->with('error', 'Gagal import tagihan: terdapat baris yang tidak valid.')
                    ->with('import_errors', $errors);
            }

            DB::commit();

            return back()->with('success', "Import tagihan selesai. Tagihan baru: {$created}, diperbarui: {$updated}, tarif baru: {$tarifCreated}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import tagihan: ' . $e->getMessage());
        }
    }

    public function importPembayaran(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|max:10240',
        ], [
            'file_csv.required' => 'File CSV harus dipilih.',
            'file_csv.file' => 'File yang diunggah tidak valid.',
            'file_csv.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $rows = $this->readCsvAssoc($request->file('file_csv')->getRealPath());
        if (empty($rows)) {
            return back()->with('error', 'File CSV pembayaran kosong.');
        }

        $created = 0;
        $skippedDuplicate = 0;
        $errors = [];

        $importKasir = $this->getOrCreateImportKasir();

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $line = (int) ($r['__line'] ?? 0);
                $kode = trim((string) ($r['kode_pembayaran'] ?? ''));
                if ($kode === '') {
                    $errors[] = "Baris {$line}: kode_pembayaran kosong.";
                    continue;
                }
                $tagihan = Tagihan::where('kode_pembayaran', $kode)->first();
                if (!$tagihan) {
                    $errors[] = "Baris {$line}: tagihan dengan kode {$kode} tidak ditemukan (import tagihan dulu).";
                    continue;
                }

                $tanggal = $this->parseDate($r['tanggal_bayar'] ?? '') ?: now()->toDateString();
                $metode = trim((string) ($r['metode_pembayaran'] ?? 'Tunai')) ?: 'Tunai';
                $jumlahBayar = $this->parseInt($r['jumlah_bayar'] ?? '');

                $isCicilan = $this->parseBool($r['is_cicilan'] ?? '');
                $dibatalkan = $this->parseBool($r['status_dibatalkan'] ?? '');
                $alasanPembatalan = trim((string) ($r['alasan_pembatalan'] ?? '')) ?: null;

                $exists = Pembayaran::where('tagihan_id', $tagihan->tagihan_id)
                    ->whereDate('tanggal_bayar', $tanggal)
                    ->where('jumlah_bayar', $jumlahBayar)
                    ->where('metode_pembayaran', $metode)
                    ->first();
                if ($exists) {
                    $skippedDuplicate++;
                    continue;
                }

                Pembayaran::create([
                    'tagihan_id' => $tagihan->tagihan_id,
                    'konfirmasi_id' => null,
                    'diverifikasi_oleh' => $importKasir->id,
                    'tanggal_bayar' => $tanggal,
                    'metode_pembayaran' => $metode,
                    'jumlah_bayar' => $jumlahBayar,
                    'is_cicilan' => $isCicilan,
                    'status_dibatalkan' => $dibatalkan,
                    'alasan_pembatalan' => $alasanPembatalan,
                ]);
                $created++;

                $tagihan->refresh();
                $tagihan->updateAngsuran();
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()
                    ->with('error', 'Gagal import pembayaran: terdapat baris yang tidak valid.')
                    ->with('import_errors', $errors);
            }

            DB::commit();
            return back()->with('success', "Import pembayaran selesai. Pembayaran baru: {$created}, duplikat dilewati: {$skippedDuplicate}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import pembayaran: ' . $e->getMessage());
        }
    }

    private function getOrCreateImportKasir(): User
    {
        $username = (string) (env('IMPORT_KASIR_USERNAME') ?: 'kasir_import');
        $user = User::where('username', $username)->first();
        if ($user) {
            return $user;
        }
        return User::create([
            'nama_lengkap' => 'Kasir Import',
            'email' => null,
            'username' => $username,
            'password' => Hash::make(Str::random(16)),
            'role' => 'kasir',
        ]);
    }

    private function readCsvAssoc(string $path): array
    {
        $h = fopen($path, 'r');
        if (!$h) return [];
        $header = fgetcsv($h);
        if (!$header) {
            fclose($h);
            return [];
        }
        $header = array_map(function ($v) {
            $s = trim((string) $v);
            $s = preg_replace('/^\xEF\xBB\xBF/', '', $s); // strip UTF-8 BOM
            return strtolower($s);
        }, $header);

        $rows = [];
        $line = 1;
        while (($row = fgetcsv($h)) !== false) {
            $line++;
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }
            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $assoc[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $assoc['__line'] = $line;
            $rows[] = $assoc;
        }
        fclose($h);
        return $rows;
    }

    private function normalizeStatus(string $s): string
    {
        $s = trim($s);
        if ($s === '') return 'Belum Lunas';
        $lower = strtolower($s);
        if ($lower === 'lunas') return 'Lunas';
        if (str_contains($lower, 'menunggu verifikasi')) return 'Menunggu Verifikasi Transfer';
        if (str_contains($lower, 'menunggu pembayaran tunai')) return 'Menunggu Pembayaran Tunai';
        if (str_contains($lower, 'ditolak')) return 'Ditolak';
        return 'Belum Lunas';
    }

    private function parseDate(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
            return substr($v, 0, 10);
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $v, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return null;
    }

    private function parseInt(string $value): int
    {
        $v = trim($value);
        if ($v === '') return 0;
        $digits = preg_replace('/[^\d-]/', '', $v);
        return (int) ($digits === '' ? 0 : $digits);
    }

    private function parseBool(string $value): bool
    {
        $v = strtolower(trim($value));
        return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'ya';
    }
}

