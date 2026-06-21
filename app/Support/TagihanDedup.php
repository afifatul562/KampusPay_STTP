<?php

namespace App\Support;

use App\Models\MahasiswaDetail;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Cek apakah tagihan setara sudah ada (import legacy + aktivasi pakai kunci yang sama).
 */
class TagihanDedup
{
    public static function alreadyExistsForAktivasi(
        int $mahasiswaId,
        TarifMaster $tarif,
        MahasiswaDetail $mahasiswa,
        string $semesterLabel,
    ): bool {
        $nama = (string) $tarif->nama_pembayaran;
        $semPerkuliahan = SemesterPerkuliahan::fromMahasiswa($mahasiswa);
        $tarifIds = self::equivalentTarifIds($nama);

        if ($tarifIds === []) {
            return false;
        }

        $base = Tagihan::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('tarif_id', $tarifIds, 'and', false)
            ->where(function (Builder $q) {
                $q->whereNull('is_bss')->orWhere('is_bss', false);
            });

        if (TarifBillingRules::isOncePerMahasiswaTarifName($nama)) {
            return (clone $base)->exists();
        }

        if (LegacyTagihanGrouping::usesSemesterInGroupKey($nama)) {
            if ($semPerkuliahan !== null) {
                if ((clone $base)->where('semester_perkuliahan', $semPerkuliahan)->exists()) {
                    return true;
                }
            }

            return (clone $base)->where('semester_label', $semesterLabel)->exists();
        }

        return (clone $base)->where('semester_label', $semesterLabel)->exists();
    }

    /**
     * Tagihan lain yang setara (untuk deteksi duplikat aktivasi vs import).
     */
    public static function findConflictingTagihan(
        Tagihan $candidate,
        MahasiswaDetail $mahasiswa,
    ): ?Tagihan {
        $tarif = $candidate->tarif;
        if (! $tarif) {
            return null;
        }

        $nama = (string) $tarif->nama_pembayaran;
        $semesterLabel = (string) ($candidate->semester_label ?? '');
        $semPerkuliahan = $candidate->semester_perkuliahan !== null
            ? (int) $candidate->semester_perkuliahan
            : SemesterPerkuliahan::fromMahasiswa($mahasiswa);
        $tarifIds = self::equivalentTarifIds($nama);

        if ($tarifIds === []) {
            return null;
        }

        $base = Tagihan::query()
            ->where('mahasiswa_id', $candidate->mahasiswa_id)
            ->whereIn('tarif_id', $tarifIds, 'and', false)
            ->where('tagihan_id', '!=', $candidate->tagihan_id)
            ->where(function (Builder $q) {
                $q->whereNull('is_bss')->orWhere('is_bss', false);
            });

        if (TarifBillingRules::isOncePerMahasiswaTarifName($nama)) {
            return (clone $base)->orderByDesc('tagihan_id')->first();
        }

        if (LegacyTagihanGrouping::usesSemesterInGroupKey($nama)) {
            if ($semPerkuliahan !== null) {
                $match = (clone $base)->where('semester_perkuliahan', $semPerkuliahan)->orderByDesc('tagihan_id')->first();
                if ($match) {
                    return $match;
                }
            }

            return (clone $base)->where('semester_label', $semesterLabel)->orderByDesc('tagihan_id')->first();
        }

        return (clone $base)->where('semester_label', $semesterLabel)->orderByDesc('tagihan_id')->first();
    }

    /**
     * @return list<int>
     */
    public static function equivalentTarifIds(string $namaPembayaran): array
    {
        $n = mb_strtolower(trim($namaPembayaran));

        $query = TarifMaster::query();

        if (KampusTarifCatalog::isPpmPkkmbTarifName($n)) {
            return TarifMaster::query()
                ->get()
                ->filter(fn (TarifMaster $t) => KampusTarifCatalog::isPpmPkkmbTarifName(
                    mb_strtolower((string) $t->nama_pembayaran)
                ))
                ->pluck('tarif_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if (str_contains($n, 'uang semester')) {
            $query->where(DB::raw('LOWER(nama_pembayaran)'), 'like', '%uang semester%');
        } elseif (str_contains($n, 'kemahasiswaan')) {
            $query->where(DB::raw('LOWER(nama_pembayaran)'), 'like', '%kemahasiswaan%');
        } elseif (TarifBillingRules::isUasTarifName($n)) {
            $legacy = KampusTarifCatalog::legacyTarifNames();
            $query->where(function (Builder $q) use ($legacy) {
                $q->where('nama_pembayaran', KampusTarifCatalog::NAMA_UAS)
                    ->orWhereIn('nama_pembayaran', $legacy);
            });
        } else {
            $query->where(DB::raw('LOWER(nama_pembayaran)'), '=', $n);
        }

        return $query->pluck('tarif_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
