<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Services\FcmPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TagihanFcmRemindersCommand extends Command
{
    protected $signature = 'tagihan:fcm-reminders';

    protected $description = 'Kirim push FCM untuk tagihan jatuh tempo hari ini & besok (belum lunas)';

    public function handle(FcmPushService $fcm): int
    {
        if (! $fcm->isEnabled()) {
            $this->warn('FCM dinonaktifkan: set FIREBASE_CREDENTIALS ke path service account JSON.');

            return self::SUCCESS;
        }

        $statuses = [
            'Belum Lunas',
            'Belum Dibayarkan',
            'Menunggu Verifikasi Transfer',
            'Menunggu Pembayaran Tunai',
            'Ditolak',
        ];

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $this->sendForDate($fcm, $statuses, $today, 'today', 'Jatuh tempo hari ini');
        $this->sendForDate($fcm, $statuses, $tomorrow, 'tomorrow', 'Besok jatuh tempo');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $statuses
     */
    private function sendForDate(
        FcmPushService $fcm,
        array $statuses,
        string $date,
        string $cacheSegment,
        string $label,
    ): void {
        $query = Tagihan::query()
            ->with('mahasiswa.user')
            ->whereIn('status', $statuses)
            ->whereDate('tanggal_jatuh_tempo', $date);

        foreach ($query->cursor() as $tagihan) {
            $key = "fcm_jt:{$cacheSegment}:{$tagihan->tagihan_id}:{$date}";
            if (Cache::has($key)) {
                continue;
            }

            $fcm->notifyTagihanJatuhTempo($tagihan, $label);
            Cache::put($key, 1, now()->addDay());
        }
    }
}
