<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    /** GET /admin/notifications/unread — last 20 unread for the current admin */
    public function unread(): JsonResponse
    {
        $adminId = auth('admin')->id();

        $baseQuery = fn () => AdminNotification::where(function ($q) use ($adminId) {
            $q->where('admin_user_id', $adminId)
              ->orWhereNull('admin_user_id');
        })->where('is_read', false);

        $totalUnread   = $baseQuery()->count();
        $notifications = $baseQuery()->latest()->limit(20)
            ->get(['id', 'type', 'title', 'body', 'url', 'created_at']);

        return response()->json([
            'count'         => $totalUnread,
            'notifications' => $notifications,
        ]);
    }

    /**
     * GET /admin/notifications/stream — kept for backward compatibility.
     * Now responds with a single `init` event and closes immediately so it
     * no longer holds a PHP-FPM worker. The JS side has switched to polling.
     */
    public function stream(): StreamedResponse
    {
        request()->session()->save();

        $adminId = auth('admin')->id();

        $notifications = AdminNotification::where(function ($q) use ($adminId) {
            $q->where('admin_user_id', $adminId)
              ->orWhereNull('admin_user_id');
        })->where('is_read', false)->latest()->limit(20)
          ->get(['id', 'type', 'title', 'body', 'url', 'created_at']);

        return response()->stream(function () use ($notifications) {
            echo "event: init\n";
            echo 'data: ' . json_encode([
                'count'         => $notifications->count(),
                'notifications' => $notifications,
            ]) . "\n\n";
            @ob_flush();
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=utf-8',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'close',
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
