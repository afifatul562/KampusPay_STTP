<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->isKasir()) {
            abort(403);
        }

        $limit = min(50, max(5, (int) $request->query('limit', 20)));

        $notifications = $user->notifications()
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($n) {
                $data = is_array($n->data) ? $n->data : [];

                return [
                    'id' => $n->id,
                    'type' => $data['type'] ?? class_basename($n->type),
                    'message' => $data['message'] ?? '',
                    'link' => $data['link'] ?? null,
                    'konfirmasi_id' => $data['konfirmasi_id'] ?? null,
                    'nama' => $data['nama'] ?? null,
                    'npm' => $data['npm'] ?? null,
                    'jenis_tagihan' => $data['jenis_tagihan'] ?? null,
                    'jumlah' => $data['jumlah'] ?? null,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        if (! $user->isKasir()) {
            abort(403);
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (! $notification) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
