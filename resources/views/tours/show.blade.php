@extends('layouts.app')
@section('title', $tour->title)

@section('content')
<!-- Tour Hero -->
<div class="tour-hero">
    <div class="tour-hero-img" style="background-image: url('{{ cdn_url($tour->main_image, asset('images/placeholder-tour.jpg')) }}')">
        <div class="tour-hero-overlay"></div>
    </div>
    <div class="container">
        <div class="tour-hero-content">
            <div class="breadcrumb-nav">
                <a href="{{ route('home') }}">Home</a> /
                <a href="{{ route('tours.index') }}">Tours</a> /
                <a href="{{ route('tours.index', ['continent' => $tour->continent]) }}">{{ $tour->continent }}</a> /
                <span>{{ $tour->title }}</span>
            </div>
            <h1>{{ $tour->title }}</h1>
            <div class="tour-hero-meta">
                <span><i class="fas fa-map-marker-alt"></i> {{ $tour->line }} &mdash; {{ $tour->continent }}</span>
                <span><i class="fas fa-clock"></i> {{ $tour->duration_days }} Days</span>
                @if($tour->guaranteed_departure)
                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Guaranteed Departure</span>
                @endif
                @if($tour->average_rating > 0)
                    <span><i class="fas fa-star text-yellow"></i> {{ number_format($tour->average_rating, 1) }} ({{ $tour->total_reviews }} reviews)</span>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="tour-detail-layout">

            <!-- Main Content -->
            <div class="tour-detail-main">

                <!-- Image Gallery -->
                @php $galleryImages = $tour->gallery_images ?? []; @endphp
                @if(count($galleryImages) > 0)
                    <div class="tour-gallery">
                        <div class="gallery-main" id="galleryMain">
                            <img src="{{ cdn_url($galleryImages[0]) }}"
                                 alt="{{ $tour->title }}" id="mainGalleryImg">
                        </div>
                        <div class="gallery-thumbs" id="galleryThumbs">
                            @foreach($galleryImages as $i => $imgPath)
                                <img src="{{ cdn_url($imgPath) }}"
                                     alt="{{ $tour->title }}"
                                     class="gallery-thumb {{ $i === 0 ? 'active' : '' }}"
                                     onclick="changeGalleryImage(this)"
                                     loading="lazy">
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Tab Navigation -->
                <div class="tour-tabs">
                    <nav class="tab-nav">
                        <button class="tab-btn active" data-tab="overview">Overview</button>
                        @if($tour->full_stops && count($tour->full_stops) > 0)
                            <button class="tab-btn" data-tab="destinations"><i class="fas fa-route"></i> Destinations</button>
                        @endif
                        @if($tour->itinerary && count($tour->itinerary) > 0)
                            <button class="tab-btn" data-tab="itinerary">Itinerary</button>
                        @endif
                        @if(($tour->optional_tours && count($tour->optional_tours) > 0) || ($tour->cash_freebies && count($tour->cash_freebies) > 0))
                            <button class="tab-btn" data-tab="extras">Extras</button>
                        @endif
                        <button class="tab-btn" data-tab="reviews">
                            Reviews ({{ $tour->total_reviews }})
                        </button>
                    </nav>

                    <!-- Overview Tab -->
                    <div class="tab-content active" id="tab-overview">
                        <h3>About This Tour</h3>
                        <div class="tour-description">
                            {!! nl2br(e($tour->summary)) !!}
                        </div>

                        @if($tour->highlights && count($tour->highlights) > 0)
                            <h4 class="mt-4"><i class="fas fa-check-circle text-green"></i> Tour Highlights</h4>
                            <ul class="highlights-list">
                                @foreach($tour->highlights as $highlight)
                                    <li><i class="fas fa-star text-yellow"></i> {{ $highlight }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="tour-info-grid mt-4">
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <strong>Tour Line</strong>
                                <span>{{ $tour->line }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-globe"></i>
                                <strong>Continent</strong>
                                <span>{{ $tour->continent }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <strong>Duration</strong>
                                <span>{{ $tour->duration_days }} Days</span>
                            </div>
                            @if($tour->travel_window)
                                <div class="info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong>Travel Window</strong>
                                    <span>
                                        {{ \Carbon\Carbon::parse($tour->travel_window['start'])->format('M Y') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($tour->travel_window['end'])->format('M Y') }}
                                    </span>
                                </div>
                            @endif
                            @if($tour->additional_info)
                                @if(!empty($tour->additional_info['startingPoint']))
                                    <div class="info-item">
                                        <i class="fas fa-plane-departure"></i>
                                        <strong>Starts At</strong>
                                        <span>{{ $tour->additional_info['startingPoint'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($tour->additional_info['endingPoint']))
                                    <div class="info-item">
                                        <i class="fas fa-plane-arrival"></i>
                                        <strong>Ends At</strong>
                                        <span>{{ $tour->additional_info['endingPoint'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($tour->additional_info['countriesVisited']) && count($tour->additional_info['countriesVisited']) > 0)
                                    <div class="info-item">
                                        <i class="fas fa-flag"></i>
                                        <strong>Countries</strong>
                                        <span>{{ implode(', ', $tour->additional_info['countriesVisited']) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Destinations / Full Stops Tab -->
                    @if($tour->full_stops && count($tour->full_stops) > 0)
                    <div class="tab-content" id="tab-destinations">
                        <h3>Tour Destinations</h3>
                        <p class="text-muted" style="margin-bottom:1.5rem;">Cities and stops covered on this tour.</p>
                        <div class="route-timeline">
                            @foreach($tour->full_stops as $stop)
                            <div class="route-stop {{ $loop->first ? 'route-stop--first' : '' }} {{ $loop->last ? 'route-stop--last' : '' }}">
                                <div class="route-stop-icon">
                                    <i class="{{ $loop->first ? 'fas fa-plane-departure' : ($loop->last ? 'fas fa-plane-arrival' : 'fas fa-map-marker-alt') }}"></i>
                                </div>
                                <div class="route-stop-line"></div>
                                <div class="route-stop-body">
                                    <h4 class="route-stop-location">{{ $stop['city'] }}</h4>
                                    @if(!empty($stop['country']))
                                        <span class="route-stop-country">
                                            <i class="fas fa-globe-asia"></i> {{ $stop['country'] }}
                                        </span>
                                    @endif
                                    @if(!empty($stop['days']))
                                        <span class="route-stop-duration">
                                            <i class="fas fa-clock"></i> {{ $stop['days'] }} {{ Str::plural('day', $stop['days']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Itinerary Tab -->
                    @if($tour->itinerary && count($tour->itinerary) > 0)
                    <div class="tab-content" id="tab-itinerary">
                        <div class="itinerary-list">
                            @foreach($tour->itinerary as $i => $day)
                                <div class="itinerary-day">
                                    <div class="itinerary-day-num">Day {{ $day['day'] ?? ($i + 1) }}</div>
                                    <div class="itinerary-day-content">
                                        <h4>{{ $day['title'] }}</h4>
                                        <p>{{ $day['description'] }}</p>
                                        @if(!empty($day['accommodation']))
                                            <span class="itinerary-chip">
                                                <i class="fas fa-bed"></i> {{ $day['accommodation'] }}
                                            </span>
                                        @endif
                                        @if(!empty($day['meals']) && is_array($day['meals']))
                                            <span class="itinerary-chip">
                                                <i class="fas fa-utensils"></i> {{ implode(', ', $day['meals']) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Extras Tab -->
                    @if(($tour->optional_tours && count($tour->optional_tours) > 0) || ($tour->cash_freebies && count($tour->cash_freebies) > 0))
                    <div class="tab-content" id="tab-extras">
                        @if($tour->cash_freebies && count($tour->cash_freebies) > 0)
                            <h4 class="text-green"><i class="fas fa-gift"></i> Freebies & Inclusions</h4>
                            <ul class="includes-list includes-list--yes mb-4">
                                @foreach($tour->cash_freebies as $freebie)
                                    <li><i class="fas fa-check"></i> {{ $freebie['label'] }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if($tour->optional_tours && count($tour->optional_tours) > 0)
                            <h4 class="mt-3"><i class="fas fa-plus-circle"></i> Optional Add-ons</h4>
                            <div class="optional-tours-list">
                                @foreach($tour->optional_tours as $opt)
                                    <div class="optional-tour-item">
                                        <strong>{{ $opt['name'] ?? $opt['title'] ?? 'Add-on' }}</strong>
                                        @if(!empty($opt['price']))
                                            <span class="optional-tour-price">+₱{{ number_format($opt['price'], 2) }}</span>
                                        @endif
                                        @if(!empty($opt['description']))
                                            <p class="text-muted small">{{ $opt['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Reviews Tab -->
                    <div class="tab-content" id="tab-reviews">
                        @if($tour->reviews->count() > 0)
                            <div class="reviews-summary">
                                <div class="rating-big">
                                    <span class="rating-num">{{ number_format($tour->average_rating, 1) }}</span>
                                    <div class="rating-stars-big">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $tour->average_rating ? 'text-yellow' : 'text-gray' }}"></i>
                                        @endfor
                                    </div>
                                    <span>{{ $tour->total_reviews }} reviews</span>
                                </div>
                            </div>

                            <div class="reviews-list">
                                @foreach($tour->reviews as $review)
                                    <div class="review-item">
                                        <div class="review-item-header">
                                            <div class="reviewer-avatar-sm">
                                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $review->user->name }}</strong>
                                                <div class="review-stars-sm">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow' : 'text-gray' }}"></i>
                                                    @endfor
                                                </div>
                                                <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                        <h5 class="review-title">{{ $review->title }}</h5>
                                        <p>{{ $review->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-star fa-2x text-muted"></i>
                                <p>No reviews yet. Be the first to review this tour!</p>
                            </div>
                        @endif

                        <!-- Write Review -->
                        @auth
                            <div class="write-review mt-4">
                                <h4>Write a Review</h4>
                                <form action="{{ route('reviews.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                                    <div class="form-group">
                                        <label>Your Rating</label>
                                        <div class="star-rating-input" id="starRating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star" data-value="{{ $i }}"></i>
                                            @endfor
                                            <input type="hidden" name="rating" id="ratingInput" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Summarize your experience" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Your Review</label>
                                        <textarea name="body" class="form-control" rows="4"
                                            placeholder="Tell others about your experience (min 20 characters)..." required minlength="20"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit Review
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="mt-3"><a href="{{ route('login') }}">Login</a> to write a review.</p>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <aside class="tour-booking-sidebar">
                <div class="booking-widget">
                    <div class="booking-widget-header">
                        <div class="booking-price">
                            @if($tour->promo_price_per_person)
                                <span class="price-original">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
                                <span class="price-current large">₱{{ number_format($tour->promo_price_per_person, 2) }}</span>
                            @else
                                <span class="price-current large">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
                            @endif
                            <small>per person</small>
                        </div>
                        @if($tour->discount_percent > 0)
                            <span class="discount-badge">{{ $tour->discount_percent }}% OFF</span>
                        @endif
                    </div>

                    @if($tour->allows_downpayment && $tour->fixed_downpayment_amount)
                        <div class="downpayment-note">
                            <i class="fas fa-info-circle"></i>
                            Reserve with ₱{{ number_format($tour->fixed_downpayment_amount, 2) }} downpayment
                            @if($tour->balance_due_days_before_travel)
                                &mdash; balance due {{ $tour->balance_due_days_before_travel }} days before travel
                            @endif
                        </div>
                    @endif

                    <!-- Departure Dates -->
                    @php $dates = $tour->departure_dates ?? []; @endphp
                    @if(count($dates) > 0)
                        <div class="departure-dates-list">
                            <h5><i class="fas fa-calendar"></i> Available Departures</h5>
                            @foreach($dates as $date)
                                @if($date['isAvailable'] ?? true)
                                    <div class="departure-date-row">
                                        <span>
                                            {{ \Carbon\Carbon::parse($date['start'])->format('M d') }}
                                            &ndash;
                                            {{ \Carbon\Carbon::parse($date['end'])->format('M d, Y') }}
                                        </span>
                                        @php
                                            $remaining = ($date['maxCapacity'] ?? 0) - ($date['currentBookings'] ?? 0);
                                        @endphp
                                        @if(isset($date['maxCapacity']) && $remaining <= 5)
                                            <span class="seats-badge seats-low">{{ $remaining }} left</span>
                                        @elseif(isset($date['maxCapacity']))
                                            <span class="seats-badge">{{ $remaining }} seats</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Book Now Button (internal) -->
                    @auth
                        <a href="{{ route('booking.create', ['tour_id' => $tour->id]) }}"
                           class="btn btn-primary btn-block btn-lg mt-3">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg mt-3">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    @endauth

                    <!-- External Booking / Flipbook Links -->
                    @php
                        $bookingLinks = $tour->booking_links ?? [];
                        // Group back by year for display, support both formats
                        $linksByYear = [];
                        foreach ($bookingLinks as $link) {
                            $year = $link['year'] ?? null;
                            $urls = isset($link['urls']) && is_array($link['urls'])
                                ? $link['urls']
                                : (isset($link['url']) ? [$link['url']] : []);
                            if ($urls) {
                                $linksByYear[$year ?? 'Other'][] = $urls;
                                // flatten
                                $linksByYear[$year ?? 'Other'] = array_merge(...$linksByYear[$year ?? 'Other']);
                            }
                        }
                    @endphp
                    @if(count($linksByYear) > 0)
                        <div class="online-booking-links mt-3">
                            <p style="font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;margin-bottom:.5rem">
                                Tour Brochure / Flipbook
                            </p>
                            @foreach($linksByYear as $year => $urls)
                                @foreach($urls as $idx => $url)
                                    @if(!empty($url))
                                        @php $suffix = count($urls) > 1 ? ' ' . chr(65 + $idx) : ''; @endphp
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                           class="btn btn-outline btn-block mb-2" style="font-size:.875rem">
                                            <i class="fas fa-book-open"></i>
                                            View {{ $year }} Flipbook{{ $suffix }}
                                        </a>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    <!-- Wishlist Toggle -->
                    @auth
                    <button type="button"
                            class="btn btn-outline btn-block mt-2 wishlist-toggle-btn {{ $isWishlisted ? 'wishlist-active' : '' }}"
                            data-tour="{{ $tour->id }}"
                            data-url="{{ route('tours.wishlist.toggle', $tour) }}">
                        <i class="fas fa-heart"></i>
                        <span>{{ $isWishlisted ? 'In Wishlist' : 'Add to Wishlist' }}</span>
                    </button>
                    @endauth

                    <div class="booking-widget-footer">
                        <i class="fas fa-shield-alt"></i> Secure booking. Free cancellation 48h before.
                    </div>
                </div>
            </aside>
        </div>

        <!-- Related Tours -->
        @if($relatedTours->count() > 0)
            <div class="related-tours mt-5">
                <h3>You Might Also Like</h3>
                <div class="tours-grid tours-grid--4">
                    @foreach($relatedTours as $relatedTour)
                        @include('partials.tour-card', ['tour' => $relatedTour])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
const basePrice = {{ $tour->effective_price ?? 0 }};
const childRate = 0.75;

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

// Gallery
function changeGalleryImage(thumb) {
    document.getElementById('mainGalleryImg').src = thumb.src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

// Star Rating Input
document.querySelectorAll('#starRating .fa-star').forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.value);
        document.getElementById('ratingInput').value = val;
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < val);
        });
    });
    star.addEventListener('mouseover', function() {
        const val = parseInt(this.dataset.value);
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < val);
        });
    });
    star.addEventListener('mouseout', function() {
        const current = parseInt(document.getElementById('ratingInput').value || 0);
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < current);
        });
    });
});

// Wishlist toggle
document.querySelectorAll('.wishlist-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            const span = this.querySelector('span');
            if (data.wishlisted) {
                this.classList.add('wishlist-active');
                span.textContent = 'In Wishlist';
            } else {
                this.classList.remove('wishlist-active');
                span.textContent = 'Add to Wishlist';
            }
        });
    });
});
</script>
@endpush
