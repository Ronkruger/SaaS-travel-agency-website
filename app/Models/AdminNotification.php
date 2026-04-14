<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'admin_user_id',
        'type',
        'title',
        'body',
        'url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * Broadcast a notification to all admin users (or a specific one).
     */
    public static function broadcast(
        string $type,
        string $title,
        string $body,
        ?string $url = null,
        ?int $adminUserId = null
    ): void {
        if ($adminUserId) {
            static::create([
                'admin_user_id' => $adminUserId,
                'type'          => $type,
                'title'         => $title,
                'body'          => $body,
                'url'           => $url,
            ]);
            return;
        }

        // Broadcast to all admins
        $adminIds = AdminUser::pluck('id');
        foreach ($adminIds as $id) {
            static::create([
                'admin_user_id' => $id,
                'type'          => $type,
                'title'         => $title,
                'body'          => $body,
                'url'           => $url,
            ]);
        }
    }
}
