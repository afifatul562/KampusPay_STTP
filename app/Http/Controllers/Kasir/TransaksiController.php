<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\TarifMaster;
use App\Exports\TransaksiKasirExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function export(Request $request)
    {
        $filter = $request->all();
        $tanggal = now()->format('Y-m-d');

        return Excel::download(new TransaksiKasirExport($filter), "transaksi-kasir-{$tanggal}.xlsx");
    }

    /**
     * Menampilkan halaman riwayat transaksi untuk kasir.
     */
    public function index(Request $request)
    {
        $kasirId = Auth::id();

        // 1. Ambil semua jenis tarif untuk pilihan filter
        $jenisTarif = TarifMaster::select('nama_pembayaran')->distinct()->orderBy('nama_pembayaran')->get();

        // 2. Mulai query pembayaran
        $query = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif')
            ->where('diverifikasi_oleh', $kasirId)
            ->latest('tanggal_bayar');

        // 3. Terapkan filter jika ada input
        if ($request->filled('jenis_filter')) {
            $query->whereHas('tagihan.tarif', function ($q) use ($request) {
                $q->where('nama_pembayaran', $request->jenis_filter);
            });
        }

        // 4. Eksekusi query dengan paginasi
        // withQueryString() agar filter tetap aktif saat pindah halaman
        $transaksi = $query->paginate(15)->withQueryString();

        // 5. Kirim data transaksi dan jenis tarif ke view
        return view('kasir.transaksi', [
            'transaksi' => $transaksi,
            'jenisTarif' => $jenisTarif,
        ]);
    }
}