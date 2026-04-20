<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform Admin') — TourSaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #0A2D74;
            --primary-light: #1a47a0;
            --accent: #F5A623;
            --sidebar-bg: #0f1c3f;
            --sidebar-text: rgba(255,255,255,.75);
            --sidebar-active: rgba(255,255,255,.1);
            --border: #e5e7eb;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(10,45,116,.08);
        }
        body { font-family: 'Poppins', sans-serif; color: var(--text); background: var(--bg); display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: .5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: .85rem; cursor: pointer; border: none; transition: all .2s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-light); }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-sm { padding: .35rem .9rem; font-size: .82rem; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-success { background: #d1fae5; color: #065f46; }
        .btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); }

        /* Sidebar */
        .sidebar { width: 250px; min-height: 100vh; background: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-logo { padding: 1.5rem 1.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar-logo .brand { font-size: 1.2rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: .5rem; }
        .sidebar-logo .brand span { color: var(--accent); }
        .sidebar-logo .role { font-size: .75rem; color: var(--sidebar-text); margin-top: .2rem; }
        .sidebar nav { flex: 1; padding: 1rem 0; }
        .sidebar-section { padding: .5rem 1.5rem .3rem; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.3); }
        .sidebar a { display: flex; align-items: center; gap: .8rem; padding: .65rem 1.5rem; color: var(--sidebar-text); transition: all .2s; font-size: .88rem; border-left: 3px solid transparent; }
        .sidebar a:hover { background: var(--sidebar-active); color: #fff; }
        .sidebar a.active { background: var(--sidebar-active); color: #fff; border-left-color: var(--accent); }
        .sidebar a i { width: 18px; text-align: center; }
        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,.08); }
        .sidebar-user { display: flex; align-items: center; gap: .8rem; }
        .sidebar-user .avatar { width: 36px; height: 36px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
        .sidebar-user .info .name { font-size: .85rem; font-weight: 600; color: #fff; }
        .sidebar-user .info .email { font-size: .75rem; color: var(--sidebar-text); }
        .sidebar-user .logout { margin-left: auto; }

        /* Main content */
        .main-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { background: var(--card-bg); border-bottom: 1px solid var(--border); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar h1 { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
        .content { padding: 2rem; flex: 1; }

        /* Cards */
        .card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); }
        .card-body { padding: 1.5rem; }
        .card-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: 1rem; font-weight: 700; }

        /* Stats grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
        .stat-card { background: var(--card-bg); border-radius: var(--radius); padding: 1.3rem 1.5rem; border: 1px solid var(--border); display: flex; gap: 1rem; align-items: center; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; flex-shrink: 0; }
        .stat-icon.blue { background: linear-gradient(135deg, var(--primary), var(--primary-light)); }
        .stat-icon.green { background: linear-gradient(135deg, #059669, #10b981); }
        .stat-icon.amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .stat-icon.red { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .stat-num { font-size: 1.8rem; font-weight: 800; color: var(--text); line-height: 1.1; }
        .stat-label { font-size: .8rem; color: var(--text-muted); font-weight: 500; }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        th { padding: .7rem 1rem; text-align: left; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); border-bottom: 2px solid var(--border); background: var(--bg); }
        td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg); }
        .badge { display: inline-block; padding: .2rem .6rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-muted { background: #f3f4f6; color: #6b7280; }

        /* Flash */
        .flash { padding: .9rem 1.2rem; border-radius: var(--radius); margin-bottom: 1.2rem; font-size: .9rem; font-weight: 500; display: flex; gap: .6rem; align-items: center; }
        .flash-success { background: #d1fae5; color: #065f46; }
        .flash-error { background: #fee2e2; color: #991b1b; }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand">Tour<span>SaaS</span></div>
        <div class="role">Platform Administration</div>
    </div>
    <nav>
        <div class="sidebar-section">Overview</div>
        <a href="{{ route('platform.dashboard') }}" class="{{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="sidebar-section">Tenants</div>
        <a href="{{ route('platform.tenants.index') }}" class="{{ request()->routeIs('platform.tenants.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> All Agencies
        </a>

        <div class="sidebar-section">Settings</div>
        <a href="{{ route('platform.plans.index') }}" class="{{ request()->routeIs('platform.plans.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Subscription Plans
        </a>
        <a href="{{ route('platform.gateway-requests.index') }}" class="{{ request()->routeIs('platform.gateway-requests.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Gateway Requests
            @php $pendingGwCount = \App\Models\GatewayRequest::where('status', 'pending')->count(); @endphp
            @if($pendingGwCount > 0)
                <span style="margin-left:auto;background:var(--accent);color:#fff;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:10px">{{ $pendingGwCount }}</span>
            @endif
        </a>
        <a href="{{ route('central.home') }}" target="_blank">
            <i class="fas fa-globe"></i> View Site
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar">{{ strtoupper(substr(auth('platform')->user()->name, 0, 1)) }}</div>
            <div class="info">
                <div class="name">{{ auth('platform')->user()->name }}</div>
                <div class="email">{{ auth('platform')->user()->email }}</div>
            </div>
            <form action="{{ route('platform.logout') }}" method="POST" class="logout">
                @csrf
                <button class="btn btn-sm" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:none;cursor:pointer" title="Log out">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="main-content">
    <div class="topbar">
        <h1>@yield('page-title', 'Dashboard')</h1>
        <div>@yield('topbar-actions')</div>
    </div>
    <div class="content">
        @if(session('success'))
            <div class="flash flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
