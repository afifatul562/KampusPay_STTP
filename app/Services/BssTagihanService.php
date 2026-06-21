<?php

namespace App\Services;

use App\Models\AktivasiStatus;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use Carbon\Carbon;

class BssTagihanService
{
    public function ensureForAktivasi(AktivasiStatus $aktivasi): void
    {
        $amount = config('academic.bss_amount');
        $dueIn = (int) config('academic.bss_due_in_days', 14);
        $tarifName = config('academic.bss_tarif_name', 'Administrasi BSS');

        $tarif = TarifMaster::firstOrCreate(
            ['nama_pembayaran' => $tarifName],
            ['nominal' => $amount]
        );

        $existing = Tagihan::query()
            ->where('mahasiswa_id', $aktivasi->mahasiswa_id)
            ->where('tarif_id', $tarif->tarif_id)
            ->where('is_bss', true)
            ->first();

        if ($existing) {
            return;
        }

        Tagihan::create([
            'mahasiswa_id' => $aktivasi->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => PaymentCodeGenerator::generate($tarif->tarif_id),
            'jumlah_tagihan' => $amount,
            'total_angsuran' => 0,
            'sisa_pokok' => null,
            'tanggal_jatuh_tempo' => Carbon::now()->addDays($dueIn)->toDateString(),
            'semester_label' => $aktivasi->semester_label,
            'is_bss' => true,
            'status' => 'Belum Lunas',
        ]);
    }
}
