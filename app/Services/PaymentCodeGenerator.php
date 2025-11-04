<?php

namespace App\Services;

use App\Models\Tagihan;
use Illuminate\Support\Str;

class PaymentCodeGenerator
{
    /**
     * Generate unique, consistent payment code for tagihan.
     *
     * New format (unambiguous & ordered): INV-KP-YYYYMMDD-XXXX
     * - YYYYMMDD: tanggal pembuatan
     * - XXXX     : running sequence (zero-padded 4 digits) per tanggal
     *
     * Contoh: INV-KP-20251103-0007
     *
     * Catatan: Parameter $tarifId dibiarkan untuk kompatibilitas backward,
     *          namun tidak digunakan dalam format baru.
     *
     * @param int $tarifId
     * @return string
     */
    public static function generate(int $tarifId): string
    {
        $datePart = now()->format('Ymd');
        $bizPrefix = strtoupper((string) config('app.payment_code_prefix', 'STTP'));
        $prefix = 'INV-' . $bizPrefix . '-' . $datePart . '-';

        // Ambil sequence terakhir untuk tanggal ini
        $lastCode = Tagihan::where('kode_pembayaran', 'like', $prefix . '%')
            ->orderBy('kode_pembayaran', 'desc')
            ->value('kode_pembayaran');

        $lastSeq = 0;
        if ($lastCode && preg_match('/^(?:INV-' . preg_quote($bizPrefix, '/') . '-\d{8}-)(\d{4})$/', $lastCode, $m)) {
            $lastSeq = (int) $m[1];
        }

        // Cari kode berikutnya yang benar-benar unik
        do {
            $lastSeq++;
            $candidate = $prefix . str_pad((string)$lastSeq, 4, '0', STR_PAD_LEFT);
        } while (Tagihan::where('kode_pembayaran', $candidate)->exists());

        return $candidate;
    }
}

