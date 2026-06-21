<?php

namespace App\Notifications;

use App\Models\KonfirmasiPembayaran;
use Illuminate\Notifications\Notification;

class TransferPaymentSubmitted extends Notification
{
    public function __construct(public KonfirmasiPembayaran $konfirmasi)
    {
        $this->konfirmasi->loadMissing('tagihan.tarif', 'tagihan.mahasiswa.user');
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
        $tagihan = $this->konfirmasi->tagihan;
        $mhs = $tagihan?->mahasiswa;
        $nama = $mhs?->user?->nama_lengkap ?? 'Mahasiswa';
        $npm = $mhs?->npm ?? '-';
        $jenis = $tagihan?->jenis_tampilan ?? optional($tagihan?->tarif)->nama_pembayaran ?? 'Tagihan';

        $nominal = $this->konfirmasi->is_cicilan && $this->konfirmasi->jumlah_bayar
            ? (float) $this->konfirmasi->jumlah_bayar
            : (float) ($tagihan?->sisa_pokok ?? $tagihan?->jumlah_tagihan ?? 0);

        $nominalFmt = 'Rp '.number_format($nominal, 0, ',', '.');
        $cicilanNote = $this->konfirmasi->is_cicilan ? ' (cicilan)' : '';

        $link = rtrim((string) config('app.public_url'), '/')
            .route('kasir.verifikasi.index', ['konfirmasi' => $this->konfirmasi->konfirmasi_id], false);

        return [
            'type' => 'transfer_submitted',
            'konfirmasi_id' => $this->konfirmasi->konfirmasi_id,
            'tagihan_id' => $this->konfirmasi->tagihan_id,
            'mahasiswa_id' => $mhs?->mahasiswa_id,
            'nama' => $nama,
            'npm' => $npm,
            'jenis_tagihan' => $jenis,
            'jumlah' => $nominal,
            'is_cicilan' => (bool) $this->konfirmasi->is_cicilan,
            'message' => "{$nama} ({$npm}) mengirim bukti transfer{$cicilanNote} — {$jenis} — {$nominalFmt}",
            'link' => $link,
        ];
    }
}
