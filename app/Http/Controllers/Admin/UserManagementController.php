<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index()
    {   $mahasiswa = User::where('role', 'mahasiswa')->latest()->get();
        $staff = User::where('role', '!=', 'mahasiswa')->latest()->get();
        return view('admin.users.index', compact('mahasiswa', 'staff'));
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        //
    }

    /**
     * Menyimpan data pengguna baru.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Menampilkan detail pengguna.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Menampilkan form untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Cek email unik, KECUALI untuk user ini
            ],
            'role' => 'required|string',

            // Validasi password HANYA JIKA diisi
            'password' => 'nullable|string|min:8|confirmed',
            // 'confirmed' akan otomatis mencocokkan dengan 'password_confirmation'
        ]);

        // 2. Update data user (selain password)
        $user->nama_lengkap = $request->nama_lengkap;
        $user->email = $request->email;
        $user->role = $request->role;

        // Cek apakah admin ingin mengganti password
        if ($request->filled('password')) {
            // Jika field password diisi, hash dan update passwordnya
            $user->password = Hash::make($request->password);
        }

        // 4. Simpan semua perubahan ke database
        $user->save();

        // 5. Kembalikan ke halaman index dengan pesan sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'Data user ('. $user->nama_lengkap .') berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        $namaUser = $user->nama_lengkap;
        $user->delete();
        return redirect()->route('admin.users.index')
                         ->with('success', 'User (' . $namaUser . ') berhasil dihapus.');
    }
    }
