<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - Service Platform</title>

    <!-- Favicon -->
    @if($brandFaviconUrl)
        <link rel="icon" href="{{ $brandFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ global_asset('favicon.ico') }}">
    @endif
    <meta name="theme-color" content="#0A2D74">

    <!-- Brand Fonts: Poppins (headings/brand fallback) + Dancing Script (Blacksword fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- GSAP Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="{{ global_asset('css/styles.css') }}">

    @stack('styles')
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand" aria-label="DiscoverGroup Home">
            @if($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="navbar-logo-full" style="max-height:40px;width:auto">
                @if($brandFaviconUrl)
                    <img src="{{ $brandFaviconUrl }}" alt="{{ $brandName }}" class="navbar-logo-mark" style="max-height:40px;width:auto">
                @else
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="navbar-logo-mark" style="max-height:40px;width:auto">
                @endif
            @else
                {{-- Fallback: text-based logo using tenant name --}}
                <span class="navbar-logo-full" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:9px;background:#0A2D74;color:#fff;font-weight:900;font-size:20px;font-family:'Poppins',sans-serif">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
                    <span style="font-weight:800;font-size:15px;color:#0A2D74;font-family:'Poppins',sans-serif;letter-spacing:0.5px">{{ $brandName }}</span>
                </span>
                <span class="navbar-logo-mark" style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:9px;background:#0A2D74;color:#fff;font-weight:900;font-size:20px;font-family:'Poppins',sans-serif">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
            @endif
        </a>

        <ul class="navbar-nav" id="navMenu">
            {{-- Mobile close button (hidden on desktop via CSS) --}}
            <button class="navbar-nav-close" id="navClose" aria-label="Close navigation menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('tours.index') }}" class="{{ request()->routeIs('tours.*') ? 'active' : '' }}">Plans</a></li>
            <li class="dropdown" id="destinationsDropdown">
                <a href="#" onclick="if(window.innerWidth<992){event.preventDefault();this.closest('.dropdown').classList.toggle('open');}">Categories <i class="fas fa-chevron-down" aria-hidden="true"></i></a>
                <ul class="dropdown-menu">
                    @foreach(['Africa','Asia','Europe','North America','Oceania','South America'] as $continent)
                        <li><a href="{{ route('tours.index', ['continent' => $continent]) }}">{{ $continent }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="{{ route('tours.index', ['sort' => 'popular']) }}">Popular</a></li>
            <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a></li>
            <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
            <li><a href="{{ route('diy.index') }}" class="{{ request()->routeIs('diy.*') ? 'active' : '' }}" style="color:#28A2DC;font-weight:600;">✨ Build My Plan <sup style="font-size:.6em;background:#28A2DC;color:#fff;padding:1px 5px;border-radius:4px;vertical-align:super;">BETA</sup></a></li>
        </ul>

        <div class="navbar-actions">
            @auth
                <div class="user-dropdown">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ Str::words(auth()->user()->name, 1, '') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-menu">
                        <a href="{{ route('profile') }}"><i class="fas fa-user"></i> My Profile</a>
                        <a href="{{ route('booking.index') }}"><i class="fas fa-calendar-check"></i> My Subscriptions</a>
                        <a href="{{ route('wishlist') }}"><i class="fas fa-heart"></i> Wishlist</a>
                        <a href="{{ route('diy.my-tours') }}"><i class="fas fa-magic"></i> My Custom Plans</a>
                        @if(auth()->user()->isAdmin())
                            <div class="divider"></div>
                            <a href="{{ route('admin.dashboard') }}" class="admin-link"><i class="fas fa-cog"></i> Admin Panel</a>
                        @endif
                        <div class="divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>

        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

{{-- Mobile nav backdrop: clicking it closes the nav drawer --}}
<div class="nav-backdrop" id="navBackdrop" aria-hidden="true"></div>

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert alert-success" id="flashMsg">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" id="flashMsg">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif
@if($errors->any() && !$errors->has('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif

<!-- Page Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-brand">
                    @php $footerLogo = $brandLogoDarkUrl ?? $brandLogoUrl; @endphp
                    @if($footerLogo)
                        <img src="{{ $footerLogo }}" alt="{{ $brandName }}" style="max-height:40px;width:auto">
                    @else
                        <span style="display:inline-flex;align-items:center;gap:10px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:9px;background:#fff;color:#0A2D74;font-weight:900;font-size:20px;font-family:'Poppins',sans-serif">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
                            <span style="font-weight:800;font-size:15px;color:#fff;font-family:'Poppins',sans-serif;letter-spacing:0.5px">{{ $brandName }}</span>
                        </span>
                    @endif
                </div>
                @if($brandTagline)
                    <p>{{ $brandTagline }}</p>
                @else
                    <p>Your trusted partner in creating exceptional travel experiences.</p>
                @endif
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <ul>
                    <li><a href="{{ route('tours.index') }}">Destinations</a></li>
                    <li><a href="{{ route('tours.index', ['sort' => 'popular']) }}">Special Deals</a></li>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Get in Touch</h4>
                <ul class="contact-list">
                    @if($currentTenant->company_address ?? false)
                        <li><i class="fas fa-map-marker-alt"></i> {{ $currentTenant->company_address }}</li>
                    @endif
                    @if($currentTenant->company_phone ?? false)
                        <li><i class="fas fa-phone"></i> {{ $currentTenant->company_phone }}</li>
                    @endif
                    <li><i class="fas fa-envelope"></i> {{ $currentTenant->email ?? '' }}</li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Stay Updated</h4>
                <p style="font-size:.88rem;color:#94a3b8;margin-bottom:14px">Subscribe to our newsletter for exclusive deals and travel inspiration.</p>
                <form onsubmit="event.preventDefault();this.querySelector('button').textContent='✅ Subscribed!'" style="display:flex;gap:8px">
                    <input type="email" placeholder="Your email" required
                        style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid #334155;background:#1e293b;color:#fff;font-size:.88rem;outline:none">
                    <button type="submit"
                        style="background:#F5A623;color:#fff;border:none;padding:10px 18px;border-radius:8px;font-weight:700;cursor:pointer;white-space:nowrap">Join</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.</p>
            <div style="display:flex;gap:16px;font-size:.82rem">
                <a href="#" style="color:#64748b;text-decoration:none">Privacy Policy</a>
                <span style="color:#334155">•</span>
                <a href="#" style="color:#64748b;text-decoration:none">Terms of Service</a>
                <span style="color:#334155">•</span>
                <a href="#" style="color:#64748b;text-decoration:none">FAQ</a>
                <span style="color:#334155">•</span>
                <a href="#" style="color:#64748b;text-decoration:none">Careers</a>
            </div>
        </div>
    </div>
</footer>

<!-- Main JS -->
<script src="{{ global_asset('js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
