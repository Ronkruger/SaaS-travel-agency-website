@extends('layouts.app')
@section('title', 'Checkout — ' . $booking->booking_number)

@section('content')
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-credit-card"></i> Secure Checkout</h1>
        <p>Complete your payment for {{ $booking->tour->title }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Progress Steps -->
        <div class="checkout-steps">
            <div class="checkout-step completed"><i class="fas fa-check"></i> Booking Details</div>
            <div class="checkout-step-divider"><i class="fas fa-chevron-right"></i></div>
            <div class="checkout-step active"><i class="fas fa-credit-card"></i> Payment</div>
            <div class="checkout-step-divider"><i class="fas fa-chevron-right"></i></div>
            <div class="checkout-step"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <div class="checkout-layout">
            <!-- Payment Form -->
            <div class="checkout-form">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-credit-card"></i> Secure Payment via Xendit</h4>
                    </div>
                    <div class="card-body text-center py-4">
                        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:1rem; margin-bottom:1.5rem;">
                            <span class="badge-pm"><i class="fas fa-credit-card"></i> Credit Card</span>
                            <span class="badge-pm"><i class="fas fa-university"></i> BPI / BDO</span>
                            <span class="badge-pm"><img src="https://www.gcash.com/favicon.ico" style="width:14px;height:14px"> GCash</span>
                            <span class="badge-pm"><i class="fas fa-wallet"></i> GrabPay</span>
                            <span class="badge-pm"><i class="fas fa-mobile-alt"></i> Maya</span>
                        </div>
                        <p class="text-muted mb-4">
                            You will be redirected to Xendit's secure payment page to complete your booking.<br>
                            Choose any payment method you prefer there.
                        </p>
                        <form action="{{ route('checkout.process', $booking) }}" method="POST">
                            @csrf
                            @error('error')
                                <div class="alert alert-danger mb-3">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="secure-note mb-3">
                                <i class="fas fa-shield-alt text-green"></i>
                                Encrypted &amp; secured by Xendit
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="payBtn"
                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Redirecting to Xendit...'; this.form.submit();">
                                <i class="fas fa-lock"></i> Pay ₱{{ number_format($booking->total_amount, 2) }} via Xendit
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <aside class="checkout-summary">
                <div class="card">
                    <div class="card-header"><h4><i class="fas fa-receipt"></i> Order Summary</h4></div>
                    <div class="card-body">
                        <h5>{{ $booking->tour->title }}</h5>
                        <p class="text-muted">
                            <i class="fas fa-calendar"></i> {{ $booking->tour_date->format('M d, Y') }}
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-users"></i> {{ $booking->total_guests }} guest(s)
                        </p>

                        <div class="price-breakdown mt-3">
                            <div class="price-row">
                                <span>{{ $booking->adults }} Adult(s)</span>
                                <span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span>
                            </div>
                            @if($booking->children > 0)
                                <div class="price-row">
                                    <span>{{ $booking->children }} Child(ren)</span>
                                    <span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Travel Tax</span>
                                <span>₱{{ number_format($booking->tax_amount, 2) }}</span>
                            </div>
                            <div class="price-row price-row--total">
                                <strong>Total Due</strong>
                                <strong class="text-primary">₱{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                        </div>

                        <div class="checkout-guarantees mt-3">
                            <div><i class="fas fa-check text-green"></i> Free cancellation (48h)</div>
                            <div><i class="fas fa-check text-green"></i> Instant confirmation</div>
                            <div><i class="fas fa-check text-green"></i> Secure payment</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Payment method switching
document.querySelectorAll('input[name=payment_method]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method-option').forEach(el => el.classList.remove('active'));
        this.closest('.payment-method-option').classList.add('active');

        const method = this.value;
        document.getElementById('cardDetails').style.display    = ['credit_card', 'debit_card'].includes(method) ? 'block' : 'none';
        document.getElementById('paypalDetails').style.display  = method === 'paypal' ? 'block' : 'none';
        document.getElementById('bankDetails').style.display    = method === 'bank_transfer' ? 'block' : 'none';
    });
});

// Card number formatting
document.getElementById('cardNumber')?.addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
});

// Expiry formatting
document.getElementById('cardExpiry')?.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.slice(0, 2) + ' / ' + v.slice(2);
    this.value = v.slice(0, 7);
});

// Prevent form double submit
document.getElementById('paymentForm').addEventListener('submit', function() {
    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});
</script>
@endpush
