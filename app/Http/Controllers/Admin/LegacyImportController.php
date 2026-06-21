<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanSlipAlias;
use App\Models\TarifMaster;
use App\Models\User;
use App\Support\KampusTarifCatalog;
use App\Support\LegacyTagihanGrouping;
use App\Support\SemesterPerkuliahan;
use App\Support\TagihanDedup;
use App\Support\TarifBillingRules;
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
            'gabung_slip_access' => 'nullable|boolean',
        ], [
            'file_csv.required' => 'File CSV harus dipilih.',
            'file_csv.file' => 'File yang diunggah tidak valid.',
            'file_csv.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $rows = $this->readCsvAssoc($request->file('file_csv')->getRealPath());
        if (empty($rows)) {
            return back()->with('error', 'File CSV tagihan kosong.');
        }

        $gabungSlip = $request->boolean('gabung_slip_access', true);

        if ($gabungSlip) {
            return $this->importTagihanGrouped($rows);
        }

        return $this->importTagihanPerSlip($rows);
    }

    /**
     * Mode lama: 1 baris CSV = 1 tagihan (checkbox grouping OFF).
     */
    private function importTagihanPerSlip(array $rows)
    {
        $created = 0;
        $updated = 0;
        $tarifCreated = 0;
        $pembayaranCreated = 0;
        $errors = [];
        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $result = $this->processTagihanRowPerSlip($r, $errors);
                if ($result === null) {
                    continue;
                }

                $created += $result['created'];
                $updated += $result['updated'];
                $tarifCreated += $result['tarif_created'];
                $pembayaranCreated += $result['pembayaran_created'];
            }

            if (! empty($errors)) {
                return $this->rollbackWithImportErrors('tagihan', $errors);
            }

            DB::commit();

            return back()->with('success', "Import tagihan selesai. Tagihan baru: {$created}, diperbarui: {$updated}, tarif baru: {$tarifCreated}, riwayat bayar: {$pembayaranCreated}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal import tagihan: '.$e->getMessage());
        }
    }

    /**
     * Mode Access: gabung slip per npm + jenis + semester → 1 tagihan + alias kode slip.
     */
    private function importTagihanGrouped(array $rows)
    {
        $errors = [];
        $warnings = [];
        $parsedRows = [];
        $kodeInFile = [];

        foreach ($rows as $r) {
            $line = (int) ($r['__line'] ?? 0);
            $kode = $this->normalizeKodePembayaran($r['kode_pembayaran'] ?? '');
            $npm = $this->normalizeNpm($r['npm'] ?? $r['nomor_bp'] ?? '');
            $namaPembayaran = trim((string) ($r['nama_pembayaran'] ?? ''));

            if ($kode === '' || $npm === '' || $namaPembayaran === '') {
                $errors[] = "Baris {$line}: wajib isi kode_pembayaran, npm, nama_pembayaran.";
                continue;
            }

            if (isset($kodeInFile[$kode])) {
                $errors[] = "Baris {$line}: kode_pembayaran '{$kode}' duplikat di file (baris {$kodeInFile[$kode]}).";
                continue;
            }
            $kodeInFile[$kode] = $line;

            if (! $this->looksLikeNpm($npm)) {
                $errors[] = "Baris {$line}: NPM tidak valid ('{$npm}').";
                continue;
            }

            $mhs = MahasiswaDetail::query()->where('npm', $npm)->first();
            if (! $mhs) {
                $errors[] = "Baris {$line}: mahasiswa NPM {$npm} tidak ditemukan — import mahasiswa dulu.";
                continue;
            }

            $semesterPerkuliahan = $this->resolveSemesterPerkuliahanForImport($r);
            if (LegacyTagihanGrouping::usesSemesterInGroupKey($namaPembayaran) && $semesterPerkuliahan === null) {
                $errors[] = "Baris {$line}: keterangan semester wajib untuk '{$namaPembayaran}' (contoh: Semester VII).";
                continue;
            }

            $parsedRows[] = [
                'line' => $line,
                'kode' => $kode,
                'npm' => $npm,
                'mahasiswa' => $mhs,
                'nama_pembayaran' => $namaPembayaran,
                'jumlah_slip' => $this->parseInt($r['jumlah_tagihan'] ?? ''),
                'jatuh_tempo' => $this->resolveJatuhTempoForTagihanImport($r),
                'semester_label' => trim((string) ($r['semester_label'] ?? '')) ?: null,
                'semester_perkuliahan' => $semesterPerkuliahan,
                'group_key' => LegacyTagihanGrouping::buildGroupKey($npm, $namaPembayaran, $semesterPerkuliahan),
            ];
        }

        if (! empty($errors)) {
            return $this->rollbackWithImportErrors('tagihan', $errors);
        }

        $groups = [];
        foreach ($parsedRows as $row) {
            $groups[$row['group_key']][] = $row;
        }

        $created = 0;
        $updated = 0;
        $tarifCreated = 0;
        $slipsMerged = 0;
        $aliasesSaved = 0;

        DB::beginTransaction();
        try {
            foreach ($groups as $groupKey => $groupRows) {
                usort($groupRows, function (array $a, array $b) {
                    $cmp = strcmp($a['jatuh_tempo'], $b['jatuh_tempo']);
                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    return $a['line'] <=> $b['line'];
                });

                $jatuhTempos = array_unique(array_column($groupRows, 'jatuh_tempo'));
                if (count($jatuhTempos) > 1) {
                    $warnings[] = "Grup {$groupKey}: jatuh tempo berbeda — dipakai yang terawal ({$groupRows[0]['jatuh_tempo']}).";
                }

                $primary = $groupRows[0];
                $slipKodes = array_column($groupRows, 'kode');
                $slipsMerged += max(0, count($groupRows) - 1);

                $tarif = $this->findOrCreateTarifForImport(
                    $primary['nama_pembayaran'],
                    max(array_column($groupRows, 'jumlah_slip')),
                    $tarifCreated,
                    $warnings,
                    $groupKey,
                );

                $jumlahTagihan = $this->resolveJumlahTagihanForImport($tarif, $groupRows);
                $jumlahDariCsv = max(array_column($groupRows, 'jumlah_slip'));
                if ($jumlahDariCsv > 0 && (int) $tarif->nominal > 0 && $jumlahDariCsv !== (int) $tarif->nominal) {
                    $warnings[] = "Grup {$groupKey}: jumlah tagihan mengikuti CSV (Rp "
                        .number_format($jumlahDariCsv, 0, ',', '.')
                        .'), bukan nominal tarif master (Rp '
                        .number_format((int) $tarif->nominal, 0, ',', '.').').';
                } elseif ((float) $tarif->nominal <= 0 && $jumlahTagihan > 0) {
                    $warnings[] = "Grup {$groupKey}: nominal tarif kosong — jumlah tagihan memakai CSV Rp ".number_format($jumlahTagihan, 0, ',', '.').'.';
                }

                $payload = [
                    'mahasiswa_id' => $primary['mahasiswa']->mahasiswa_id,
                    'tarif_id' => $tarif->tarif_id,
                    'kode_pembayaran' => $primary['kode'],
                    'jumlah_tagihan' => $jumlahTagihan,
                    'tanggal_jatuh_tempo' => $primary['jatuh_tempo'],
                    'semester_label' => $primary['semester_label'],
                    'semester_perkuliahan' => $primary['semester_perkuliahan'],
                    'status' => 'Belum Lunas',
                    'total_angsuran' => 0,
                    'sisa_pokok' => $jumlahTagihan,
                ];

                $tagihan = $this->findExistingGroupedTagihan($primary, $tarif);
                if ($tagihan) {
                    $tagihan->fill($payload);
                    $tagihan->save();
                    $tagihan = $tagihan->fresh();
                    $updated++;
                } else {
                    $tagihan = Tagihan::create($payload);
                    $created++;
                }

                $aliasesSaved += $this->syncSlipAliases($tagihan, $slipKodes);
            }

            DB::commit();

            $warningText = empty($warnings) ? '' : ' Peringatan: '.implode(' | ', array_slice($warnings, 0, 2)).(count($warnings) > 2 ? ' (+' . (count($warnings) - 2) . ' lainnya)' : '');

            return back()->with('success', "Import tagihan (gabung slip) selesai. Tagihan baru: {$created}, diperbarui: {$updated}, slip digabung: {$slipsMerged}, alias slip: {$aliasesSaved}, tarif baru: {$tarifCreated}.{$warningText}");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal import tagihan: '.$e->getMessage());
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
        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $line = (int) ($r['__line'] ?? 0);
                $kode = $this->normalizeKodePembayaran($r['kode_pembayaran'] ?? '');
                if ($kode === '') {
                    $errors[] = "Baris {$line}: kode_pembayaran kosong.";
                    continue;
                }

                $tagihan = $this->resolveTagihanBySlipKode($kode);
                if (! $tagihan) {
                    $errors[] = "Baris {$line}: tagihan/slip kode {$kode} tidak ditemukan (import tagihan dulu).";
                    continue;
                }

                $tanggal = $this->resolveTanggalBayarForImport($r);
                if ($tanggal === null) {
                    $raw = trim((string) ($r['tanggal_bayar'] ?? $r['tanggal'] ?? ''));
                    $errors[] = "Baris {$line}: tanggal_bayar tidak valid atau kosong ('{$raw}'). Gunakan YYYY-MM-DD atau 1-Jul-25.";
                    continue;
                }

                $metode = trim((string) ($r['metode_pembayaran'] ?? 'Tunai')) ?: 'Tunai';
                $jumlahBayar = $this->parseInt($r['jumlah_bayar'] ?? '');

                $isCicilanRaw = trim((string) ($r['is_cicilan'] ?? ''));
                $isCicilan = $isCicilanRaw === ''
                    ? ($jumlahBayar > 0 && $jumlahBayar < (int) $tagihan->jumlah_tagihan)
                    : $this->parseBool($isCicilanRaw);
                $dibatalkan = $this->parseBool($r['status_dibatalkan'] ?? '');
                $alasanPembatalan = trim((string) ($r['alasan_pembatalan'] ?? '')) ?: null;

                $exists = Pembayaran::query()
                    ->where('tagihan_id', $tagihan->tagihan_id)
                    ->whereDate('tanggal_bayar', '=', $tanggal, 'and')
                    ->where('jumlah_bayar', $jumlahBayar)
                    ->where('metode_pembayaran', $metode)
                    ->first();
                if ($exists) {
                    $tagihan->refresh();
                    $tagihan->updateAngsuran();
                    $skippedDuplicate++;
                    continue;
                }

                $kasir = $this->resolveKasirForImport($r, $line, $errors);
                if ($kasir === null) {
                    continue;
                }

                Pembayaran::create([
                    'tagihan_id' => $tagihan->tagihan_id,
                    'konfirmasi_id' => null,
                    'diverifikasi_oleh' => $kasir->id,
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

            if (! empty($errors)) {
                return $this->rollbackWithImportErrors('pembayaran', $errors);
            }

            DB::commit();

            return back()->with('success', "Import pembayaran selesai. Pembayaran baru: {$created}, duplikat dilewati: {$skippedDuplicate}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal import pembayaran: '.$e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $errors
     * @return array{created:int,updated:int,tarif_created:int,pembayaran_created:int}|null
     */
    private function processTagihanRowPerSlip(array $row, array &$errors): ?array
    {
        $line = (int) ($row['__line'] ?? 0);
        $kode = $this->normalizeKodePembayaran($row['kode_pembayaran'] ?? '');
        $npm = $this->normalizeNpm($row['npm'] ?? $row['nomor_bp'] ?? '');
        $namaPembayaran = trim((string) ($row['nama_pembayaran'] ?? ''));
        if ($kode === '' || $npm === '' || $namaPembayaran === '') {
            $errors[] = "Baris {$line}: wajib isi kode_pembayaran, npm, nama_pembayaran.";
            return null;
        }

        if (! $this->looksLikeNpm($npm)) {
            $errors[] = "Baris {$line}: NPM tidak valid ('{$npm}').";
            return null;
        }

        $mhs = MahasiswaDetail::query()->where('npm', $npm)->first();
        if (! $mhs) {
            $errors[] = "Baris {$line}: mahasiswa NPM {$npm} tidak ditemukan — import mahasiswa dulu.";
            return null;
        }

        $jumlah = $this->parseInt($row['jumlah_tagihan'] ?? '');
        $jatuhTempo = $this->resolveJatuhTempoForTagihanImport($row);
        $statusDb = $this->normalizeStatus($row['status'] ?? '');
        $semesterLabel = trim((string) ($row['semester_label'] ?? '')) ?: null;
        $semesterPerkuliahan = $this->resolveSemesterPerkuliahanForImport($row);

        $tarifCreated = 0;
        $warnings = [];
        $tarif = $this->findOrCreateTarifForImport($namaPembayaran, $jumlah, $tarifCreated, $warnings, "baris {$line}");

        $payload = [
            'mahasiswa_id' => $mhs->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => $kode,
            'jumlah_tagihan' => $jumlah,
            'tanggal_jatuh_tempo' => $jatuhTempo,
            'semester_label' => $semesterLabel,
            'semester_perkuliahan' => $semesterPerkuliahan,
            'status' => $statusDb,
        ];

        if ($statusDb === 'Lunas') {
            $payload['total_angsuran'] = $jumlah;
            $payload['sisa_pokok'] = 0;
        } else {
            $payload['total_angsuran'] = 0;
            $payload['sisa_pokok'] = $jumlah;
        }

        $created = 0;
        $updated = 0;
        $existing = Tagihan::query()->where('kode_pembayaran', $kode)->first();
        if ($existing) {
            $existing->fill($payload);
            $existing->save();
            $tagihan = $existing->fresh();
            $updated = 1;
        } else {
            $tagihan = Tagihan::create($payload);
            $created = 1;
        }

        $pembayaranCreated = 0;
        if ($statusDb === 'Lunas') {
            $kasir = $this->resolveKasirForImport($row, $line, $errors);
            if ($kasir === null) {
                return null;
            }
            $pembayaranCreated = $this->ensurePembayaranForImportedTagihan($tagihan, $row, $kasir);
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'tarif_created' => $tarifCreated,
            'pembayaran_created' => $pembayaranCreated,
        ];
    }

    private function resolveTagihanBySlipKode(string $kode): ?Tagihan
    {
        $direct = Tagihan::query()->where('kode_pembayaran', $kode)->first();
        if ($direct) {
            return $direct;
        }

        $alias = TagihanSlipAlias::query()->where('kode_slip', $kode)->first();
        if (! $alias) {
            return null;
        }

        return Tagihan::query()->find($alias->tagihan_id);
    }

    /**
     * @param  array<string, mixed>  $primary
     */
    private function findExistingGroupedTagihan(array $primary, TarifMaster $tarif): ?Tagihan
    {
        $byKode = Tagihan::query()->where('kode_pembayaran', $primary['kode'])->first();
        if ($byKode) {
            return $byKode;
        }

        $tarifIds = TagihanDedup::equivalentTarifIds($primary['nama_pembayaran']);
        $query = Tagihan::query()
            ->where('mahasiswa_id', $primary['mahasiswa']->mahasiswa_id)
            ->whereIn('tarif_id', $tarifIds, 'and', false);

        if (LegacyTagihanGrouping::usesSemesterInGroupKey($primary['nama_pembayaran'])) {
            $query->where('semester_perkuliahan', $primary['semester_perkuliahan']);
        }

        return $query->orderByDesc('tagihan_id')->first();
    }

    private function canonicalTarifNameForImport(string $namaPembayaran): string
    {
        $lower = mb_strtolower(trim($namaPembayaran));

        if (KampusTarifCatalog::isPpmPkkmbTarifName($lower)) {
            return KampusTarifCatalog::NAMA_PPMB_ALMAMATER_CB;
        }

        if (TarifBillingRules::isUasTarifName($lower)) {
            return KampusTarifCatalog::NAMA_UAS;
        }

        return trim($namaPembayaran);
    }

    /**
     * @param  list<string>  $warnings
     */
    private function findOrCreateTarifForImport(
        string $namaPembayaran,
        int $fallbackNominal,
        int &$tarifCreated,
        array &$warnings,
        string $context,
    ): TarifMaster {
        $canonicalName = $this->canonicalTarifNameForImport($namaPembayaran);

        $tarif = TarifMaster::query()
            ->where(DB::raw('LOWER(nama_pembayaran)'), '=', strtolower($canonicalName))
            ->where(function ($q) {
                $q->whereNull('program_studi')->orWhere('program_studi', '');
            })
            ->where(function ($q) {
                $q->whereNull('angkatan')->orWhere('angkatan', '');
            })
            ->first();

        if ($tarif) {
            return $tarif;
        }

        $ruleJson = null;
        if ($canonicalName !== trim($namaPembayaran)) {
            $warnings[] = "Grup {$context}: nama '{$namaPembayaran}' dipetakan ke tarif kanonik '{$canonicalName}'.";
        }
        if ($canonicalName === KampusTarifCatalog::NAMA_PPMB_ALMAMATER_CB) {
            $ruleJson = ['only_semester_in' => [1]];
        }

        $tarif = TarifMaster::create([
            'nama_pembayaran' => $canonicalName,
            'nominal' => $fallbackNominal > 0 ? $fallbackNominal : 0,
            'program_studi' => null,
            'angkatan' => null,
            'billing_code' => null,
            'rule_json' => $ruleJson,
        ]);
        $tarifCreated++;
        $warnings[] = "Tarif '{$canonicalName}' baru dibuat — jalankan php artisan tarif:sync-kampus jika perlu.";

        return $tarif;
    }

    /**
     * @param  list<string>  $slipKodes
     */
    private function syncSlipAliases(Tagihan $tagihan, array $slipKodes): int
    {
        $saved = 0;
        foreach (array_unique($slipKodes) as $kode) {
            TagihanSlipAlias::query()->updateOrCreate(
                ['kode_slip' => $kode],
                ['tagihan_id' => $tagihan->tagihan_id]
            );
            $saved++;
        }

        return $saved;
    }

    /**
     * @param  list<array<string, mixed>>  $groupRows
     */
    private function resolveJumlahTagihanForImport(TarifMaster $tarif, array $groupRows): int
    {
        $maxSlip = max(array_map(
            static fn (array $row) => (int) ($row['jumlah_slip'] ?? 0),
            $groupRows,
        ));

        // Import data lama: jumlah di CSV adalah sumber utama (bukan nominal tarif master).
        if ($maxSlip > 0) {
            return $maxSlip;
        }

        $nominal = (int) $tarif->nominal;

        return $nominal > 0 ? $nominal : 0;
    }

    /**
     * @param  list<string>  $errors
     */
    private function rollbackWithImportErrors(string $type, array $errors)
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        $preview = implode(' | ', array_slice($errors, 0, 3));
        $suffix = count($errors) > 3 ? ' (+' . (count($errors) - 3) . ' baris lainnya)' : '';

        return back()
            ->with('error', "Gagal import {$type}: ".count($errors)." baris tidak valid. {$preview}{$suffix}")
            ->with('import_errors', $errors);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveKasirForImport(array $row, int $line, array &$errors): ?User
    {
        $username = $this->resolveKasirUsernameFromRow($row);
        if ($username === '') {
            return $this->getOrCreateImportKasir();
        }

        $kasir = User::query()
            ->where('role', 'kasir')
            ->where(function ($q) use ($username) {
                $q->where('username', $username)
                    ->orWhereRaw('LOWER(username) = ?', [mb_strtolower($username)]);
            })
            ->first();

        if ($kasir) {
            return $kasir;
        }

        $errors[] = "Baris {$line}: kasir username '{$username}' tidak ditemukan. Daftarkan kasir di Admin → Users terlebih dahulu.";

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveKasirUsernameFromRow(array $row): string
    {
        foreach (['kasir_username', 'username_kasir', 'kasir', 'verifikator'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function getOrCreateImportKasir(): User
    {
        $username = (string) config('accounts.import_kasir_username', 'kasir_import');
        $user = User::query()->where('username', $username)->first();
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
        if (! $h) {
            return [];
        }

        $header = fgetcsv($h);
        if (! $header) {
            fclose($h);

            return [];
        }

        $header = array_map(fn ($v) => $this->normalizeCsvHeaderKey((string) $v), $header);

        $rows = [];
        $line = 1;
        while (($row = fgetcsv($h)) !== false) {
            $line++;
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $assoc['__line'] = $line;
            $rows[] = $assoc;
        }

        fclose($h);

        return $rows;
    }

    private function normalizeCsvHeaderKey(string $raw): string
    {
        $s = mb_strtolower(trim($raw));
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = preg_replace('/\s+/', '_', $s) ?? $s;

        $aliases = [
            'nomor_bp' => 'npm',
            'nomor_slip' => 'kode_pembayaran',
            'no_slip' => 'kode_pembayaran',
            'nama_mahasiswa' => 'nama_lengkap',
            'pembayaran' => 'nama_pembayaran',
            'jenis_pembayaran' => 'nama_pembayaran',
            'jumlah' => 'jumlah_tagihan',
            'tgl_bayar' => 'tanggal_bayar',
            'tanggal_setor' => 'tanggal_bayar',
            'tgl_jatuh_tempo' => 'tanggal_jatuh_tempo',
            'catatan' => 'keterangan',
            'username_kasir' => 'kasir_username',
            'kasir' => 'kasir_username',
            'verifikator' => 'kasir_username',
        ];

        return $aliases[$s] ?? $s;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveSemesterPerkuliahanForImport(array $row): ?int
    {
        foreach (['keterangan', 'semester_perkuliahan', 'semester'] as $key) {
            $parsed = SemesterPerkuliahan::parse((string) ($row[$key] ?? ''));
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveTanggalBayarForImport(array $row): ?string
    {
        foreach (['tanggal_bayar', 'tanggal', 'tgl_bayar'] as $key) {
            $parsed = $this->parseDate((string) ($row[$key] ?? ''));
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveJatuhTempoForTagihanImport(array $row): string
    {
        foreach (['tanggal_jatuh_tempo', 'tanggal', 'tgl_jatuh_tempo'] as $key) {
            $parsed = $this->parseDate((string) ($row[$key] ?? ''));
            if ($parsed !== null) {
                return $parsed;
            }
        }

        $fallbackDays = (int) config('academic.default_due_in_days', 30);

        return now()->addDays($fallbackDays)->toDateString();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function ensurePembayaranForImportedTagihan(Tagihan $tagihan, array $row, User $kasir): int
    {
        $tanggal = $this->resolveTanggalBayarForImport($row);
        if ($tanggal === null) {
            return 0;
        }

        $jumlahBayar = (int) $tagihan->jumlah_tagihan;
        $metode = trim((string) ($row['metode_pembayaran'] ?? 'Tunai')) ?: 'Tunai';

        $exists = Pembayaran::query()
            ->where('tagihan_id', $tagihan->tagihan_id)
            ->whereDate('tanggal_bayar', '=', $tanggal, 'and')
            ->where('jumlah_bayar', $jumlahBayar)
            ->exists();

        if ($exists) {
            return 0;
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'konfirmasi_id' => null,
            'diverifikasi_oleh' => $kasir->id,
            'tanggal_bayar' => $tanggal,
            'metode_pembayaran' => $metode,
            'jumlah_bayar' => $jumlahBayar,
            'is_cicilan' => $this->parseBool((string) ($row['is_cicilan'] ?? '')),
            'status_dibatalkan' => false,
            'alasan_pembatalan' => null,
        ]);

        $tagihan->refresh();
        $tagihan->updateAngsuran();

        return 1;
    }

    private function normalizeStatus(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return 'Belum Lunas';
        }

        $lower = strtolower($s);
        if ($lower === 'lunas') {
            return 'Lunas';
        }
        if (str_contains($lower, 'menunggu verifikasi')) {
            return 'Menunggu Verifikasi Transfer';
        }
        if (str_contains($lower, 'menunggu pembayaran tunai')) {
            return 'Menunggu Pembayaran Tunai';
        }
        if (str_contains($lower, 'ditolak')) {
            return 'Ditolak';
        }

        return 'Belum Lunas';
    }

    private function parseDate(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $v, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        $ts = strtotime($v);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function parseInt(string $value): int
    {
        $v = trim($value);
        if ($v === '') {
            return 0;
        }

        $digits = preg_replace('/[^\d-]/', '', $v);

        return (int) ($digits === '' ? 0 : $digits);
    }

    private function parseBool(string $value): bool
    {
        $v = strtolower(trim($value));

        return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'ya';
    }

    private function normalizeNpm(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (preg_match('/^[\d,.]+[eE][+\-]?\d+$/', $value)) {
            $value = sprintf('%.0f', (float) str_replace(',', '.', $value));
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function looksLikeNpm(string $npm): bool
    {
        return $npm !== '' && preg_match('/^\d{8,}$/', $npm) === 1;
    }

    private function normalizeKodePembayaran(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (preg_match('/^[\d,.]+[eE][+\-]?\d+$/', $value)) {
            $value = sprintf('%.0f', (float) str_replace(',', '.', $value));
        }

        return $value;
    }
}
