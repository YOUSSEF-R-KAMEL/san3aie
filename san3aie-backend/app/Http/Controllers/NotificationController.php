<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return response()->json($notifications);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'تم تحديد الإشعار كمقروء.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'message' => 'تم تحديد جميع الإشعارات كمقروءة.',
        ]);
    }
}
