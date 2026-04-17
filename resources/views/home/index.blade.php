@extends('layouts.app')
@section('title', 'The Complete Platform for Travel Agencies')

@section('content')

<!-- Hero Section -->
<section class="hero saas-hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <span class="hero-label">Built for Travel Agencies</span>
            <h1 class="hero-title">
                <span class="hero-title-block">Run Your Agency</span>
                <span class="hero-title-script">Smarter</span>
            </h1>
            <p class="hero-subtitle">{{ $currentTenant->company_name ?? $currentTenant->name ?? 'TourSaaS' }} gives your travel agency everything it needs — bookings, tours, staff, payments, and AI-powered itineraries — in one platform.</p>
            <div class="saas-hero-actions">
                <a href="{{ route('admin.auth.login') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Start Free Trial
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline btn-lg btn-white">
                    <i class="fas fa-phone"></i> Book a Demo
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-num">30</span>
                    <span class="stat-label">Day Free Trial</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num">0</span>
                    <span class="stat-label">Setup Fees</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num">24/7</span>
                    <span class="stat-label">Support</span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll"><i class="fas fa-chevron-down"></i></div>
</section>

<!-- Feature Icons Strip -->
<section class="section-icon-strip">
    <div class="container">
        <div class="icon-strip-grid">
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-tours"><i class="fas fa-box-open"></i></div>
                <span>Tour Builder</span>
            </div>
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-destinations"><i class="fas fa-handshake"></i></div>
                <span>Bookings</span>
            </div>
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-popular"><i class="fas fa-magic"></i></div>
                <span>AI Itineraries</span>
            </div>
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-buildtour"><i class="fas fa-chart-line"></i></div>
                <span>Analytics</span>
            </div>
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-about"><i class="fas fa-user-shield"></i></div>
                <span>Multi-Staff</span>
            </div>
            <div class="icon-strip-item">
                <div class="icon-strip-circle icon-contact"><i class="fas fa-credit-card"></i></div>
                <span>Payments</span>
            </div>
        </div>
    </div>
</section>

