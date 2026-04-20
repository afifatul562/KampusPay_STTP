<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Di lokal, cookie sesi melekat pada host persis (localhost ≠ 127.0.0.1).
 * Jika APP_URL memakai salah satu dan browser membuka yang lain, CSRF jadi 419.
 * Middleware ini mengarahkan ke host di APP_URL agar sesi & token konsisten.
 */
class RedirectLocalDevHostToAppUrl
{
    private const DEV_HOSTS = ['127.0.0.1', 'localhost'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $appUrl = (string) config('app.url');
        if ($appUrl === '') {
            return $next($request);
        }

        $parts = parse_url($appUrl);
        $appHost = isset($parts['host']) ? mb_strtolower((string) $parts['host']) : '';
        if ($appHost === '' || ! in_array($appHost, self::DEV_HOSTS, true)) {
            return $next($request);
        }

        $reqHost = mb_strtolower($request->getHost());
        if (! in_array($reqHost, self::DEV_HOSTS, true)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        $target = url($path);
        if ($request->getQueryString()) {
            $target .= '?'.$request->getQueryString();
        }

        if (rtrim($request->fullUrl(), '/') === rtrim($target, '/')) {
            return $next($request);
        }

        return redirect()->away($target);
    }
}
