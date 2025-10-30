<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TarifMaster;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman utama Laporan Mahasiswa.
     */
    public function index()
    {
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;

        // Ambil data tunggakan untuk ditampilkan di kartu Laporan Data Tunggakan
        $tunggakan = \App\Models\Tagihan::where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'Belum Lunas')
            ->get();

        // Ambil jenis tarif untuk dropdown filter
        $jenisTarif = TarifMaster::select('nama_pembayaran')->distinct()->orderBy('nama_pembayaran')->get();

        return view('mahasiswa.laporan', compact('tunggakan', 'jenisTarif'));
    }

    /**
     * Mengunduh Laporan Histori Pembayaran dalam bentuk PDF.
     */
    public function downloadHistori(Request $request)
    {
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;

        $query = Pembayaran::with('tagihan.tarif')
            ->whereHas('tagihan', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            });

        // Filter berdasarkan tanggal jika ada
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_bayar', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_bayar', '<=', $request->sampai_tanggal);
        }

        // Filter berdasarkan jenis pembayaran jika ada
        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('tagihan.tarif', function ($q) use ($request) {
                $q->where('nama_pembayaran', $request->jenis_pembayaran);
            });
        }

        $histori = $query->latest('tanggal_bayar')->get();

        $pdf = Pdf::loadView('mahasiswa.pdf.laporan_histori', [
            'histori' => $histori,
            'user' => Auth::user(),
            'filter' => $request->all()
        ]);

        return $pdf->download('laporan-histori-pembayaran.pdf');
    }

    /**
     * Mengunduh Laporan Data Tunggakan dalam bentuk PDF.
     */
    public function downloadTunggakan()
    {
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;

        $tunggakan = \App\Models\Tagihan::with('tarif')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'Belum Lunas')
            ->get();

        $pdf = Pdf::loadView('mahasiswa.pdf.laporan_tunggakan', [
            'tunggakan' => $tunggakan,
            'user' => Auth::user()
        ]);

        return $pdf->download('laporan-data-tunggakan.pdf');
    }
}

