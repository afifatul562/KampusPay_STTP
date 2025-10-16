<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfilController extends Controller
{
    public function index()
    {
        // 1. Ambil user dan semua relasi yang dibutuhkan
        $user = User::with('mahasiswaDetail.tagihan')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $allTagihan = $user->mahasiswaDetail->tagihan;

        // 2. Hitung data untuk Ringkasan Keuangan
        $totalTunggakan = $allTagihan->where('status', 'Belum Lunas')->sum('jumlah_tagihan');
        $jumlahTunggakan = $allTagihan->where('status', 'Belum Lunas')->count();
        $totalTerbayar = $allTagihan->where('status', 'Lunas')->sum('jumlah_tagihan');
        $pembayaranSelesai = $allTagihan->where('status', 'Lunas')->count();

        // 3. Kirim semua data ke view
        return view('mahasiswa.profil', [
            'user' => $user,
            'detail' => $user->mahasiswaDetail,
            'totalTunggakan' => $totalTunggakan,
            'jumlahTunggakan' => $jumlahTunggakan,
            'totalTerbayar' => $totalTerbayar,
            'pembayaranSelesai' => $pembayaranSelesai,
        ]);
    }

    /**
     * Memperbarui password user yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        // 1. Validasi input dari form
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // 2. Jika validasi berhasil, update password di database
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Arahkan kembali ke halaman profil dengan pesan sukses
        return back()->with('status', 'password-updated');
    }
}