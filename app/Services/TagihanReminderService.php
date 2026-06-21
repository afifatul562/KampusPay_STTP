<?php

namespace App\Services;

use App\Models\Tagihan;
use App\Models\User;
use App\Notifications\TagihanJatuhTempoReminder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TagihanReminderService
{
    public function __construct(
        private FcmPushService $fcm,
    ) {}

    /**
     * Kirim pengingat in-app + FCM untuk tiap offset (H-7, H-3, H-1, H-0).
     */
    public function sendDailyReminders(): int
    {
        $offsets = config('academic.reminder_days_before_due', [7, 3, 1, 0]);
        $sent = 0;

        foreach ($offsets as $days) {
            $sent += $this->sendForDaysBefore((int) $days);
        }

        return $sent;
    }

    public function sendForDaysBefore(int $daysBefore): int
    {
        $dueDate = now()->addDays($daysBefore)->toDateString();
        $today = now()->toDateString();
        $statuses = Tagihan::statusesTunggakan();
        $sent = 0;

        $tagihans = Tagihan::query()
            ->with(['mahasiswa.user', 'tarif'])
            ->whereIn('status', $statuses)
            ->whereDate('tanggal_jatuh_tempo', $dueDate)
            ->where(function ($q) use ($today) {
                $q->whereNull('available_from')
                    ->orWhereDate('available_from', '<=', $today);
            })
            ->get()
            ->filter(fn (Tagihan $t) => $t->masihTunggakan());

        /** @var Collection<int, Collection<int, Tagihan>> $byUser */
        $byUser = $tagihans->groupBy(fn (Tagihan $t) => (int) ($t->mahasiswa?->user_id ?? 0));

        foreach ($byUser as $userId => $group) {
            if ($userId <= 0) {
                continue;
            }

            $user = $group->first()?->mahasiswa?->user;
            if (!$user instanceof User) {
                continue;
            }

            $cacheKey = "tagihan_reminder:{$daysBefore}:user:{$userId}:{$dueDate}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            $total = (float) $group->sum(fn (Tagihan $t) => $t->nominalTunggakan());
            $label = TagihanJatuhTempoReminder::labelForDaysBefore($daysBefore);
            $count = $group->count();
            $totalFmt = 'Rp '.number_format($total, 0, ',', '.');

            $body = $count === 1
                ? "{$label}: {$group->first()->jenis_tampilan} ({$totalFmt})"
                : "{$label}: {$count} tagihan (total {$totalFmt})";

            try {
                $user->notify(new TagihanJatuhTempoReminder($daysBefore, $group->values(), $total));
            } catch (\Throwable $e) {
                Log::warning('In-app reminder gagal: '.$e->getMessage(), ['user_id' => $userId]);
            }

            $this->fcm->notifyUserDevices(
                $user,
                'Pengingat tagihan',
                $body,
                [
                    'type' => 'tagihan_jatuh_tempo',
                    'days_before' => (string) $daysBefore,
                    'count' => (string) $count,
                ],
            );

            Cache::put($cacheKey, 1, now()->addDay());
            $sent++;
        }

        return $sent;
    }
}
