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
     * Display a listing of the resource.
     */
    public function index()
    {   $mahasiswa = User::where('role', 'mahasiswa')->latest()->get();
        $staff = User::where('role', '!=', 'mahasiswa')->latest()->get();
        return view('admin.users.index', compact('mahasiswa', 'staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
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
            'role' => 'required|string', // Sesuaikan jika perlu (misal: 'in:admin,mahasiswa,kasir')

            // Validasi password HANYA JIKA diisi
            'password' => 'nullable|string|min:8|confirmed',
            // 'confirmed' akan otomatis mencocokkan dengan 'password_confirmation'
        ]);

        // 2. Update data user (selain password)
        $user->nama_lengkap = $request->nama_lengkap;
        $user->email = $request->email;
        $user->role = $request->role;
        // Tambahkan field lain jika ada (misal: npm, dll)

        // 3. Cek apakah admin ingin mengganti password
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
     * Remove the specified resource from storage.
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
