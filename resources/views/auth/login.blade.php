@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-compass"></i>
            <h2>Welcome Back</h2>
            <p>Sign in to manage your bookings</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="auth-form">
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

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••" required>
                    <button type="button" class="password-toggle" aria-label="Show/hide password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-check-row">
                <label class="form-check">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Create one free</a>
        </div>
    </div>
</div>
@endsection
