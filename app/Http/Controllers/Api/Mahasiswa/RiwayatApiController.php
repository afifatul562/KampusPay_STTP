<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatApiController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id ?? null;

        if (!$mahasiswaId) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $perPage = (int) ($request->query('per_page', 10));

        $paginator = Pembayaran::with('tagihan.tarif', 'tagihan.mahasiswa', 'verifier')
            ->whereHas('tagihan', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            })
            ->where(function ($q) {
                $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
            })
            ->latest('tanggal_bayar')
            ->paginate($perPage);

        $data = collect($paginator->items())->map(function (Pembayaran $p) {
            return [
                'id' => $p->pembayaran_id,
                'tanggal_bayar' => optional($p->tanggal_bayar)->toDateTimeString(),
                'metode_pembayaran' => $p->metode_pembayaran,
                'jumlah_bayar' => $p->jumlah_bayar,
                'is_cicilan' => (bool) $p->is_cicilan,
                'tagihan' => [
                    'id' => $p->tagihan?->tagihan_id,
                    'kode_pembayaran' => $p->tagihan?->kode_pembayaran,
                    'nama_pembayaran' => $p->tagihan?->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $p->tagihan?->jenis_tampilan,
                ],
                'verifier' => $p->verifier ? [
                    'id' => $p->verifier->id,
                    'nama' => $p->verifier->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }
}

