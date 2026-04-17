@extends('layouts.app')
@section('title', 'Subscribe — ' . $tour->title)

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Subscribe to Plan</h1>
        <p>{{ $tour->title }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="booking-layout">
            <!-- Booking Form -->
            <div class="booking-form-main">
                @error('error')
                <div class="alert alert-danger" style="margin-bottom:1.25rem">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
                @enderror

                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                    <input type="hidden" name="schedule_id" id="hiddenScheduleId" value="{{ request('schedule_id', old('schedule_id', '')) }}">

                    <!-- Step 1: Tour Details -->
                    <div class="booking-step">
                        <div class="step-header">
                            <span class="step-num">1</span>
                            <h3>Plan Details</h3>
                        </div>

                        @php
                            $preDate       = request('departure_date', old('departure_date', ''));
                            $preScheduleId = request('schedule_id', old('schedule_id', ''));
                            $useSchedules  = $tour->schedules->isNotEmpty();
                            $departureDates = collect($tour->departure_dates ?? [])->filter(fn($d) => ($d['isAvailable'] ?? true));
                            $hasOptions = $useSchedules || $departureDates->count() > 0;
                        @endphp
                        @if($hasOptions)
                            <div class="form-group">
                                <label>Scheduled Departure</label>
                                <select name="departure_date" id="departureDateSelect" class="form-control" onchange="updateScheduleDate(this)">
                                    <option value="">Choose a departure date...</option>
                                    @if($useSchedules)
                                        @foreach($tour->schedules as $sched)
                                            @php
                                                $remaining = $sched->available_seats - $sched->booked_seats;
                                                $isFull    = $remaining <= 0 || $sched->status === 'sold_out';
                                                $dateKey   = $sched->departure_date->format('Y-m-d');
                                                $isSelected = ($preScheduleId == $sched->id) || ($preDate === $dateKey && !$preScheduleId);
                                            @endphp
                                            <option value="{{ $dateKey }}"
                                                data-date="{{ $dateKey }}"
                                                data-schedule-id="{{ $sched->id }}"
                                                data-price="{{ $sched->price_override ?? '' }}"
                                                data-slots="{{ $remaining }}"
                                                {{ $isFull ? 'disabled' : '' }}
                                                {{ $isSelected && !$isFull ? 'selected' : '' }}>
                                                {{ $sched->departure_date->format('M d, Y') }}
                                                @if($sched->return_date)
                                                    — {{ $sched->return_date->format('M d, Y') }}
                                                @endif
                                                @if($sched->price_override > 0)
                                                    — ₱{{ number_format($sched->price_override, 2) }}/person
                                                @endif
                                                @if($isFull)
                                                    — SOLD OUT
                                                @elseif($remaining <= 5)
                                                    — {{ $remaining }} slot{{ $remaining == 1 ? '' : 's' }} left
                                                @else
                                                    — {{ $remaining }} slots open
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($departureDates as $dateEntry)
                                            @php
                                                $dateKey   = $dateEntry['start'] ?? '';
                                                $maxCap    = $dateEntry['maxCapacity'] ?? null;
                                                $booked    = (int) ($bookedByDate[$dateKey] ?? 0);
                                                $slotsLeft = $maxCap ? max(0, (int) $maxCap - $booked) : null;
                                                $soldOut   = $maxCap && $slotsLeft === 0;
                                                $isSelected = ($preDate === $dateKey);
                                            @endphp
                                            <option value="{{ $dateKey }}"
                                                data-date="{{ $dateKey }}"
                                                data-price="{{ $dateEntry['price'] ?? '' }}"
                                                data-slots="{{ $slotsLeft ?? '' }}"
                                                {{ $soldOut ? 'disabled' : '' }}
                                                {{ $isSelected && !$soldOut ? 'selected' : '' }}>
                                                {{ $dateKey ? \Carbon\Carbon::parse($dateKey)->format('M d, Y') : '' }}
                                                @if(!empty($dateEntry['end']))
                                                    — {{ \Carbon\Carbon::parse($dateEntry['end'])->format('M d, Y') }}
                                                @endif
                                                @if(!empty($dateEntry['price']))
                                                    — ₱{{ number_format($dateEntry['price'], 2) }}/person
                                                @endif
                                                @if($soldOut)
                                                    — SOLD OUT
                                                @elseif($slotsLeft !== null)
                                                    — {{ $slotsLeft }} slot{{ $slotsLeft === 1 ? '' : 's' }} left
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif

                        {{-- Hidden tour_date — populated from the departure dropdown --}}
                        <input type="hidden" name="tour_date" id="tourDate"
                            value="{{ old('tour_date', request('departure_date', '')) }}">

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
                                @include('components.phone-input', [
                                    'name'     => 'contact_phone',
                                    'value'    => old('contact_phone', auth()->user()->phone ?? ''),
                                    'required' => true,
                                    'error'    => $errors->first('contact_phone'),
                                ])
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

                    <!-- Step 3: Payment Method -->
                    <div class="booking-step">
                        <div class="step-header">
                            <span class="step-num">3</span>
                            <h3>Payment Method</h3>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:.75rem">

                            {{-- Option A: Online via Xendit --}}
                            <label style="display:flex;align-items:flex-start;gap:.875rem;padding:1rem 1.125rem;border:2px solid #1e3a5f;border-radius:.75rem;cursor:pointer;transition:border-color .15s" id="pmXenditLabel">
                                <input type="radio" name="payment_method" value="xendit" checked
                                    style="margin-top:.2rem;accent-color:#1e3a5f" onchange="onPaymentMethodChange()">
                                <div>
                                    <strong><i class="fas fa-credit-card" style="color:#1e3a5f"></i> Online Payment</strong>
                                    <div class="text-muted" style="font-size:.85rem;margin-top:.2rem">
                                        Credit/debit card, GCash, GrabPay, Maya — full payment now via Xendit.
                                    </div>
                                </div>
                            </label>

                            {{-- GCash / Maya daily-limit advisory (shown when total > ₱100k) --}}
                            <div id="gcashLimitWarning" style="display:none;padding:.75rem 1rem;background:#fefce8;border:1px solid #fde047;border-radius:.625rem;font-size:.85rem;color:#854d0e;margin-top:-.25rem">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>GCash &amp; Maya daily outgoing limit: ₱100,000.</strong>
                                Your total (<strong id="gcashWarnAmt"></strong>) exceeds this limit.
                                For payments over ₱100,000 via e-wallet, you may need to send across multiple days.
                                Consider <strong>Payment Terms&nbsp;/&nbsp;Installment</strong> or pay via <strong>Credit/Debit Card or Bank Transfer</strong> instead.
                            </div>

                            {{-- Option B: Full Cash at Office --}}
                            <label style="display:flex;align-items:flex-start;gap:.875rem;padding:1rem 1.125rem;border:2px solid #d1d5db;border-radius:.75rem;cursor:pointer;transition:border-color .15s" id="pmCashFullLabel">
                                <input type="radio" name="payment_method" value="cash"
                                    style="margin-top:.2rem;accent-color:#16a34a" onchange="onPaymentMethodChange()">
                                <div>
                                    <strong><i class="fas fa-money-bill-wave" style="color:#16a34a"></i> Full Cash — Office / Bank Transfer</strong>
                                    <div class="text-muted" style="font-size:.85rem;margin-top:.2rem">
                                        Pay the full amount in person or via bank transfer. Our team will contact you with details.
                                    </div>
                                </div>
                            </label>

                            {{-- Option C: Installment --}}
                            <label style="display:flex;align-items:flex-start;gap:.875rem;padding:1rem 1.125rem;border:2px solid #d1d5db;border-radius:.75rem;cursor:pointer;transition:border-color .15s" id="pmInstallmentLabel">
                                <input type="radio" name="payment_method" value="installment"
                                    style="margin-top:.2rem;accent-color:#7c3aed" onchange="onPaymentMethodChange()">
                                <div>
                                    <strong><i class="fas fa-calendar-alt" style="color:#7c3aed"></i> Payment Terms / Installment</strong>
                                    <div class="text-muted" style="font-size:.85rem;margin-top:.2rem">
                                        Pay in monthly installments — up to 15 months.
                                        @if($tour->fixed_downpayment_amount)
                                            Down payment: ₱{{ number_format($tour->fixed_downpayment_amount, 2) }}.
                                        @endif
                                    </div>
                                </div>
                            </label>

                        </div>

                        {{-- Installment sub-options (shown when installment selected) --}}
                        <div id="installmentOptions" style="display:none;margin-top:.75rem;padding:1.125rem;background:#faf5ff;border:1px solid #d8b4fe;border-radius:.75rem">

                            <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
                                <div class="form-group" style="flex:1;min-width:160px;margin-bottom:0">
                                    <label style="font-weight:600">Number of Monthly Terms</label>
                                    <select name="installment_months" id="installmentMonthsSel" class="form-control"
                                        onchange="renderInstallmentSchedule()">
                                        @for($i = 1; $i <= 15; $i++)
                                            <option value="{{ $i }}"
                                                {{ $i == ($tour->installment_months ?? 10) ? 'selected' : '' }}>
                                                {{ $i }} month{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div id="installmentSchedulePreview" style="font-size:.875rem"></div>
                        </div>
                    </div>

                    {{-- Hidden: installment_months is submitted only for installment method --}}
                    <input type="hidden" name="installment_months" id="installmentMonthsHidden" value="" disabled>

                    <!-- Terms Agreement -->
                    <div class="booking-step">
                        <label class="form-check">
                            <input type="checkbox" required>
                            I have read and agree to the <a href="#" target="_blank">Terms & Conditions</a>
                            and <a href="#" target="_blank">Cancellation Policy</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="proceedBtn">
                        <i class="fas fa-arrow-right"></i> <span id="proceedBtnText">Proceed to Payment</span>
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <aside class="booking-summary">
                <div class="summary-card">
                    <div class="summary-tour-img">
                        @if($tour->main_image)
                            <img src="{{ cdn_url($tour->main_image) }}" alt="{{ $tour->title }}">
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
                                <span id="sumTaxLabel">Service Tax</span>
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
    document.getElementById('sumTaxLabel').textContent    = `Service Tax (${taxableCount} pax × ₱${fmt(TRAVEL_TAX)})`;
    document.getElementById('sumTax').textContent         = `₱${fmt(tax)}`;
    document.getElementById('sumTotal').textContent       = `₱${fmt(grand)}`;
    document.getElementById('sumChildRow').style.display  = children > 0 ? 'flex' : 'none';
    updateGcashWarning();
}

function updateScheduleDate(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.date) {
        document.getElementById('tourDate').value = opt.dataset.date;
    }
    // Sync hidden schedule_id field
    const schedInput = document.getElementById('hiddenScheduleId');
    if (schedInput) schedInput.value = opt.dataset.scheduleId || '';
    // Update price from the selected departure date
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
    } else if (sel && sel.options.length > 1) {
        // Auto-select first non-disabled option if nothing pre-selected
        for (let i = 1; i < sel.options.length; i++) {
            if (!sel.options[i].disabled) {
                sel.selectedIndex = i;
                updateScheduleDate(sel);
                break;
            }
        }
    } else {
        updateBookingSummary();
    }
    onPaymentMethodChange();
});

