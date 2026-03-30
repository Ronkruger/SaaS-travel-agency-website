<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration — DiscoverGRP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary:      #0e7490;
            --primary-dark: #0c6278;
            --primary-light:#e0f2f8;
            --success:      #16a34a;
            --danger:       #dc2626;
            --gray-900:     #111827;
            --gray-700:     #374151;
            --gray-500:     #6b7280;
            --gray-300:     #d1d5db;
        }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; background: #f8fafc; }
        .auth-wrap { display: flex; min-height: 100vh; width: 100%; }

        /* Brand panel (narrower on register page) */
        .auth-brand {
            width: 380px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 55%, #0e7490 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 2.75rem;
            position: relative;
            overflow: hidden;
        }
        .auth-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(14,116,144,.18);
        }
        .brand-inner { position: relative; z-index: 1; }
        .brand-logo { display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem; }
        .brand-logo-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.12);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #7dd3fc;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .brand-logo h2 { font-size: 1.5rem; font-weight: 800; color: #fff; }
        .brand-logo span { font-size: .8rem; color: rgba(255,255,255,.5); font-weight: 500; display: block; }
        .brand-headline { margin-bottom: 2rem; }
        .brand-headline h1 { font-size: 1.875rem; font-weight: 900; color: #fff; line-height: 1.25; margin-bottom: .75rem; }
        .brand-headline p { color: rgba(255,255,255,.65); font-size: .9rem; line-height: 1.65; }
        .steps { display: flex; flex-direction: column; gap: 0; }
        .step {
            display: flex; align-items: flex-start; gap: 1rem;
            padding: .875rem 0;
            position: relative;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 14px; top: 46px;
            width: 2px; height: calc(100% - 12px);
            background: rgba(125,211,252,.3);
        }
        .step-num {
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(125,211,252,.2);
            border: 2px solid rgba(125,211,252,.5);
            display: flex; align-items: center; justify-content: center;
            font-size: .8125rem; font-weight: 800; color: #7dd3fc;
            flex-shrink: 0;
        }
        .step-info strong { display: block; color: #fff; font-size: .9rem; font-weight: 700; margin-bottom: .2rem; }
        .step-info span { color: rgba(255,255,255,.55); font-size: .8125rem; }

        /* Form side */
        .auth-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            overflow-y: auto;
        }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2.75rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.05);
            border: 1px solid #e5e7eb;
        }
        .auth-card-head { margin-bottom: 1.75rem; }
        .auth-card-head h3 { font-size: 1.5rem; font-weight: 800; color: var(--gray-900); margin-bottom: .375rem; }
        .auth-card-head p { color: var(--gray-500); font-size: .9375rem; }

        /* Auth0 */
        .btn-auth0 {
            display: flex; align-items: center; justify-content: center; gap: .75rem;
            width: 100%; padding: .75rem 1.25rem;
            border: 2px solid #e5e7eb; border-radius: 10px;
            background: #fff; font-family: 'Inter', sans-serif;
            font-size: .9375rem; font-weight: 600; color: var(--gray-700);
            cursor: pointer; transition: all .2s; text-decoration: none;
        }
        .btn-auth0:hover { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
        .auth0-badge {
            width: 22px; height: 22px; border-radius: 50%;
            background: #eb5424; display: flex; align-items: center; justify-content: center;
        }
        .auth0-badge svg { width: 14px; height: 14px; fill: #fff; }

        .auth-divider {
            display: flex; align-items: center; gap: .875rem;
            color: #9ca3af; font-size: .8125rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em;
            margin: 1.25rem 0;
        }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }

        /* Form elements */
        .form-group { display: flex; flex-direction: column; gap: .375rem; margin-bottom: 1rem; }
        .form-group label { font-weight: 600; font-size: .9rem; color: var(--gray-700); }
        .form-control {
            width: 100%; padding: .65rem .875rem;
            border: 2px solid var(--gray-300); border-radius: 10px;
            font-size: .9375rem; color: var(--gray-900);
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s; background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,116,144,.12); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { color: var(--danger); font-size: .8125rem; margin-top: .2rem; display: block; }

        /* Password strength hint */
        .password-hint {
            font-size: .8125rem; color: var(--gray-500);
            margin-top: .25rem;
            display: flex; align-items: center; gap: .4rem;
        }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .75rem 1.5rem; border-radius: 10px; font-size: .9375rem; font-weight: 700; cursor: pointer; border: none; font-family: 'Inter', sans-serif; transition: all .2s; width: 100%; margin-top: .75rem; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }

        .alert { display: flex; align-items: flex-start; gap: .625rem; padding: .875rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: .875rem; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert ul { margin-top: .4rem; padding-left: 1.25rem; }
        .alert i { margin-top: 2px; flex-shrink: 0; }

        .auth-footer { text-align: center; margin-top: 1.5rem; font-size: .9rem; color: var(--gray-500); }
        .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .back-link { display: flex; align-items: center; gap: .4rem; justify-content: center; margin-top: .875rem; font-size: .8125rem; color: var(--gray-500); text-decoration: none; }
        .back-link:hover { color: var(--primary); }

        @media (max-width: 900px) { .auth-brand { display: none; } }
        @media (max-width: 480px) { .auth-card { padding: 1.75rem 1.5rem; border-radius: 16px; } }
    </style>
</head>
<body>
<div class="auth-wrap">

    {{-- Brand Panel --}}
    <div class="auth-brand">
        <div class="brand-inner">
            <div class="brand-logo">
                <div class="brand-logo-icon"><i class="fas fa-globe-asia"></i></div>
                <div>
                    <h2>DiscoverGRP</h2>
                    <span>Employee Portal</span>
                </div>
            </div>
            <div class="brand-headline">
                <h1>Join the Team.</h1>
                <p>Create your employee account and complete your profile to start using the DiscoverGRP admin panel.</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-info">
                        <strong>Create Account</strong>
                        <span>Register with your work email</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-info">
                        <strong>Select Department &amp; Role</strong>
                        <span>Tell us your department and position</span>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-info">
                        <strong>Access Admin Panel</strong>
                        <span>Start managing tours, bookings &amp; more</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Side --}}
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-card-head">
                <h3>Create Employee Account</h3>
                <p>Step 1 of 2 — Account details</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Auth0 Register --}}
            <a href="{{ route('admin.auth.auth0.redirect') }}?mode=register" class="btn-auth0">
                <div class="auth0-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19.2 6.4L17 0H7L4.8 6.4 12 11.2l7.2-4.8zM4.8 6.4L2.6 13.2l4.6 3.4L4.8 6.4zM7 24l3.4-4.4H7L4.8 13 2.6 13.2 7 24zm10-0l4.4-10.8-2.2-.2L16.6 19.6H13L16.6 24h.4zm1.2-17.6l-2.4 10-6-3.4-6 3.4-2.4-10"/></svg>
                </div>
                Sign up with Auth0
            </a>

            <div class="auth-divider">or register with email</div>

            <form method="POST" action="{{ route('admin.auth.register.post') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        id="name" type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                        value="{{ old('name') }}" required autofocus autocomplete="name"
                        placeholder="Juan dela Cruz"
                    >
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Work Email Address</label>
                    <input
                        id="email" type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                        value="{{ old('email') }}" required autocomplete="email"
                        placeholder="you@discovergrp.com"
                    >
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password" type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        required autocomplete="new-password" placeholder="••••••••"
                    >
                    <span class="password-hint"><i class="fas fa-info-circle"></i> Min 8 characters, upper &amp; lowercase, and a number</span>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input
                        id="password_confirmation" type="password" name="password_confirmation"
                        class="form-control" required autocomplete="new-password" placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account &amp; Continue
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="{{ route('admin.auth.login') }}">Sign in</a>
            </div>

            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to website
            </a>
        </div>
    </div>

</div>
</body>
</html>
