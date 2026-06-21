<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Daftar pengguna ber-role kasir atau admin (JSON).
     */
    public function index()
    {
        $users = User::query()
            ->whereIn('role', ['kasir', 'admin', 'yayasan'], 'and', false)
            ->select(['id', 'nama_lengkap', 'email', 'username', 'role', 'created_at'])
            ->get();

        return response()->json($users);
    }

    /**
     * Mendaftarkan pengguna baru ber-role kasir (JSON).
     */
    public function registerKasir(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|unique:users,username',
                'password' => ['nullable', 'confirmed', Password::defaults()],
            ]);

            // Tentukan password: gunakan input jika disediakan, jika tidak gunakan env dengan fallback
            $password = $request->filled('password')
                ? $request->input('password')
                : config('accounts.kasir_default_password');

            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username'],
                'password' => Hash::make($password),
                'role' => 'kasir',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kasir berhasil didaftarkan.',
                'data' => $user
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mendaftarkan pengguna baru ber-role yayasan (read-only, JSON).
     */
    public function registerYayasan(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|unique:users,username',
                'password' => ['nullable', 'confirmed', Password::defaults()],
            ]);

            $password = $request->filled('password')
                ? $request->input('password')
                : config('accounts.yayasan_default_password');

            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username'],
                'password' => Hash::make($password),
                'role' => 'yayasan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun yayasan berhasil didaftarkan.',
                'data' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
