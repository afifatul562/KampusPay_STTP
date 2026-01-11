<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\MahasiswaDetail;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Notifications\AktivasiStatusChanged;
use App\Services\PaymentCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AktivasiKasirController extends Controller
{
    /**
     * Menampilkan halaman aktivasi mahasiswa untuk kasir.
     */
    public function show()
    {
        return view('kasir.aktivasi');
    }

    /**
     * Mengambil daftar aktivasi mahasiswa untuk semester saat ini.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isKasir()) {
            abort(403);
        }
        $semester = config('academic.current_semester');
        $data = AktivasiStatus::with('mahasiswa.user')
            ->where('semester_label', $semester)
            ->latest()
            ->take(20)
            ->get();
        return response()->json(['data' => $data, 'semester' => $semester]);
    }

    /**
     * Mengubah status aktivasi yang sudah ada.
     */
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
            $aktivasi->update([
                'status' => $request->status,
                'note' => $request->note,
                'chosen_by_user_id' => $user->id,
                'chosen_by_role' => $user->role,
            ]);

            Notification::send([$user], new AktivasiStatusChanged($aktivasi));
            $kasir = \App\Models\User::where('role', 'kasir')->get();
            Notification::send($kasir, new AktivasiStatusChanged($aktivasi));

            if ($request->status === 'bss') {
                $this->ensureBssTagihan($aktivasi);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status di-override.', 'data' => $aktivasi]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Override aktivasi gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal override status.'], 500);
        }
    }

    /**
     * Membuat status aktivasi baru dari data mahasiswa.
     */
    public function createFromMahasiswa(Request $request, MahasiswaDetail $mahasiswa)
    {
        $request->validate([
            'status' => ['required', Rule::in(['aktif', 'bss'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $semester = config('academic.current_semester');
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

            $kasir = \App\Models\User::where('role', 'kasir')->get();
            Notification::send($kasir, new AktivasiStatusChanged($aktivasi));

            if ($request->status === 'bss') {
                $this->ensureBssTagihan($aktivasi);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status disimpan.', 'data' => $aktivasi]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Simpan aktivasi kasir gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan status.'], 500);
        }
    }

    /**
     * Memastikan tagihan BSS dibuat jika status adalah BSS.
     */
    protected function ensureBssTagihan(AktivasiStatus $aktivasi): void
    {
        $semester = $aktivasi->semester_label;
        $amount = config('academic.bss_amount');
        $dueIn = (int) config('academic.bss_due_in_days', 14);
        $tarifName = config('academic.bss_tarif_name', 'Administrasi BSS');

        $tarif = TarifMaster::firstOrCreate(
            ['nama_pembayaran' => $tarifName],
            ['nominal' => $amount]
        );

        $existing = Tagihan::where('mahasiswa_id', $aktivasi->mahasiswa_id)
            ->where('tarif_id', $tarif->tarif_id)
            ->where('is_bss', true)
            ->first();

        if ($existing) {
            return;
        }

        $kode = PaymentCodeGenerator::generate($tarif->tarif_id);
        $jatuhTempo = Carbon::now()->addDays($dueIn)->toDateString();

        Tagihan::create([
            'mahasiswa_id' => $aktivasi->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => $kode,
            'jumlah_tagihan' => $amount,
            'total_angsuran' => 0,
            'sisa_pokok' => null,
            'tanggal_jatuh_tempo' => $jatuhTempo,
            'semester_label' => $semester,
            'is_bss' => true,
            'status' => 'Belum Lunas',
        ]);
    }
}

