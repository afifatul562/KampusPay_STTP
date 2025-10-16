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

        // Ambil semua tagihan yang statusnya 'Lunas'
        $riwayat = \App\Models\Tagihan::with('tarif', 'pembayaran')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'Lunas')
            ->latest() // Urutkan dari yang terbaru
            ->paginate(10);

        return view('mahasiswa.riwayat', compact('riwayat'));
    }
}