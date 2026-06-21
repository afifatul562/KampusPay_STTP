<?php

namespace App\Services;

use App\Models\KonfirmasiPembayaran;
use App\Models\User;
use App\Notifications\TransferPaymentSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class KasirTransferNotificationService
{
    public function notifyForKonfirmasi(KonfirmasiPembayaran $konfirmasi): void
    {
        $kasirUsers = User::query()->where('role', 'kasir')->get();
        if ($kasirUsers->isEmpty()) {
            Log::info('Notifikasi transfer: tidak ada user kasir.');

            return;
        }

        try {
            Notification::send($kasirUsers, new TransferPaymentSubmitted($konfirmasi));
        } catch (\Throwable $e) {
            Log::warning('Notifikasi transfer ke kasir gagal: '.$e->getMessage(), [
                'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            ]);
        }
    }
}