<!-- Core Features Section -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Everything You Need</span>
            <h2>One Platform. Every Feature.</h2>
            <p class="section-subtitle">Stop juggling spreadsheets, emails, and separate tools. Manage your entire agency in one place.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h4>Tour Management</h4>
                <p>Create, schedule and publish tours with availability calendars, pricing tiers, and slot tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h4>Booking Engine</h4>
                <p>Accept bookings online, process payments, send confirmations, and track every reservation in real time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-magic"></i></div>
                <h4>AI Itinerary Builder</h4>
                <p>Let clients design their own tours with your AI-powered DIY planner. Increase sales with zero extra effort.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                <h4>Revenue Analytics</h4>
                <p>Track MRR, booking trends, top-performing tours, and client behaviour with live dashboards.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
                <h4>Staff & Permissions</h4>
                <p>Add unlimited team members with role-based access. Keep operations, sales, and support in their lanes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-palette"></i></div>
                <h4>White-Label Branding</h4>
                <p>Upload your logo, set brand colours, and present a fully branded experience to your clients.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Get Started in Minutes</span>
            <h2>How It Works</h2>
        </div>
        <div class="diy-choice-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem;">
            <div class="diy-choice-card package-card" style="text-align:center">
                <div class="diy-card-header" style="justify-content:center">
                    <div class="diy-choice-icon-wrap package-icon-wrap">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <h3 style="font-size:1.1rem">1. Sign Up</h3>
                <p class="diy-card-desc">Create your agency account in under 2 minutes. No credit card required for the 30-day trial.</p>
            </div>
            <div class="diy-choice-card package-card" style="text-align:center">
                <div class="diy-card-header" style="justify-content:center">
                    <div class="diy-choice-icon-wrap diy-icon-wrap">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                </div>
                <h3 style="font-size:1.1rem">2. Configure</h3>
                <p class="diy-card-desc">Add your branding, tours, team members, and payment gateway — all from a simple admin panel.</p>
            </div>
            <div class="diy-choice-card featured" style="text-align:center">
                <div class="diy-card-header" style="justify-content:center">
                    <div class="diy-choice-icon-wrap diy-icon-wrap">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <span class="diy-card-badge ai-badge"><i class="fas fa-bolt"></i> Live</span>
                </div>
                <h3 style="font-size:1.1rem">3. Go Live</h3>
                <p class="diy-card-desc">Share your booking page, start accepting clients, and watch subscriptions grow in your dashboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Why Agencies Choose Us</span>
            <h2>Built for Growth</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h4>Secure Payments</h4>
                <p>Integrated with Xendit and Stripe — collect payments safely with full PCI-DSS compliance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                <h4>Instant Setup</h4>
                <p>No developers needed. Your agency is live within minutes, not weeks.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-expand-arrows-alt"></i></div>
                <h4>Scales With You</h4>
                <p>From solo operators to large agencies — our plans grow as your business grows.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h4>24/7 Support</h4>
                <p>Our dedicated team is always available via chat, email, or phone — no ticket queues.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-envelope-open-text"></i></div>
                <h4>Automated Emails</h4>
                <p>Booking confirmations, payment follow-ups, and OTP verification sent automatically.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-cloud"></i></div>
                <h4>Cloud-Hosted</h4>
                <p>Zero infrastructure to manage. Automatic backups, updates, and 99.9% uptime guaranteed.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Simple Pricing</span>
            <h2>Plans for Every Agency</h2>
            <p class="section-subtitle">Start free. Upgrade when you're ready. Cancel anytime.</p>
        </div>
        <div class="diy-choice-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem; align-items: start;">
            {{-- Starter --}}
            <div class="diy-choice-card package-card">
                <div class="diy-card-header">
                    <div class="diy-choice-icon-wrap package-icon-wrap">
                        <i class="fas fa-seedling"></i>
                    </div>
                </div>
                <h3>Starter</h3>
                <p style="font-size:2rem;font-weight:800;margin:.5rem 0">₱2,999<span style="font-size:1rem;font-weight:400">/mo</span></p>
                <ul class="diy-choice-features">
                    <li><i class="fas fa-check-circle"></i> Up to 20 active tours</li>
                    <li><i class="fas fa-check-circle"></i> Unlimited bookings</li>
                    <li><i class="fas fa-check-circle"></i> 3 staff accounts</li>
                    <li><i class="fas fa-check-circle"></i> Basic analytics</li>
                    <li><i class="fas fa-check-circle"></i> Email support</li>
                </ul>
                <a href="{{ route('admin.auth.login') }}" class="diy-card-btn package-btn">
                    Start Free Trial <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            {{-- Professional --}}
            <div class="diy-choice-card diy-card featured">
                <div class="diy-card-header">
                    <div class="diy-choice-icon-wrap diy-icon-wrap">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span class="diy-card-badge ai-badge"><i class="fas fa-bolt"></i> Most Popular</span>
                </div>
                <h3>Professional</h3>
                <p style="font-size:2rem;font-weight:800;margin:.5rem 0">₱7,999<span style="font-size:1rem;font-weight:400">/mo</span></p>
                <ul class="diy-choice-features">
                    <li><i class="fas fa-check-circle"></i> Unlimited tours</li>
                    <li><i class="fas fa-check-circle"></i> Unlimited bookings</li>
                    <li><i class="fas fa-check-circle"></i> Unlimited staff</li>
                    <li><i class="fas fa-check-circle"></i> AI itinerary builder</li>
                    <li><i class="fas fa-check-circle"></i> Advanced analytics & MRR</li>
                    <li><i class="fas fa-check-circle"></i> White-label branding</li>
                    <li><i class="fas fa-check-circle"></i> Priority 24/7 support</li>
                </ul>
                <a href="{{ route('admin.auth.login') }}" class="diy-card-btn diy-btn">
                    Start Free Trial <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            {{-- Enterprise --}}
            <div class="diy-choice-card package-card">
                <div class="diy-card-header">
                    <div class="diy-choice-icon-wrap package-icon-wrap">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <h3>Enterprise</h3>
                <p style="font-size:2rem;font-weight:800;margin:.5rem 0">Custom</p>
                <ul class="diy-choice-features">
                    <li><i class="fas fa-check-circle"></i> Everything in Professional</li>
                    <li><i class="fas fa-check-circle"></i> Custom integrations</li>
                    <li><i class="fas fa-check-circle"></i> Dedicated account manager</li>
                    <li><i class="fas fa-check-circle"></i> SLA guarantee</li>
                    <li><i class="fas fa-check-circle"></i> On-boarding & training</li>
                </ul>
                <a href="{{ route('contact') }}" class="diy-card-btn package-btn">
                    Contact Sales <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content text-center">
            <h2>Ready to Grow Your Travel Agency?</h2>
            <p>Join agencies already using {{ $currentTenant->company_name ?? $currentTenant->name ?? 'TourSaaS' }} to streamline operations and boost revenue.</p>
            <div class="cta-btns">
                <a href="{{ route('admin.auth.login') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Start Free Trial
                </a>
                <a href="{{ route('contact') }}" class="btn btn-outline btn-lg btn-white">
                    <i class="fas fa-phone"></i> Talk to Sales
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.saas-hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; margin: 1.5rem 0; }
.gsap-hero-init .hero-label,
.gsap-hero-init .hero-title-block,
.gsap-hero-init .hero-title-script,
.gsap-hero-init .hero-subtitle,
.gsap-hero-init .saas-hero-actions,
.gsap-hero-init .hero-stats .hero-stat,
.gsap-hero-init .hero-scroll { opacity: 0; }
.gsap-reveal { opacity: 0; transform: translateY(40px); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    var hero = document.querySelector('.hero');
    if (hero) {
        hero.classList.add('gsap-hero-init');
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.fromTo('.hero-label', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.6 })
          .fromTo('.hero-title-block', { opacity: 0, y: 50 }, { opacity: 1, y: 0, duration: 0.7 }, '-=0.3')
          .fromTo('.hero-title-script', { opacity: 0, x: -50 }, { opacity: 1, x: 0, duration: 0.8 }, '-=0.4')
          .fromTo('.hero-subtitle', { opacity: 0, y: 20 }, { opacity: 0.9, y: 0, duration: 0.6 }, '-=0.4')
          .fromTo('.saas-hero-actions', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
          .fromTo('.hero-stats .hero-stat', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.15 }, '-=0.2')
          .fromTo('.hero-scroll', { opacity: 0 }, { opacity: 0.7, duration: 0.4 }, '-=0.1');
    }

    gsap.fromTo('.icon-strip-item',
        { opacity: 0, y: 40, scale: 0.85 },
        { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'back.out(1.4)',
          scrollTrigger: { trigger: '.section-icon-strip', start: 'top 85%', once: true } }
    );

    gsap.fromTo('.feature-card',
        { opacity: 0, y: 35 },
        { opacity: 1, y: 0, duration: 0.5, stagger: 0.08, ease: 'power2.out',
          scrollTrigger: { trigger: '.features-grid', start: 'top 80%', once: true } }
    );

    gsap.fromTo('.diy-choice-card',
        { opacity: 0, y: 50 },
        { opacity: 1, y: 0, duration: 0.5, stagger: 0.15, ease: 'power2.out',
          scrollTrigger: { trigger: '.diy-choice-grid', start: 'top 80%', once: true } }
    );

    var cta = document.querySelector('.cta-content');
    if (cta) {
        gsap.fromTo(cta, { opacity: 0, y: 40 },
            { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out',
              scrollTrigger: { trigger: '.cta-section', start: 'top 80%', once: true } }
        );
    }
});
</script>
@endpush


