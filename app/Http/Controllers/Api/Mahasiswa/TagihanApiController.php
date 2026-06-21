<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AktivasiStatus;
use App\Models\Tagihan;
use App\Support\TagihanPayability;
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

        TagihanPayability::applyAktivasiScope($tagihanQuery, $aktivasi);

        if ($request->filled('status')) {
            $this->applyStatusFilter($tagihanQuery, (string) $request->status);
        }

        $tagihan = $tagihanQuery->get()->map(function (Tagihan $t) {
            $status = $t->statusForMobileApi();
            $wajibLunas = $this->tagihanWajibLunas($t);

            return [
                'id' => $t->tagihan_id,
                'kode_pembayaran' => $t->kode_pembayaran,
                'nama_pembayaran' => $t->tarif?->nama_pembayaran,
                'jenis_tampilan' => $t->jenis_tampilan,
                'jumlah_tagihan' => $t->jumlah_tagihan,
                'sisa_pokok' => $t->sisa_pokok,
                'status' => $status['status'],
                'status_kode' => $status['status_kode'],
                'status_verifikasi' => $status['status_verifikasi'],
                'status_warna' => $status['status_warna'],
                'status_warna_bg' => $status['status_warna_bg'],
                'status_warna_teks' => $status['status_warna_teks'],
                'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo,
                'is_wajib_lunas' => $wajibLunas,
                'bisa_cicil' => ! $wajibLunas,
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
            $message = TagihanPayability::violationMessage(
                $tagihan,
                (int) $user->mahasiswaDetail->mahasiswa_id,
            );
            if ($message) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
        }

        $settings = Setting::getCachedMap();

        $lastKonfirmasi = $tagihan->konfirmasi;
        $status = $tagihan->statusForMobileApi();
        $wajibLunas = $this->tagihanWajibLunas($tagihan);

        return response()->json([
            'success' => true,
            'tagihan' => [
                'id' => $tagihan->tagihan_id,
                'kode_pembayaran' => $tagihan->kode_pembayaran,
                'nama_pembayaran' => $tagihan->tarif?->nama_pembayaran,
                'jenis_tampilan' => $tagihan->jenis_tampilan,
                'deskripsi' => $tagihan->tarif?->keterangan ?? null,
                'jumlah_tagihan' => $tagihan->jumlah_tagihan,
                'sisa_pokok' => $tagihan->sisa_pokok,
                'status' => $status['status'],
                'status_kode' => $status['status_kode'],
                'status_verifikasi' => $status['status_verifikasi'],
                'status_warna' => $status['status_warna'],
                'status_warna_bg' => $status['status_warna_bg'],
                'status_warna_teks' => $status['status_warna_teks'],
                'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                'is_wajib_lunas' => $wajibLunas,
                'bisa_cicil' => ! $wajibLunas,
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

        if (! $this->tagihanWajibLunas($tagihan)) {
            $allowed[] = 'cicil';
        }

        return $allowed;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Tagihan>|\Illuminate\Database\Eloquent\Relations\Relation  $query
     */
    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'Belum Dibayarkan') {
            $query->where('status', '=', 'Belum Lunas')
                ->where(function ($q) {
                    $q->whereNull('total_angsuran')->orWhere('total_angsuran', '<=', 0);
                });

            return;
        }

        $query->where('status', '=', $status);
    }

    private function tagihanWajibLunas(Tagihan $tagihan): bool
    {
        return method_exists($tagihan, 'isWajibLunas') && $tagihan->isWajibLunas();
    }
}

