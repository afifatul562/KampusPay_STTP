<?php

namespace App\Services;

use App\Models\AktivasiStatus;
use App\Models\AcademicCalendar;
use App\Models\MahasiswaDetail;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Notifications\TagihanBaruCreated;
use App\Support\SemesterPerkuliahan;
use App\Support\TagihanDedup;
use App\Support\TarifBillingRules;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TagihanGenerator
{
    /**
     * Generate tagihan non-BSS saat mahasiswa memilih status Aktif.
     * Uang Wisuda di-skip (muncul lewat form daftar wisuda — Fase 2).
     */
    public function generateForAktif(AktivasiStatus $aktivasi): Collection
    {
        $semesterLabel = (string) $aktivasi->semester_label;
        $mahasiswa = MahasiswaDetail::query()->with('user')->find($aktivasi->mahasiswa_id);
        if (!$mahasiswa) {
            return collect();
        }

        $tarifs = $this->resolveTarifsForMahasiswa($mahasiswa);
        $created = collect();

        foreach ($tarifs as $tarif) {
            if (TarifBillingRules::skipOnAktivasi($tarif)) {
                continue;
            }

            if (!TarifBillingRules::appliesToMahasiswa($tarif, $mahasiswa, $semesterLabel)) {
                continue;
            }

            $amount = (float) ($tarif->nominal ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $dates = $this->resolveDatesForTarif($tarif, $semesterLabel);

            if (TagihanDedup::alreadyExistsForAktivasi(
                $aktivasi->mahasiswa_id,
                $tarif,
                $mahasiswa,
                $semesterLabel,
            )) {
                continue;
            }

            $tagihan = Tagihan::create([
                'mahasiswa_id' => $aktivasi->mahasiswa_id,
                'tarif_id' => $tarif->tarif_id,
                'kode_pembayaran' => PaymentCodeGenerator::generate($tarif->tarif_id),
                'jumlah_tagihan' => $amount,
                'total_angsuran' => 0,
                'sisa_pokok' => null,
                'tanggal_jatuh_tempo' => $dates['due_date'],
                'available_from' => $dates['available_from'],
                'semester_label' => $semesterLabel,
                'semester_perkuliahan' => SemesterPerkuliahan::fromMahasiswa($mahasiswa),
                'is_bss' => false,
                'status' => 'Belum Lunas',
            ]);

            $this->notifyMahasiswaTagihanBaru($tagihan, $mahasiswa);
            $created->push($tagihan);
        }

        if ($created->isEmpty()) {
            Log::info('TagihanGenerator: tidak ada tagihan baru dibuat.', [
                'mahasiswa_id' => $aktivasi->mahasiswa_id,
                'semester_label' => $semesterLabel,
            ]);
        } else {
            Log::info('TagihanGenerator: tagihan aktif dibuat.', [
                'mahasiswa_id' => $aktivasi->mahasiswa_id,
                'semester_label' => $semesterLabel,
                'count' => $created->count(),
            ]);
        }

        return $created;
    }

    /**
     * Ambil tarif yang cocok untuk mahasiswa; per nama pembayaran ambil yang paling spesifik.
     */
    protected function resolveTarifsForMahasiswa(MahasiswaDetail $mahasiswa): Collection
    {
        $angkatan = (string) $mahasiswa->angkatan;
        $prodi = (string) $mahasiswa->program_studi;
        $tarifBssName = config('academic.bss_tarif_name', 'Administrasi BSS');

        $candidates = TarifMaster::query()
            ->where('nama_pembayaran', '!=', $tarifBssName)
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
            ->orderBy('nama_pembayaran', 'asc')
            ->get();

        return $candidates
            ->groupBy('nama_pembayaran')
            ->map(function (Collection $group) use ($angkatan, $prodi) {
                return $group->sortByDesc(function (TarifMaster $t) use ($angkatan, $prodi) {
                    $score = 0;
                    if ((string) $t->angkatan === $angkatan) {
                        $score += 2;
                    }
                    if ((string) $t->program_studi === $prodi) {
                        $score += 2;
                    }

                    return $score;
                })->first();
            })
            ->filter()
            ->values();
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
            Log::warning('Notifikasi tagihan baru gagal: '.$e->getMessage(), [
                'tagihan_id' => $tagihan->tagihan_id,
            ]);
        }
    }

    protected function resolveDatesForTarif(TarifMaster $tarif, string $semesterLabel): array
    {
        $rule = TarifBillingRules::effectiveRule($tarif);

        $now = Carbon::now();
        $calendar = AcademicCalendar::query()->where('semester_label', $semesterLabel)->first();

        $availableFrom = $this->resolveTriggerDate(
            $rule['available_trigger'] ?? null,
            (int) ($rule['available_offset_days'] ?? ($rule['available_from_offset_days'] ?? 0)),
            $now,
            $calendar,
            $semesterLabel,
            'available'
        )->toDateString();

        $dueTriggerRaw = $rule['due_trigger'] ?? null;
        $dueTriggerNorm = $dueTriggerRaw !== null && trim((string) $dueTriggerRaw) !== ''
            ? mb_strtolower(trim((string) $dueTriggerRaw))
            : null;

        $usesCalendarDue = $dueTriggerNorm !== null
            && $dueTriggerNorm !== 'now_plus_days';

        $configDefault = (int) config('academic.default_due_in_days', 30);
        $ruleDueOffset = array_key_exists('due_offset_days', $rule) || array_key_exists('due_in_days', $rule)
            ? (int) ($rule['due_offset_days'] ?? $rule['due_in_days'] ?? $configDefault)
            : null;

        if ($usesCalendarDue) {
            $dueOffset = (int) ($rule['due_offset_days'] ?? ($rule['due_in_days'] ?? 0));
        } elseif ($ruleDueOffset !== null) {
            $dueOffset = $ruleDueOffset;
        } else {
            $dueOffset = $configDefault;
        }

        $dueDate = $this->resolveTriggerDate(
            $usesCalendarDue ? ($rule['due_trigger'] ?? null) : null,
            $dueOffset,
            $now,
            $calendar,
            $semesterLabel,
            'due'
        )->toDateString();

        return [
            'available_from' => $availableFrom,
            'due_date' => $dueDate,
        ];
    }

    /**
     * Trigger yang didukung:
     * - null / "now_plus_days": base=now, +offsetDays
     * - "semester_start_plus_days": base=calendar.semester_start_at, +offsetDays
     * - "uts_minus_days": base=calendar.uts_start_at, -offsetDays
     * - "uas_minus_days": base=calendar.uas_start_at, -offsetDays
     */
    protected function resolveTriggerDate(
        ?string $trigger,
        int $offsetDays,
        Carbon $now,
        ?AcademicCalendar $calendar,
        string $semesterLabel,
        string $context,
    ): Carbon {
        $t = $trigger ? mb_strtolower((string) $trigger) : null;
        $fallbackDays = (int) config('academic.default_due_in_days', 30);

        if ($t === 'semester_start_plus_days') {
            if ($calendar?->semester_start_at) {
                return Carbon::parse($calendar->semester_start_at)->addDays($offsetDays);
            }
            Log::warning("Kalender akademik belum punya mulai semester ({$semesterLabel}) untuk {$context}.");

            return $now->copy()->addDays($fallbackDays);
        }

        if ($t === 'uts_minus_days') {
            if ($calendar?->uts_start_at) {
                return Carbon::parse($calendar->uts_start_at)->subDays($offsetDays);
            }
            Log::warning("Kalender akademik belum punya tanggal UTS ({$semesterLabel}) untuk {$context}.");

            return $now->copy()->addDays($fallbackDays);
        }

        if ($t === 'uas_minus_days') {
            if ($calendar?->uas_start_at) {
                return Carbon::parse($calendar->uas_start_at)->subDays($offsetDays);
            }
            Log::warning("Kalender akademik belum punya tanggal UAS ({$semesterLabel}) untuk {$context}.");

            return $now->copy()->addDays($fallbackDays);
        }

        return $now->copy()->addDays($offsetDays);
    }
}
