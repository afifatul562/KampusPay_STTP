<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Menangani permintaan autentikasi yang masuk.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Buat token jika yang login adalah Admin ATAU Kasir
        if ($user->isAdmin() || $user->isKasir()) {
            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        // Redirect ke dashboard sesuai tujuan sebelumnya
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Menghancurkan sesi yang terautentikasi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

