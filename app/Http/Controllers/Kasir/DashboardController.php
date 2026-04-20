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
        ])->where('npm', $request->npm)->first();

        // Filter tambahan: sembunyikan tagihan lunas / yang sudah punya pembayaran aktif, dan
        // sembunyikan tagihan dibatalkan jika sudah ada pengganti jenis sama yang lunas.
        if ($mahasiswa && $mahasiswa->tagihan) {
            $allTagihan = Tagihan::with('pembayaran', 'tarif')
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->get();

            $mahasiswa->tagihan = $mahasiswa->tagihan->filter(function ($tagihan) use ($allTagihan) {
                if ($tagihan->status === 'Lunas') {
                    return false;
                }

                // 2. Tagihan punya pembayaran yang tidak dibatalkan (sudah lunas)
                if ($tagihan->pembayaran) {
                    $isCancelled = $tagihan->pembayaran->status_dibatalkan ?? false;
                    if (!$isCancelled) {
                        return false;
                    }

                    if ($isCancelled) {
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
                                if (!$otherIsCancelled) {
                                    return true;
                                }
                            }

                            return false;
                        });

                        if ($hasReplacement) {
                            return false;
                        }
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

        // Pastikan semua tagihan milik mahasiswa yang sama
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
                    $tagihan = $tagihanCollection->firstWhere('tagihan_id', $tagihanId);
                    if (!$tagihan) {
                        throw new \RuntimeException('Tagihan tidak ditemukan saat proses pembayaran.');
                    }

                    KonfirmasiPembayaran::where('tagihan_id', $tagihan->tagihan_id)
                        ->where('status_verifikasi', 'Menunggu Verifikasi')
                        ->update([
                            'status_verifikasi' => 'Dibatalkan (Oleh Kasir Tunai)'
                        ]);

                    $cicilanData = $validated['cicilan'][$tagihanId] ?? null;
                    $isCicilan = $cicilanData && isset($cicilanData['is_cicilan']) && $cicilanData['is_cicilan'];
                    $jumlahBayar = $cicilanData && isset($cicilanData['jumlah_bayar'])
                        ? $cicilanData['jumlah_bayar']
                        : $tagihan->jumlah_tagihan;

                    if ($isCicilan) {
                        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;
                        $minBayar = min(50000, $sisaPokok);
                        if ($jumlahBayar > $sisaPokok) {
                            throw new \RuntimeException("Jumlah pembayaran melebihi sisa pokok untuk tagihan {$tagihan->kode_pembayaran}.");
                        }
                        if ($jumlahBayar < $minBayar && $sisaPokok >= 50000) {
                            throw new \RuntimeException("Minimal pembayaran cicilan adalah Rp 50.000 untuk tagihan {$tagihan->kode_pembayaran}.");
                        }
                        if ($jumlahBayar <= 0) {
                            throw new \RuntimeException("Jumlah pembayaran harus lebih dari 0 untuk tagihan {$tagihan->kode_pembayaran}.");
                        }
                    } else {
                        $jumlahBayar = $tagihan->jumlah_tagihan;
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
            ->whereDate('created_at', $today)
            ->get();

        $transaksiCount = $paymentsToday->count();

        $totalPenerimaan = $paymentsToday->sum(function($pembayaran) {
            return $pembayaran->jumlah_bayar ?? 0;
        });

        // Hitung pending hanya jika tagihan masih "Menunggu Verifikasi Transfer"
        // agar konfirmasi yang sudah terselesaikan tidak ikut terhitung.
        $pendingVerifikasiCount = KonfirmasiPembayaran::where('status_verifikasi', 'Menunggu Verifikasi')
            ->whereHas('tagihan', function($q) {
                $q->where('status', 'Menunggu Verifikasi Transfer');
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
