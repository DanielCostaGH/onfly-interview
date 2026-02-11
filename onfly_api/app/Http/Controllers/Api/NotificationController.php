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
        $onlyUnread = $request->boolean('unread');

        $notifications = $onlyUnread
            ? $request->user()->unreadNotifications()
            : $request->user()->notifications();

        return NotificationResource::collection(
            $notifications->orderByDesc('created_at')->get()
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
