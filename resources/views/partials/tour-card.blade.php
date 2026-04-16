<div class="tour-card">
    @php
        $tourUrl = route('tours.show', $tour->slug);
        $salesMessage = "Hi Discover Group! I am interested in this tour: {$tour->title} - {$tourUrl}";
        $contactSalesUrl = 'https://www.facebook.com/messages/t/discovergrp';
    @endphp

    <div class="tour-card-img">
        <img src="{{ cdn_url($tour->main_image, asset('images/tour-placeholder.jpg')) }}"
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
                <a href="{{ $contactSalesUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="price-current js-contact-sales"
                   data-sales-message="{{ $salesMessage }}">Contact Sales</a>
            @endif
            @if($tour->regular_price_per_person)
                <small>per person</small>
            @endif
        </div>
        @php
            $allFull = $tour->schedules->isNotEmpty() && $tour->schedules->every(fn($s) => ($s->available_seats - $s->booked_seats) <= 0 || $s->status === 'sold_out');
        @endphp
        @if($allFull)
            <a href="{{ route('tours.index', ['continent' => $tour->continent]) }}" class="btn btn-outline btn-sm" style="border-color:#1e3a5f;color:#1e3a5f;font-weight:600">
                <i class="fas fa-compass"></i> Explore Similar Tours
            </a>
        @else
            <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-primary btn-sm">
                View Details
            </a>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            function showSalesCopyToast(message) {
                const existing = document.getElementById('salesCopyToast');
                if (existing) existing.remove();

                const toast = document.createElement('div');
                toast.id = 'salesCopyToast';
                toast.className = 'sales-copy-toast';
                toast.textContent = message;
                document.body.appendChild(toast);

                requestAnimationFrame(function () {
                    toast.classList.add('is-visible');
                });

                setTimeout(function () {
                    toast.classList.remove('is-visible');
                    setTimeout(function () {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 180);
                }, 1800);
            }

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('.js-contact-sales');
                if (!trigger) return;

                const message = trigger.getAttribute('data-sales-message');
                if (!message) return;

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(message)
                        .then(function () {
                            showSalesCopyToast('Message copied. Paste it in Facebook chat.');
                        })
                        .catch(function () {
                            showSalesCopyToast('Copy not allowed by browser. Please copy manually.');
                        });
                    return;
                }

                const input = document.createElement('textarea');
                input.value = message;
                input.setAttribute('readonly', 'readonly');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                input.style.pointerEvents = 'none';
                document.body.appendChild(input);
                input.focus();
                input.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(input);
                showSalesCopyToast('Message copied. Paste it in Facebook chat.');
            });
        </script>
    @endpush
@endonce
