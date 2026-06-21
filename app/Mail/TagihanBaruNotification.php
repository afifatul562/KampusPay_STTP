<?php

namespace App\Mail;

use App\Models\Tagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Dikirim dengan Mail::send() saat tagihan dibuat agar tidak bergantung pada queue worker.
 */
class TagihanBaruNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Tagihan $tagihan;

    public function __construct(Tagihan $tagihan)
    {
        $this->tagihan = $tagihan->load('tarif', 'mahasiswa.user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tagihan Baru KampusPay: ' . $this->tagihan->jenis_tampilan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tagihan-baru',
        );
    }
}
