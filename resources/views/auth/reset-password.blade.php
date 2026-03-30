@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-lock"></i>
            <h2>Set New Password</h2>
            <p>Choose a strong password for your account</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Min. 8 characters" required>
                    <button type="button" class="password-toggle" aria-label="Show/hide password">
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
                        class="form-control" placeholder="Repeat your password" required>
                    <button type="button" class="password-toggle" aria-label="Show/hide password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Reset Password
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Back to sign in</a>
        </div>
    </div>
</div>
@endsection
