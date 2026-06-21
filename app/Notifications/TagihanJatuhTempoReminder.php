<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TagihanJatuhTempoReminder extends Notification
{
    /**
     * @param  Collection<int, \App\Models\Tagihan>  $tagihans
     */
    public function __construct(
        public int $daysBefore,
        public Collection $tagihans,
        public float $totalNominal,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $count = $this->tagihans->count();
        $label = self::labelForDaysBefore($this->daysBefore);
        $total = 'Rp '.number_format($this->totalNominal, 0, ',', '.');

        $message = $count === 1
            ? "{$label}: {$this->tagihans->first()->jenis_tampilan} ({$total})."
            : "{$label}: {$count} tagihan (total {$total}).";

        return [
            'type' => 'tagihan_jatuh_tempo',
            'days_before' => $this->daysBefore,
            'count' => $count,
            'total' => $this->totalNominal,
            'message' => $message,
            'link' => rtrim(config('app.public_url', config('app.url')), '/')
                .route('mahasiswa.pembayaran.index', [], false),
            'tagihan_ids' => $this->tagihans->pluck('tagihan_id')->values()->all(),
        ];
    }

    public static function labelForDaysBefore(int $days): string
    {
        return match ($days) {
            0 => 'Jatuh tempo hari ini',
            1 => 'Besok jatuh tempo',
            3 => '3 hari lagi jatuh tempo',
            7 => '7 hari lagi jatuh tempo',
            default => "{$days} hari lagi jatuh tempo",
        };
    }
}
