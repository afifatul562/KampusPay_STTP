<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiController extends Controller
{
    /**
     * Mengunduh kwitansi pembayaran dalam format PDF.
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

        $namaFile = 'kwitansi-' . $pembayaran->tagihan->kode_pembayaran . '.pdf';
        return $pdf->download($namaFile);
    }
}
