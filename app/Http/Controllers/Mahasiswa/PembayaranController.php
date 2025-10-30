<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\KonfirmasiPembayaran;
use App\Models\Setting; // <-- Pastikan ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user = User::with('mahasiswaDetail')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $tagihanQuery = $user->mahasiswaDetail->tagihan()
            ->with('tarif', 'konfirmasi', 'pembayaran')
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        if ($request->filled('status')) {
            $tagihanQuery->where('status', $request->status);
        }

        $tagihan = $tagihanQuery->get();

        return view('mahasiswa.pembayaran', compact('tagihan'));
    }

    public function show(Tagihan $tagihan)
    {
        // PENTING: Cek keamanan
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;
        if ($tagihan->mahasiswa_id !== $mahasiswaId) {
            abort(403, 'AKSES DITOLAK');
        }

        // Ambil semua data dari tabel 'settings'
        $settings = Setting::all()->pluck('value', 'key');

        // Kirim data tagihan DAN data settings ke view
        return view('mahasiswa.pembayaran_show', compact('tagihan', 'settings'));
    }

    /**
     * Tampilkan halaman untuk memilih metode pembayaran (Tunai / Transfer).
     */
    public function pilihMetode(Tagihan $tagihan)
    {
        // Keamanan: Cek apakah tagihan ini boleh dibayar.
        // Hanya status 'Belum Lunas' atau 'Ditolak' yang boleh lanjut.
        if ( !in_array($tagihan->status, ['Belum Lunas', 'Ditolak']) ) {
            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini sedang diproses atau sudah lunas.');
        }

        // Tampilkan view 'pilih-metode.blade.php' dan kirim data tagihan
        return view('mahasiswa.pilih-metode', [
            'tagihan' => $tagihan
        ]);
    }

    /**
     * Proses pilihan metode pembayaran dari mahasiswa.
     */
    public function prosesMetode(Request $request, Tagihan $tagihan)
    {
        $metode = $request->input('metode');

        if ($metode == 'transfer') {
            // Jika pilih 'transfer', arahkan ke halaman upload bukti
            // (Halaman 'show' kamu yang sudah ada)
            return redirect()->route('mahasiswa.pembayaran.show', $tagihan->tagihan_id);

        } elseif ($metode == 'tunai') {
            // Jika pilih 'tunai', UPDATE STATUS TAGIHAN
            $tagihan->update(['status' => 'Menunggu Pembayaran Tunai']);

            // Kembalikan ke daftar tagihan dengan pesan sukses
            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('success', 'Pilihan bayar tunai berhasil. Silakan lakukan pembayaran ke kasir.');
        }

        // Jika metode tidak valid
        return redirect()->back()->with('error', 'Metode pembayaran tidak valid.');
    }

    public function storeKonfirmasi(Request $request, Tagihan $tagihan)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id;
        if ($tagihan->mahasiswa_id !== $mahasiswaId) {
            abort(403, 'AKSES DITOLAK');
        }

        // 1. Tentukan nama folder
        $folder = 'bukti_pembayaran';
        // 2. Simpan file ke public/storage/bukti_pembayaran
        $path = $request->file('bukti_pembayaran')->store($folder, 'public');

        // 3. Simpan path LENGKAP (termasuk folder) ke database
        //    CATATAN: Kamu mungkin mau ganti 'KonfirmasiPembayaran' jadi 'Pembayaran'
        //    sesuai modelmu. Aku pakai 'KonfirmasiPembayaran' sesuai kodemu.
        KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi', // atau 'PENDING'
        ]);

        // 4. <-- INI LANGKAH YANG HILANG -->
        //    Update status di tabel 'tagihan' utamanya
        $tagihan->update(['status' => 'Menunggu Verifikasi Transfer']);
        // <-- SELESAI LANGKAH TAMBAHAN -->

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran berhasil di-upload dan sedang menunggu verifikasi.');
    }
}

