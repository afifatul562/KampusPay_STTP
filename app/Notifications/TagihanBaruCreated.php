<?php

namespace App\Notifications;

use App\Models\Tagihan;
use Illuminate\Notifications\Notification;

class TagihanBaruCreated extends Notification
{
    public Tagihan $tagihan;

    public function __construct(Tagihan $tagihan)
    {
        $this->tagihan = $tagihan->load('tarif', 'mahasiswa');
    }

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
        return [
            'tagihan_id' => $this->tagihan->tagihan_id,
            'nama_tagihan' => $this->tagihan->jenis_tampilan,
            'jumlah' => $this->tagihan->jumlah_tagihan,
            'message' => "Tagihan baru '{$this->tagihan->jenis_tampilan}' sejumlah Rp ".number_format($this->tagihan->jumlah_tagihan, 0, ',', '.').' telah dibuat.',
            'link' => rtrim(config('app.public_url'), '/')
                .route('mahasiswa.pembayaran.pilih-metode', $this->tagihan->tagihan_id, false),
        ];
    }
}
