<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\KonfirmasiPembayaran;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Cari mahasiswa berdasarkan NPM dan ambil tagihan yang masih bisa diproses.
     */
    public function searchMahasiswa(Request $request)
    {
        $request->validate(['npm' => 'required|string|exists:mahasiswa_detail,npm']);

        $mahasiswa = MahasiswaDetail::with([
            'user',
            'tagihan' => fn ($query) => $query->where('status', '!=', 'Lunas'),
            'tagihan.tarif',
            'tagihan.pembayaran',
            'tagihan.pembayaranAll',
        ])->where('npm', $request->npm)->first();

        if ($mahasiswa && $mahasiswa->tagihan) {
            $allTagihan = Tagihan::with('pembayaran', 'pembayaranAll', 'tarif')
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->get();

            $mahasiswa->tagihan = $mahasiswa->tagihan->filter(function ($tagihan) use ($allTagihan) {
                if (! $tagihan->masihTunggakan()) {
                    return false;
                }

                $pembayaran = $tagihan->pembayaran;
                if ($pembayaran && ($pembayaran->status_dibatalkan ?? false)) {
                    $hasReplacement = $allTagihan->contains(function ($otherTagihan) use ($tagihan) {
                        if ($otherTagihan->tagihan_id === $tagihan->tagihan_id) {
                            return false;
                        }

                        if ($otherTagihan->tarif_id !== $tagihan->tarif_id) {
                            return false;
                        }

                        if ($otherTagihan->status === 'Lunas') {
                            return true;
                        }

                        if ($otherTagihan->pembayaran) {
                            $otherIsCancelled = $otherTagihan->pembayaran->status_dibatalkan ?? false;
                            if (! $otherIsCancelled) {
                                return true;
                            }
                        }

                        return false;
                    });

                    if ($hasReplacement) {
                        return false;
                    }
                }

                return true;
            })->values();
        }

        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $mahasiswa]);
    }

    /**
     * Memproses pembayaran untuk satu atau lebih tagihan.
     * Termasuk logika "Otomatis Hilang" (Override).
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'tagihan_ids'   => 'required|array|min:1',
            'tagihan_ids.*' => ['required', 'integer', Rule::exists('tagihan', 'tagihan_id')->whereNot('status', 'Lunas')],
            'metode_pembayaran' => 'required|string|in:Tunai,Transfer Bank Nagari,Transfer',
            'cicilan' => 'sometimes|array',
            'cicilan.*.jumlah_bayar' => 'required_with:cicilan|numeric|min:1',
            'cicilan.*.is_cicilan' => 'sometimes|boolean',
        ]);

        $tagihanCollection = Tagihan::with(['mahasiswa.user', 'tarif'])
            ->whereIn('tagihan_id', $validated['tagihan_ids'])
            ->get();

        if ($tagihanCollection->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan.'
            ], 404);
        }

        if ($tagihanCollection->pluck('mahasiswa_id')->unique()->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih tagihan dari mahasiswa yang sama untuk satu transaksi tunai.'
            ], 422);
        }

        $mahasiswa = optional($tagihanCollection->first()->mahasiswa);
        $kasir = Auth::user();
        $kasirId = $kasir->id;
        $tanggalBayarUtc = now();
        $tanggalBayarDisplay = $tanggalBayarUtc->clone()->setTimezone(config('app.timezone'));
        $receiptItems = [];

        try {
            DB::transaction(function () use ($validated, $tagihanCollection, &$receiptItems, $kasirId, $tanggalBayarUtc) {
                foreach ($validated['tagihan_ids'] as $tagihanId) {
                    // Kunci baris tagihan agar tidak bisa diproses paralel (double submit / request dobel).
                    $tagihan = Tagihan::query()
                        ->where('tagihan_id', $tagihanId)
                        ->lockForUpdate()
                        ->first();
                    if (!$tagihan) {
                        throw new \RuntimeException('Tagihan tidak ditemukan saat proses pembayaran.');
                    }

                    // Hitung sisa berdasarkan total pembayaran non-dibatalkan saat ini (source of truth).
                    $totalTerverifikasi = (float) Pembayaran::query()
                        ->where('tagihan_id', $tagihan->tagihan_id)
                        ->where(function ($q) {
                            $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
                        })
                        ->sum('jumlah_bayar');
                    $sisaPokok = max(0.0, (float) $tagihan->jumlah_tagihan - $totalTerverifikasi);

                    if ($sisaPokok <= 0.00001 || $tagihan->status === 'Lunas') {
                        throw new \RuntimeException("Tagihan {$tagihan->kode_pembayaran} sudah lunas / tidak memiliki sisa pembayaran.");
                    }

                    KonfirmasiPembayaran::query()
                        ->where('tagihan_id', $tagihan->tagihan_id)
                        ->where('status_verifikasi', 'Menunggu Verifikasi')
                        ->update([
                            'status_verifikasi' => 'Dibatalkan (Oleh Kasir Tunai)'
                        ]);

                    $cicilanData = $validated['cicilan'][$tagihanId] ?? null;
                    $isCicilan = $cicilanData && isset($cicilanData['is_cicilan']) && $cicilanData['is_cicilan'];
                    $jumlahBayar = $cicilanData && isset($cicilanData['jumlah_bayar'])
                        ? $cicilanData['jumlah_bayar']
                        : $sisaPokok;

                    if ($isCicilan) {
                        $tagihan->loadMissing('tarif');
                        if ($tagihan->isWajibLunas()) {
                            throw new \RuntimeException("Tagihan {$tagihan->kode_pembayaran} wajib lunas dan tidak dapat dicicil.");
                        }

                        $jumlahBayar = (float) $jumlahBayar;
                        if (!$tagihan->cicilanAmountValid($jumlahBayar)) {
                            throw new \RuntimeException("Jumlah pembayaran tidak valid untuk tagihan {$tagihan->kode_pembayaran}.");
                        }
                    } else {
                        // Pembayaran lunas: paksa sesuai sisa agar tidak bisa overpay walau ada submit ganda.
                        $jumlahBayar = $sisaPokok;
                    }

                    $pembayaran = Pembayaran::create([
                        'tagihan_id'        => $tagihan->tagihan_id,
                        'diverifikasi_oleh' => $kasirId,
                        'tanggal_bayar'     => $tanggalBayarUtc,
                        'metode_pembayaran' => $validated['metode_pembayaran'],
                        'jumlah_bayar'      => $jumlahBayar,
                        'is_cicilan'        => $isCicilan,
                    ]);

                    $tagihan->updateAngsuran();

                    $receiptItems[] = [
                        'pembayaran_id'   => $pembayaran->pembayaran_id,
                        'kode_pembayaran' => $tagihan->kode_pembayaran ?? $tagihan->kode ?? ('TAG-' . $tagihan->tagihan_id),
                        'nama_tagihan'    => $tagihan->jenis_tampilan,
                        'jumlah'          => $jumlahBayar,
                        'kwitansi_url'    => route('kasir.kwitansi.download', ['pembayaran' => $pembayaran->pembayaran_id]),
                    ];
                }
            });
        } catch (\Exception $e) {
            Log::error('Gagal proses pembayaran kasir: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal saat memproses pembayaran.'
            ], 500);
        }

        $totalBayar = $receiptItems ? collect($receiptItems)->sum('jumlah') : 0;

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diproses.',
            'data' => [
                'mahasiswa' => [
                    'nama' => $mahasiswa->user->nama_lengkap ?? null,
                    'npm'  => $mahasiswa->npm ?? null,
                    'prodi'=> $mahasiswa->program_studi ?? null,
                ],
                'kasir' => [
                    'nama' => $kasir->nama_lengkap ?? $kasir->username,
                ],
                'tanggal_bayar' => $tanggalBayarDisplay->isoFormat('D MMMM YYYY HH:mm'),
                'total_bayar'   => $totalBayar,
                'pembayaran'    => $receiptItems,
            ]
        ]);
    }

    /**
     * Mengambil statistik dashboard untuk kasir yang sedang login hari ini.
     */
    public function getDashboardStats()
    {
        $kasirId = Auth::id();
        $today = Carbon::today();

        $paymentsToday = Pembayaran::with('tagihan')
            ->where('diverifikasi_oleh', $kasirId)
            ->whereDate('tanggal_bayar', $today)
            ->where(function ($q) {
                $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
            })
            ->get();

        $transaksiCount = $paymentsToday->count();

        $totalPenerimaan = $paymentsToday->sum(function($pembayaran) {
            return $pembayaran->jumlah_bayar ?? 0;
        });

        // Hitung pending hanya jika tagihan masih "Menunggu Verifikasi Transfer"
        // agar konfirmasi yang sudah terselesaikan tidak ikut terhitung.
        $pendingVerifikasiCount = KonfirmasiPembayaran::query()
            ->whereIn('status_verifikasi', ['Menunggu Verifikasi', 'Ditinjau'], 'and', false)
            ->whereHas('tagihan', function ($q) {
                $q->where('status', '=', 'Menunggu Verifikasi Transfer');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'transaksi_count' => $transaksiCount,
                'total_penerimaan' => $totalPenerimaan,
                'pending_verifikasi_count' => $pendingVerifikasiCount,
            ]
        ]);
    }
}