@section('content')
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <span class="hero-label">Explore the World</span>
            <h1 class="hero-title">
                <span class="hero-title-block">Discover Your</span>
                <span class="hero-title-script">Journey</span>
            </h1>
            <p class="hero-subtitle">Unlock the magic of the world with our expertly crafted journeys. Immerse yourself in rich cultures and create unforgettable memories.</p>

            <!-- Search Bar -->
            <form action="{{ route('tours.index') }}" method="GET" class="hero-search">
                <div class="search-field">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="search" placeholder="Where do you want to go?"
                        value="{{ request('search') }}">
                </div>
                <div class="search-field">
                    <i class="fas fa-tags"></i>
                    <select name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Search Tours
                </button>
            </form>

            <!-- Quick Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-num">{{ number_format($stats['total_tours']) }}+</span>
                    <span class="stat-label">Tours</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num">{{ number_format($stats['destinations']) }}+</span>
                    <span class="stat-label">Destinations</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num">{{ number_format($stats['total_reviews']) }}+</span>
                    <span class="stat-label">Happy Travelers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="hero-scroll">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- Quick Navigation Icon Strip -->
<section class="section-icon-strip">
    <div class="container">
        <div class="icon-strip-grid">
            <a href="{{ route('tours.index') }}" class="icon-strip-item" aria-label="Tours">
                <div class="icon-strip-circle icon-tours">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <span>Tours</span>
            </a>
            <a href="{{ route('destinations.index') }}" class="icon-strip-item" aria-label="Destinations">
                <div class="icon-strip-circle icon-destinations">
                    <i class="fas fa-globe-asia"></i>
                </div>
                <span>Destinations</span>
            </a>
            <a href="{{ route('tours.index', ['sort' => 'popular']) }}" class="icon-strip-item" aria-label="Popular">
                <div class="icon-strip-circle icon-popular">
                    <i class="fas fa-fire"></i>
                </div>
                <span>Popular</span>
            </a>
            <a href="{{ route('diy.index') }}" class="icon-strip-item" aria-label="Build My Tour">
                <div class="icon-strip-circle icon-buildtour">
                    <i class="fas fa-magic"></i>
                </div>
                <span>Build My Tour</span>
            </a>
            <a href="{{ route('about') }}" class="icon-strip-item" aria-label="About Us">
                <div class="icon-strip-circle icon-about">
                    <i class="fas fa-users"></i>
                </div>
                <span>About Us</span>
            </a>
            <a href="{{ route('contact') }}" class="icon-strip-item" aria-label="Contact">
                <div class="icon-strip-circle icon-contact">
                    <i class="fas fa-envelope"></i>
                </div>
                <span>Contact</span>
            </a>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
