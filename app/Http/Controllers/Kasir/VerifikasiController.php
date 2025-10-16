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
        // Validasi untuk alasan penolakan jika ada
        // $request->validate(['alasan' => 'nullable|string|max:255']);

        if ($konfirmasi->status_verifikasi !== 'Menunggu Verifikasi') {
            return response()->json(['success' => false, 'message' => 'Status pembayaran ini sudah diubah.'], 409);
        }

        // Cukup update status konfirmasi menjadi 'Ditolak'
        $konfirmasi->update([
            'status_verifikasi' => 'Ditolak',
            // 'alasan_penolakan' => $request->alasan, // Kolom opsional jika kamu mau menambahkannya
        ]);

        return response()->json(['success' => true, 'message' => 'Pembayaran telah ditolak.']);
    }
}