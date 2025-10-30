    <?php

    namespace App\Notifications;

    use App\Models\Tagihan; // <-- Import Tagihan
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Notifications\Notification;

    class TagihanBaruCreated extends Notification implements ShouldQueue // <-- Bisa juga pakai antrian
    {
        use Queueable;

        public $tagihan; // Properti untuk menyimpan data tagihan

        /**
         * Create a new notification instance.
         */
        public function __construct(Tagihan $tagihan)
        {
            $this->tagihan = $tagihan->load('tarif'); // Load relasi tarif
        }

        /**
         * Get the notification's delivery channels.
         *
         * @return array<int, string>
         */
        public function via(object $notifiable): array
        {
            // Kita hanya ingin menyimpan ke database untuk notifikasi in-app
            return ['database'];
        }

        /**
         * Get the array representation of the notification.
         *
         * @return array<string, mixed>
         */
        public function toArray(object $notifiable): array
        {
            // Data inilah yang akan disimpan di kolom 'data' (JSON) tabel 'notifications'
            return [
                'tagihan_id' => $this->tagihan->tagihan_id,
                'nama_tagihan' => $this->tagihan->tarif->nama_pembayaran ?? 'Tagihan Baru',
                'jumlah' => $this->tagihan->jumlah_tagihan,
                'message' => "Tagihan baru '{$this->tagihan->tarif->nama_pembayaran}' sejumlah Rp " . number_format($this->tagihan->jumlah_tagihan, 0, ',', '.') . " telah dibuat.",
                // Link yang akan dituju saat notifikasi diklik
                'link' => route('mahasiswa.pembayaran.show', $this->tagihan->tagihan_id)
            ];
        }
    }

