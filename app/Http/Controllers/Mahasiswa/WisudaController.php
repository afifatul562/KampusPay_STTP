<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaDetail;
use App\Services\WisudaRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WisudaController extends Controller
{
    public function __construct(
        protected WisudaRegistrationService $wisudaService,
    ) {}

    public function show(Request $request)
    {
        $mahasiswa = $this->resolveMahasiswa($request);
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $status = $this->wisudaService->statusFor($mahasiswa);

        return view('mahasiswa.wisuda', [
            'status' => $status,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function current(Request $request)
    {
        $mahasiswa = $this->resolveMahasiswa($request);
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatStatusPayload($this->wisudaService->statusFor($mahasiswa)),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'confirmed_skripsi' => ['accepted'],
        ], [
            'confirmed_skripsi.accepted' => 'Anda harus menyatakan telah menyelesaikan skripsi/tugas akhir.',
        ]);

        $mahasiswa = $this->resolveMahasiswa($request);
        if (!$mahasiswa) {
            return $this->registrationErrorResponse($request, 'Data mahasiswa tidak ditemukan.', 403);
        }

        try {
            $result = $this->wisudaService->register(
                $mahasiswa,
                (bool) $request->boolean('confirmed_skripsi'),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->registrationErrorResponse($request, $e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            Log::error('Gagal daftar wisuda: '.$e->getMessage());

            return $this->registrationErrorResponse($request, $e->getMessage(), 500);
        } catch (\Throwable $e) {
            Log::error('Gagal daftar wisuda: '.$e->getMessage());

            return $this->registrationErrorResponse($request, 'Gagal memproses pendaftaran wisuda.', 500);
        }

        $tagihan = $result['tagihan'];
        $registration = $result['registration'];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran wisuda berhasil. Tagihan Uang Wisuda telah dibuat.',
                'data' => [
                    'registration_id' => $registration->id,
                    'tagihan_id' => $tagihan->tagihan_id,
                    'kode_pembayaran' => $tagihan->kode_pembayaran,
                    'jumlah_tagihan' => (float) $tagihan->jumlah_tagihan,
                    'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo?->toDateString(),
                ],
            ], 201);
        }

        return redirect()
            ->route('mahasiswa.wisuda')
            ->with('success', 'Pendaftaran wisuda berhasil. Silakan lakukan pembayaran Uang Wisuda.');
    }

    protected function resolveMahasiswa(Request $request): ?MahasiswaDetail
    {
        $user = $request->user() ?? Auth::user();
        if (!$user) {
            return null;
        }

        return MahasiswaDetail::query()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $status
     * @return array<string, mixed>
     */
    protected function formatStatusPayload(array $status): array
    {
        $registration = $status['registration'] ?? null;
        $tagihan = $status['active_tagihan'] ?? null;

        return [
            'eligible' => (bool) ($status['eligible'] ?? false),
            'ineligible_reason' => $status['ineligible_reason'] ?? null,
            'min_semester' => $status['min_semester'] ?? null,
            'skripsi_label' => $status['skripsi_label'] ?? 'Skripsi',
            'state' => $status['state'] ?? 'not_registered',
            'due_in_days' => $status['due_in_days'] ?? 30,
            'tarif_preview' => $status['tarif_preview'] ?? null,
            'registration' => $registration ? [
                'id' => $registration->id,
                'registered_at' => $registration->registered_at?->toIso8601String(),
                'confirmed_skripsi' => (bool) $registration->confirmed_skripsi,
            ] : null,
            'tagihan' => $tagihan ? [
                'tagihan_id' => $tagihan->tagihan_id,
                'kode_pembayaran' => $tagihan->kode_pembayaran,
                'jumlah_tagihan' => (float) $tagihan->jumlah_tagihan,
                'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo?->toDateString(),
                'status' => $tagihan->status,
                'nama' => optional($tagihan->tarif)->nama_pembayaran,
            ] : null,
        ];
    }

    protected function registrationErrorResponse(Request $request, string $message, int $code)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $code);
        }

        return redirect()
            ->route('mahasiswa.wisuda')
            ->withInput()
            ->with('error', $message);
    }
}
