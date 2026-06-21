<?php

namespace App\Console\Commands;

use App\Services\TagihanReminderService;
use Illuminate\Console\Command;

class TagihanFcmRemindersCommand extends Command
{
    protected $signature = 'tagihan:fcm-reminders';

    protected $description = 'Kirim pengingat tagihan H-7/H-3/H-1/H-0 (in-app + FCM push)';

    public function handle(TagihanReminderService $reminders): int
    {
        $offsets = config('academic.reminder_days_before_due', [7, 3, 1, 0]);
        $this->info('Mengirim pengingat untuk offset: '.implode(', ', array_map(fn ($d) => "H-{$d}", $offsets)));

        $sent = $reminders->sendDailyReminders();

        $this->info("Selesai. Notifikasi terkirim ke {$sent} mahasiswa (per offset/hari).");

        return self::SUCCESS;
    }
}
