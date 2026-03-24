@extends('layouts.app')
@section('title', 'Booking Confirmed!')

@section('content')
<section class="section">
    <div class="container">
        <div class="confirmation-card">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Booking Confirmed!</h1>
            <p class="confirmation-subtitle">
                Thank you, <strong>{{ $booking->contact_name }}</strong>!
                Your booking has been confirmed and a confirmation email will be sent to
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
    </div>
</section>
@endsection
