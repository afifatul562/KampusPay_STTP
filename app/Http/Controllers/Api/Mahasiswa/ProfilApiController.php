<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfilApiController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = User::with([
            'mahasiswaDetail.tagihan.pembayaranAll' => function ($query) {
                $query->where('status_dibatalkan', false);
            }
        ])->find(Auth::id());

        if (!$user || !$user->mahasiswaDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $detail = $user->mahasiswaDetail;
        $allTagihan = $detail->tagihan;

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

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'mahasiswa' => [
                'id' => $detail->mahasiswa_id,
                'npm' => $detail->npm,
                'program_studi' => $detail->program_studi,
                'angkatan' => $detail->angkatan,
                'semester_aktif' => $detail->semester_aktif,
                'status' => $detail->status,
            ],
            'ringkasan' => [
                'total_tunggakan' => $totalTunggakan,
                'jumlah_tunggakan' => $jumlahTunggakan,
                'total_terbayar' => $totalTerbayar,
                'pembayaran_selesai' => $pembayaranSelesai,
            ],
        ]);
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill([
            'email' => $validated['email'],
            'email_verified_at' => null,
        ]);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->fill([
            'password' => Hash::make($validated['password']),
        ]);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}

