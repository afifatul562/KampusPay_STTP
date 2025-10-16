<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Mengambil riwayat laporan.
     */
    public function index()
    {
        return Report::latest()->get(['id', 'created_at', 'jenis_laporan', 'periode', 'file_name']);
    }

    /**
     * Membuat file laporan baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jenis_laporan' => 'required|string|in:pembayaran,mahasiswa',
            'periode'       => 'required|date_format:Y-m',
        ]);

        try {
            $jenis_laporan = $validatedData['jenis_laporan'];
            $periode = $validatedData['periode'];
            list($year, $month) = explode('-', $periode);

            $data_laporan = [];
            $kolom_header = [];

            if ($jenis_laporan === 'pembayaran') {
                $kolom_header = ['ID', 'Tanggal Bayar', 'NPM', 'Nama Mahasiswa', 'Jenis Pembayaran', 'Jumlah'];
                $data_laporan = Pembayaran::with('tagihan.mahasiswa_detail.user', 'tagihan.tarif')
                                ->whereYear('tanggal_bayar', $year)
                                ->whereMonth('tanggal_bayar', $month)
                                ->get();
            } elseif ($jenis_laporan === 'mahasiswa') {
                $kolom_header = ['NPM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Status'];
                $data_laporan = MahasiswaDetail::with('user')
                                ->whereYear('created_at', $year)
                                ->whereMonth('created_at', $month)
                                ->get();
            }

            $data_untuk_pdf = [
                'jenis_laporan_title' => ucfirst($jenis_laporan),
                'periode_formatted'   => \Carbon\Carbon::parse($periode)->format('F Y'),
                'tanggal_cetak'       => now()->format('d F Y'),
                'kolom_header'        => $kolom_header,
                'data_laporan'        => $data_laporan,
                'jenis_laporan_raw'   => $jenis_laporan, // Menambahkan ini untuk logika di view
            ];

            $pdf = Pdf::loadView('reports.template', $data_untuk_pdf);
            $fileName = 'Laporan_' . $jenis_laporan . '_' . time() . '.pdf';

            Storage::put('reports/' . $fileName, $pdf->output());

            $report = Report::create([
                'jenis_laporan' => $jenis_laporan,
                'periode'       => $periode,
                'file_name'     => $fileName,
            ]);

            return response()->json(['success' => true, 'message' => 'Laporan berhasil dibuat!', 'data' => $report], 201);

        } catch (\Exception $e) {
            Log::error('Gagal membuat laporan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat laporan. Periksa log untuk detail.'], 500);
        }
    }

    /**
     * Mengunduh file laporan.
     */
    public function download(Report $report)
    {
        $filePath = 'reports/' . $report->file_name;

        if (!Storage::exists($filePath)) {
            abort(404, 'File laporan tidak ditemukan.');
        }

        return Storage::download($filePath, $report->file_name);
    }
}
