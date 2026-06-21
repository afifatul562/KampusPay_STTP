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
use App\Support\TagihanPayability;

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
            ->filter(static fn (Tagihan $t) => TagihanPayability::isVisibleForMahasiswa($t, $aktivasi, $today));

        $totalTerbayar = $allTagihan->sum(function ($tagihan) {
            $payments = $tagihan->relationLoaded('pembayaranAll') ? $tagihan->pembayaranAll : collect();
            $dibayarByPayment = $payments->sum('jumlah_bayar');
            $fallbackAngsuran = $tagihan->total_angsuran ?? 0;
            return $dibayarByPayment > 0 ? $dibayarByPayment : $fallbackAngsuran;
        });

        $totalTunggakan = (float) $allTagihan->sum(static fn ($tagihan) => $tagihan->nominalTunggakan());

        $jumlahTunggakan = $allTagihan->filter(static fn ($tagihan) => $tagihan->masihTunggakan())->count();

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

