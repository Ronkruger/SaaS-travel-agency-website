<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayRequest extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'gateway_name',
        'message',
        'status',
        'admin_notes',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
