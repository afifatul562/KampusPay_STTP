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
    public function index()
    {
        $mahasiswa = User::query()->where('role', 'mahasiswa')->latest()->get();
        $staff = User::query()->where('role', '!=', 'mahasiswa')->latest()->get();

        return view('admin.users.index', compact('mahasiswa', 'staff'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'kasir', 'mahasiswa', 'yayasan'])],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->nama_lengkap = $request->nama_lengkap;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Data user ('. $user->nama_lengkap .') berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        $namaUser = $user->nama_lengkap;
        User::query()->whereKey($user->getKey())->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User (' . $namaUser . ') berhasil dihapus.');
    }
}
