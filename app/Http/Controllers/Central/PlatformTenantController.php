<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformTenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('domains');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->input('plan'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->active(),
                'trial' => $query->where('trial_ends_at', '>', now()),
                'expired' => $query->expired(),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        $tenants = $query->latest()->paginate(20)->withQueryString();

        return view('central.platform.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('domains');
        return view('central.platform.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('central.platform.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:tenants,email,' . $tenant->id],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string'],
            'plan' => ['required', 'string', 'in:trial,starter,professional,enterprise'],
            'is_active' => ['boolean'],
            'trial_ends_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $tenant->update($validated);

        return redirect()->route('platform.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        $status = $tenant->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Tenant {$status} successfully.");
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('platform.tenants.index')
            ->with('success', 'Tenant and all their data deleted successfully.');
    }

    public function impersonate(Tenant $tenant)
    {
        $domain = $tenant->domains()->first();
        if (!$domain) {
            return back()->with('error', 'Tenant has no domain configured.');
        }

        // Set impersonation token in session
        session(['platform_impersonating' => $tenant->id]);
        $scheme = request()->secure() ? 'https' : 'http';
        return redirect()->away("{$scheme}://{$domain->domain}");
    }
}
