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
    public function approve(KonfirmasiPembayaran $konfirmasi)
    {
        Log::info('Kasir mencoba menyetujui konfirmasi', [
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'tagihan_id' => $konfirmasi->tagihan_id,
            'kasir_id' => Auth::id(),
            'status_sekarang' => $konfirmasi->status_verifikasi,
        ]);
        // Pastikan statusnya masih 'Menunggu Verifikasi' untuk mencegah aksi ganda
        if ($konfirmasi->status_verifikasi !== 'Menunggu Verifikasi') {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409); // 409 Conflict
        }

        try {
            DB::transaction(function () use ($konfirmasi) {
                // 1. Buat data pembayaran resmi
                Pembayaran::create([
                    'tagihan_id'        => $konfirmasi->tagihan_id,
                    'konfirmasi_id'     => $konfirmasi->konfirmasi_id,
                    'diverifikasi_oleh' => Auth::id(),
                    'tanggal_bayar'     => now(),
                    'metode_pembayaran' => 'Transfer', // Asumsi metode transfer
                ]);

                // 2. Update status tagihan menjadi 'Lunas'
                $konfirmasi->tagihan()->update(['status' => 'Lunas']);

                // 3. Update status konfirmasi menjadi 'Disetujui'
                $konfirmasi->update(['status_verifikasi' => 'Disetujui']);
            });
        } catch (\Exception $e) {
            Log::error('Gagal menyetujui pembayaran: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Pembayaran berhasil disetujui.']);
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
        // 1. Validasi: Pastikan alasan_ditolak dikirim dan valid
        //    (Ini sesuai dengan 'alasan_ditolak' yang dikirim JavaScript)
        //    (Dan 'min:10' sesuai dengan validasi di SweetAlert)
        $validated = $request->validate([
            'alasan_ditolak' => 'required|string|min:10|max:500',
        ]);

        // 2. Cek status (logika Anda sudah benar)
        if ($konfirmasi->status_verifikasi !== 'Menunggu Verifikasi') {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        try {
            // 3. Update status DAN simpan alasan penolakan
            $konfirmasi->update([
                'status_verifikasi' => 'Ditolak',
                // Pastikan nama kolom di database Anda adalah 'alasan_ditolak'
                'alasan_ditolak' => $validated['alasan_ditolak'],
            ]);

            $konfirmasi->tagihan()->update(['status' => 'Ditolak']);

            return response()->json(['success' => true, 'message' => 'Pembayaran telah ditolak.']);

        } catch (\Exception $e) {
            Log::error('Gagal menolak pembayaran: ' . $e->getMessage());
            // Kirim pesan error jika gagal (misal: MassAssignmentException)
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan alasan: ' . $e->getMessage()], 500);
        }
    }
}
