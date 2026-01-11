<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman form login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Menangani proses login pengguna.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif (method_exists($user, 'isKasir') && $user->isKasir()) {
                return redirect()->route('kasir.dashboard');
            } elseif (method_exists($user, 'isMahasiswa') && $user->isMahasiswa()) {
                return redirect()->route('mahasiswa.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Role pengguna tidak dikenali.',
                ]);
            }
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Menangani proses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
