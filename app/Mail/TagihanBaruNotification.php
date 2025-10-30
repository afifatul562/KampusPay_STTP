<?php

namespace App\Mail;

use App\Models\Tagihan; // <-- 1. Import model Tagihan
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- 2. Implementasi antrian
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TagihanBaruNotification extends Mailable implements ShouldQueue // <-- 3. Tambahkan "implements ShouldQueue"
{
    use Queueable, SerializesModels;

    // 4. Buat properti publik untuk menyimpan data tagihan
    public $tagihan;

    /**
     * Create a new message instance.
     */
    public function __construct(Tagihan $tagihan) // <-- 5. Terima data tagihan saat Mailable dibuat
    {
        $this->tagihan = $tagihan->load('tarif', 'mahasiswa.user'); // 6. Load relasi agar bisa dipakai di email
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // 7. Tentukan subjek email
            subject: 'Tagihan Baru KampusPay: ' . $this->tagihan->tarif->nama_pembayaran,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // 8. Tentukan file view mana yang akan digunakan
            view: 'emails.tagihan-baru',
        );
    }
}
