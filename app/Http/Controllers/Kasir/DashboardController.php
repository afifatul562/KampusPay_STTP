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
     * Mencari mahasiswa berdasarkan NPM dan mengambil tagihan yang belum lunas.
     */
    public function searchMahasiswa(Request $request)
    {
        $request->validate(['npm' => 'required|string|exists:mahasiswa_detail,npm']);

        $mahasiswa = MahasiswaDetail::with([
            'user',
            'tagihan' => function ($query) {
                $query->where('status', 'Belum Lunas');
            },
            'tagihan.tarif'
        ])->where('npm', $request->npm)->first();

        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $mahasiswa]);
    }

    /**
     * Memproses pembayaran untuk satu atau lebih tagihan.
     */
    public function processPayment(Request $request)
    {
        // ▼▼▼ PERBAIKAN ADA DI SINI ▼▼▼
        $validated = $request->validate([
            'tagihan_ids'       => 'required|array|min:1',
            'tagihan_ids.*'     => ['required', 'integer', Rule::exists('tagihan', 'tagihan_id')->where('status', 'Belum Lunas')],
            'metode_pembayaran' => 'required|string|in:Tunai,Transfer Bank Nagari', // Diubah
        ]);
        // ▲▲▲ SELESAI ▲▲▲

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['tagihan_ids'] as $tagihanId) {
                    $tagihan = Tagihan::find($tagihanId);

                    Pembayaran::create([
                        'tagihan_id'        => $tagihan->tagihan_id,
                        'diverifikasi_oleh' => Auth::id(),
                        'tanggal_bayar'     => now(),
                        'metode_pembayaran' => $validated['metode_pembayaran'],
                    ]);

                    $tagihan->status = 'Lunas';
                    $tagihan->save();
                }
            });
        } catch (\Exception $e) {
            Log::error('Gagal proses pembayaran kasir: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal saat memproses pembayaran.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diproses.'
        ]);
    }

    /**
     * Mengambil statistik dashboard untuk kasir yang sedang login hari ini.
     */
    public function getDashboardStats()
    {
        $kasirId = Auth::id();
        $today = Carbon::today();

        // Ambil semua pembayaran yang diverifikasi oleh kasir ini pada hari ini
        $paymentsToday = Pembayaran::with('tagihan')
            ->where('diverifikasi_oleh', $kasirId)
            ->whereDate('created_at', $today)
            ->get();

        // Hitung jumlah transaksi
        $transaksiCount = $paymentsToday->count();

        // Hitung total penerimaan dari semua tagihan terkait
        $totalPenerimaan = $paymentsToday->sum(function($pembayaran) {
            return $pembayaran->tagihan->jumlah_tagihan ?? 0;
        });

        $pendingVerifikasiCount = KonfirmasiPembayaran::where('status_verifikasi', 'Menunggu Verifikasi')->count();

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