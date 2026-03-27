<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DIYTourCollaborator extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_collaborators';

    protected $fillable = [
        'session_id',
        'user_id',
        'permission_level',
        'invited_by',
        'invited_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DIYTourSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
