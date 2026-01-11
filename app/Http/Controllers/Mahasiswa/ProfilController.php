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
    /**
     * Menampilkan halaman profil utama mahasiswa.
     */
    public function index()
    {
        $user = User::with([
            'mahasiswaDetail.tagihan.pembayaranAll' => function ($query) {
                $query->where('status_dibatalkan', false);
            }
        ])->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $allTagihan = $user->mahasiswaDetail->tagihan;

        $totalTerbayar = $allTagihan->sum(function ($tagihan) {
            $payments = $tagihan->relationLoaded('pembayaranAll') ? $tagihan->pembayaranAll : collect();
            $dibayarByPayment = $payments->sum('jumlah_bayar');
            $fallbackAngsuran = $tagihan->total_angsuran ?? 0;
            return $dibayarByPayment > 0 ? $dibayarByPayment : $fallbackAngsuran;
        });

        $totalTunggakan = $allTagihan->sum(function ($tagihan) {
            $payments = $tagihan->relationLoaded('pembayaranAll') ? $tagihan->pembayaranAll : collect();
            $dibayarByPayment = $payments->sum('jumlah_bayar');
            $fallbackAngsuran = $tagihan->total_angsuran ?? 0;
            $dibayar = $dibayarByPayment > 0 ? $dibayarByPayment : $fallbackAngsuran;

            $sisa = $tagihan->sisa_pokok;
            if ($sisa === null) {
                $sisa = ($tagihan->jumlah_tagihan ?? 0) - $dibayar;
            }

            return max($sisa, 0);
        });

        $jumlahTunggakan = $allTagihan->filter(function ($tagihan) {
            $payments = $tagihan->relationLoaded('pembayaranAll') ? $tagihan->pembayaranAll : collect();
            $dibayar = $payments->sum('jumlah_bayar');
            if ($dibayar <= 0) {
                $dibayar = $tagihan->total_angsuran ?? 0;
            }
            $sisa = $tagihan->sisa_pokok ?? (($tagihan->jumlah_tagihan ?? 0) - $dibayar);
            return $sisa > 0;
        })->count();

        $pembayaranSelesai = $allTagihan->sum(function ($tagihan) {
            $payments = $tagihan->relationLoaded('pembayaranAll') ? $tagihan->pembayaranAll : collect();
            return $payments->count();
        });

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
     * Menampilkan halaman form untuk mengubah password.
     */
    public function editPassword()
    {
        return view('mahasiswa.ubah-password');
    }

    /**
     * Memproses permintaan untuk mengubah password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}

