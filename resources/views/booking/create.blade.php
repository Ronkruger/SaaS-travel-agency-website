@extends('layouts.app')
@section('title', 'Book Tour — ' . $tour->title)

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Book Your Tour</h1>
        <p>{{ $tour->title }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="booking-layout">
            <!-- Booking Form -->
            <div class="booking-form-main">
                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                    @if(request('schedule_id'))
                        <input type="hidden" name="schedule_id" value="{{ request('schedule_id') }}">
                    @endif

                    <!-- Step 1: Tour Details -->
                    <div class="booking-step">
                        <div class="step-header">
                            <span class="step-num">1</span>
                            <h3>Tour Details</h3>
                        </div>

                        @php $departureDates = collect($tour->departure_dates ?? [])->filter(fn($d) => ($d['isAvailable'] ?? true)); @endphp
                        @if($departureDates->count() > 0)
                            <div class="form-group">
                                <label>Scheduled Departure</label>
                                <select name="departure_date" class="form-control" onchange="updateScheduleDate(this)">
                                    <option value="">Choose a departure date...</option>
                                    @foreach($departureDates as $idx => $dateEntry)
                                        <option value="{{ $dateEntry['start'] ?? '' }}"
                                            data-date="{{ $dateEntry['start'] ?? '' }}"
                                            data-price="{{ $dateEntry['price'] ?? '' }}"
                                            {{ old('departure_date') == ($dateEntry['start'] ?? '') ? 'selected' : '' }}>
                                            {{ isset($dateEntry['start']) ? \Carbon\Carbon::parse($dateEntry['start'])->format('M d, Y') : '' }}
                                            @if(!empty($dateEntry['end']))
                                                — {{ \Carbon\Carbon::parse($dateEntry['end'])->format('M d, Y') }}
                                            @endif
                                            @if(!empty($dateEntry['maxCapacity']) && !empty($dateEntry['currentBookings']))
                                                — {{ $dateEntry['maxCapacity'] - ($dateEntry['currentBookings'] ?? 0) }} seats left
                                            @endif
                                            @if(!empty($dateEntry['price']))
                                                — ₱{{ number_format($dateEntry['price'], 2) }}/person
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Tour Date *</label>
                                <input type="date" name="tour_date"
                                    value="{{ old('tour_date', now()->addDays(7)->format('Y-m-d')) }}"
                                    min="{{ now()->format('Y-m-d') }}"
                                    class="form-control @error('tour_date') is-invalid @enderror"
                                    id="tourDate" required>
                                @error('tour_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="guests-grid">
                            <div class="form-group">
                                <label>Adults (13+)</label>
                                <div class="guest-counter-inline">
                                    <button type="button" onclick="changeCount('adults', -1)">−</button>
                                    <input type="number" name="adults" id="adults"
                                        value="{{ old('adults', 1) }}" min="1" max="20"
                                        class="form-control" readonly>
                                    <button type="button" onclick="changeCount('adults', 1)">+</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Children (2–12)</label>
                                <div class="guest-counter-inline">
                                    <button type="button" onclick="changeCount('children', -1)">−</button>
                                    <input type="number" name="children" id="children"
                                        value="{{ old('children', 0) }}" min="0" max="20"
                                        class="form-control" readonly>
                                    <button type="button" onclick="changeCount('children', 1)">+</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Infants (under 2)</label>
                                <div class="guest-counter-inline">
                                    <button type="button" onclick="changeCount('infants', -1)">−</button>
                                    <input type="number" name="infants" id="infants"
                                        value="{{ old('infants', 0) }}" min="0" max="5"
                                        class="form-control" readonly>
                                    <button type="button" onclick="changeCount('infants', 1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Contact Information -->
                    <div class="booking-step">
                        <div class="step-header">
                            <span class="step-num">2</span>
                            <h3>Contact Information</h3>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="contact_name"
                                    value="{{ old('contact_name', auth()->user()->name) }}"
                                    class="form-control @error('contact_name') is-invalid @enderror" required>
                                @error('contact_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="contact_phone"
                                    value="{{ old('contact_phone', auth()->user()->phone) }}"
                                    class="form-control @error('contact_phone') is-invalid @enderror" required>
                                @error('contact_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="contact_email"
                                value="{{ old('contact_email', auth()->user()->email) }}"
                                class="form-control @error('contact_email') is-invalid @enderror" required>
                            @error('contact_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>Special Requests <small class="text-muted">(optional)</small></label>
                            <textarea name="special_requests" class="form-control" rows="3"
                                placeholder="Dietary requirements, accessibility needs, special occasions...">{{ old('special_requests') }}</textarea>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="booking-step">
                        <label class="form-check">
                            <input type="checkbox" required>
                            I have read and agree to the <a href="#" target="_blank">Terms & Conditions</a>
                            and <a href="#" target="_blank">Cancellation Policy</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-arrow-right"></i> Proceed to Payment
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <aside class="booking-summary">
                <div class="summary-card">
                    <div class="summary-tour-img">
                        @if($tour->main_image)
                            <img src="{{ asset('storage/' . $tour->main_image) }}" alt="{{ $tour->title }}">
                        @endif
                    </div>
                    <div class="summary-content">
                        <h4>{{ $tour->title }}</h4>
                        @if($tour->continent)
                            <p><i class="fas fa-globe"></i> {{ $tour->continent }}</p>
                        @endif
                        <p><i class="fas fa-clock"></i> {{ $tour->duration_days }} Day{{ $tour->duration_days > 1 ? 's' : '' }}</p>

                        <div class="summary-prices" id="summaryPrices">
                            <div class="summary-row">
                                <span id="sumAdultLabel">1 Adult</span>
                                <span id="sumAdultTotal">₱{{ number_format($tour->effective_price, 2) }}</span>
                            </div>
                            <div class="summary-row" id="sumChildRow" style="display:none;">
                                <span id="sumChildLabel">0 Children</span>
                                <span id="sumChildTotal">₱0.00</span>
                            </div>
                            <div class="summary-row summary-row--divider"></div>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="sumSubtotal">₱{{ number_format($tour->effective_price ?? 0, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span id="sumTaxLabel">Travel Tax</span>
                                <span id="sumTax">₱1,620.00</span>
                            </div>
                            <div class="summary-row summary-row--total">
                                <strong>Total</strong>
                                <strong id="sumTotal">₱{{ number_format(($tour->effective_price ?? 0) + 1620, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="summary-footer">
                        <i class="fas fa-lock"></i> Secure checkout
                        <i class="fas fa-undo-alt"></i> Free cancellation 48h before
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function fmt(n) {
    return n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Base price: use per-person tour price, fallback to 0 (will be overridden by departure date price)
let currentPrice = {{ $tour->effective_price ?? 0 }};
const childRate    = 0.75;
const TRAVEL_TAX   = 1620; // Fixed Philippine travel tax per person (economy, infants exempt)

function changeCount(field, delta) {
    const input  = document.getElementById(field);
    const newVal = Math.max(parseInt(input.min), Math.min(parseInt(input.max), parseInt(input.value) + delta));
    input.value  = newVal;
    updateBookingSummary();
}

function updateBookingSummary() {
    const adults   = parseInt(document.getElementById('adults').value) || 1;
    const children = parseInt(document.getElementById('children').value) || 0;

    const adultTotal  = adults * currentPrice;
    const childPrice  = currentPrice * childRate;
    const childTotal  = children * childPrice;
    const subtotal    = adultTotal + childTotal;
    const taxableCount = adults + children; // infants exempt from travel tax
    const tax         = taxableCount * TRAVEL_TAX;
    const grand       = subtotal + tax;

    document.getElementById('sumAdultLabel').textContent  = `${adults} Adult${adults>1?'s':''}`;
    document.getElementById('sumAdultTotal').textContent  = `₱${fmt(adultTotal)}`;
    document.getElementById('sumChildLabel').textContent  = `${children} Child${children>1?'ren':''}`;
    document.getElementById('sumChildTotal').textContent  = `₱${fmt(childTotal)}`;
    document.getElementById('sumSubtotal').textContent    = `₱${fmt(subtotal)}`;
    document.getElementById('sumTaxLabel').textContent    = `Travel Tax (${taxableCount} pax × ₱${fmt(TRAVEL_TAX)})`;
    document.getElementById('sumTax').textContent         = `₱${fmt(tax)}`;
    document.getElementById('sumTotal').textContent       = `₱${fmt(grand)}`;
    document.getElementById('sumChildRow').style.display  = children > 0 ? 'flex' : 'none';
}

function updateScheduleDate(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.date) {
        document.getElementById('tourDate').value = opt.dataset.date;
    }
    // Update price from the selected departure date (stored as data-price on the option)
    const datePrice = parseFloat(opt.dataset.price);
    if (!isNaN(datePrice) && datePrice > 0) {
        currentPrice = datePrice;
    }
    updateBookingSummary();
}

// On page load: if a departure date is pre-selected, apply its price
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.querySelector('select[name="departure_date"]');
    if (sel && sel.value) {
        updateScheduleDate(sel);
    } else {
        updateBookingSummary();
    }
});
</script>
@endpush
