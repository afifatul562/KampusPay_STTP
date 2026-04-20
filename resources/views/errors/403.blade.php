<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses ditolak</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .box { max-width: 28rem; text-align: center; }
        h1 { font-size: 1.25rem; font-weight: 600; margin: 0 0 1rem; }
        p { font-size: 0.95rem; line-height: 1.6; color: #94a3b8; margin: 0 0 1.25rem; }
        a { color: #93c5fd; }
    </style>
</head>
<body>
    <div class="box">
        <h1>403 — Akses ditolak</h1>
        <p>{{ $exception->getMessage() !== 'Unauthorized' ? $exception->getMessage() : 'Anda tidak punya izin untuk halaman ini.' }}</p>
        <p>
            <a href="{{ url('/login') }}">Login</a>
            @auth
                · <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            @endauth
        </p>
        @auth
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        @endauth
    </div>
</body>
</html>
