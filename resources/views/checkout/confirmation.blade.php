@extends('layouts.app')
@section('title', $booking->payment_status === 'paid' ? 'Booking Confirmed!' : 'Processing Payment…')

@section('content')
<section class="section">
    <div class="container">
        @if($booking->payment_status !== 'paid')
        {{-- Webhook hasn't fired yet — show processing state and auto-refresh --}}
        <div class="confirmation-card" style="text-align:center">
            <div class="confirmation-icon" style="color:#f59e0b">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h1 style="color:#92400e">Processing Your Payment…</h1>
            <p class="confirmation-subtitle">
                We've received your payment request and are confirming it with Xendit.<br>
                <strong>Do not close this page.</strong> It will update automatically in a few seconds.
            </p>
            <p style="font-size:.85rem;color:#6b7280;margin-top:.5rem">
                A confirmation email will be sent to <strong>{{ $booking->contact_email }}</strong> once confirmed.
            </p>
            <div style="margin-top:1.5rem">
                <a href="{{ route('booking.show', $booking) }}" class="btn btn-outline btn-lg">
                    <i class="fas fa-clipboard-list"></i> View Booking
                </a>
            </div>
        </div>
        @else
        <div class="confirmation-card">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Booking Confirmed!</h1>
            <p class="confirmation-subtitle">
                Thank you, <strong>{{ $booking->contact_name }}</strong>!
                Your booking has been confirmed and a confirmation email has been sent to
                <strong>{{ $booking->contact_email }}</strong>.
            </p>

            <div class="confirmation-details">
                <div class="detail-row">
                    <span>Booking Number</span>
                    <strong class="text-primary">{{ $booking->booking_number }}</strong>
                </div>
                <div class="detail-row">
                    <span>Tour</span>
                    <strong>{{ $booking->tour->title }}</strong>
                </div>
                <div class="detail-row">
                    <span>Destination</span>
                    <strong>{{ $booking->tour->destination }}, {{ $booking->tour->country }}</strong>
                </div>
                <div class="detail-row">
                    <span>Tour Date</span>
                    <strong>{{ $booking->tour_date->format('F d, Y') }}</strong>
                </div>
                <div class="detail-row">
                    <span>Guests</span>
                    <strong>{{ $booking->total_guests }} person(s)</strong>
                </div>
                <div class="detail-row">
                    <span>Amount Paid</span>
                    <strong class="text-green">₱{{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                @if($booking->payment)
                    <div class="detail-row">
                        <span>Transaction ID</span>
                        <strong>{{ $booking->payment->transaction_id }}</strong>
                    </div>
                @endif
            </div>

            <div class="confirmation-actions">
                <a href="{{ route('booking.show', $booking) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-clipboard-list"></i> View Booking Details
                </a>
                <a href="{{ route('tours.index') }}" class="btn btn-outline btn-lg">
                    <i class="fas fa-compass"></i> Explore More Tours
                </a>
            </div>

            <div class="confirmation-tips">
                <h4><i class="fas fa-info-circle"></i> What's Next?</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> You'll receive a confirmation email with all details.</li>
                    <li><i class="fas fa-phone"></i> Our team may contact you 48 hours before the tour for final details.</li>
                    <li><i class="fas fa-calendar"></i> Meet at {{ $booking->tour->meeting_point ?? 'the designated meeting point' }} on your tour date.</li>
                    <li><i class="fas fa-times-circle"></i> Free cancellation up to 48 hours before departure.</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
@if($booking->payment_status !== 'paid')
// Poll the lightweight status endpoint instead of reloading the whole page
(function() {
    var statusUrl = '{{ parse_url(route("checkout.payment-status", $booking), PHP_URL_PATH) }}';
    var maxAttempts = 20;
    var attempt = 0;

    function poll() {
        if (attempt >= maxAttempts) return;
        attempt++;
        fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.payment_status === 'partial' || data.payment_status === 'paid') {
                    window.location.reload();
                } else {
                    setTimeout(poll, 3000);
                }
            })
            .catch(function() { setTimeout(poll, 5000); });
    }

    setTimeout(poll, 3000);
})();
@endif
</script>
@endpush
