<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // ──────────────────────────────────────────────
    // Step 1 – Show email request form
    // ──────────────────────────────────────────────
    public function showRequest()
    {
        return view('auth.forgot-password');
    }

    // ──────────────────────────────────────────────
    // Step 2 – Send OTP to email
    // ──────────────────────────────────────────────
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        // Always show "success" message even if email not found (prevents enumeration)
        if (!User::where('email', $email)->exists()) {
            return back()->with('status', 'If that email is registered, you will receive an OTP shortly.');
        }

        // Delete existing OTPs for this email
        DB::table('otp_codes')->where('email', $email)->delete();

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('otp_codes')->insert([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(15),
            'created_at' => Carbon::now(),
        ]);

        Mail::to($email)->send(new OtpMail($otp, $email));

        $request->session()->put('otp_email', $email);

        return redirect()->route('password.verify')
            ->with('status', 'A 6-digit OTP has been sent to your email.');
    }

    // ──────────────────────────────────────────────
    // Step 3 – Show OTP verification form
    // ──────────────────────────────────────────────
    public function showVerify(Request $request)
    {
        if (!$request->session()->has('otp_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('otp_email'),
        ]);
    }

    // ──────────────────────────────────────────────
    // Step 4 – Verify OTP
    // ──────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        $record = DB::table('otp_codes')
            ->where('email', $email)
            ->where('otp', $request->input('otp'))
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        // OTP verified — mark session so reset form is accessible
        $request->session()->put('otp_verified', true);

        return redirect()->route('password.reset');
    }

    // ──────────────────────────────────────────────
    // Step 5 – Show new password form
    // ──────────────────────────────────────────────
    public function showReset(Request $request)
    {
        if (!$request->session()->get('otp_verified') || !$request->session()->has('otp_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    // ──────────────────────────────────────────────
    // Step 6 – Update password
    // ──────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $email = $request->session()->get('otp_email');
        $verified = $request->session()->get('otp_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Clean up
        DB::table('otp_codes')->where('email', $email)->delete();
        $request->session()->forget(['otp_email', 'otp_verified']);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. You can now log in.');
    }
}
