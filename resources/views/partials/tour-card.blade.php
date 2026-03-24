<div class="tour-card">
    <div class="tour-card-img">
        <img src="{{ $tour->main_image ? asset('storage/' . $tour->main_image) : asset('images/tour-placeholder.jpg') }}"
             alt="{{ $tour->title }}"
             loading="lazy"
             onerror="this.src='{{ asset('images/tour-placeholder.jpg') }}'">

        @if($tour->discount_percent > 0)
            <span class="tour-badge tour-badge--discount">-{{ $tour->discount_percent }}%</span>
        @endif
        @if($tour->is_featured)
            <span class="tour-badge tour-badge--featured">Featured</span>
        @endif

        <button class="wishlist-btn {{ isset($wishedIds) && in_array($tour->id, $wishedIds) ? 'active' : '' }}"
                data-tour="{{ $tour->id }}"
                aria-label="Add to wishlist">
            <i class="fas fa-heart"></i>
        </button>
    </div>

    <div class="tour-card-body">
        <div class="tour-card-meta">
            @if($tour->line)
                <span class="tour-category">
                    <i class="fas fa-tag"></i> {{ $tour->line }}
                </span>
            @endif
            @if($tour->continent)
                <span class="tour-location">
                    <i class="fas fa-globe"></i> {{ $tour->continent }}
                </span>
            @endif
        </div>

        <h3 class="tour-card-title">
            <a href="{{ route('tours.show', $tour->slug) }}">{{ $tour->title }}</a>
        </h3>

        <p class="tour-card-desc">{{ Str::limit($tour->short_description, 100) }}</p>

        <div class="tour-card-details">
            <span><i class="fas fa-clock"></i> {{ $tour->duration_days }} Days</span>
            @if($tour->guaranteed_departure)
                <span><i class="fas fa-check-circle text-green"></i> Guaranteed</span>
            @endif
            @if($tour->average_rating > 0)
                <span><i class="fas fa-star text-yellow"></i> {{ number_format($tour->average_rating, 1) }}
                    <small>({{ $tour->total_reviews }})</small>
                </span>
            @endif
        </div>
    </div>

    <div class="tour-card-footer">
        <div class="tour-price">
            @if($tour->promo_price_per_person)
                <span class="price-original">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
                <span class="price-current">₱{{ number_format($tour->promo_price_per_person, 2) }}</span>
            @elseif($tour->regular_price_per_person)
                <span class="price-current">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
            @else
                <span class="price-current">Contact Us</span>
            @endif
            @if($tour->regular_price_per_person)
                <small>per person</small>
            @endif
        </div>
        <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-primary btn-sm">
            View Details
        </a>
    </div>
</div>
