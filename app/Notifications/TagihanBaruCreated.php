<?php

namespace App\Notifications;

use App\Models\Tagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TagihanBaruCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $tagihan;

    /**
     * Membuat instance notifikasi baru.
     */
    public function __construct(Tagihan $tagihan)
    {
        $this->tagihan = $tagihan->load('tarif');
    }

    /**
     * Mengambil saluran pengiriman notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Mengambil representasi array dari notifikasi.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tagihan_id' => $this->tagihan->tagihan_id,
            'nama_tagihan' => $this->tagihan->tarif->nama_pembayaran ?? 'Tagihan Baru',
            'jumlah' => $this->tagihan->jumlah_tagihan,
            'message' => "Tagihan baru '{$this->tagihan->tarif->nama_pembayaran}' sejumlah Rp " . number_format($this->tagihan->jumlah_tagihan, 0, ',', '.') . " telah dibuat.",
            'link' => route('mahasiswa.pembayaran.show', $this->tagihan->tagihan_id)
        ];
    }
}

