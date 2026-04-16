@extends('layouts.app')
@section('title', 'Home')

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