// ── Payment Method ──────────────────────────────────────────────────────────
const FIXED_MONTHLY   = {{ $tour->monthly_installment_amount ?? 0 }};
const DOWNPAYMENT     = {{ $tour->fixed_downpayment_amount ?? 0 }};
const GCASH_MAYA_LIMIT = 100000;

function updateGcashWarning() {
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'xendit';
    const total  = getGrandTotal();
    const warn   = document.getElementById('gcashLimitWarning');
    const amtEl  = document.getElementById('gcashWarnAmt');
    if (!warn) return;
    if (method === 'xendit' && total > GCASH_MAYA_LIMIT) {
        if (amtEl) amtEl.textContent = '₱' + fmt(total);
        warn.style.display = '';
    } else {
        warn.style.display = 'none';
    }
}

function onPaymentMethodChange() {
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'xendit';

    const xenditLabel      = document.getElementById('pmXenditLabel');
    const cashFullLabel    = document.getElementById('pmCashFullLabel');
    const installmentLabel = document.getElementById('pmInstallmentLabel');
    const installmentBox   = document.getElementById('installmentOptions');
    const hiddenMonths     = document.getElementById('installmentMonthsHidden');
    const btnText          = document.getElementById('proceedBtnText');

    // Reset all borders
    [xenditLabel, cashFullLabel, installmentLabel].forEach(el => {
        if (el) el.style.borderColor = '#d1d5db';
    });

    if (method === 'xendit') {
        if (xenditLabel) xenditLabel.style.borderColor = '#1e3a5f';
        if (installmentBox) installmentBox.style.display = 'none';
        if (hiddenMonths) hiddenMonths.disabled = true;
        if (btnText) btnText.textContent = 'Proceed to Payment';
    } else if (method === 'cash') {
        if (cashFullLabel) cashFullLabel.style.borderColor = '#16a34a';
        if (installmentBox) installmentBox.style.display = 'none';
        if (hiddenMonths) hiddenMonths.disabled = true;
        if (btnText) btnText.textContent = 'Confirm Booking (Cash)';
    } else if (method === 'installment') {
        if (installmentLabel) installmentLabel.style.borderColor = '#7c3aed';
        if (installmentBox) installmentBox.style.display = '';
        if (hiddenMonths) hiddenMonths.disabled = false;
        if (btnText) btnText.textContent = 'Confirm Booking (Installment)';
        renderInstallmentSchedule();
    }
    updateGcashWarning();
}

