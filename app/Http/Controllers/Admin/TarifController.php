<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarifMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TarifController extends Controller
{
    public function index()
    {
        $tarifs = TarifMaster::orderBy('created_at', 'desc')->get();
        return response()->json($tarifs);
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

            // ▼▼▼ DI SINI KEAJAIBANNYA TERJADI ▼▼▼
            // Jika input kosong (dari dropdown "Berlaku untuk Semua"), ubah jadi teks.
            if (empty($validatedData['program_studi'])) {
                $validatedData['program_studi'] = 'Semua Jurusan';
            }
            if (empty($validatedData['angkatan'])) {
                $validatedData['angkatan'] = 'Semua Angkatan';
            }
            // ▲▲▲ SELESAI PERUBAHAN ▲▲▲

            $tarif = TarifMaster::create($validatedData);

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

            // Terapkan logika yang sama untuk update
            if (empty($validatedData['program_studi'])) {
                $validatedData['program_studi'] = 'Semua Jurusan';
            }
            if (empty($validatedData['angkatan'])) {
                $validatedData['angkatan'] = 'Semua Angkatan';
            }

            $tarif = TarifMaster::findOrFail($id);
            $tarif->update($validatedData);

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

            return response()->json(['success' => true, 'message' => 'Tarif berhasil dihapus']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saat hapus tarif: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}