@if($promoBannerUrl ?? false)
<section class="promo-banner-section">
    <div class="container">
        @if($promoBannerLink ?? false)
            <a href="{{ $promoBannerLink }}" target="_blank" rel="noopener" class="promo-banner-link">
                <img src="{{ $promoBannerUrl }}" alt="Promotion" class="promo-banner-img">
            </a>
        @else
            <img src="{{ $promoBannerUrl }}" alt="Promotion" class="promo-banner-img">
        @endif
    </div>
</section>
@endif

<!-- Embedded Media (Facebook + YouTube) -->
@php
    $fbEmbeds = $fbEmbeds ?? [];
    $ytEmbeds = $ytEmbeds ?? [];
    $hasFb = count($fbEmbeds) > 0;
    $hasYt = count($ytEmbeds) > 0;
@endphp
@if($hasFb || $hasYt)
<section class="section section-gray section-media-embeds">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Stay Connected</span>
            <h2>Follow Our Journey</h2>
        </div>
        <div class="media-embeds-grid {{ ($hasFb && $hasYt) ? 'two-cols' : 'one-col' }}">
            @if($hasFb)
            <div class="media-embed-card">
                <div class="media-embed-label"><i class="fab fa-facebook"></i> Facebook</div>
                <div class="media-carousel media-carousel-fb" id="carousel-fb">
                    <div class="media-carousel-track">
                        @foreach($fbEmbeds as $i => $embed)
                        <div class="media-carousel-slide{{ $i===0?' active':'' }}">{!! $embed !!}</div>
                        @endforeach
                    </div>
                    @if(count($fbEmbeds) > 1)
                    <button class="carousel-btn carousel-btn-prev" onclick="carouselShift('carousel-fb',-1)" aria-label="Previous">&#8963;</button>
                    <button class="carousel-btn carousel-btn-next" onclick="carouselShift('carousel-fb',1)" aria-label="Next">&#8964;</button>
                    <div class="carousel-dots">
                        @foreach($fbEmbeds as $i => $e)
                        <span class="carousel-dot{{ $i===0?' active':'' }}" onclick="carouselGoto('carousel-fb',{{$i}})"></span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @if($hasYt)
            <div class="media-embed-card">
                <div class="media-embed-label"><i class="fab fa-youtube"></i> YouTube</div>
                <div class="media-carousel media-carousel-yt" id="carousel-yt">
                    <div class="media-carousel-track">
                        @foreach($ytEmbeds as $url)
                        <div class="media-carousel-slide">
                            <div class="yt-responsive">
                                <iframe src="{{ $url }}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if(count($ytEmbeds) > 1)
                    <button class="carousel-btn carousel-btn-prev" onclick="carouselShift('carousel-yt',-1)" aria-label="Previous">&#8249;</button>
                    <button class="carousel-btn carousel-btn-next" onclick="carouselShift('carousel-yt',1)" aria-label="Next">&#8250;</button>
                    <div class="carousel-dots">
                        @foreach($ytEmbeds as $i => $url)
                        <span class="carousel-dot{{ $i===0?' active':'' }}" onclick="carouselGoto('carousel-yt',{{$i}})"></span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
