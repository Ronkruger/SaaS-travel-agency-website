@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="page-header" style="background: var(--gradient-primary);">
    <div class="container">
        <h1>My Profile</h1>
        <p>Manage your account and view bookings</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="profile-grid">
            <!-- Profile Card -->
            <div class="profile-sidebar">
                <div class="profile-avatar-card">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>{{ $user->name }}</h3>
                    <p>{{ $user->email }}</p>
                    <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                </div>

                <nav class="profile-nav">
                    <a href="#personal-info" class="active"><i class="fas fa-user"></i> Personal Info</a>
                    <a href="#change-password"><i class="fas fa-lock"></i> Change Password</a>
                    <a href="{{ route('booking.index') }}"><i class="fas fa-calendar-check"></i> My Bookings</a>
                    <a href="{{ route('wishlist') }}"><i class="fas fa-heart"></i> Wishlist</a>
                </nav>
            </div>

            <!-- Profile Main -->
            <div class="profile-main">
                <!-- Personal Info -->
                <div class="card" id="personal-info">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                    </div>
                    <form action="{{ route('profile.update') }}" method="POST" class="card-body">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="{{ $user->email }}" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" value="{{ old('city', $user->city) }}"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" value="{{ old('country', $user->country) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card mt-4" id="change-password">
                    <div class="card-header">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                    </div>
                    <form action="{{ route('profile.password') }}" method="POST" class="card-body">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>

                <!-- Recent Bookings -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-check"></i> Recent Bookings</h3>
                        <a href="{{ route('booking.index') }}" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($bookings->take(5) as $booking)
                            <div class="booking-row">
                                <div class="booking-row-info">
                                    <strong>{{ $booking->booking_number }}</strong>
                                    <span>{{ $booking->tour->title }}</span>
                                    <span class="text-muted">{{ $booking->tour_date->format('M d, Y') }}</span>
                                </div>
                                <div class="booking-row-amount">
                                    ₱{{ number_format($booking->total_amount, 2) }}
                                </div>
                                <span class="status-badge status-{{ $booking->status }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <a href="{{ route('booking.show', $booking) }}" class="btn btn-sm btn-outline">
                                    View
                                </a>
                            </div>
                        @empty
                            <div class="empty-state p-4">
                                <i class="fas fa-calendar-times fa-2x text-muted"></i>
                                <p class="mt-2">No bookings yet. <a href="{{ route('tours.index') }}">Browse tours</a></p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
