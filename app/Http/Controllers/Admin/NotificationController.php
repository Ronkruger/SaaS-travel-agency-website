<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /** GET /admin/notifications/unread — last 20 unread for the current admin */
    public function unread(): JsonResponse
    {
        $adminId = auth('admin')->id();

        $notifications = AdminNotification::where(function ($q) use ($adminId) {
            $q->where('admin_user_id', $adminId)
              ->orWhereNull('admin_user_id');
        })
            ->where('is_read', false)
            ->latest()
            ->limit(20)
            ->get(['id', 'type', 'title', 'body', 'url', 'created_at']);

        return response()->json([
            'count'         => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    /** PATCH /admin/notifications/{notification}/read */
    public function markRead(AdminNotification $notification): JsonResponse
    {
        $notification->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /** POST /admin/notifications/read-all */
    public function markAllRead(): JsonResponse
    {
        $adminId = auth('admin')->id();

        AdminNotification::where(function ($q) use ($adminId) {
            $q->where('admin_user_id', $adminId)
              ->orWhereNull('admin_user_id');
        })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /** DELETE /admin/notifications/clear — delete ALL notifications for the current admin */
    public function clearAll(): JsonResponse
    {
        $adminId = auth('admin')->id();

        AdminNotification::where('admin_user_id', $adminId)->delete();

        return response()->json(['ok' => true]);
    }
}
