<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        $plans = Plan::active()->get();
        return view('central.auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'company_name' => ['required', 'string', 'max:255'],
            'tenant_slug'  => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
                'unique:tenants,id',
            ],
        ]);

        $tenantId = $validated['tenant_slug'];

        // Tenant::create() fires TenantCreated → CreateDatabase + MigrateDatabase + SeedDatabase
        $tenant = Tenant::create([
            'id'           => $tenantId,
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'company_name' => $validated['company_name'],
            'plan'         => 'trial',
            'trial_ends_at' => now()->addDays(30),
            'is_active'    => true,
        ]);

        // Switch to tenant context and create their first admin user
        tenancy()->initialize($tenant);

        \App\Models\AdminUser::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'department'   => 'executives',
            'position'     => 'Owner',
            'is_onboarded' => false,
        ]);

        tenancy()->end();

        // Store tenant owner session for billing portal
        session(['tenant_owner' => $tenantId]);

        return redirect(url("/t/{$tenantId}/admin/auth/login"))
            ->with('success', 'Your agency has been created! Log in below to get started.');
    }

    public function showLoginForm()
    {
        return view('central.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $tenant = Tenant::where('email', $validated['email'])->first();

        if (!$tenant || !Hash::check($validated['password'], $tenant->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->only('email'));
        }

        session(['tenant_owner' => $tenant->id]);

        // Send directly to their admin panel
        return redirect(url("/t/{$tenant->id}/admin/auth/login"))
            ->with('success', 'Welcome back! Log in to your agency dashboard.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('tenant_owner');
        return redirect()->route('central.home');
    }
}
