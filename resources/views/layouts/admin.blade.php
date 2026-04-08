<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — DiscoverGRP Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @if($brandFaviconUrl ?? false)
        <link rel="icon" href="{{ $brandFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    @stack('styles')
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                @if($brandLogoUrl ?? false)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName ?? 'DiscoverGRP' }}" style="max-height:32px;width:auto;filter:brightness(0) invert(1)">
                @else
                    <i class="fas fa-compass"></i>
                    <span>{{ $brandName ?? 'DiscoverGRP' }}</span>
                @endif
            </a>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <div class="nav-section">TOURS</div>
            <a href="{{ route('admin.tours.index') }}" class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt"></i> All Tours
            </a>
            <a href="{{ route('admin.tours.create') }}" class="{{ request()->routeIs('admin.tours.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> Add Tour
            </a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Categories
            </a>
            <div class="nav-section">BOOKINGS</div>
            <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> All Bookings
                @php $pending = \App\Models\Booking::where('status','pending')->count(); @endphp
                @if($pending > 0)
                    <span class="badge">{{ $pending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.diy.index') }}" class="{{ request()->routeIs('admin.diy.*') ? 'active' : '' }}">
                <i class="fas fa-magic"></i> DIY Tours
                @php $pendingDiy = \App\Models\DIYTourSession::where('status','pending_review')->count(); @endphp
                @if($pendingDiy > 0)
                    <span class="badge">{{ $pendingDiy }}</span>
                @endif
            </a>
            <div class="nav-section">CUSTOMERS</div>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i> Reviews
                @php $pendingReviews = \App\Models\Review::where('is_approved', false)->count(); @endphp
                @if($pendingReviews > 0)
                    <span class="badge">{{ $pendingReviews }}</span>
                @endif
            </a>
            <div class="nav-section">REPORTS</div>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Monthly Report
            </a>
            <div class="nav-section">ACCOUNT</div>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-palette"></i> Branding
            </a>
            <a href="{{ route('home') }}" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-breadcrumb">
                @yield('breadcrumb')
            </div>
            <div class="header-user">
                <div style="text-align:right">
                    <div style="font-weight:700;font-size:.9375rem">{{ auth()->user()->name }}</div>
                    @if(auth()->user()->is_onboarded)
                    <div style="font-size:.75rem;color:var(--gray-500);margin-top:.1rem">
                        {{ auth()->user()->department_label }} &mdash; {{ auth()->user()->position }}
                    </div>
                    @endif
                </div>
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--gray-300)">
                @else
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning" style="background:#fefce8;border:1px solid #fde047;color:#854d0e">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif
            @if(session('error') || $errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    @if($errors->any())
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
