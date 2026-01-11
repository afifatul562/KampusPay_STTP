<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiController extends Controller
{
    /**
     * Mengunduh kwitansi pembayaran dalam format PDF.
     */
    public function download(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);

        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier');

        $pdf = Pdf::loadView('mahasiswa.kwitansi_pdf', compact('pembayaran'));

        $namaFile = 'kwitansi-' . $pembayaran->tagihan->kode_pembayaran . '.pdf';
        return $pdf->download($namaFile);
    }
}
