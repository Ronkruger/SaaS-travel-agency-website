@extends('central.layouts.app')
@section('title', 'Log In')

@push('styles')
<style>
    .login-page { min-height: calc(100vh - 72px); display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; background: var(--bg-alt); }
    .login-card { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); padding: 3rem; width: 100%; max-width: 440px; }
    .login-card h2 { font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-bottom: .4rem; }
    .login-card .sub { color: var(--text-muted); margin-bottom: 2rem; font-size: .95rem; }
    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: .4rem; }
    .form-group input { width: 100%; padding: .75rem 1rem; border: 2px solid var(--border); border-radius: 8px; font-family: inherit; font-size: .95rem; transition: border-color .2s; }
    .form-group input:focus { outline: none; border-color: var(--primary); }
    .form-group .input-error { border-color: #dc2626; }
    .error-msg { color: #dc2626; font-size: .82rem; margin-top: .3rem; }
    .register-link { text-align: center; margin-top: 1.5rem; font-size: .9rem; color: var(--text-muted); }
    .register-link a { color: var(--primary); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="login-page">
    <div class="login-card">
        <h2>Welcome back</h2>
        <p class="sub">Log in to manage your agency billing and subscription.</p>

        @if($errors->any())
            <div class="flash flash-error mb-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('central.login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="jane@youragency.com" class="{{ $errors->has('email') ? 'input-error' : '' }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:.5rem">
                Log In
            </button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="{{ route('central.register') }}">Start free trial</a>
        </div>
    </div>
</div>
@endsection
