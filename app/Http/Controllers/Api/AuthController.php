<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login untuk API (mobile).
     *
     * Request body (JSON / form):
     * - username
     * - password
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        $user = $request->user();

        if (!$user->isMahasiswa()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya akun mahasiswa yang dapat login ke aplikasi mobile.',
            ], 403);
        }

        // Hapus token lama jika mau lebih aman (opsional)
        $user->tokens()->delete();

        $token = $user->createToken('mahasiswa-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Logout dari API (revoke token saat ini).
     */
    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}

