<?php

namespace App\Services;

use App\Models\AktivasiStatus;
use App\Models\AcademicCalendar;
use App\Models\MahasiswaDetail;
use App\Models\MahasiswaProgram;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TagihanGenerator
{
    /**
     * Generate tagihan non-BSS untuk mahasiswa pada semester tertentu (dipakai tes / skrip).
     * Pemilihan status "Aktif" pada aktivasi mahasiswa tidak lagi memanggil method ini;
     * tagihan dibuat manual oleh admin/kasir. Status BSS tetap membuat tagihan BSS lewat controller aktivasi.
     *
     * Catatan:
     * - Tagihan dibuat dengan `available_from` agar bisa "muncul belakangan" tanpa scheduler.
     * - Jika ada `rule_json` di database (data lama), generator tetap membacanya; form admin tidak lagi mengubahnya.
     */
    public function generateForAktif(AktivasiStatus $aktivasi): Collection
    {
        $semesterLabel = (string) $aktivasi->semester_label;
        $mahasiswa = MahasiswaDetail::query()->with('program')->find($aktivasi->mahasiswa_id);
        if (!$mahasiswa) {
            return collect();
        }

        $program = $mahasiswa->program ?: new MahasiswaProgram([
            'program_type' => 'reguler',
            'kip_scheme' => null,
            'is_transfer' => false,
            'start_semester' => 1,
        ]);

        // Transfer: jangan menagih semester di bawah start_semester
        if ((int) $mahasiswa->semester_aktif < (int) ($program->start_semester ?? 1)) {
            return collect();
        }

        $tarifBssName = config('academic.bss_tarif_name', 'Administrasi BSS');

        $tarifs = TarifMaster::query()
            ->where('angkatan', (string) $mahasiswa->angkatan)
            ->where('program_studi', (string) $mahasiswa->program_studi)
            ->where('nama_pembayaran', '!=', $tarifBssName)
            ->orderBy('nama_pembayaran')
            ->get();

        $created = collect();
        foreach ($tarifs as $tarif) {
            if (!$this->tarifAppliesToSemester($tarif, $mahasiswa, $semesterLabel)) {
                continue;
            }

            $amount = (float) ($tarif->nominal ?? 0);
            $override = $this->resolveProgramOverride($tarif, $program);
            if ($override['action'] === 'skip') {
                continue;
            }
            if ($override['action'] === 'amount') {
                $amount = (float) $override['amount'];
            }
            if ($amount <= 0) {
                continue;
            }

            $dates = $this->resolveDatesForTarif($tarif, $semesterLabel);

            $exists = Tagihan::query()
                ->where('mahasiswa_id', $aktivasi->mahasiswa_id)
                ->where('tarif_id', $tarif->tarif_id)
                ->where('semester_label', $semesterLabel)
                ->where(function ($q) {
                    $q->whereNull('is_bss')->orWhere('is_bss', false);
                })
                ->exists();

            if ($exists) {
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
                'is_bss' => false,
                'status' => 'Belum Lunas',
            ]);

            $created->push($tagihan);
        }

        return $created;
    }

    protected function tarifAppliesToSemester(TarifMaster $tarif, MahasiswaDetail $mahasiswa, string $semesterLabel): bool
    {
        $rule = is_array($tarif->rule_json) ? $tarif->rule_json : [];

        // hanya semester genap (berdasarkan label, mis ".... Genap")
        if (!empty($rule['only_even_semester'])) {
            return str_contains(mb_strtolower($semesterLabel), 'genap');
        }
        if (!empty($rule['only_odd_semester'])) {
            return str_contains(mb_strtolower($semesterLabel), 'ganjil');
        }

        // hanya semester tertentu (angka semester_aktif mahasiswa)
        if (!empty($rule['only_semester_in']) && is_array($rule['only_semester_in'])) {
            return in_array((int) $mahasiswa->semester_aktif, array_map('intval', $rule['only_semester_in']), true);
        }
        if (!empty($rule['only_semester_range']) && is_array($rule['only_semester_range'])) {
            $min = isset($rule['only_semester_range'][0]) ? (int) $rule['only_semester_range'][0] : null;
            $max = isset($rule['only_semester_range'][1]) ? (int) $rule['only_semester_range'][1] : null;
            $sem = (int) $mahasiswa->semester_aktif;
            if ($min !== null && $sem < $min) return false;
            if ($max !== null && $sem > $max) return false;
        }

        return true;
    }

    /**
     * Override per program (KIP/reguler/eksklusif).
     * Bentuk rule_json yang didukung:
     *   program_overrides: {
     *     kip: { "1": { action: "skip" }, "2": { action: "amount", amount: 0 } }
     *   }
     */
    protected function resolveProgramOverride(TarifMaster $tarif, MahasiswaProgram $program): array
    {
        $rule = is_array($tarif->rule_json) ? $tarif->rule_json : [];
        $overrides = is_array($rule['program_overrides'] ?? null) ? $rule['program_overrides'] : [];

        $type = (string) ($program->program_type ?? 'reguler');
        $typeOverrides = is_array($overrides[$type] ?? null) ? $overrides[$type] : [];

        if ($type !== 'kip') {
            return ['action' => 'none'];
        }

        $scheme = (string) ((int) ($program->kip_scheme ?? 0));
        $schemeOverride = is_array($typeOverrides[$scheme] ?? null) ? $typeOverrides[$scheme] : [];

        $action = (string) ($schemeOverride['action'] ?? 'none');
        if ($action === 'skip') {
            return ['action' => 'skip'];
        }
        if ($action === 'amount') {
            return ['action' => 'amount', 'amount' => (float) ($schemeOverride['amount'] ?? 0)];
        }

        return ['action' => 'none'];
    }

    /**
     * Resolve available_from dan due_date.
     * Default pakai offset sederhana dari sekarang (non-breaking),
     * tapi bisa diatur di rule_json:
     * - available_from_offset_days
     * - due_in_days
     */
    protected function resolveDatesForTarif(TarifMaster $tarif, string $semesterLabel): array
    {
        $rule = is_array($tarif->rule_json) ? $tarif->rule_json : [];

        $now = Carbon::now();
        $calendar = AcademicCalendar::query()->where('semester_label', $semesterLabel)->first();

        $availableFrom = $this->resolveTriggerDate(
            $rule['available_trigger'] ?? null,
            (int) ($rule['available_offset_days'] ?? ($rule['available_from_offset_days'] ?? 0)),
            $now,
            $calendar
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
            $calendar
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
     * - "fixed_date": base=rule.fixed_date (Y-m-d), offset diabaikan
     */
    protected function resolveTriggerDate(?string $trigger, int $offsetDays, Carbon $now, ?AcademicCalendar $calendar): Carbon
    {
        $t = $trigger ? mb_strtolower((string) $trigger) : null;

        if ($t === 'semester_start_plus_days' && $calendar?->semester_start_at) {
            return Carbon::parse($calendar->semester_start_at)->addDays($offsetDays);
        }
        if ($t === 'uts_minus_days' && $calendar?->uts_start_at) {
            return Carbon::parse($calendar->uts_start_at)->subDays($offsetDays);
        }
        if ($t === 'uas_minus_days' && $calendar?->uas_start_at) {
            return Carbon::parse($calendar->uas_start_at)->subDays($offsetDays);
        }
        if ($t === 'fixed_date') {
            // dipakai kalau rule punya fixed_date
            return $now;
        }

        // default: now + offsetDays
        return $now->copy()->addDays($offsetDays);
    }
}

