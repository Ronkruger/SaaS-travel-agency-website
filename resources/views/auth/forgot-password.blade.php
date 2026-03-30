@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-key"></i>
            <h2>Forgot Password?</h2>
            <p>Enter your email and we'll send you a one-time code</p>
        </div>

        @if(session('status'))
            <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@example.com" required autofocus>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Send OTP
            </button>
        </form>

        <div class="auth-footer">
            Remembered it? <a href="{{ route('login') }}">Back to sign in</a>
        </div>
    </div>
</div>
@endsection
