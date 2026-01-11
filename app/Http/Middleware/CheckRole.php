<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Menangani request yang masuk dan memeriksa role pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        if (!$user || $user->role !== $role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Insufficient role permissions'
                ], 403);
            }
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
