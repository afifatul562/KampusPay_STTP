<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PrintController extends Controller
{
    public function cetakBuktiPembayaran(Pembayaran $pembayaran)
    {
        // Lengkapi relasi yang dibutuhkan oleh template
        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier');

        // Generate PDF dari view
        $pdf = PDF::loadView('mahasiswa.kwitansi_pdf', compact('pembayaran'))
            ->setPaper('a4', 'portrait');

        $filename = 'Kwitansi_' . ($pembayaran->tagihan->kode_pembayaran ?? $pembayaran->tagihan->kode ?? 'pembayaran') . '.pdf';

        // Stream ke browser
        return $pdf->stream($filename);
    }
}
