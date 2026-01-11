<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivasiStatus;
use App\Models\MahasiswaDetail;
use App\Notifications\AktivasiStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\TarifMaster;
use App\Models\Tagihan;
use App\Services\PaymentCodeGenerator;
use Carbon\Carbon;

class AktivasiController extends Controller
{
    /**
     * Menampilkan halaman aktivasi mahasiswa.
     */
    public function show()
    {
        return view('mahasiswa.aktivasi');
    }

    /**
     * Mengambil status aktivasi mahasiswa untuk semester saat ini.
     */
    public function current(Request $request)
    {
        $semester = config('academic.current_semester');
        $mhsId = $request->user()->mahasiswaDetail?->mahasiswa_id;

        $status = AktivasiStatus::where('mahasiswa_id', $mhsId)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $status,
            'semester' => $semester,
        ]);
    }

    /**
     * Menyimpan status aktivasi mahasiswa untuk semester saat ini.
     */
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

        $semester = config('academic.current_semester');

        // Cek apakah sudah ada status (tidak peduli siapa yang pilih)
        $existingStatus = AktivasiStatus::where('mahasiswa_id', $mhsId)
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

            // Kirim notifikasi ke semua kasir
            $kasir = \App\Models\User::where('role', 'kasir')->get();
            Notification::send($kasir, new AktivasiStatusChanged($aktivasi));

            if ($request->status === 'bss') {
                $this->ensureBssTagihan($aktivasi);
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

    /**
     * Memastikan tagihan BSS dibuat jika status adalah BSS.
     */
    protected function ensureBssTagihan(AktivasiStatus $aktivasi): void
    {
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

        if ($existing) return;

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
            'semester_label' => $aktivasi->semester_label,
            'is_bss' => true,
            'status' => 'Belum Lunas',
        ]);
    }
}

