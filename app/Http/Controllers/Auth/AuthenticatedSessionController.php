<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Menangani permintaan login yang masuk dari form web.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Proses otentikasi standar dari Laravel
        $request->authenticate();

        // Regenerasi session untuk keamanan
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika user yang login adalah admin, buatkan token API
        // dan simpan di session agar bisa diakses oleh Blade.
        if ($user->isAdmin() || $user->isKasir()) {
            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        // Redirect ke halaman dashboard yang sesuai dengan rolenya
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isKasir()) {
            return redirect()->route('kasir.dashboard');
        } elseif ($user->isMahasiswa()) {
            return redirect()->route('mahasiswa.dashboard');
        }

        // Redirect default jika role tidak dikenali
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Menghancurkan sesi (logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

