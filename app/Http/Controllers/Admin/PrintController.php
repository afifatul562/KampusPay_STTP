<?php

namespace App\Http\Controllers\Admin; // Atau namespace controller kamu

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PrintController extends Controller // Atau nama controller kamu
{
    public function cetakBuktiPembayaran(Pembayaran $pembayaran) // Terima objek Pembayaran
    {
        // Load relasi yang dibutuhkan oleh view kwitansi.blade.php
        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier'); // verifier adalah relasi ke user kasir

        // Kembalikan view kwitansi.blade.php dengan data $pembayaran
        return view('mahasiswa.kwitansi_pdf', compact('pembayaran')); // Pastikan path view-nya benar
    }
}
