<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        // Ambil ID mahasiswa yang sedang login
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;

        // Ambil semua pembayaran (tunai/transfer), termasuk cicilan, yang tidak dibatalkan
        $pembayaran = \App\Models\Pembayaran::with('tagihan.tarif', 'tagihan.mahasiswa', 'verifier')
            ->whereHas('tagihan', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where(function ($q) {
                $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
            })
            ->latest('tanggal_bayar')
            ->paginate(10);

        return view('mahasiswa.riwayat', ['pembayaran' => $pembayaran]);
    }
}
