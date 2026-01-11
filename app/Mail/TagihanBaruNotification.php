<?php

namespace App\Mail;

use App\Models\Tagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TagihanBaruNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tagihan;

    /**
     * Membuat instance pesan baru.
     */
    public function __construct(Tagihan $tagihan)
    {
        $this->tagihan = $tagihan->load('tarif', 'mahasiswa.user');
    }

    /**
     * Mengambil envelope pesan.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tagihan Baru KampusPay: ' . $this->tagihan->tarif->nama_pembayaran,
        );
    }

    /**
     * Mengambil definisi konten pesan.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tagihan-baru',
        );
    }
}
