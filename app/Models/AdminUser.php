<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'auth0_id',
        'avatar',
        'department',
        'position',
        'role',
        'is_onboarded',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_onboarded'      => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return true; // All AdminUser records are admins by definition
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function deletionRequests()
    {
        return $this->hasMany(DeletionRequest::class, 'requested_by');
    }

    public function getDepartmentLabelAttribute(): string
    {
        return \App\Http\Controllers\Admin\Auth\AdminOnboardingController::DEPARTMENTS[$this->department]['label']
            ?? ucfirst(str_replace('_', ' ', $this->department ?? ''));
    }
}
