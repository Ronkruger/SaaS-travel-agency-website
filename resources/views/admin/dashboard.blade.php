@extends('layouts.admin')
@section('title', 'Dashboard')

@section('breadcrumb')
    <span>Dashboard</span>
@endsection

@section('content')
<div class="page-title">
    <h2>Dashboard</h2>
    <p>Welcome back, {{ auth()->user()->name }}</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card--blue">
        <div class="stat-icon"><i class="fas fa-map-marked-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['active_tours'] }}</span>
            <span class="stat-label">Active Tours</span>
        </div>
    </div>
    <div class="stat-card stat-card--green">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_bookings'] }}</span>
            <span class="stat-label">Total Bookings</span>
        </div>
    </div>
    <div class="stat-card stat-card--orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['pending_bookings'] }}</span>
            <span class="stat-label">Pending Bookings</span>
        </div>
    </div>
    <div class="stat-card stat-card--purple">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <span class="stat-value">₱{{ number_format($stats['total_revenue'], 0) }}</span>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_users'] }}</span>
            <span class="stat-label">Registered Users</span>
        </div>
    </div>
    <div class="stat-card stat-card--yellow">
        <div class="stat-icon"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['pending_reviews'] }}</span>
            <span class="stat-label">Pending Reviews</span>
        </div>
    </div>
</div>

<div class="dashboard-grid mt-4">
    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-calendar-check"></i> Recent Bookings</h4>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $booking)
                        <tr>
                            <td><code>{{ $booking->booking_number }}</code></td>
                            <td>{{ $booking->user->name }}</td>
                            <td>{{ Str::limit($booking->tour->title, 30) }}</td>
                            <td>{{ $booking->tour_date->format('M d, Y') }}</td>
                            <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                            <td><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-xs btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Tours & Revenue -->
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-trophy"></i> Top Tours</h4>
            </div>
            <div class="card-body p-0">
                @foreach($topTours as $tour)
                    <div class="top-item">
                        <img src="{{ asset('storage/' . $tour->featured_image) }}" alt="{{ $tour->title }}" class="top-item-img">
                        <div class="top-item-info">
                            <strong>{{ Str::limit($tour->title, 35) }}</strong>
                            <span>{{ $tour->total_bookings }} bookings</span>
                        </div>
                        <div class="top-item-rating">
                            <i class="fas fa-star text-yellow"></i>
                            {{ number_format($tour->average_rating, 1) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($stats['pending_reviews'] > 0)
            <div class="card alert-card">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x text-yellow mb-2"></i>
                    <h5>{{ $stats['pending_reviews'] }} Pending Reviews</h5>
                    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning">
                        Review Them
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
