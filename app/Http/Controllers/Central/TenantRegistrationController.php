<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Mail\PasswordResetMail;
use App\Mail\TrialActivationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
            'password'     => ['required', 'confirmed', PasswordRule::min(8)],
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
        $activationToken = Str::random(64);

        // Create tenant WITHOUT firing events (no database creation yet)
        $tenant = Tenant::withoutEvents(function () use ($validated, $tenantId, $activationToken) {
            return Tenant::create([
                'id'                => $tenantId,
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'company_name'      => $validated['company_name'],
                'activation_token'  => Hash::make($activationToken),
                'trial_activated'   => false,
                'plan'              => 'trial',
                'trial_ends_at'     => now()->addDays(30), // Will be set from activation date
                'is_active'         => false, // Not active until email confirmed
            ]);
        });

        // Send activation email
        $activationUrl = url("/trial/activate/{$tenantId}?token=" . urlencode($activationToken));
        
        try {
            Mail::to($validated['email'])->send(
                new TrialActivationMail($activationUrl, $validated['company_name'], $validated['name'])
            );
            \Log::info("Trial activation email sent to {$validated['email']} for tenant {$tenantId}");
        } catch (\Exception $e) {
            \Log::error("Failed to send trial activation email: " . $e->getMessage());
            \Log::info("Trial activation link (email failed): {$activationUrl}");
        }

        return redirect()->route('central.registration.pending')
            ->with('email', $validated['email'])
            ->with('company_name', $validated['company_name']);
    }

    public function showRegistrationPending()
    {
        return view('central.auth.registration-pending');
    }

    public function activateTrial(Request $request, $tenantId)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return redirect()->route('central.register')
                ->withErrors(['error' => 'Invalid activation link.']);
        }

        // Check if already activated
        if ($tenant->trial_activated) {
            return redirect(url("/t/{$tenantId}/admin/auth/login"))
                ->with('info', 'Your trial has already been activated. Please log in.');
        }

        // Verify activation token
        if (!Hash::check($request->token, $tenant->activation_token)) {
            return redirect()->route('central.register')
                ->withErrors(['error' => 'Invalid or expired activation token.']);
        }

        // Activate trial and create tenant database
        $tenant->update([
            'trial_activated' => true,
            'activated_at'    => now(),
            'trial_ends_at'   => now()->addDays(30),
            'is_active'       => true,
            'activation_token' => null, // Clear token after use
        ]);

        // Manually trigger tenant database creation
        $tenant->createDatabase();
        $tenant->runMigrations();

        // Switch to tenant context and create their first admin user
        tenancy()->initialize($tenant);

        \App\Models\AdminUser::create([
            'name'         => $tenant->name,
            'email'        => $tenant->email,
            'password'     => $tenant->password, // Already hashed during registration
            'department'   => 'executives',
            'position'     => 'Owner',
            'role'         => 'super_admin',
            'is_onboarded' => false,
        ]);

        tenancy()->end();

        // Store tenant owner session for billing portal
        session(['tenant_owner' => $tenantId]);

        return redirect(url("/t/{$tenantId}/admin/auth/login"))
            ->with('success', 'Your 30-day free trial has been activated! Log in below to get started.');
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

        // Check if trial has been activated
        if (!$tenant->trial_activated) {
            return back()->withErrors(['email' => 'Please activate your account by clicking the link in your email.'])
                ->withInput($request->only('email'));
        }

        // Check if trial has expired and no active subscription
        if ($tenant->plan === 'trial' && $tenant->trial_ends_at && $tenant->trial_ends_at->isPast()) {
            session(['tenant_owner' => $tenant->id]);
            return redirect()->route('central.billing.plans')
                ->with('error', 'Your 30-day trial has expired. Please upgrade to continue using your agency dashboard.');
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

    // Password Reset
    public function showForgotPasswordForm()
    {
        return view('central.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $tenant = Tenant::where('email', $request->email)->first();

        if (!$tenant) {
            // Security: Don't reveal whether email exists
            return back()->with('status', 'If that email is registered, you will receive a password reset link shortly.');
        }

        // Generate password reset token
        $token = Str::random(64);
        
        // Store token in database (you'll need a password_resets table)
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send email with reset link
        $resetUrl = url("/password/reset/{$token}?email=" . urlencode($request->email));
        
        try {
            Mail::to($request->email)->send(new PasswordResetMail($resetUrl, $tenant->company_name ?? $tenant->name));
            \Log::info("Password reset email sent to {$request->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to send password reset email: " . $e->getMessage());
            \Log::info("Password reset link (email failed): {$resetUrl}");
            // Still return success message for security (don't reveal if email failed)
        }

        return back()->with('status', 'If that email is registered, you will receive a password reset link shortly.');
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        return view('central.auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        // Verify token
        $resetRecord = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token is expired (24 hours)
        if (now()->diffInHours($resetRecord->created_at) > 24) {
            \DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Reset link has expired.']);
        }

        // Update password
        $tenant = Tenant::where('email', $request->email)->first();
        
        if (!$tenant) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $tenant->update(['password' => Hash::make($request->password)]);

        // Delete reset token
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('central.login')->with('success', 'Password reset successfully. You can now log in.');
    }
}
