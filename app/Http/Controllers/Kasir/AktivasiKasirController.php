<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\MahasiswaDetail;
use App\Models\Setting;
use App\Services\BssTagihanService;
use App\Services\TagihanGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AktivasiKasirController extends Controller
{
    /**
     * Menampilkan halaman aktivasi mahasiswa untuk kasir.
     */
    public function show()
    {
        return view('kasir.aktivasi');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isKasir()) {
            abort(403);
        }
        $semester = Setting::currentSemesterLabel();
        $data = AktivasiStatus::with('mahasiswa.user')
            ->where('semester_label', $semester)
            ->latest()
            ->take(20)
            ->get();
        return response()->json(['data' => $data, 'semester' => $semester]);
    }

    public function override(Request $request, AktivasiStatus $aktivasi)
    {
        $request->validate([
            'status' => ['required', Rule::in(['aktif', 'bss'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        if (!$user->isKasir() && !$user->isAdmin()) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $aktivasi->fill([
                'status' => $request->status,
                'note' => $request->note,
                'chosen_by_user_id' => $user->id,
                'chosen_by_role' => $user->role,
            ]);
            $aktivasi->save();

            if ($request->status === 'bss') {
                app(BssTagihanService::class)->ensureForAktivasi($aktivasi);
            } elseif ($request->status === 'aktif') {
                app(TagihanGenerator::class)->generateForAktif($aktivasi);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status di-override.', 'data' => $aktivasi]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Override aktivasi gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal override status.'], 500);
        }
    }

    public function createFromMahasiswa(Request $request, MahasiswaDetail $mahasiswa)
    {
        $request->validate([
            'status' => ['required', Rule::in(['aktif', 'bss'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $semester = Setting::currentSemesterLabel();
        $user = $request->user();
        if (!$user->isKasir() && !$user->isAdmin()) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $aktivasi = AktivasiStatus::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa->mahasiswa_id, 'semester_label' => $semester],
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
            return response()->json(['success' => true, 'message' => 'Status disimpan.', 'data' => $aktivasi]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Simpan aktivasi kasir gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan status.'], 500);
        }
    }

}

