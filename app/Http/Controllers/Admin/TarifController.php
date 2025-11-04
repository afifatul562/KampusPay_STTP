<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarifMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class TarifController extends Controller
{
    public function index()
    {
        // Gunakan cache ringan untuk daftar tarif
        $tarifs = TarifMaster::getCachedAll();
        return response()->json([
            'success' => true,
            'data' => $tarifs
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_pembayaran' => 'required|string|max:255',
                'nominal'         => 'required|numeric|min:0',
                'program_studi'   => 'nullable|string|max:255',
                'angkatan'        => 'nullable|string|max:10'
            ]);

            $tarif = TarifMaster::create($validatedData);
            Cache::forget('tarif_master:all');

            return response()->json([
                'success' => true,
                'message' => 'Tarif berhasil dibuat',
                'data' => $tarif
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show($id)
    {
        $tarif = TarifMaster::findOrFail($id);
        return response()->json(['success' => true, 'data' => $tarif]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'nama_pembayaran' => 'sometimes|required|string|max:255',
                'nominal'         => 'sometimes|required|numeric|min:0',
                'program_studi'   => 'nullable|string|max:255',
                'angkatan'        => 'nullable|string|max:10'
            ]);

            $tarif = TarifMaster::findOrFail($id);
            $tarif->update($validatedData);
            Cache::forget('tarif_master:all');

            return response()->json([
                'success' => true,
                'message' => 'Tarif berhasil diperbarui',
                'data' => $tarif
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $tarif = TarifMaster::withCount('tagihan')->findOrFail($id);

            if ($tarif->tagihan_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus: Tarif ini sudah digunakan oleh ' . $tarif->tagihan_count . ' data tagihan.'
                ], 409);
            }

            $tarif->delete();
            Cache::forget('tarif_master:all');

            return response()->json(['success' => true, 'message' => 'Tarif berhasil dihapus']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saat hapus tarif: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}
