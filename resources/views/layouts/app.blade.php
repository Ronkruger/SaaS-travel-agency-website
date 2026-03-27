<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DiscoverGroup') - Tour Reservations</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#0A2D74">

    <!-- Brand Fonts: Poppins (headings/brand fallback) + Dancing Script (Blacksword fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    @stack('styles')
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand" aria-label="DiscoverGroup Home">
            {{-- Full logo: shown on tablet (480px+) --}}
            <svg class="navbar-logo-full" width="168" height="40" viewBox="0 0 168 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="40" height="40" rx="9" fill="#0A2D74"/>
                <text x="20" y="28.5" text-anchor="middle" font-family="'LemonMilk','Poppins',sans-serif" font-size="22" font-weight="900" fill="#ffffff">D</text>
                <text x="50" y="20" font-family="'LemonMilk','Poppins',sans-serif" font-size="11" font-weight="800" letter-spacing="1.5" fill="#0A2D74">DISCOVER</text>
                <text x="50" y="35" font-family="'LemonMilk','Poppins',sans-serif" font-size="9" font-weight="700" letter-spacing="4.5" fill="#28A2DC">GROUP</text>
            </svg>
            {{-- Submark: shown on small phones (<480px) --}}
            <svg class="navbar-logo-mark" width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="40" height="40" rx="9" fill="#0A2D74"/>
                <text x="20" y="28.5" text-anchor="middle" font-family="'LemonMilk','Poppins',sans-serif" font-size="22" font-weight="900" fill="#ffffff">D</text>
            </svg>
        </a>

        <ul class="navbar-nav" id="navMenu">
            {{-- Mobile close button (hidden on desktop via CSS) --}}
            <button class="navbar-nav-close" id="navClose" aria-label="Close navigation menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('tours.index') }}" class="{{ request()->routeIs('tours.*') ? 'active' : '' }}">Tours</a></li>
            <li class="dropdown" id="destinationsDropdown">
                <a href="#" onclick="if(window.innerWidth<992){event.preventDefault();this.closest('.dropdown').classList.toggle('open');}">Destinations <i class="fas fa-chevron-down" aria-hidden="true"></i></a>
                <ul class="dropdown-menu">
                    @foreach(['Africa','Asia','Europe','North America','Oceania','South America'] as $continent)
                        <li><a href="{{ route('tours.index', ['continent' => $continent]) }}">{{ $continent }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="{{ route('tours.index', ['sort' => 'popular']) }}">Popular</a></li>
            <li><a href="{{ route('diy.index') }}" class="{{ request()->routeIs('diy.*') ? 'active' : '' }}" style="color:#28A2DC;font-weight:600;">✨ Build My Tour</a></li>
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
                        <a href="{{ route('booking.index') }}"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <a href="{{ route('wishlist') }}"><i class="fas fa-heart"></i> Wishlist</a>
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
@if($errors->any())
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
                    <svg width="168" height="40" viewBox="0 0 168 40" xmlns="http://www.w3.org/2000/svg" aria-label="DiscoverGroup" role="img">
                        <rect width="40" height="40" rx="9" fill="#ffffff"/>
                        <text x="20" y="28.5" text-anchor="middle" font-family="'LemonMilk','Poppins',sans-serif" font-size="22" font-weight="900" fill="#0A2D74">D</text>
                        <text x="50" y="20" font-family="'LemonMilk','Poppins',sans-serif" font-size="11" font-weight="800" letter-spacing="1.5" fill="#ffffff">DISCOVER</text>
                        <text x="50" y="35" font-family="'LemonMilk','Poppins',sans-serif" font-size="9" font-weight="700" letter-spacing="4.5" fill="#28A2DC">GROUP</text>
                    </svg>
                </div>
                <p>Explore the world with confidence. We craft unforgettable tour experiences for every kind of traveler.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('tours.index') }}">All Tours</a></li>
                    <li><a href="{{ route('tours.index', ['sort' => 'popular']) }}">Popular Tours</a></li>
                    <li><a href="{{ route('tours.index', ['sort' => 'rating']) }}">Top Rated</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Cancellation Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="contact-list">
                    <li><i class="fas fa-map-marker-alt"></i> 123 Travel Street, City</li>
                    <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-envelope"></i> info@discovergrp.com</li>
                    <li><i class="fas fa-clock"></i> Mon–Sat 9:00 AM – 6:00 PM</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} DiscoverGroup. All rights reserved.</p>
            <div class="payment-icons">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-amex"></i>
            </div>
        </div>
    </div>
</footer>

<!-- Main JS -->
<script src="{{ asset('js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
