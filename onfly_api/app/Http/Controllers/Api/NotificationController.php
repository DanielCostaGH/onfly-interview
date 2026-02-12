<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * List notifications (optionally only unread).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'unread' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $onlyUnread = $request->boolean('unread');
        $perPage = (int) ($validated['per_page'] ?? 15);

        $notifications = $onlyUnread
            ? $request->user()->unreadNotifications()
            : $request->user()->notifications();

        return NotificationResource::collection(
            $notifications->orderByDesc('created_at')->paginate($perPage)->withQueryString()
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notificação marcada como lida.',
        ]);
    }
}
