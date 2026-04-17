@extends('central.layouts.app')
@section('title', 'Create Your Agency')

@push('styles')
<style>
    .auth-page { min-height: calc(100vh - 72px); display: grid; grid-template-columns: 1fr 1fr; }
    .auth-left { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; padding: 4rem; display: flex; flex-direction: column; justify-content: center; }
    .auth-left h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 1rem; }
    .auth-left p { color: rgba(255,255,255,.85); font-size: 1rem; margin-bottom: 2rem; }
    .auth-benefits { list-style: none; display: flex; flex-direction: column; gap: .8rem; }
    .auth-benefits li { display: flex; gap: .8rem; align-items: center; font-size: .95rem; }
    .auth-benefits li i { color: var(--accent); font-size: 1rem; }
    .auth-right { display: flex; flex-direction: column; justify-content: center; padding: 4rem; }
    .auth-right h2 { font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-bottom: .5rem; }
    .auth-right .sub { color: var(--text-muted); margin-bottom: 2rem; }
    .form-group { margin-bottom: 1.3rem; }
    .form-group label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: .4rem; color: var(--text); }
    .form-group input, .form-group select { width: 100%; padding: .7rem 1rem; border: 2px solid var(--border); border-radius: 8px; font-family: inherit; font-size: .95rem; transition: border-color .2s; color: var(--text); }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); }
    .form-group .hint { font-size: .8rem; color: var(--text-muted); margin-top: .3rem; }
    .subdomain-wrap { display: flex; align-items: center; border: 2px solid var(--border); border-radius: 8px; overflow: hidden; transition: border-color .2s; }
    .subdomain-wrap:focus-within { border-color: var(--primary); }
    .subdomain-wrap input { border: none; flex: 1; padding: .7rem 1rem; font-family: inherit; font-size: .95rem; }
    .subdomain-wrap input:focus { outline: none; }
    .subdomain-suffix { padding: .7rem 1rem; background: var(--bg-alt); color: var(--text-muted); font-size: .9rem; white-space: nowrap; border-left: 1px solid var(--border); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .error-msg { color: #dc2626; font-size: .82rem; margin-top: .3rem; }
    .form-group .input-error { border-color: #dc2626; }
    .auth-right .login-link { text-align: center; margin-top: 1.5rem; font-size: .9rem; color: var(--text-muted); }
    .auth-right .login-link a { color: var(--primary); font-weight: 600; }
    @media (max-width: 768px) {
        .auth-page { grid-template-columns: 1fr; }
        .auth-left { display: none; }
        .auth-right { padding: 2rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-page">
    <div class="auth-left">
        <h1>Start your free<br>30-day trial</h1>
        <p>Launch your full-featured travel agency platform in minutes. No credit card required.</p>
        <ul class="auth-benefits">
            <li><i class="fas fa-check-circle"></i> Full access to all features for 30 days</li>
            <li><i class="fas fa-check-circle"></i> Your own agency portal instantly</li>
            <li><i class="fas fa-check-circle"></i> AI-powered tour builder included</li>
            <li><i class="fas fa-check-circle"></i> No credit card required to start</li>
            <li><i class="fas fa-check-circle"></i> Cancel or upgrade anytime</li>
        </ul>
    </div>
    <div class="auth-right">
        <h2>Create your agency</h2>
        <p class="sub">Fill in the details below to get started instantly.</p>

        <form method="POST" action="{{ route('central.register') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Jane Smith" class="{{ $errors->has('name') ? 'input-error' : '' }}" required autofocus>
                    @error('name')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="email">Work Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="jane@youragency.com" class="{{ $errors->has('email') ? 'input-error' : '' }}" required>
                    @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="company_name">Agency / Company Name</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Sunshine Travel Agency" class="{{ $errors->has('company_name') ? 'input-error' : '' }}" required>
                @error('company_name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="tenant_slug">Your Agency URL</label>
                <div class="subdomain-wrap">
                    <span class="subdomain-suffix" style="border-left:none;border-right:1px solid var(--border);background:var(--bg-alt)">/t/</span>
                    <input type="text" id="tenant_slug" name="tenant_slug"
                        value="{{ old('tenant_slug') }}"
                        placeholder="sunshinetravel"
                        pattern="[a-z0-9][a-z0-9\-]*[a-z0-9]"
                        title="Lowercase letters, numbers and hyphens only"
                        oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'');document.getElementById('slug-preview').textContent=this.value||'yourslug'"
                        class="{{ $errors->has('tenant_slug') ? 'input-error' : '' }}"
                        required>
                </div>
                <div class="hint">Your admin panel will be at <strong>/t/<span id="slug-preview">{{ old('tenant_slug','yourslug') }}</span>/admin</strong>. Lowercase, numbers and hyphens only.</div>
                @error('tenant_slug')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" class="{{ $errors->has('password') ? 'input-error' : '' }}" required>
                    @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:.5rem">
                <i class="fas fa-rocket"></i> Start My Free Trial
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('central.login') }}">Log in</a>
        </div>
    </div>
</div>
@endsection
