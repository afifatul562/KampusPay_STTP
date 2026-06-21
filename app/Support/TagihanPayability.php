<?php

namespace App\Support;

use App\Models\AktivasiStatus;
use App\Models\Setting;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Visibilitas & syarat tagihan boleh dibayar mahasiswa (BSS/aktif, available_from).
 */
class TagihanPayability
{
    /**
     * Filter query tagihan untuk menu pembayaran / API list.
     */
    public static function applyAktivasiScope(Builder|Relation $query, ?AktivasiStatus $aktivasi): void
    {
        if ($aktivasi && $aktivasi->status === 'bss') {
            $query->where('is_bss', true);

            return;
        }

        // Aktif / belum pilih: tagihan rutin + BSS yang masih tunggakan.
        $query->where(function (Builder $q) {
            $q->where(function (Builder $inner) {
                $inner->whereNull('is_bss')->orWhere('is_bss', false);
            })->orWhere(function (Builder $inner) {
                $inner->where('is_bss', true)->where('status', '!=', 'Lunas');
            });
        });
    }

    public static function isVisibleForMahasiswa(
        Tagihan $tagihan,
        ?AktivasiStatus $aktivasi,
        ?string $today = null,
    ): bool {
        $today = $today ?? now()->toDateString();

        if ($tagihan->available_from && (string) $tagihan->available_from->toDateString() > $today) {
            return false;
        }

        if ($aktivasi && $aktivasi->status === 'bss') {
            return (bool) $tagihan->is_bss;
        }

        if ($tagihan->is_bss) {
            return $tagihan->masihTunggakan();
        }

        return true;
    }

    public static function violationMessage(Tagihan $tagihan, int $mahasiswaId): ?string
    {
        $tagihan->loadMissing('tarif');

        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        if ($aktivasi && $aktivasi->status === 'bss' && !$tagihan->is_bss) {
            return 'Status BSS: hanya tagihan BSS yang dapat dibayar.';
        }

        if ((!$aktivasi || $aktivasi->status === 'aktif') && $tagihan->is_bss && !$tagihan->masihTunggakan()) {
            return 'Tagihan BSS sudah lunas.';
        }

        $today = now()->toDateString();
        if ($tagihan->available_from && (string) $tagihan->available_from->toDateString() > $today) {
            return 'Tagihan ini belum tersedia.';
        }

        if (!self::isVisibleForMahasiswa($tagihan, $aktivasi, $today)) {
            return 'Tagihan ini tidak tersedia untuk status semester Anda.';
        }

        return null;
    }
}
