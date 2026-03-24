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
            <h1 class="hero-title">Discover Amazing<br><span>Tour Experiences</span></h1>
            <p class="hero-subtitle">Hand-crafted tours to breathtaking destinations. Book with confidence, travel with joy.</p>

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
