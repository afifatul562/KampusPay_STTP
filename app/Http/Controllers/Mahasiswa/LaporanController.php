<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TarifMaster;
use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

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
        // Load user dengan mahasiswaDetail dan relasi user
        $user = User::with('mahasiswaDetail.user')->findOrFail(Auth::id());
        $mahasiswa = $user->mahasiswaDetail;
        $mahasiswaId = $mahasiswa?->mahasiswa_id;

        $query = Pembayaran::with('tagihan.tarif', 'userKasir')
            ->whereHas('tagihan', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            });

        // Parse tanggal dari format Y-m-d (sudah dikonversi oleh JavaScript)
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date')) {
            try {
                // Format yang diterima sudah Y-m-d dari JavaScript
                $startDate = \Carbon\Carbon::parse($request->start_date)->format('Y-m-d');
                $query->whereDate('tanggal_bayar', '>=', $startDate);
            } catch (\Exception $e) {
                // Jika parsing gagal, skip filter tanggal
            }
        }
        if ($request->filled('end_date')) {
            try {
                // Format yang diterima sudah Y-m-d dari JavaScript
                $endDate = \Carbon\Carbon::parse($request->end_date)->format('Y-m-d');
                $query->whereDate('tanggal_bayar', '<=', $endDate);
            } catch (\Exception $e) {
                // Jika parsing gagal, skip filter tanggal
            }
        }

        // Filter berdasarkan jenis pembayaran jika ada
        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('tagihan.tarif', function ($q) use ($request) {
                $q->where('nama_pembayaran', $request->jenis_pembayaran);
            });
        }

        $histori = $query->latest('tanggal_bayar')->get();

        // Ambil tahun akademik dari settings
        $settings = Setting::getCachedMap();
        $tahunAkademik = $settings['academic_year'] ?? ($settings['tahun_akademik'] ?? ($settings['tahun_ajaran'] ?? '-'));

        // Siapkan filters untuk view
        $filters = [
            'tahun_akademik' => $tahunAkademik,
            'dari_tanggal' => $startDate ?? now()->format('Y-m-d'),
            'sampai_tanggal' => $endDate ?? now()->format('Y-m-d'),
        ];

        $pdf = Pdf::loadView('mahasiswa.pdf.laporan_histori', [
            'histori' => $histori,
            'mahasiswa' => $mahasiswa,
            'filters' => $filters
        ]);

        return $pdf->download('laporan-histori-pembayaran.pdf');
    }

    /**
     * Mengunduh Laporan Data Tunggakan dalam bentuk PDF.
     */
    public function downloadTunggakan()
    {
        $user = \App\Models\User::with('mahasiswaDetail.user')->findOrFail(Auth::id());
        $mahasiswa = $user->mahasiswaDetail;
        $mahasiswaId = $mahasiswa?->mahasiswa_id;

        $tunggakan = \App\Models\Tagihan::with('tarif')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'Belum Lunas')
            ->get();

        $settings = Setting::getCachedMap();
        $tahunAkademik = $settings['academic_year'] ?? ($settings['tahun_akademik'] ?? ($settings['tahun_ajaran'] ?? null));

        $pdf = Pdf::loadView('mahasiswa.pdf.laporan_tunggakan', [
            'tunggakan' => $tunggakan,
            'mahasiswa' => $mahasiswa,
            'tahunAkademik' => $tahunAkademik,
        ]);

        return $pdf->download('laporan-data-tunggakan.pdf');
    }
}