function getGrandTotal() {
    const adults   = parseInt(document.getElementById('adults').value) || 1;
    const children = parseInt(document.getElementById('children').value) || 0;
    const childTotal = children * currentPrice * childRate;
    const subtotal   = adults * currentPrice + childTotal;
    const tax        = (adults + children) * TRAVEL_TAX;
    return subtotal + tax;
}

function renderInstallmentSchedule() {
    const preview  = document.getElementById('installmentSchedulePreview');
    const sel      = document.getElementById('installmentMonthsSel');
    const hiddenM  = document.getElementById('installmentMonthsHidden');
    if (!preview || !sel) return;

    const months     = parseInt(sel.value);
    if (hiddenM) hiddenM.value = months;

    const grandTotal = getGrandTotal();
    // Use fixed monthly amount from tour if set, otherwise divide total by months
    const monthly    = FIXED_MONTHLY > 0 ? FIXED_MONTHLY : Math.ceil(grandTotal / months);
    const today      = new Date();

    let rows = '';
    if (DOWNPAYMENT > 0) {
        const dp = new Date(today);
        dp.setDate(dp.getDate() + 7);
        rows += `<tr style="background:#faf5ff"><td><strong>Down Payment</strong></td><td>${fmtDate(dp)}</td><td><strong>₱${fmt(DOWNPAYMENT)}</strong></td><td><span style="background:#fef9c3;color:#854d0e;padding:.1rem .45rem;border-radius:.25rem;font-size:.78rem">Pending</span></td></tr>`;
    }
    for (let i = 1; i <= months; i++) {
        const d = new Date(today);
        d.setMonth(d.getMonth() + i);
        rows += `<tr><td>Month ${i}</td><td>${fmtDate(d)}</td><td>₱${fmt(monthly)}</td><td><span style="background:#e5e7eb;color:#374151;padding:.1rem .45rem;border-radius:.25rem;font-size:.78rem">Pending</span></td></tr>`;
    }

    const scheduleTotal = DOWNPAYMENT + (monthly * months);
    preview.innerHTML = `
        <p style="margin-bottom:.5rem;font-weight:600;color:#7c3aed">
            ₱${fmt(monthly)}/month × ${months} months
            ${DOWNPAYMENT > 0 ? ' + down payment' : ''}
        </p>
        <table style="width:100%;border-collapse:collapse;font-size:.83rem">
            <thead><tr style="border-bottom:1px solid #d8b4fe;color:#6d28d9">
                <th style="text-align:left;padding:.3rem .5rem">Term</th>
                <th style="text-align:left;padding:.3rem .5rem">Due Date</th>
                <th style="text-align:left;padding:.3rem .5rem">Amount</th>
                <th style="text-align:left;padding:.3rem .5rem">Status</th>
            </tr></thead>
            <tbody>${rows}</tbody>
            <tfoot><tr style="border-top:1px solid #d8b4fe;font-weight:700;color:#6d28d9">
                <td colspan="2" style="padding:.4rem .5rem">Total via installment</td>
                <td style="padding:.4rem .5rem">₱${fmt(scheduleTotal)}</td>
                <td></td>
            </tr></tfoot>
        </table>
        <p style="margin-top:.5rem;font-size:.8rem;color:#6b7280">
            <i class="fas fa-info-circle"></i>
            Our team will send payment reminders and bank details before each due date.
        </p>`;
}

function fmtDate(d) {
    return d.toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
}
</script>
@endpush
