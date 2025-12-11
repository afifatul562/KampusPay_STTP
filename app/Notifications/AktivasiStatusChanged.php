<?php

namespace App\Notifications;

use App\Models\AktivasiStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AktivasiStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AktivasiStatus $aktivasi)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $mhs = $this->aktivasi->mahasiswa()->with('user')->first();
        return [
            'mahasiswa_id' => $this->aktivasi->mahasiswa_id,
            'semester' => $this->aktivasi->semester_label,
            'status' => $this->aktivasi->status,
            'nama' => $mhs?->user?->nama_lengkap,
            'npm' => $mhs?->npm,
            'chosen_by_role' => $this->aktivasi->chosen_by_role,
            'chosen_by_user_id' => $this->aktivasi->chosen_by_user_id,
        ];
    }
}

