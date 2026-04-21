<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In — {{ $currentTenant->company_name ?? $currentTenant->name }}</title>
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
            --gray-100:     #f3f4f6;
        }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; background: #f8fafc; }

        /* ── Layout ── */
        .auth-wrap { display: flex; min-height: 100vh; width: 100%; }

        /* ── Brand panel ── */
        .auth-brand {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 55%, #0e7490 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 3rem;
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
        .brand-headline { margin-bottom: 2.25rem; }
        .brand-headline h1 { font-size: 2.125rem; font-weight: 900; color: #fff; line-height: 1.2; margin-bottom: .75rem; }
        .brand-headline p { color: rgba(255,255,255,.65); font-size: .9375rem; line-height: 1.65; }
        .brand-features { display: flex; flex-direction: column; gap: .75rem; }
        .brand-feature {
            display: flex; align-items: center; gap: .875rem;
            padding: .875rem 1.125rem;
            background: rgba(255,255,255,.07);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.1);
        }
        .brand-feature .feat-icon {
            width: 2.25rem; height: 2.25rem; border-radius: 8px;
            background: rgba(125,211,252,.15);
            display: flex; align-items: center; justify-content: center;
            color: #7dd3fc; font-size: .9rem; flex-shrink: 0;
        }
        .brand-feature span { color: rgba(255,255,255,.82); font-size: .9rem; font-weight: 500; }

        /* ── Form side ── */
        .auth-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
        }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.75rem 3rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.05);
            border: 1px solid #e5e7eb;
        }
        .auth-card-head { margin-bottom: 2rem; }
        .auth-card-head h3 { font-size: 1.625rem; font-weight: 800; color: var(--gray-900); margin-bottom: .375rem; }
        .auth-card-head p { color: var(--gray-500); font-size: .9375rem; }

        /* Divider */
        .auth-divider {
            display: flex; align-items: center; gap: .875rem;
            color: #9ca3af; font-size: .8125rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em;
            margin: 0 0 1.5rem;
        }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }

        /* Form elements */
        .form-group { display: flex; flex-direction: column; gap: .375rem; margin-bottom: 1.125rem; }
        .form-group label { font-weight: 600; font-size: .9rem; color: var(--gray-700); }
        .form-control {
            width: 100%; padding: .65rem .875rem;
            border: 2px solid var(--gray-300); border-radius: 10px;
            font-size: .9375rem; color: var(--gray-900);
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,116,144,.12); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { color: var(--danger); font-size: .8125rem; margin-top: .2rem; }

        /* Remember row */
        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .form-check { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .form-check input[type=checkbox] { width: 1rem; height: 1rem; accent-color: var(--primary); }
        .form-check span { font-size: .9rem; color: var(--gray-700); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .75rem 1.5rem; border-radius: 10px; font-size: .9375rem; font-weight: 700; cursor: pointer; border: none; font-family: 'Inter', sans-serif; transition: all .2s; width: 100%; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }

        /* Alerts */
        .alert { display: flex; align-items: flex-start; gap: .625rem; padding: .875rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: .875rem; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert i { margin-top: 1px; flex-shrink: 0; }

        /* Footer */
        .auth-footer { text-align: center; margin-top: 1.75rem; font-size: .9rem; color: var(--gray-500); }
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
                <div class="brand-logo-icon"><i class="fas fa-building"></i></div>
                <div>
                    <h2>{{ $currentTenant->company_name ?? $currentTenant->name }}</h2>
                    <span>Admin Portal</span>
                </div>
            </div>
            <div class="brand-headline">
                <h1>Manage Your<br>Agency.</h1>
                <p>Sign in to manage plans, subscriptions, clients, team members, and your platform operations from one place.</p>
            </div>
            <div class="brand-features">
                <div class="brand-feature">
                    <div class="feat-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <span>Plans &amp; Schedule Management</span>
                </div>
                <div class="brand-feature">
                    <div class="feat-icon"><i class="fas fa-calendar-check"></i></div>
                    <span>Subscriptions &amp; Reservations</span>
                </div>
                <div class="brand-feature">
                    <div class="feat-icon"><i class="fas fa-users-cog"></i></div>
                    <span>Team &amp; Department Workflows</span>
                </div>
                <div class="brand-feature">
                    <div class="feat-icon"><i class="fas fa-chart-line"></i></div>
                    <span>Analytics &amp; Reports</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Side --}}
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-card-head">
                <h3>Admin Sign In</h3>
                <p>Access the {{ $currentTenant->company_name ?? $currentTenant->name }} Portal</p>
            </div>

            @if(session('auth0_error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('auth0_error') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->has('email') && $errors->first('email') === 'The provided credentials do not match our records.')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Invalid email or password. Please try again.
                </div>
            @endif

            <div class="auth-divider">Sign in with email</div>

            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;gap:.625rem;align-items:center;font-size:.8125rem;color:#92400e">
                <i class="fas fa-lock" style="flex-shrink:0"></i>
                Email &amp; password sign-in is reserved for the <strong>system administrator</strong> only.
            </div>

            <form method="POST" action="{{ route('admin.auth.login.post') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        id="email" type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                        value="{{ old('email') }}" required autofocus autocomplete="email"
                        placeholder="you@example.com"
                    >
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password" type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        required autocomplete="current-password" placeholder="••••••••"
                    >
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-row">
                    <label class="form-check">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel
                </button>
            </form>

            <div class="auth-footer">
                New employee? <a href="{{ route('admin.auth.register') }}">Create an account</a>
            </div>

            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to website
            </a>
        </div>
    </div>

</div>
<script>
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
</body>
</html>
