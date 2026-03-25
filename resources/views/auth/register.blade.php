@extends('layouts.app')
@section('title', 'Create Account')

@section('content')
<div class="auth-page">
    <div class="auth-card auth-card--wide">
        <div class="auth-header">
            <i class="fas fa-compass"></i>
            <h2>Start Your Journey</h2>
            <p>Create a free account to book tours</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="John Doe" required autofocus>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone (optional)</label>
                    @include('components.phone-input', [
                        'value' => old('phone'),
                        'name'  => 'phone',
                    ])
                    @error('phone')
                        <span class="invalid-feedback" style="display:block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@example.com" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Min 8 characters" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Repeat password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-check-row">
                <label class="form-check">
                    <input type="checkbox" required> I agree to the
                    <a href="#" target="_blank">Terms & Conditions</a> and
                    <a href="#" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-divider" style="display:flex;align-items:center;gap:.75rem;margin:1.25rem 0">
            <hr style="flex:1;border:none;border-top:1px solid #e5e7eb">
            <span style="color:#9ca3af;font-size:.875rem">or</span>
            <hr style="flex:1;border:none;border-top:1px solid #e5e7eb">
        </div>

        <a href="{{ route('auth0.redirect', ['mode' => 'register']) }}" class="btn btn-block" style="display:flex;align-items:center;justify-content:center;gap:.625rem;background:#fff;border:1px solid #d1d5db;color:#374151;font-weight:500;padding:.625rem 1rem;border-radius:.5rem;text-decoration:none">
            <img src="https://cdn.auth0.com/styleguide/latest/lib/logos/img/favicon.png" alt="Auth0" style="width:1.125rem;height:1.125rem">
            Sign up with Auth0
        </a>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
