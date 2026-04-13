<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletionRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'type',
        'target_id',
        'target_label',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(AdminUser::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
