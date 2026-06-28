<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KwitansiApiController extends Controller
{
    /**
     * Mengunduh kwitansi pembayaran dalam bentuk PDF (untuk mobile).
     */
    public function download(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);

        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier');

        $scope = $request->query('scope', 'transaksi');
        if ($scope === 'lunas' && $pembayaran->tagihan->status !== 'Lunas') {
            $scope = 'transaksi';
        }

        $pdf = Pdf::loadView('mahasiswa.kwitansi_pdf', compact('pembayaran', 'scope'));

        $content = $pdf->output();
        $namaFile = 'kwitansi-' . $pembayaran->tagihan->kode_pembayaran . '.pdf';

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $namaFile . '"');
    }
}

