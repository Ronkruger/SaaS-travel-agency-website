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
    @stack('styles')
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="fas fa-compass"></i>
                <span>DiscoverGRP</span>
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
            <div class="nav-section">ACCOUNT</div>
            <a href="{{ route('home') }}" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <form action="{{ route('logout') }}" method="POST">
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
                <span>{{ auth()->user()->name }}</span>
                <i class="fas fa-user-circle fa-lg"></i>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
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
