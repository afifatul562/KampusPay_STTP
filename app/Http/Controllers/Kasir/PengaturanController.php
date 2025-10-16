<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman pengaturan untuk kasir.
     */
    public function index()
    {
        return view('kasir.pengaturan');
    }

    /**
     * Memproses permintaan untuk mengubah password.
     */
    public function updatePassword(Request $request)
    {
        // 1. Validasi input dari form
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // 2. Update password user yang sedang login
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Arahkan kembali dengan pesan sukses
        return back()->with('status', 'password-updated');
    }
}
