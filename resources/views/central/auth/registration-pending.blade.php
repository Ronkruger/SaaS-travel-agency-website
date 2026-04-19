@extends('central.layouts.app')
@section('title', 'Check Your Email')

@push('styles')
<style>
    .pending-page { min-height: calc(100vh - 72px); display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; background: var(--bg-alt); }
    .pending-card { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); padding: 3rem; width: 100%; max-width: 520px; text-align: center; }
    .pending-icon { font-size: 4rem; margin-bottom: 1.5rem; }
    .pending-card h2 { font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-bottom: .8rem; }
    .pending-card .sub { color: var(--text-muted); margin-bottom: 2rem; font-size: 1rem; line-height: 1.6; }
    .email-highlight { color: var(--primary); font-weight: 600; }
    .info-box { background: #f0f9ff; border: 2px solid #bae6fd; border-radius: 8px; padding: 1.5rem; margin: 2rem 0; text-align: left; }
    .info-box h3 { font-size: 1rem; font-weight: 700; color: #0c4a6e; margin-bottom: .8rem; }
    .info-box ul { margin: 0; padding-left: 1.3rem; }
    .info-box li { color: #0c4a6e; margin-bottom: .5rem; font-size: .9rem; }
    .help-text { font-size: .85rem; color: var(--text-muted); margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border); }
    .help-text a { color: var(--primary); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="pending-page">
    <div class="pending-card">
        <div class="pending-icon">📧</div>
        
        <h2>Check Your Email!</h2>
        
        <p class="sub">
            We've sent an activation link to<br>
            <span class="email-highlight">{{ session('email') }}</span>
        </p>

        <div class="info-box">
            <h3>What's Next?</h3>
            <ul>
                <li>Click the activation link in your email</li>
                <li>Your 30-day free trial will begin</li>
                <li>You'll be redirected to your agency dashboard</li>
                <li>Start adding tours and accepting bookings!</li>
            </ul>
        </div>

        <p style="color: var(--text-muted); font-size: .9rem; margin-top: 1.5rem;">
            <strong>{{ session('company_name') }}</strong> is ready to go!<br>
            Just one click away from getting started.
        </p>

        <div class="help-text">
            Didn't receive the email? Check your spam folder or <a href="{{ route('central.register') }}">try registering again</a>.<br>
            Need help? Contact support for assistance.
        </div>
    </div>
</div>
@endsection