<script>
function carouselGoto(id, idx) {
    var el = document.getElementById(id);
    if (!el) return;
    var slides = el.querySelectorAll('.media-carousel-slide');
    var n = slides.length;
    if (idx < 0) idx = n - 1;
    if (idx >= n) idx = 0;
    if (el.classList.contains('media-carousel-fb')) {
        slides.forEach(function(s, i) { s.classList.toggle('active', i === idx); });
    } else {
        el.querySelector('.media-carousel-track').style.transform = 'translateX(-' + (idx * 100) + '%)';
    }
    el.querySelectorAll('.carousel-dot').forEach(function(d, i) { d.classList.toggle('active', i === idx); });
    el._idx = idx;
}
function carouselShift(id, dir) {
    var el = document.getElementById(id);
    carouselGoto(id, (el ? (el._idx || 0) : 0) + dir);
}
</script>
@endif


<section class="section section-diy-choice">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">How Would You Like to Travel?</span>
            <h2>Choose Your Perfect Adventure</h2>
            <p class="section-subtitle">Whether you prefer a ready-made experience or a fully custom journey, we've got you covered.</p>
        </div>
        <div class="diy-choice-grid">
            {{-- Package Tours Card --}}
            <div class="diy-choice-card package-card">
                <div class="diy-card-header">
                    <div class="diy-choice-icon-wrap package-icon-wrap">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <span class="diy-card-badge package-badge">Popular</span>
                </div>
                <h3>Package Tours</h3>
                <p class="diy-card-desc">Pre-designed, expertly curated itineraries. Everything planned — just show up and enjoy.</p>
                <ul class="diy-choice-features">
                    <li><i class="fas fa-check-circle"></i> From ₱150,000 per person</li>
                    <li><i class="fas fa-check-circle"></i> Guaranteed departures</li>
                    <li><i class="fas fa-check-circle"></i> Group &amp; private options</li>
                </ul>
                <a href="{{ route('tours.index') }}" class="diy-card-btn package-btn">
                    Browse Tours <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            {{-- Create Your Own Tour Card --}}
            <div class="diy-choice-card diy-card featured">
                <div class="diy-card-header">
                    <div class="diy-choice-icon-wrap diy-icon-wrap">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <span class="diy-card-badge ai-badge"><i class="fas fa-bolt"></i> AI-Powered</span>
                </div>
                <h3>Create Your Own Tour</h3>
                <p class="diy-card-desc">Tell our AI your dream trip. It designs a personalised itinerary you can fully customise.</p>
                <ul class="diy-choice-features">
                    <li><i class="fas fa-check-circle"></i> 100% customisable</li>
                    <li><i class="fas fa-check-circle"></i> AI route optimisation</li>
                    <li><i class="fas fa-check-circle"></i> Real-time pricing</li>
                    <li><i class="fas fa-check-circle"></i> Interactive map builder</li>
                </ul>
                <a href="{{ route('diy.index') }}" class="diy-card-btn diy-btn">
                    Start Building <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="diy-quiz-cta text-center">
            <div class="diy-quiz-cta-inner">
                <i class="fas fa-lightbulb"></i>
                <span>Not sure which is right for you?</span>
                <a href="{{ route('diy.index') }}" class="diy-quiz-link">Take our 2-minute quiz <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Browse By</span>
            <h2>Tour Categories</h2>
            <p>Find tours that match your travel style</p>
        </div>
        <div class="categories-grid">
            @foreach($categories as $cat)
                <a href="{{ route('tours.index', ['category' => $cat->slug]) }}" class="category-card">
                    <div class="category-icon">
                        <i class="{{ $cat->icon ?? 'fas fa-globe' }}"></i>
                    </div>
                    <h4>{{ $cat->name }}</h4>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Tours -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-label">Handpicked</span>
                <h2>Featured Tours</h2>
            </div>
            <a href="{{ route('tours.index', ['sort' => 'popular']) }}" class="btn btn-outline">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="tours-grid">
            @foreach($featuredTours as $tour)
                @include('partials.tour-card', ['tour' => $tour])
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Why Us</span>
            <h2>Travel With Confidence</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h4>Safe & Secure</h4>
                <p>All payments are encrypted and secure. Book with complete peace of mind.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-medal"></i></div>
                <h4>Expert Guides</h4>
                <p>Our certified local guides bring destinations to life with insider knowledge.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-undo-alt"></i></div>
                <h4>Free Cancellation</h4>
                <p>Cancel up to 48 hours before your tour for a full refund, hassle-free.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h4>24/7 Support</h4>
                <p>Our dedicated team is always available to assist you before and during your trip.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-dollar-sign"></i></div>
                <h4>Best Price Guarantee</h4>
                <p>We guarantee the best prices. Find it cheaper? We'll match it or refund.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-star"></i></div>
                <h4>Verified Reviews</h4>
                <p>All reviews come from verified travelers who completed their booking with us.</p>
            </div>
        </div>
    </div>
