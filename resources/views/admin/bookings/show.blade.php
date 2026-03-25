@extends('layouts.admin')
@section('title', 'Booking ' . $booking->booking_number)

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Bookings</a> / {{ $booking->booking_number }}
@endsection

@section('content')
<div class="page-title-row">
    <h2>Booking #{{ $booking->booking_number }}</h2>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="booking-admin-layout">
    <div>
        <!-- Status -->
        <div class="card mb-4">
            <div class="card-header"><h4>Update Status</h4></div>
            <div class="card-body">
                <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="inline-form">
                    @csrf @method('PATCH')
                    <select name="status" class="form-control">
                        @foreach(['pending','confirmed','cancelled','completed','refunded'] as $s)
                            <option value="{{ $s }}" {{ $booking->status === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>

        <!-- Booking Info -->
        <div class="card mb-4">
            <div class="card-header"><h4>Booking Information</h4></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span>Booking #</span><strong>{{ $booking->booking_number }}</strong></div>
                    <div class="detail-item"><span>Tour Date</span><strong>{{ $booking->tour_date->format('M d, Y') }}</strong></div>
                    <div class="detail-item"><span>Adults</span><strong>{{ $booking->adults }}</strong></div>
                    <div class="detail-item"><span>Children</span><strong>{{ $booking->children }}</strong></div>
                    <div class="detail-item"><span>Total Guests</span><strong>{{ $booking->total_guests }}</strong></div>
                    <div class="detail-item"><span>Status</span>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div class="detail-item"><span>Payment</span>
                        <span class="payment-badge payment-{{ $booking->payment_status }}">{{ ucfirst($booking->payment_status) }}</span>
                    </div>
                    <div class="detail-item"><span>Booked On</span><strong>{{ $booking->created_at->format('M d, Y h:i A') }}</strong></div>
                </div>
                @if($booking->special_requests)
                    <div class="mt-3">
                        <strong>Special Requests:</strong>
                        <p>{{ $booking->special_requests }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card mb-4">
            <div class="card-header"><h4>Contact Information</h4></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span>Name</span><strong>{{ $booking->contact_name }}</strong></div>
                    <div class="detail-item"><span>Email</span><strong>{{ $booking->contact_email }}</strong></div>
                    <div class="detail-item"><span>Phone</span><strong>{{ $booking->contact_phone }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- Tour Info -->
        <div class="card mb-4">
            <div class="card-header"><h4>Tour</h4></div>
            <div class="card-body">
                <img src="{{ cdn_url($booking->tour->main_image) }}"
                     alt="{{ $booking->tour->title }}" class="img-fluid rounded mb-3">
                <h5>{{ $booking->tour->title }}</h5>
                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }}D / {{ $booking->tour->duration_nights }}N</p>
                <a href="{{ route('admin.tours.edit', $booking->tour) }}" class="btn btn-sm btn-outline">
                    Edit Tour
                </a>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="card">
            <div class="card-header"><h4>Payment Summary</h4></div>
            <div class="card-body">
                <div class="price-breakdown">
                    <div class="price-row"><span>Adults ({{ $booking->adults }})</span><span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span></div>
                    @if($booking->children > 0)
                        <div class="price-row"><span>Children ({{ $booking->children }})</span><span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span></div>
                    @endif
                    <div class="price-row"><span>Subtotal</span><span>₱{{ number_format($booking->subtotal, 2) }}</span></div>
                    <div class="price-row"><span>Tax</span><span>₱{{ number_format($booking->tax_amount, 2) }}</span></div>
                    <div class="price-row price-row--total"><strong>Total</strong><strong>₱{{ number_format($booking->total_amount, 2) }}</strong></div>
                </div>

                @if($booking->payment)
                    <div class="payment-details mt-3 pt-3 border-top">
                        <div class="detail-item"><span>Transaction ID</span><strong>{{ $booking->payment->transaction_id }}</strong></div>
                        <div class="detail-item"><span>Method</span><strong>{{ ucfirst($booking->payment->method) }}</strong></div>
                        <div class="detail-item"><span>Gateway ID</span><strong>{{ $booking->payment->gateway_transaction_id ?? '—' }}</strong></div>
                        @if($booking->payment->paid_at)
                            <div class="detail-item"><span>Paid At</span><strong>{{ $booking->payment->paid_at->format('M d, Y H:i') }}</strong></div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
