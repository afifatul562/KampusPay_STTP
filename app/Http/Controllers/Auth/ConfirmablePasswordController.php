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
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ▼▼▼ INI BAGIAN YANG DIPERBAIKI ▼▼▼
        // Buat token jika yang login adalah Admin ATAU Kasir
        if ($user->isAdmin() || $user->isKasir()) {
            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }
        // ▲▲▲ SELESAI ▲▲▲

        // Redirect ke dashboard "pintar"
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

