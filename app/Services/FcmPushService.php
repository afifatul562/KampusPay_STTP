<?php

namespace App\Services;

use App\Models\DeviceFcmToken;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmPushService
{
    private ?Messaging $messaging = null;

    public function __construct()
    {
        $path = $this->resolveCredentialsPath();
        if ($path === null) {
            Log::warning('FCM: file service account tidak ditemukan atau tidak terbaca. Cek FIREBASE_CREDENTIALS di .env (disarankan: storage/firebase/service-account.json relatif ke folder project).');

            return;
        }

        try {
            $this->messaging = (new Factory)->withServiceAccount($path)->createMessaging();
        } catch (\Throwable $e) {
            Log::warning('FCM: gagal inisialisasi Firebase: '.$e->getMessage());
        }
    }

    /**
     * Path di .env bisa relatif; saat request web, working directory sering bukan root project.
     */
    private function resolveCredentialsPath(): ?string
    {
        $configured = config('services.firebase.credentials');
        if (! is_string($configured) || trim($configured) === '') {
            return null;
        }

        $path = trim($configured);
        if (! str_starts_with($path, '/') && ! preg_match('#^[A-Za-z]:[/\\\\]#', $path)) {
            $path = base_path($path);
        }

        return is_readable($path) ? $path : null;
    }

    public function isEnabled(): bool
    {
        return $this->messaging !== null;
    }

    /**
     * @param  list<string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (! $this->messaging) {
            Log::warning('FCM: push dilewati (Firebase belum aktif).');

            return;
        }
        if ($tokens === []) {
            Log::info('FCM: push dilewati (tidak ada token perangkat untuk user ini).');

            return;
        }

        $strData = [];
        foreach ($data as $k => $v) {
            $strData[(string) $k] = is_scalar($v) ? (string) $v : (string) json_encode($v);
        }

        foreach (array_values(array_unique(array_filter($tokens))) as $token) {
            try {
                $message = CloudMessage::new()
                    ->withToken($token)
                    ->withNotification(Notification::create($title, $body));
                if ($strData !== []) {
                    $message = $message->withData($strData);
                }

                $this->messaging->send($message);
            } catch (NotFound) {
                DeviceFcmToken::where('token', $token)->delete();
            } catch (\Throwable $e) {
                Log::warning('FCM: gagal kirim: '.$e->getMessage());
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyUserDevices(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceFcmToken::query()->where('user_id', $user->id)->pluck('token')->all();
        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function notifyTagihanBaru(Tagihan $tagihan): void
    {
        $tagihan->loadMissing('mahasiswa.user');
        $user = $tagihan->mahasiswa?->user;
        if (! $user) {
            return;
        }

        $nama = $tagihan->jenis_tampilan ?? 'Tagihan';
        $this->notifyUserDevices(
            $user,
            'Tagihan baru',
            "Ada tagihan: {$nama}",
            [
                'type' => 'tagihan_baru',
                'tagihan_id' => (string) $tagihan->tagihan_id,
            ],
        );
    }

    public function notifyTagihanJatuhTempo(Tagihan $tagihan, string $whenLabel): void
    {
        $tagihan->loadMissing('mahasiswa.user');
        $user = $tagihan->mahasiswa?->user;
        if (! $user) {
            return;
        }

        $due = $tagihan->tanggal_jatuh_tempo?->translatedFormat('d M Y') ?? '-';
        $nama = $tagihan->jenis_tampilan ?? 'Tagihan';
        $this->notifyUserDevices(
            $user,
            'Jatuh tempo tagihan',
            "{$whenLabel}: {$nama} ({$due})",
            [
                'type' => 'tagihan_jatuh_tempo',
                'tagihan_id' => (string) $tagihan->tagihan_id,
            ],
        );
    }
}
