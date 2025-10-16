<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            ]);

            $user = User::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username'],
                'password' => Hash::make('password'), // Password default
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