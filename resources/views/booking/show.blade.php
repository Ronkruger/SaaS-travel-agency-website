@extends('layouts.app')
@section('title', 'Booking #' . $booking->booking_number)

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Booking Details</h1>
        <p>{{ $booking->booking_number }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="booking-detail-layout">
            <div class="booking-detail-main">
                <!-- Booking Status -->
                <div class="card mb-4">
                    <div class="card-body status-card">
                        <div class="status-info">
                            <div class="status-dot status-dot--{{ $booking->status }}"></div>
                            <div>
                                <span class="status-badge status-{{ $booking->status }} status-lg">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <p class="mt-1 text-muted">Booked on {{ $booking->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="payment-info">
                            <span class="payment-badge payment-{{ $booking->payment_status }}">
                                Payment: {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tour Info -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-map-marked-alt"></i> Tour Information</h4></div>
                    <div class="card-body">
                        <div class="tour-detail-row">
                            <img src="{{ cdn_url($booking->tour->main_image) }}"
                                 alt="{{ $booking->tour->title }}" class="tour-thumb-sm">
                            <div>
                                <h4><a href="{{ route('tours.show', $booking->tour->slug) }}">
                                    {{ $booking->tour->title }}
                                </a></h4>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }} Days / {{ $booking->tour->duration_nights }} Nights</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-clipboard-list"></i> Booking Details</h4></div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Booking Number</span>
                                <strong>{{ $booking->booking_number }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Tour Date</span>
                                <strong>{{ $booking->tour_date->format('M d, Y') }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Adults</span>
                                <strong>{{ $booking->adults }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Children</span>
                                <strong>{{ $booking->children }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Infants</span>
                                <strong>{{ $booking->infants }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Total Guests</span>
                                <strong>{{ $booking->total_guests }}</strong>
                            </div>
                        </div>

                        @if($booking->special_requests)
                            <div class="mt-3">
                                <strong>Special Requests:</strong>
                                <p class="mt-1 text-muted">{{ $booking->special_requests }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-user"></i> Contact Information</h4></div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Name</span>
                                <strong>{{ $booking->contact_name }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Email</span>
                                <strong>{{ $booking->contact_email }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Phone</span>
                                <strong>{{ $booking->contact_phone }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Summary -->
            <aside class="booking-detail-sidebar">
                <div class="card">
                    <div class="card-header"><h4><i class="fas fa-receipt"></i> Price Summary</h4></div>
                    <div class="card-body">
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>{{ $booking->adults }} Adult(s) × ₱{{ number_format($booking->price_per_adult, 2) }}</span>
                                <span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span>
                            </div>
                            @if($booking->children > 0)
                                <div class="price-row">
                                    <span>{{ $booking->children }} Child(ren) × ₱{{ number_format($booking->price_per_child, 2) }}</span>
                                    <span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Subtotal</span>
                                <span>₱{{ number_format($booking->subtotal, 2) }}</span>
                            </div>
                            @if($booking->discount_amount > 0)
                                <div class="price-row text-green">
                                    <span>Discount</span>
                                    <span>−₱{{ number_format($booking->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Tax (10%)</span>
                                <span>₱{{ number_format($booking->tax_amount, 2) }}</span>
                            </div>
                            <div class="price-row price-row--total">
                                <strong>Total</strong>
                                <strong>₱{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                        </div>

                        @if($booking->status === 'unpaid')
                            <a href="{{ route('checkout.show', $booking) }}" class="btn btn-primary btn-block mt-3">
                                <i class="fas fa-credit-card"></i> Complete Payment
                            </a>
                        @endif

                        @if($booking->isConfirmed())
                            <a href="{{ route('booking.pdf.download', $booking) }}"
                               class="btn btn-outline-primary btn-block mt-2">
                                <i class="fas fa-file-pdf"></i> Download Confirmation PDF
                            </a>
                        @endif

                        @if($booking->isCancellable())
                            <form action="{{ route('booking.cancel', $booking) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block mt-2">
                                    <i class="fas fa-times-circle"></i> Cancel Booking
                                </button>
                            </form>
                        @endif

                        @if($booking->payment)
                            <div class="payment-details mt-3">
                                <strong>Payment Details</strong>
                                <p>Method: {{ ucfirst($booking->payment->method) }}</p>
                                <p>Transaction: {{ $booking->payment->transaction_id }}</p>
                                @if($booking->payment->paid_at)
                                    <p>Paid: {{ $booking->payment->paid_at->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($booking->status === 'completed' && !$booking->review)
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x text-yellow mb-2"></i>
                            <p>How was your tour?</p>
                            <a href="{{ route('tours.show', $booking->tour->slug) }}#tab-reviews"
                               class="btn btn-warning btn-block">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection
