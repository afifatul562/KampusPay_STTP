<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\TarifMaster;
use App\Exports\TransaksiKasirExport; // Pastikan class Export ini ada
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pastikan Carbon di-import
use Illuminate\Support\Facades\Log; // Tambahkan Log

class TransaksiController extends Controller
{
    /**
     * Mengekspor data transaksi kasir ke Excel.
     */
    public function export(Request $request)
    {
        // Ambil semua parameter filter dari request
        $filters = $request->query();
        $tanggal = now()->format('Y-m-d');
        Log::info('Mengekspor transaksi kasir dengan filter:', $filters); // Log filter yg dipakai

        // Kirim filter ke class Export
        // Pastikan TransaksiKasirExport bisa menangani filter ini
        return Excel::download(
            new TransaksiKasirExport($filters),
            "transaksi-kasir-{$tanggal}.csv",
            \Maatwebsite\Excel\Excel::CSV,
            [ 'Content-Type' => 'text/csv' ]
        );
    }

    /**
     * Menampilkan halaman riwayat transaksi untuk kasir.
     */
    public function index(Request $request)
    {
        $kasirId = Auth::id();
        Log::info('Menampilkan riwayat transaksi kasir ID: ' . $kasirId . ' dengan filter:', $request->query());

        // 1. Ambil semua jenis tarif untuk pilihan filter dropdown
        try {
            $jenisTarif = TarifMaster::select('nama_pembayaran')->distinct()->orderBy('nama_pembayaran')->get();
        } catch (\Exception $e) {
            Log::error('Gagal mengambil jenis tarif: ' . $e->getMessage());
            $jenisTarif = collect(); // Berikan collection kosong jika gagal
        }


        // 2. Mulai query pembayaran
        $query = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'konfirmasi')
            ->where('diverifikasi_oleh', $kasirId) // Filter berdasarkan kasir yg login
            ->latest('tanggal_bayar'); // Urutkan terbaru dulu

        // 3. Terapkan filter berdasarkan input request

        // Filter 'hari_ini' dari dashboard
        if ($request->input('filter') === 'hari_ini') {
            Log::info('Menerapkan filter: hari_ini');
            $query->whereDate('tanggal_bayar', Carbon::today());
        }

        // Filter 'jenis_filter' (Jenis Pembayaran)
        if ($request->filled('jenis_filter')) {
            $jenis = $request->jenis_filter;
            Log::info('Menerapkan filter: jenis_filter = ' . $jenis);
            $query->whereHas('tagihan.tarif', function ($q) use ($jenis) {
                $q->where('nama_pembayaran', $jenis);
            });
        }

        // ============================================
        // !! LOGIKA FILTER TANGGAL ADA DI SINI !!
        // ============================================
        if ($request->filled('start_date')) {
            try {
                 $startDate = Carbon::parse($request->start_date)->startOfDay();
                 Log::info('Menerapkan filter: start_date >= ' . $startDate->toDateString());
                 $query->where('tanggal_bayar', '>=', $startDate);
             } catch (\Exception $e) {
                 Log::warning('Format start_date tidak valid: ' . $request->start_date);
             }
        }
        if ($request->filled('end_date')) {
             try {
                 $endDate = Carbon::parse($request->end_date)->endOfDay();
                  Log::info('Menerapkan filter: end_date <= ' . $endDate->toDateString());
                 $query->where('tanggal_bayar', '<=', $endDate);
             } catch (\Exception $e) {
                  Log::warning('Format end_date tidak valid: ' . $request->end_date);
             }
        }
        // ============================================
        // !! AKHIR FILTER TANGGAL !!
        // ============================================


        // 4. Eksekusi query dengan paginasi
        try {
            // withQueryString() agar filter tetap aktif saat pindah halaman
            $transaksi = $query->paginate(15)->withQueryString();
             Log::info('Menampilkan ' . $transaksi->count() . ' transaksi dari total ' . $transaksi->total());
        } catch (\Exception $e) {
             Log::error('Error saat query transaksi kasir: ' . $e->getMessage());
             // Berikan paginator kosong jika query gagal
             $transaksi = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
             // Opsional: Tambahkan pesan error ke session untuk ditampilkan di view
             session()->flash('error', 'Gagal memuat data transaksi.');
        }


        // 5. Kirim data transaksi dan jenis tarif ke view
        return view('kasir.transaksi', [
            'transaksi' => $transaksi,
            'jenisTarif' => $jenisTarif,
        ]);
    }

    /**
     * Batalkan pembayaran (hanya untuk pembayaran Transfer).
     */
    public function cancel(Request $request, Pembayaran $pembayaran)
    {
        // Validasi alasan
        $validated = $request->validate([
            'alasan_pembatalan' => 'required|string|min:10|max:500',
        ]);

        // Hanya izinkan batalkan jika metode adalah Transfer dan belum dibatalkan
        if (strtolower($pembayaran->metode_pembayaran) !== 'transfer') {
            return back()->with('error', 'Hanya pembayaran transfer yang dapat dibatalkan.');
        }
        if ($pembayaran->status_dibatalkan) {
            return back()->with('error', 'Pembayaran ini sudah dibatalkan sebelumnya.');
        }

        $pembayaran->update([
            'alasan_pembatalan' => $validated['alasan_pembatalan'],
            'status_dibatalkan' => true,
            'tanggal_pembatalan' => now(),
        ]);

        // Opsional: Tidak mengubah status tagihan; tampilan mahasiswa/admin sudah menangani label Dibatalkan

        return back()->with('success', 'Pembayaran berhasil dibatalkan.');
    }
}