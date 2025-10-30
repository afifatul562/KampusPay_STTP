<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiController extends Controller
{
    public function download(Pembayaran $pembayaran)
    {
        // 1. KEAMANAN: Pastikan mahasiswa hanya bisa download kwitansinya sendiri
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;
        if ($pembayaran->tagihan->mahasiswa_id !== $mahasiswaId) {
            abort(403, 'AKSES DITOLAK');
        }

        // 2. Load semua relasi yang dibutuhkan untuk ditampilkan di PDF
        $pembayaran->load('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier');

        // 3. Buat PDF dari sebuah view
        $pdf = Pdf::loadView('mahasiswa.kwitansi_pdf', compact('pembayaran'));

        // 4. Atur nama file dan paksa browser untuk men-download
        $namaFile = 'kwitansi-' . $pembayaran->tagihan->kode_pembayaran . '.pdf';
        return $pdf->download($namaFile);
    }
}
