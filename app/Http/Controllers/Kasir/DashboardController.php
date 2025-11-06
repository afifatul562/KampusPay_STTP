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
     * Mencari mahasiswa berdasarkan NPM dan mengambil tagihan yang BISA DIBAYAR.
     */
    public function searchMahasiswa(Request $request)
    {
        $request->validate(['npm' => 'required|string|exists:mahasiswa_detail,npm']);

        $mahasiswa = MahasiswaDetail::with([
            'user',
            'tagihan' => function ($query) {
                // Ambil tagihan yang bisa dibayar (status bukan 'Lunas')
                // Ini akan mencakup: 'Belum Lunas', 'Menunggu Pembayaran Tunai',
                // 'Ditolak', dan 'Menunggu Verifikasi Transfer'
                $query->where('status', '!=', 'Lunas');
            },
            'tagihan.tarif',
            'tagihan.pembayaran' // Eager load untuk cek status pembayaran
        ])->where('npm', $request->npm)->first();

        // Filter tambahan: Pastikan tidak ada tagihan yang statusnya "Lunas" atau sudah punya pembayaran aktif
        // Juga sembunyikan tagihan yang dibatalkan jika sudah ada tagihan pengganti dengan jenis yang sama yang sudah lunas
        if ($mahasiswa && $mahasiswa->tagihan) {
            // Ambil semua tagihan mahasiswa untuk cek tagihan pengganti
            $allTagihan = Tagihan::with('pembayaran', 'tarif')
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->get();

            $mahasiswa->tagihan = $mahasiswa->tagihan->filter(function ($tagihan) use ($allTagihan) {
                // Jangan tampilkan jika:
                // 1. Status tagihan adalah "Lunas" (double check)
                if ($tagihan->status === 'Lunas') {
                    return false;
                }

                // 2. Tagihan punya pembayaran yang tidak dibatalkan (sudah lunas)
                if ($tagihan->pembayaran) {
                    $isCancelled = $tagihan->pembayaran->status_dibatalkan ?? false;
                    // Jika pembayaran tidak dibatalkan, berarti sudah lunas, jangan tampilkan
                    if (!$isCancelled) {
                        return false;
                    }

                    // 3. Jika pembayaran dibatalkan, cek apakah ada tagihan lain dengan jenis yang sama yang sudah lunas
                    if ($isCancelled) {
                        // Cari tagihan lain dengan tarif_id yang sama (jenis pembayaran sama) yang sudah lunas
                        $hasReplacement = $allTagihan->contains(function ($otherTagihan) use ($tagihan) {
                            // Tagihan lain dengan jenis yang sama (tarif_id sama)
                            if ($otherTagihan->tagihan_id === $tagihan->tagihan_id) {
                                return false; // Skip tagihan yang sama
                            }

                            if ($otherTagihan->tarif_id !== $tagihan->tarif_id) {
                                return false; // Bukan jenis yang sama
                            }

                            // Cek apakah tagihan lain sudah lunas
                            if ($otherTagihan->status === 'Lunas') {
                                return true;
                            }

                            // Cek apakah tagihan lain punya pembayaran yang tidak dibatalkan
                            if ($otherTagihan->pembayaran) {
                                $otherIsCancelled = $otherTagihan->pembayaran->status_dibatalkan ?? false;
                                if (!$otherIsCancelled) {
                                    return true; // Ada tagihan pengganti yang sudah lunas
                                }
                            }

                            return false;
                        });

                        // Jika ada tagihan pengganti yang sudah lunas, jangan tampilkan tagihan yang dibatalkan
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
            // DIUBAH: Izinkan bayar semua tagihan yg statusnya BUKAN 'Lunas'
            'tagihan_ids.*' => ['required', 'integer', Rule::exists('tagihan', 'tagihan_id')->whereNot('status', 'Lunas')],
            'metode_pembayaran' => 'required|string|in:Tunai,Transfer Bank Nagari,Transfer', // Ditambah 'Transfer'
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
        $tanggalBayar = now();
        $receiptItems = [];

        try {
            DB::transaction(function () use ($validated, $tagihanCollection, &$receiptItems, $kasirId, $tanggalBayar) {
                foreach ($validated['tagihan_ids'] as $tagihanId) {
                    $tagihan = $tagihanCollection->firstWhere('tagihan_id', $tagihanId);
                    if (!$tagihan) {
                        throw new \RuntimeException('Tagihan tidak ditemukan saat proses pembayaran.');
                    }

                    // --- DITAMBAHKAN: LOGIKA "OTOMATIS HILANG" (REVISI KASUS DINDA) ---
                    KonfirmasiPembayaran::where('tagihan_id', $tagihan->tagihan_id)
                        ->where('status_verifikasi', 'Menunggu Verifikasi')
                        ->update([
                            'status_verifikasi' => 'Dibatalkan (Oleh Kasir Tunai)'
                        ]);
                    // --- SELESAI LOGIKA "OTOMATIS HILANG" ---

                    // 1. Buat record pembayaran tunai baru
                    $pembayaran = Pembayaran::create([
                        'tagihan_id'        => $tagihan->tagihan_id,
                        'diverifikasi_oleh' => $kasirId,
                        'tanggal_bayar'     => $tanggalBayar,
                        'metode_pembayaran' => $validated['metode_pembayaran'],
                        'status_pembayaran' => 'LUNAS',
                        'jumlah_bayar'      => $tagihan->jumlah_tagihan,
                    ]);

                    // 2. Update status tagihan utamanya
                    $tagihan->status = 'Lunas';
                    $tagihan->save();

                    $receiptItems[] = [
                        'pembayaran_id'   => $pembayaran->pembayaran_id,
                        'kode_pembayaran' => $tagihan->kode_pembayaran ?? $tagihan->kode ?? ('TAG-' . $tagihan->tagihan_id),
                        'nama_tagihan'    => $tagihan->tarif->nama_pembayaran ?? 'Pembayaran',
                        'jumlah'          => $tagihan->jumlah_tagihan,
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

        $totalBayar = $tagihanCollection->sum('jumlah_tagihan');

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
                'tanggal_bayar' => $tanggalBayar->isoFormat('D MMMM YYYY HH:mm'),
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
            return $pembayaran->tagihan->jumlah_tagihan ?? 0;
        });

        // DIUBAH: Query ini dibuat lebih aman.
        // Hanya hitung 'pending' jika tagihan utamanya juga 'pending'.
        // Ini mencegah 'pending verifikasi' yang sudah lunas (via tunai) terhitung.
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
