<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlatformDashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $recentTenants = Tenant::latest()->take(10)->get();
        return view('central.platform.dashboard', compact('stats', 'recentTenants'));
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    protected function getStats(): array
    {
        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::active()->count(),
            'trial_tenants' => Tenant::where('trial_ends_at', '>', now())->count(),
            'expired_tenants' => Tenant::expired()->count(),
            'plans' => Plan::active()->withCount(['tenants'])->get()->map(fn($p) => [
                'name' => $p->name,
                'count' => $p->tenants_count ?? 0,
            ]),
            'new_this_month' => Tenant::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
