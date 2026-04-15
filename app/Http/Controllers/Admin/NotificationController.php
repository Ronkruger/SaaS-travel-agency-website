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

    /**
     * GET /admin/notifications/stream — Server-Sent Events stream.
     * Sends an `init` event on connect (full unread state, no sound),
     * then a `notification` event whenever new items appear (triggers sound).
     * Browser EventSource auto-reconnects; Last-Event-ID prevents re-sending seen items.
     */
    public function stream(): StreamedResponse
    {
        // Release session lock so other tabs/requests aren't blocked.
        request()->session()->save();

        $adminId = auth('admin')->id();
        $lastId  = (int) request()->header('Last-Event-ID', 0);

        return response()->stream(function () use ($adminId, $lastId) {
            set_time_limit(0);
            ignore_user_abort(true);

            $query = function (bool $onlyNew) use ($adminId, &$lastId) {
                $q = AdminNotification::where(function ($q) use ($adminId) {
                    $q->where('admin_user_id', $adminId)
                      ->orWhereNull('admin_user_id');
                })->where('is_read', false)->latest()->limit(20);

                if ($onlyNew && $lastId > 0) {
                    $q->where('id', '>', $lastId);
                }

                return $q->get(['id', 'type', 'title', 'body', 'url', 'created_at']);
            };

            $countUnread = function () use ($adminId) {
                return AdminNotification::where(function ($q) use ($adminId) {
                    $q->where('admin_user_id', $adminId)
                      ->orWhereNull('admin_user_id');
                })->where('is_read', false)->count();
            };

            $emit = function (string $event, array $payload, ?int $id = null) {
                if ($id !== null) {
                    echo "id: {$id}\n";
                }
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload) . "\n\n";
                @ob_flush();
                @flush();
            };

            // ── Initial state (no sound on client side) ────────────────
            $initial = $query(false);
            $maxId   = $initial->isNotEmpty() ? $initial->first()->id : 0;
            if ($maxId > $lastId) {
                $lastId = $maxId;
            }
            $emit('init', [
                'count'         => $initial->count(),
                'notifications' => $initial,
            ], $lastId ?: null);

            // ── Streaming loop ─────────────────────────────────────────
            $tick = 0;
            while (true) {
                sleep(3);

                if (connection_aborted()) {
                    break;
                }

                $new = $query(true); // only rows with id > $lastId

                if ($new->isNotEmpty()) {
                    $lastId = $new->first()->id;
                    $emit('notification', [
                        'count'         => $countUnread(),
                        'notifications' => $new,
                    ], $lastId);
                } else {
                    // Keepalive comment — prevents proxies and Railway from closing idle connections
                    echo ": ka\n\n";
                    @ob_flush();
                    @flush();
                }

                // Gracefully cycle the connection every ~55 s so PHP workers don't
                // accumulate indefinitely (EventSource auto-reconnects transparently).
                $tick += 3;
                if ($tick >= 55) {
                    break;
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=utf-8',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',   // disable nginx buffering
            'Connection'        => 'keep-alive',
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
