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
            ->where('status_verifikasi', 'Menunggu Verifikasi')
            ->latest() // Tampilkan yang terbaru di atas
            ->paginate(10);

        return view('kasir.verifikasi', compact('pendingVerifications'));
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
        if ($konfirmasi->status_verifikasi !== 'Menunggu Verifikasi') {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        $tagihan = $konfirmasi->tagihan;
        $isCicilan = $request->has('is_cicilan') && $request->input('is_cicilan') == '1';
        $jumlahBayar = $request->input('jumlah_bayar') ?? $konfirmasi->jumlah_bayar;

        // Jika cicilan, validasi jumlah_bayar
        if ($isCicilan) {
            $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;
            $minBayar = min(50000, $sisaPokok); // Minimal 50k, kecuali sisa < 50k
            $request->validate([
                'jumlah_bayar' => 'required|numeric|min:'.$minBayar,
            ]);

            if ($jumlahBayar > $sisaPokok) {
                return response()->json(['success' => false, 'message' => 'Jumlah pembayaran melebihi sisa pokok.'], 400);
            }
            if ($jumlahBayar < $minBayar && $sisaPokok >= 50000) {
                return response()->json(['success' => false, 'message' => 'Minimal pembayaran cicilan adalah Rp 50.000.'], 400);
            }
        } else {
            // Jika bukan cicilan, jumlah_bayar = jumlah_tagihan (lunas)
            $jumlahBayar = $tagihan->jumlah_tagihan;
        }

        try {
            DB::transaction(function () use ($konfirmasi, $tagihan, $isCicilan, $jumlahBayar) {
                // 1. Buat data pembayaran resmi
                $pembayaran = Pembayaran::create([
                    'tagihan_id'        => $tagihan->tagihan_id,
                    'konfirmasi_id'     => $konfirmasi->konfirmasi_id,
                    'diverifikasi_oleh' => Auth::id(),
                    'tanggal_bayar'     => now(),
                    'metode_pembayaran' => 'Transfer',
                    'jumlah_bayar'      => $jumlahBayar,
                    'is_cicilan'        => $isCicilan,
                ]);

                // 2. Update total_angsuran dan sisa_pokok
                $tagihan->updateAngsuran();

                // 3. Update status konfirmasi menjadi 'Disetujui'
                $konfirmasi->update(['status_verifikasi' => 'Disetujui']);
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

        if ($konfirmasi->status_verifikasi !== 'Menunggu Verifikasi') {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        try {
            $konfirmasi->update([
                'status_verifikasi' => 'Ditolak',
                'alasan_ditolak' => $validated['alasan_ditolak'],
            ]);

            $konfirmasi->tagihan()->update(['status' => 'Ditolak']);

            return response()->json(['success' => true, 'message' => 'Pembayaran telah ditolak.']);

        } catch (\Exception $e) {
            Log::error('Gagal menolak pembayaran: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan alasan: ' . $e->getMessage()], 500);
        }
    }
}
