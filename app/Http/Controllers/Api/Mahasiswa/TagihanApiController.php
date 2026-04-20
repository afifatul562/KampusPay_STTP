<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AktivasiStatus;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagihanApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->load('mahasiswaDetail');

        if (!$user->mahasiswaDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $user->mahasiswaDetail->mahasiswa_id)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        $tagihanQuery = $user->mahasiswaDetail->tagihan()
            ->with('tarif', 'konfirmasi', 'pembayaran', 'mahasiswa')
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        $today = now()->toDateString();
        $tagihanQuery->where(function ($q) use ($today) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $today);
        });

        if ($aktivasi && $aktivasi->status === 'bss') {
            $tagihanQuery->where('is_bss', true);
        } else {
            $tagihanQuery->where(function ($q) {
                $q->whereNull('is_bss')->orWhere('is_bss', false);
            });
        }

        if ($request->filled('status')) {
            $tagihanQuery->where('status', $request->status);
        }

        $tagihan = $tagihanQuery->get()->map(function (Tagihan $t) {
            return [
                'id' => $t->tagihan_id,
                'kode_pembayaran' => $t->kode_pembayaran,
                'nama_pembayaran' => $t->tarif?->nama_pembayaran,
                'jenis_tampilan' => $t->jenis_tampilan,
                'jumlah_tagihan' => $t->jumlah_tagihan,
                'sisa_pokok' => $t->sisa_pokok,
                'status' => $t->status,
                'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo,
                'is_wajib_lunas' => (bool) ($t->is_wajib_lunas ?? false),
                'bisa_cicil' => method_exists($t, 'isWajibLunas') ? !$t->isWajibLunas() : !($t->is_wajib_lunas ?? false),
            ];
        });

        return response()->json([
            'success' => true,
            'tagihan' => $tagihan,
        ]);
    }

    public function show(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        $tagihan->load('tarif', 'konfirmasi', 'pembayaran', 'mahasiswa.user');

        $user = Auth::user();
        if ($user?->isMahasiswa() && $user->mahasiswaDetail) {
            $semester = Setting::currentSemesterLabel();
            $aktivasi = AktivasiStatus::query()
                ->where('mahasiswa_id', $user->mahasiswaDetail->mahasiswa_id)
                ->where('semester_label', $semester)
                ->latest()
                ->first();

            if ($aktivasi && $aktivasi->status === 'bss' && !$tagihan->is_bss) {
                return response()->json(['success' => false, 'message' => 'Status BSS: hanya tagihan BSS yang ditampilkan.'], 403);
            }
            if ((!$aktivasi || $aktivasi->status === 'aktif') && $tagihan->is_bss) {
                return response()->json(['success' => false, 'message' => 'Tagihan BSS tidak ditampilkan saat status aktif.'], 403);
            }

            $today = now()->toDateString();
            if ($tagihan->available_from && (string) $tagihan->available_from->toDateString() > $today) {
                return response()->json(['success' => false, 'message' => 'Tagihan ini belum tersedia.'], 403);
            }
        }

        $settings = Setting::getCachedMap();

        // Relasi `konfirmasi` pada model Tagihan sudah di-set sebagai `hasOne()->latestOfMany()`
        // sehingga otomatis mengembalikan SATU konfirmasi terbaru.
        // Jadi tidak perlu lagi memanggil `sortByDesc()` (yang hanya ada di Collection, bukan di Model).
        $lastKonfirmasi = $tagihan->konfirmasi;

        return response()->json([
            'success' => true,
            'tagihan' => [
                'id' => $tagihan->tagihan_id,
                'kode_pembayaran' => $tagihan->kode_pembayaran,
                'nama_pembayaran' => $tagihan->tarif?->nama_pembayaran,
                'jenis_tampilan' => $tagihan->jenis_tampilan,
                'deskripsi' => $tagihan->tarif->keterangan ?? null,
                'jumlah_tagihan' => $tagihan->jumlah_tagihan,
                'sisa_pokok' => $tagihan->sisa_pokok,
                'status' => $tagihan->status,
                'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                'is_wajib_lunas' => (bool) ($tagihan->is_wajib_lunas ?? false),
                'bisa_cicil' => method_exists($tagihan, 'isWajibLunas') ? !$tagihan->isWajibLunas() : !($tagihan->is_wajib_lunas ?? false),
                'metode_yang_diizinkan' => $this->resolveMetodeDiizinkan($tagihan),
                'konfirmasi_terakhir' => $lastKonfirmasi ? [
                    'status_verifikasi' => $lastKonfirmasi->status_verifikasi,
                    // Di database kolomnya bernama 'alasan_ditolak'
                    'alasan_penolakan' => $lastKonfirmasi->alasan_ditolak ?? null,
                    'tanggal' => optional($lastKonfirmasi->created_at)->toDateTimeString(),
                ] : null,
                // Rekening tujuan: dukung 2 skema key:
                // - Versi lama: payment_bank_name, payment_account_number, payment_account_name
                // - Versi baru (di halaman Pengaturan): bank_name, account_number, account_holder
                'rekening_tujuan' => [
                    'bank' => $settings['payment_bank_name']
                        ?? $settings['bank_name']
                        ?? null,
                    'no_rekening' => $settings['payment_account_number']
                        ?? $settings['account_number']
                        ?? null,
                    'atas_nama' => $settings['payment_account_name']
                        ?? $settings['account_holder']
                        ?? null,
                ],
            ],
        ]);
    }

    protected function resolveMetodeDiizinkan(Tagihan $tagihan): array
    {
        $allowed = ['tunai', 'transfer'];

        if (method_exists($tagihan, 'isWajibLunas')) {
            if (!$tagihan->isWajibLunas()) {
                $allowed[] = 'cicil';
            }
        } elseif (!($tagihan->is_wajib_lunas ?? false)) {
            $allowed[] = 'cicil';
        }

        return $allowed;
    }
}

