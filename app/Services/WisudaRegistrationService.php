<?php

namespace App\Services;

use App\Models\MahasiswaDetail;
use App\Models\Setting;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\WisudaRegistration;
use App\Notifications\TagihanBaruCreated;
use App\Support\WisudaEligibility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WisudaRegistrationService
{
    /**
     * Ringkasan status pendaftaran wisuda untuk satu mahasiswa.
     *
     * @return array<string, mixed>
     */
    public function statusFor(MahasiswaDetail $mahasiswa): array
    {
        $mahasiswa->loadMissing('user');
        $assessment = WisudaEligibility::assess($mahasiswa);
        $registration = WisudaRegistration::query()
            ->with(['tagihan.tarif'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();

        $activeTagihan = $this->findActiveWisudaTagihan($mahasiswa);
        $tarif = $this->resolveWisudaTarif($mahasiswa);

        $state = 'not_registered';
        if ($registration) {
            $tagihan = $registration->tagihan;
            if ($tagihan && $tagihan->status === 'Lunas') {
                $state = 'paid';
            } else {
                $state = 'registered_unpaid';
            }
        } elseif ($activeTagihan) {
            $state = 'registered_unpaid';
        }

        return [
            'eligible' => $assessment['eligible'],
            'ineligible_reason' => $assessment['reason'],
            'min_semester' => $assessment['min_semester'],
            'skripsi_label' => $assessment['skripsi_label'],
            'state' => $state,
            'registration' => $registration,
            'active_tagihan' => $activeTagihan ?? $registration?->tagihan,
            'tarif_preview' => $tarif ? [
                'nama' => $tarif->nama_pembayaran,
                'nominal' => (float) $tarif->nominal,
            ] : null,
            'due_in_days' => (int) config('academic.wisuda_due_in_days', 30),
        ];
    }

    /**
     * Daftar wisuda + buat tagihan Uang Wisuda (H+N hari).
     *
     * @return array{registration: WisudaRegistration, tagihan: Tagihan}
     */
    public function register(MahasiswaDetail $mahasiswa, bool $confirmedSkripsi): array
    {
        $mahasiswa->loadMissing('user');
        $assessment = WisudaEligibility::assess($mahasiswa);

        if (!$assessment['eligible']) {
            throw new \InvalidArgumentException($assessment['reason'] ?? 'Anda belum memenuhi syarat pendaftaran wisuda.');
        }

        if (!$confirmedSkripsi) {
            throw new \InvalidArgumentException('Anda harus menyatakan telah menyelesaikan '.$assessment['skripsi_label'].'.');
        }

        $existingRegistration = WisudaRegistration::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->first();

        if ($existingRegistration) {
            $tagihan = $existingRegistration->tagihan;
            if ($tagihan && $tagihan->status === 'Lunas') {
                throw new \InvalidArgumentException('Anda sudah terdaftar wisuda dan pembayaran telah lunas.');
            }

            throw new \InvalidArgumentException('Anda sudah terdaftar wisuda. Silakan selesaikan pembayaran terlebih dahulu.');
        }

        $activeTagihan = $this->findActiveWisudaTagihan($mahasiswa);
        if ($activeTagihan) {
            throw new \InvalidArgumentException('Tagihan wisuda masih aktif. Silakan selesaikan pembayaran terlebih dahulu.');
        }

        $tarif = $this->resolveWisudaTarif($mahasiswa);
        if (!$tarif) {
            throw new \RuntimeException('Tarif Uang Wisuda belum dikonfigurasi untuk angkatan/program studi Anda. Hubungi admin.');
        }

        $amount = (float) ($tarif->nominal ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Nominal tarif Uang Wisuda tidak valid. Hubungi admin.');
        }

        return DB::transaction(function () use ($mahasiswa, $confirmedSkripsi, $tarif, $amount) {
            $now = Carbon::now();
            $dueIn = (int) config('academic.wisuda_due_in_days', 30);
            $semesterLabel = Setting::currentSemesterLabel();

            $tagihan = Tagihan::create([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'tarif_id' => $tarif->tarif_id,
                'kode_pembayaran' => PaymentCodeGenerator::generate($tarif->tarif_id),
                'jumlah_tagihan' => $amount,
                'total_angsuran' => 0,
                'sisa_pokok' => null,
                'tanggal_jatuh_tempo' => $now->copy()->addDays($dueIn)->toDateString(),
                'available_from' => $now->toDateString(),
                'semester_label' => $semesterLabel,
                'is_bss' => false,
                'status' => 'Belum Lunas',
            ]);

            $registration = WisudaRegistration::create([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'tagihan_id' => $tagihan->tagihan_id,
                'confirmed_skripsi' => $confirmedSkripsi,
                'registered_at' => $now,
            ]);

            $this->notifyMahasiswaTagihanBaru($tagihan, $mahasiswa);

            Log::info('Wisuda: pendaftaran berhasil.', [
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'tagihan_id' => $tagihan->tagihan_id,
            ]);

            return [
                'registration' => $registration->load('tagihan.tarif'),
                'tagihan' => $tagihan->load('tarif'),
            ];
        });
    }

    public function findActiveWisudaTagihan(MahasiswaDetail $mahasiswa): ?Tagihan
    {
        return Tagihan::query()
            ->with('tarif')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->masihTunggakan()
            ->whereHas('tarif', function ($q) {
                $q->where('nama_pembayaran', 'like', '%wisuda%');
            })
            ->latest('tagihan_id')
            ->first();
    }

    protected function resolveWisudaTarif(MahasiswaDetail $mahasiswa): ?TarifMaster
    {
        $angkatan = (string) $mahasiswa->angkatan;
        $prodi = (string) $mahasiswa->program_studi;

        $candidates = TarifMaster::query()
            ->where('nama_pembayaran', 'like', '%wisuda%')
            ->where(function ($q) use ($angkatan) {
                $q->whereNull('angkatan')
                    ->orWhere('angkatan', '')
                    ->orWhere('angkatan', $angkatan);
            })
            ->where(function ($q) use ($prodi) {
                $q->whereNull('program_studi')
                    ->orWhere('program_studi', '')
                    ->orWhere('program_studi', $prodi);
            })
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates
            ->sortByDesc(function (TarifMaster $t) use ($angkatan, $prodi) {
                $score = 0;
                if ((string) $t->angkatan === $angkatan) {
                    $score += 2;
                }
                if ((string) $t->program_studi === $prodi) {
                    $score += 2;
                }

                return $score;
            })
            ->first();
    }

    protected function notifyMahasiswaTagihanBaru(Tagihan $tagihan, MahasiswaDetail $mahasiswa): void
    {
        $user = $mahasiswa->user;
        if (!$user) {
            return;
        }

        try {
            $tagihan->loadMissing('tarif', 'mahasiswa');
            $user->notify(new TagihanBaruCreated($tagihan));
        } catch (\Throwable $e) {
            Log::warning('Notifikasi tagihan wisuda gagal: '.$e->getMessage(), [
                'tagihan_id' => $tagihan->tagihan_id,
            ]);
        }
    }
}
