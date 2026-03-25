@extends('layouts.app')
@section('title', 'My Bookings')

@section('content')
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
        <p>Track and manage all your tour reservations</p>
    </div>
</div>

<section class="section">
    <div class="container">
        @if($bookings->count() > 0)
            <div class="bookings-list">
                @foreach($bookings as $booking)
                    <div class="booking-card status-border-{{ $booking->status }}">
                        <div class="booking-card-img">
                            <img src="{{ cdn_url($booking->tour->main_image) }}"
                                 alt="{{ $booking->tour->title }}">
                        </div>
                        <div class="booking-card-body">
                            <div class="booking-card-header">
                                <div>
                                    <span class="booking-number">{{ $booking->booking_number }}</span>
                                    <h4>{{ $booking->tour->title }}</h4>
                                    <p><i class="fas fa-map-marker-alt"></i>
                                        {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                                </div>
                                <div class="booking-card-status">
                                    <span class="status-badge status-{{ $booking->status }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <span class="payment-badge payment-{{ $booking->payment_status }}">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="booking-card-details">
                                <span><i class="fas fa-calendar"></i> {{ $booking->tour_date->format('M d, Y') }}</span>
                                <span><i class="fas fa-users"></i> {{ $booking->total_guests }} guests</span>
                                <span><i class="fas fa-clock"></i>
                                    {{ $booking->tour->duration_days }}D / {{ $booking->tour->duration_nights }}N</span>
                                <span><i class="fas fa-dollar-sign"></i>
                                    <strong>₱{{ number_format($booking->total_amount, 2) }}</strong>
                                </span>
                            </div>

                            <div class="booking-card-actions">
                                <a href="{{ route('booking.show', $booking) }}" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                @if($booking->payment_status === 'unpaid')
                                    <a href="{{ route('checkout.show', $booking) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                @endif
                                @if($booking->isCancellable())
                                    <form action="{{ route('booking.cancel', $booking) }}" method="POST"
                                          onsubmit="return confirm('Cancel this booking?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                @endif
                                @if($booking->status === 'completed' && !$booking->review)
                                    <a href="{{ route('tours.show', $booking->tour->slug) }}#tab-reviews"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-star"></i> Write Review
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="empty-state large card">
                <i class="fas fa-suitcase-rolling fa-4x text-muted"></i>
                <h3 class="mt-3">No Bookings Yet</h3>
                <p class="text-muted">Start exploring tours and book your first adventure!</p>
                <a href="{{ route('tours.index') }}" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-search"></i> Browse Tours
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
