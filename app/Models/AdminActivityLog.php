<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';

    protected $fillable = [
        'admin_user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * Convenience method — call from controllers or observers.
     *
     *   AdminActivityLog::record('booking.status_changed', $booking, 'Status changed from pending to confirmed', [
     *       'from' => 'pending', 'to' => 'confirmed'
     *   ]);
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        string $description = '',
        array $changes = []
    ): self {
        return static::create([
            'admin_user_id' => auth('admin')->id(),
            'action'        => $action,
            'subject_type'  => $subject ? class_basename($subject) : null,
            'subject_id'    => $subject?->getKey(),
            'description'   => $description,
            'changes'       => count($changes) ? $changes : null,
            'ip_address'    => request()->ip(),
        ]);
    }
}
