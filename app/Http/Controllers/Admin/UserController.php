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
     * Menampilkan semua pengguna dengan role 'kasir' atau 'admin'.
     * (Menggantikan getAdminUsers)
     */
    public function index()
    {
        $users = User::whereIn('role', ['kasir', 'admin'])
            ->select('id', 'nama_lengkap', 'email', 'username', 'role', 'created_at')
            ->get();

        return response()->json($users);
    }

    /**
     * Mendaftarkan pengguna baru dengan role 'kasir'.
     * (Menggantikan registerKasir)
     */
    public function registerKasir(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|unique:users,username',
                // Opsional: jika admin ingin set password sendiri saat registrasi
                'password' => ['nullable', 'confirmed', Password::defaults()],
            ]);

            // Tentukan password: gunakan input jika disediakan, jika tidak gunakan env dengan fallback
            $password = $request->filled('password') ? $request->input('password') : env('KASIR_DEFAULT_PASSWORD', 'password123');

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
}
