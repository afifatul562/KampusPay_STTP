<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tagihan;
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
            ->with('tarif', 'konfirmasiPembayaran', 'pembayaran')
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

        // ▼▼▼ INI BAGIAN YANG DIPERBARUI ▼▼▼
        // Ambil semua data dari tabel 'settings'
        $settings = Setting::all()->pluck('value', 'key');

        // Kirim data tagihan DAN data settings ke view
        return view('mahasiswa.pembayaran_show', compact('tagihan', 'settings'));
        // ▲▲▲ SELESAI ▲▲▲
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
        KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path, // Simpan path lengkap seperti "bukti_pembayaran/namafile.jpg"
            'status_verifikasi' => 'Menunggu Verifikasi',
        ]);

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran berhasil di-upload dan sedang menunggu verifikasi.');
    }
}

