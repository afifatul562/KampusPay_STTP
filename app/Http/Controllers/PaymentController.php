<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\KonfirmasiPembayaran;
use App\Models\Pembayaran;

class PaymentController extends Controller
{
    /**
     * Menampilkan semua data pembayaran.
     * (Pindahan dari AdminController::getAllPayments)
     */
    public function index()
    {
        $payments = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier')
            ->latest('tanggal_bayar')
            ->get();

        return response()->json($payments);
    }

    /**
     * Menampilkan detail satu pembayaran.
     * (Pindahan dari AdminController::getPaymentDetail)
     */
    public function show($id)
    {
        $payment = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $payment]);
    }

    // ==========================================================
    // Method-method di bawah ini sudah ada sebelumnya, kita biarkan saja
    // karena sudah menggunakan Eloquent dengan benar.
    // ==========================================================

    /**
     * Membuat tagihan baru.
     */
    public function createTagihan(Request $request)
    {
        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa_detail,mahasiswa_id',
            'tarif_id' => 'required|exists:tarif_master,tarif_id',
            'kode_pembayaran' => 'required|string|unique:tagihan,kode_pembayaran',
            'jumlah_tagihan' => 'required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'required|date',
        ]);

        $tagihan = Tagihan::create($validatedData + ['status' => 'Belum Lunas']);

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dibuat',
            'data' => $tagihan
        ], 201);
    }

    /**
     * Membuat konfirmasi pembayaran baru.
     */
    public function createKonfirmasiPembayaran(Request $request)
    {
        $validatedData = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,tagihan_id',
            'file_bukti_pembayaran' => 'required|string', // Nanti bisa diubah menjadi file upload
        ]);

        $konfirmasi = KonfirmasiPembayaran::create($validatedData + ['status_verifikasi' => 'Menunggu Verifikasi']);

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi pembayaran berhasil dibuat',
            'data' => $konfirmasi
        ], 201);
    }

    /**
     * Membuat data pembayaran final setelah verifikasi.
     */
    public function createPembayaran(Request $request)
    {
        $validatedData = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,tagihan_id|unique:pembayaran,tagihan_id',
            'konfirmasi_id' => 'required|exists:konfirmasi_pembayaran,konfirmasi_id',
            'diverifikasi_oleh' => 'required|exists:users,id',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required|string'
        ]);

        $pembayaran = Pembayaran::create($validatedData);

        // Update status tagihan menjadi 'Lunas'
        $pembayaran->tagihan()->update(['status' => 'Lunas']);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $pembayaran
        ], 201);
    }
}