</section>

<!-- Top Rated Tours -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-label">Highly Rated</span>
                <h2>Top Rated Tours</h2>
            </div>
            <a href="{{ route('tours.index', ['sort' => 'rating']) }}" class="btn btn-outline">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="tours-grid tours-grid--4">
            @foreach($topRatedTours as $tour)
                @include('partials.tour-card', ['tour' => $tour])
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials -->
@if($latestReviews->count() > 0)
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Travelers Say</span>
            <h2>Real Reviews, Real Experiences</h2>
        </div>
        <div class="reviews-grid">
            @foreach($latestReviews as $review)
                <div class="review-card">
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow' : 'text-gray' }}"></i>
                        @endfor
                    </div>
                    <p class="review-body">"{{ Str::limit($review->body, 150) }}"</p>
                    <div class="review-meta">
                        <div class="reviewer-avatar">{{ strtoupper(substr($review->user->name, 0, 1)) }}</div>
                        <div>
                            <strong>{{ $review->user->name }}</strong>
                            <span>{{ $review->tour->title }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content text-center">
            <h2>Ready for Your Next Adventure?</h2>
            <p>Join thousands of happy travelers and book your dream tour today</p>
            <div class="cta-btns">
                <a href="{{ route('tours.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Explore Tours
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn btn-outline btn-lg btn-white">
                        <i class="fas fa-user-plus"></i> Sign Up Free
                    </a>
                @endguest
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
/* GSAP initial states — prevent flash of unstyled content */
.gsap-hero-init .hero-label,
.gsap-hero-init .hero-title-block,
.gsap-hero-init .hero-title-script,
.gsap-hero-init .hero-subtitle,
.gsap-hero-init .hero-search,
.gsap-hero-init .hero-stats .hero-stat,
.gsap-hero-init .hero-scroll { opacity: 0; }
.gsap-reveal { opacity: 0; transform: translateY(40px); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    /* ── HERO ENTRANCE TIMELINE ──────────────────────────────── */
    var hero = document.querySelector('.hero');
    if (hero) {
        hero.classList.add('gsap-hero-init');

        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        tl.fromTo('.hero-label',
            { opacity: 0, y: 30, scale: 0.9 },
            { opacity: 1, y: 0, scale: 1, duration: 0.7 }
        )
        .fromTo('.hero-title-block',
            { opacity: 0, y: 50, clipPath: 'inset(0 0 100% 0)' },
            { opacity: 1, y: 0, clipPath: 'inset(0 0 0% 0)', duration: 0.8 },
            '-=0.3'
        )
        .fromTo('.hero-title-script',
            { opacity: 0, x: -60, rotationZ: -3 },
            { opacity: 1, x: 0, rotationZ: 0, duration: 0.9 },
            '-=0.4'
        )
        .fromTo('.hero-subtitle',
            { opacity: 0, y: 25 },
            { opacity: 0.9, y: 0, duration: 0.7 },
            '-=0.4'
        )
        .fromTo('.hero-search',
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.7 },
            '-=0.3'
        )
        .fromTo('.hero-stats .hero-stat',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.15 },
            '-=0.3'
        )
        .fromTo('.hero-scroll',
            { opacity: 0 },
            { opacity: 0.7, duration: 0.5 },
            '-=0.2'
        );
    }

    /* ── ICON STRIP — stagger in from below ──────────────────── */
    gsap.utils.toArray('.icon-strip-item').forEach(function (item) {
        item.classList.add('gsap-reveal');
    });
    gsap.fromTo('.icon-strip-item',
        { opacity: 0, y: 40, scale: 0.85 },
        {
            opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08,
            ease: 'back.out(1.4)',
            scrollTrigger: { trigger: '.section-icon-strip', start: 'top 85%', once: true }
        }
    );

    /* ── GENERIC SCROLL REVEAL for sections ──────────────────── */
    var revealSections = [
        { selector: '.section-media-embeds .section-header', y: 30 },
        { selector: '.media-embed-card', y: 40, stagger: 0.2 },
        { selector: '.section-diy-choice .section-header', y: 30 },
        { selector: '.diy-choice-card', y: 50, stagger: 0.2 },
        { selector: '.diy-quiz-cta', y: 20 },
        { selector: '.section-gray .categories-grid .category-card', y: 30, stagger: 0.06 },
        { selector: '.tours-grid .tour-card', y: 40, stagger: 0.1 },
        { selector: '.section-dark .feature-card', y: 35, stagger: 0.08 },
        { selector: '.tours-grid--4 .tour-card', y: 40, stagger: 0.1 },
        { selector: '.reviews-grid .review-card', y: 30, stagger: 0.12 },
    ];

    revealSections.forEach(function (cfg) {
        var els = gsap.utils.toArray(cfg.selector);
        if (!els.length) return;

        els.forEach(function (el) { el.classList.add('gsap-reveal'); });

        var trigger = els[0].closest('section') || els[0].parentElement;

        gsap.fromTo(cfg.selector,
            { opacity: 0, y: cfg.y || 40 },
            {
                opacity: 1, y: 0,
                duration: 0.6,
                stagger: cfg.stagger || 0,
                ease: 'power2.out',
                scrollTrigger: { trigger: trigger, start: 'top 80%', once: true }
            }
        );
    });

    /* ── SECTION HEADERS — slide up with label ───────────────── */
    gsap.utils.toArray('.section-header h2').forEach(function (h2) {
        var header = h2.closest('.section-header');
        if (!header || header.closest('.hero')) return;
        var label = header.querySelector('.section-label');

        gsap.fromTo(h2,
            { opacity: 0, y: 25 },
            {
                opacity: 1, y: 0, duration: 0.6, ease: 'power2.out',
                scrollTrigger: { trigger: header, start: 'top 85%', once: true }
            }
        );
        if (label) {
            gsap.fromTo(label,
                { opacity: 0, y: 15 },
                {
                    opacity: 1, y: 0, duration: 0.5, ease: 'power2.out',
                    scrollTrigger: { trigger: header, start: 'top 85%', once: true }
                }
            );
        }
    });

    /* ── ADVENTURE CARDS — slight parallax tilt on hover ─────── */
    document.querySelectorAll('.diy-choice-card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            gsap.to(card, { scale: 1.03, duration: 0.3, ease: 'power2.out' });
        });
        card.addEventListener('mouseleave', function () {
            gsap.to(card, { scale: 1, duration: 0.3, ease: 'power2.out' });
        });
    });

    /* ── CTA SECTION — fade up on scroll ─────────────────────── */
    var cta = document.querySelector('.cta-content');
    if (cta) {
        gsap.fromTo(cta,
            { opacity: 0, y: 40 },
            {
                opacity: 1, y: 0, duration: 0.8, ease: 'power2.out',
                scrollTrigger: { trigger: '.cta-section', start: 'top 80%', once: true }
            }
        );
    }

    /* ── STAT NUMBER COUNT-UP ────────────────────────────────── */
    document.querySelectorAll('.stat-num').forEach(function (el) {
        var text = el.textContent.trim();
        var match = text.match(/^([\d,]+)/);
        if (!match) return;
        var target = parseInt(match[1].replace(/,/g, ''), 10);
        var suffix = text.replace(match[1], '');
        var obj = { val: 0 };

        gsap.to(obj, {
            val: target,
            duration: 2,
            ease: 'power1.out',
            delay: 0.8,
            onUpdate: function () {
                el.textContent = Math.round(obj.val).toLocaleString() + suffix;
            }
        });
    });

    /* ── WHY-US FEATURE ICONS — spin in ──────────────────────── */
    gsap.fromTo('.feature-card .feature-icon',
        { opacity: 0, scale: 0, rotation: -180 },
        {
            opacity: 1, scale: 1, rotation: 0,
            duration: 0.6, stagger: 0.1,
            ease: 'back.out(1.7)',
            scrollTrigger: { trigger: '.section-dark .features-grid', start: 'top 80%', once: true }
        }
    );
});
</script>
@endpush
