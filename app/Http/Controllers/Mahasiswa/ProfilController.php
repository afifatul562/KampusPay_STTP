<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\AktivasiStatus;
use App\Models\Tagihan;
use App\Models\Setting;

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

        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $user->mahasiswaDetail->mahasiswa_id)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        $today = now()->toDateString();
        $allTagihan = $user->mahasiswaDetail->tagihan
            ->filter(static function (Tagihan $t) use ($aktivasi, $today) {
                if ($aktivasi && $aktivasi->status === 'bss') {
                    return (bool) $t->is_bss && (!$t->available_from || (string) $t->available_from->toDateString() <= $today);
                }
                return !$t->is_bss && (!$t->available_from || (string) $t->available_from->toDateString() <= $today);
            });

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
     * Menyimpan atau memperbarui email kontak mahasiswa.
     */
    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update([
            'email' => $validated['email'],
            'email_verified_at' => null,
        ]);

        return redirect()->route('mahasiswa.profil')->with('status', 'email-updated');
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

