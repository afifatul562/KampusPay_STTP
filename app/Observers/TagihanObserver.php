<?php

namespace App\Observers;

use App\Models\Tagihan;
use App\Services\FcmPushService;
use Illuminate\Support\Facades\Log;

class TagihanObserver
{
    public function created(Tagihan $tagihan): void
    {
        try {
            app(FcmPushService::class)->notifyTagihanBaru($tagihan);
        } catch (\Throwable $e) {
            Log::warning('FCM tagihan baru: '.$e->getMessage());
        }
    }
}
