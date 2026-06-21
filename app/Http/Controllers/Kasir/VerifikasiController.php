<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\KonfirmasiPembayaran;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifikasiController extends Controller
{
    /**
     * Menampilkan daftar pembayaran yang menunggu verifikasi.
     */
    public function index()
    {
        $pendingVerifications = KonfirmasiPembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif')
            ->whereIn('status_verifikasi', ['Menunggu Verifikasi', 'Ditinjau'])
            ->whereHas('tagihan', function ($q) {
                $q->where('status', 'Menunggu Verifikasi Transfer');
            })
            ->latest()
            ->paginate(10);

        return view('kasir.verifikasi', compact('pendingVerifications'));
    }

    /**
     * Tandai konfirmasi sedang ditinjau (kasir cek mutasi rekening).
     */
    public function pending(KonfirmasiPembayaran $konfirmasi)
    {
        Log::info('Kasir menandai konfirmasi ditinjau', [
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'tagihan_id' => $konfirmasi->tagihan_id,
            'kasir_id' => Auth::id(),
            'status_sekarang' => $konfirmasi->status_verifikasi,
        ]);

        if (! in_array($konfirmasi->status_verifikasi, ['Menunggu Verifikasi', 'Ditinjau'], true)) {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        if ($konfirmasi->status_verifikasi === 'Ditinjau') {
            return response()->json(['success' => true, 'message' => 'Konfirmasi sudah dalam status ditinjau.']);
        }

        try {
            $konfirmasi->fill(['status_verifikasi' => 'Ditinjau']);
            $konfirmasi->save();

            return response()->json(['success' => true, 'message' => 'Konfirmasi ditandai sedang ditinjau.']);
        } catch (\Exception $e) {
            Log::error('Gagal menandai pending verifikasi: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan status.'], 500);
        }
    }

    /**
     * Menyetujui konfirmasi pembayaran.
     */
    public function approve(KonfirmasiPembayaran $konfirmasi, Request $request)
    {
        Log::info('Kasir mencoba menyetujui konfirmasi', [
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'tagihan_id' => $konfirmasi->tagihan_id,
            'kasir_id' => Auth::id(),
            'status_sekarang' => $konfirmasi->status_verifikasi,
        ]);
        if (! in_array($konfirmasi->status_verifikasi, ['Menunggu Verifikasi', 'Ditinjau'], true)) {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        $tagihan = $konfirmasi->tagihan;
        $isCicilan = ($request->has('is_cicilan') && $request->input('is_cicilan') == '1')
            || (bool) $konfirmasi->is_cicilan;
        $jumlahBayar = $request->input('jumlah_bayar') ?? $konfirmasi->jumlah_bayar;

        // Validasi nominal berdasarkan sisa aktual (source of truth).
        // Catatan: penguncian dan perhitungan ulang dilakukan di dalam transaksi.

        try {
            DB::transaction(function () use ($konfirmasi, $tagihan, $isCicilan, $jumlahBayar, $request) {
                $tagihanLocked = \App\Models\Tagihan::query()
                    ->where('tagihan_id', $tagihan->tagihan_id)
                    ->lockForUpdate()
                    ->first();
                if (!$tagihanLocked) {
                    throw new \RuntimeException('Tagihan tidak ditemukan saat verifikasi.');
                }

                $totalTerverifikasi = (float) \App\Models\Pembayaran::query()
                    ->where('tagihan_id', $tagihanLocked->tagihan_id)
                    ->where(function ($q) {
                        $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
                    })
                    ->sum('jumlah_bayar');
                $sisaPokok = max(0.0, (float) $tagihanLocked->jumlah_tagihan - $totalTerverifikasi);

                if ($sisaPokok <= 0.00001 || $tagihanLocked->status === 'Lunas') {
                    throw new \RuntimeException('Tagihan sudah lunas / tidak memiliki sisa pembayaran.');
                }

                if ($isCicilan) {
                    $tagihanLocked->loadMissing('tarif');
                    if ($tagihanLocked->isWajibLunas()) {
                        throw new \RuntimeException('Tagihan ini wajib lunas dan tidak dapat dicicil.');
                    }

                    $request->validate([
                        'jumlah_bayar' => 'required|numeric|min:1',
                    ]);

                    $jumlahBayar = (float) $jumlahBayar;
                    if (!$tagihanLocked->cicilanAmountValid($jumlahBayar)) {
                        throw new \RuntimeException('Jumlah pembayaran tidak valid.');
                    }
                } else {
                    // Lunas: paksa sesuai sisa agar tidak bisa overpay/double submit.
                    $jumlahBayar = $sisaPokok;
                }

                $pembayaran = Pembayaran::create([
                    'tagihan_id'        => $tagihanLocked->tagihan_id,
                    'konfirmasi_id'     => $konfirmasi->konfirmasi_id,
                    'diverifikasi_oleh' => Auth::id(),
                    'tanggal_bayar'     => now(),
                    'metode_pembayaran' => 'Transfer',
                    'jumlah_bayar'      => $jumlahBayar,
                    'is_cicilan'        => $isCicilan,
                ]);

                $tagihanLocked->updateAngsuran();
                $konfirmasi->fill(['status_verifikasi' => 'Disetujui']);
                $konfirmasi->save();
            });
        } catch (\Exception $e) {
            Log::error('Gagal menyetujui pembayaran: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal.'], 500);
        }

        $message = $isCicilan ? 'Pembayaran cicilan berhasil disetujui.' : 'Pembayaran berhasil disetujui.';
        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Menolak konfirmasi pembayaran.
     */
    public function reject(KonfirmasiPembayaran $konfirmasi, Request $request)
    {
        Log::info('Kasir mencoba menolak konfirmasi', [
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'tagihan_id' => $konfirmasi->tagihan_id,
            'kasir_id' => Auth::id(),
            'status_sekarang' => $konfirmasi->status_verifikasi,
        ]);
        $validated = $request->validate([
            'alasan_ditolak' => 'required|string|min:10|max:500',
        ]);

        if (! in_array($konfirmasi->status_verifikasi, ['Menunggu Verifikasi', 'Ditinjau'], true)) {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        try {
            $konfirmasi->fill([
                'status_verifikasi' => 'Ditolak',
                'alasan_ditolak' => $validated['alasan_ditolak'],
            ]);
            $konfirmasi->save();

            $konfirmasi->tagihan()->update(['status' => 'Ditolak']);

            return response()->json(['success' => true, 'message' => 'Pembayaran telah ditolak.']);

        } catch (\Exception $e) {
            Log::error('Gagal menolak pembayaran: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan alasan: ' . $e->getMessage()], 500);
        }
    }
}
