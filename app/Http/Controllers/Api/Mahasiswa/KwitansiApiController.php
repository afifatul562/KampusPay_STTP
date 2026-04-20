<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiApiController extends Controller
{
    /**
     * Mengunduh kwitansi pembayaran dalam bentuk PDF (untuk mobile).
     */
    public function download(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);

        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier');

        $pdf = Pdf::loadView('mahasiswa.kwitansi_pdf', compact('pembayaran'));

        $content = $pdf->output();
        $namaFile = 'kwitansi-' . $pembayaran->tagihan->kode_pembayaran . '.pdf';

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $namaFile . '"');
    }
}

