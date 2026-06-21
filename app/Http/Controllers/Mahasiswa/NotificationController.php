<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
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
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at?->toIso8601String(),
                    'data' => $data,
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
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
