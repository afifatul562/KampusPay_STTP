<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\MahasiswaDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Services\BssTagihanService;
use App\Services\TagihanGenerator;

class AktivasiController extends Controller
{
    public function show(Request $request)
    {
        if (!session('api_token')) {
            $user = Auth::user();
            $user->tokens()->where('name', 'api-token')->delete();
            $token = $user->createToken('api-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return view('mahasiswa.aktivasi');
    }

    public function current(Request $request)
    {
        $semester = Setting::currentSemesterLabel();
        $mhsId = $request->user()->mahasiswaDetail?->mahasiswa_id;

        $status = AktivasiStatus::query()
            ->where('mahasiswa_id', $mhsId)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $status,
            'semester' => $semester,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => ['required', Rule::in(['aktif', 'bss'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $mhsId = $user->mahasiswaDetail?->mahasiswa_id;
        if (!$mhsId) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        $semester = Setting::currentSemesterLabel();

        // Cek apakah sudah ada status (tidak peduli siapa yang pilih)
        $existingStatus = AktivasiStatus::query()
            ->where('mahasiswa_id', $mhsId)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        // Jika sudah ada status (baik dipilih mahasiswa maupun kasir), tolak perubahan
        // Hanya kasir yang bisa mengubah setelah ada status
        if ($existingStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Status sudah dipilih. Untuk mengubah, silakan hubungi kasir.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $aktivasi = AktivasiStatus::updateOrCreate(
                [
                    'mahasiswa_id' => $mhsId,
                    'semester_label' => $semester,
                ],
                [
                    'status' => $request->status,
                    'note' => $request->note,
                    'chosen_by_user_id' => $user->id,
                    'chosen_by_role' => $user->role,
                ]
            );

            if ($request->status === 'bss') {
                app(BssTagihanService::class)->ensureForAktivasi($aktivasi);
            } elseif ($request->status === 'aktif') {
                app(TagihanGenerator::class)->generateForAktif($aktivasi);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Status aktivasi tersimpan.',
                'data' => $aktivasi,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal simpan aktivasi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan status.'], 500);
        }
    }

}

