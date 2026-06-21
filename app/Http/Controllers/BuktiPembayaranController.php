<?php

namespace App\Http\Controllers;

use App\Models\KonfirmasiPembayaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuktiPembayaranController extends Controller
{
    public function show(Request $request, KonfirmasiPembayaran $konfirmasi): BinaryFileResponse
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $konfirmasi->loadMissing('tagihan.mahasiswa');

        if ($user->isMahasiswa()) {
            $mahasiswaId = $user->mahasiswaDetail?->mahasiswa_id;
            if (! $mahasiswaId || (int) $konfirmasi->tagihan?->mahasiswa_id !== (int) $mahasiswaId) {
                abort(403);
            }
        } elseif (! $user->isKasir() && ! $user->isAdmin()) {
            abort(403);
        }

        $relativePath = trim((string) $konfirmasi->file_bukti_pembayaran);
        if ($relativePath === '') {
            abort(404);
        }

        $absolutePath = $this->resolveBuktiPath($relativePath);
        if ($absolutePath === null) {
            abort(404);
        }

        return response()->file($absolutePath, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    protected function resolveBuktiPath(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return null;
        }

        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }

        $candidates = [
            storage_path('app/public/'.$relativePath),
            public_path('storage/'.$relativePath),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
