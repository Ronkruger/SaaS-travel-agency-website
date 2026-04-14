<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin_users';

    /**
     * All available granular permission keys.
     */
    public const PERMISSIONS = [
        'manage_tours'     => 'Manage Tours & Categories',
        'manage_bookings'  => 'Manage Bookings',
        'bulk_actions'     => 'Bulk Booking Actions',
        'manage_users'     => 'Manage Client Users',
        'manage_coupons'   => 'Manage Coupons',
        'manage_finances'  => 'Finances (Travel Fund, Payments)',
        'view_reports'     => 'View Reports & Analytics',
        'manage_settings'  => 'Branding & Settings',
        'manage_admins'    => 'Manage Admin Staff',
    ];

    /**
     * Default permissions granted to every staff member even without a custom set.
     */
    public const DEFAULT_STAFF_PERMISSIONS = [
        'manage_tours',
        'manage_bookings',
        'view_reports',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'auth0_id',
        'avatar',
        'department',
        'position',
        'role',
        'permissions',
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
        'permissions'       => 'array',
    ];

    public function isAdmin(): bool
    {
        return true; // All AdminUser records are admins by definition
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check whether this admin user has a specific permission.
     * Super admins always pass. Staff check their custom permissions
     * array, falling back to DEFAULT_STAFF_PERMISSIONS.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $granted = $this->permissions ?? self::DEFAULT_STAFF_PERMISSIONS;

        return in_array($key, $granted, true);
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
