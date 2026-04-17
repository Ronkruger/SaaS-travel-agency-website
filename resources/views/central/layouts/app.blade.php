<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'TourSaaS')) - Travel Agency Platform</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #0A2D74;
            --primary-light: #1a47a0;
            --accent: #F5A623;
            --accent-dark: #e0941a;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --bg: #ffffff;
            --bg-alt: #f8fafc;
            --border: #e5e7eb;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(10,45,116,0.10);
        }
        body { font-family: 'Poppins', sans-serif; color: var(--text); background: var(--bg); line-height: 1.6; }
        a { color: inherit; text-decoration: none; }

        /* Navbar */
        .central-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex; align-items: center; justify-content: space-between;
            height: 72px;
        }
        .central-nav .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.3rem; color: var(--primary); }
        .central-nav .brand span { color: var(--accent); }
        .central-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .central-nav .nav-links a { font-weight: 500; color: var(--text-muted); transition: color .2s; }
        .central-nav .nav-links a:hover { color: var(--primary); }
        .central-nav .nav-actions { display: flex; gap: 1rem; align-items: center; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: .6rem 1.4rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; transition: all .2s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-light); }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { background: var(--accent-dark); }
        .btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); }
        .btn-outline:hover { background: var(--primary); color: #fff; }
        .btn-sm { padding: .4rem 1rem; font-size: .85rem; }
        .btn-lg { padding: .9rem 2.2rem; font-size: 1rem; }

        /* Main content */
        main { padding-top: 72px; }

        /* Flash messages */
        .flash { padding: 1rem 1.5rem; border-radius: var(--radius); margin: 1rem 0; font-weight: 500; }
        .flash-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .flash-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .flash-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .flash-info { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }

        /* Footer */
        .central-footer { background: var(--primary); color: rgba(255,255,255,.7); padding: 3rem 2rem 2rem; margin-top: 6rem; }
        .central-footer .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto 2rem; }
        .central-footer .footer-brand { font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: .5rem; }
        .central-footer h4 { color: #fff; margin-bottom: 1rem; font-size: .85rem; text-transform: uppercase; letter-spacing: .1em; }
        .central-footer ul { list-style: none; display: flex; flex-direction: column; gap: .5rem; }
        .central-footer a { color: rgba(255,255,255,.7); transition: color .2s; font-size: .9rem; }
        .central-footer a:hover { color: #fff; }
        .central-footer .footer-bottom { border-top: 1px solid rgba(255,255,255,.15); padding-top: 1.5rem; text-align: center; font-size: .85rem; max-width: 1200px; margin: 0 auto; }

        /* Utilities */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .text-center { text-align: center; }
        .mt-1 { margin-top: .5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }
        .mb-1 { margin-bottom: .5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 2rem; }
        .gap-1 { gap: .5rem; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .badge { display: inline-block; padding: .2rem .7rem; border-radius: 20px; font-size: .78rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-muted { background: #f3f4f6; color: #6b7280; }

        @stack('styles')
    </style>
    @stack('styles')
</head>
<body>

<nav class="central-nav">
    <a href="{{ route('central.home') }}" class="brand">
        Tour<span>SaaS</span>
    </a>
    <div class="nav-links">
        <a href="{{ route('central.features') }}">Features</a>
        <a href="{{ route('central.pricing') }}">Pricing</a>
        <a href="{{ route('central.home') }}#how-it-works">How it works</a>
    </div>
    <div class="nav-actions">
        @if(session('tenant_owner'))
            <a href="{{ route('central.billing.index') }}" class="btn btn-outline btn-sm">My Account</a>
            <form action="{{ route('central.logout') }}" method="POST" style="display:inline">
                @csrf
                <button class="btn btn-sm" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-weight:500">Log out</button>
            </form>
        @else
            <a href="{{ route('central.login') }}" class="btn btn-outline btn-sm">Log in</a>
            <a href="{{ route('central.register') }}" class="btn btn-accent btn-sm">Start Free Trial</a>
        @endif
    </div>
</nav>

<main>
    <div class="container">
        @if(session('success'))
            <div class="flash flash-success mt-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error mt-2"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
    </div>

    @yield('content')
</main>

<footer class="central-footer">
    <div class="footer-grid">
        <div>
            <div class="footer-brand">TourSaaS</div>
            <p style="font-size:.9rem;line-height:1.7">The complete platform to run your travel agency online. Manage tours, bookings, and customers — all in one place.</p>
        </div>
        <div>
            <h4>Product</h4>
            <ul>
                <li><a href="{{ route('central.features') }}">Features</a></li>
                <li><a href="{{ route('central.pricing') }}">Pricing</a></li>
                <li><a href="{{ route('central.home') }}#how-it-works">How it works</a></li>
            </ul>
        </div>
        <div>
            <h4>Company</h4>
            <ul>
                <li><a href="#">About</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
        <div>
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; {{ date('Y') }} TourSaaS. All rights reserved.
    </div>
</footer>

@stack('scripts')
</body>
</html>
