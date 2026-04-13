@extends('layouts.admin')
@section('title', 'User Detail — Admin')

@section('skeleton')
    @include('admin.partials.skeleton-detail')
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h1 class="page-title">User Detail</h1>
        <small class="text-muted">Viewing profile of {{ $user->name }}</small>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-edit"></i> Edit
        </a>
        @if($user->id !== auth()->id())
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
              onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
        </form>
        @endif
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="booking-admin-layout" style="grid-template-columns: 1fr 300px;">

    {{-- Left: Bookings & Reviews --}}
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h3>Booking History</h3>
                <span class="badge badge-primary">{{ $user->bookings->count() }} bookings</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Tour</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->bookings as $booking)
                        <tr>
                            <td><strong>{{ $booking->booking_number }}</strong></td>
                            <td>{{ $booking->tour->title ?? 'N/A' }}</td>
                            <td>{{ $booking->created_at->format('M d, Y') }}</td>
                            <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                            <td><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-ghost btn-xs">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding:2rem;">
                                No bookings found for this user
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Reviews</h3>
                <span class="badge badge-primary">{{ $user->reviews->count() }} reviews</span>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($user->reviews as $review)
                <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--gray-300);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
                        <div>
                            <div style="display:flex;gap:.25rem;color:#f59e0b;margin-bottom:.375rem;font-size:.875rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </div>
                            <strong>{{ $review->title }}</strong>
                            <p class="text-muted" style="font-size:.875rem;margin-top:.25rem;">
                                {{ $review->tour->title ?? 'N/A' }}
                            </p>
                            <p style="margin-top:.5rem;font-size:.9375rem;">{{ $review->body }}</p>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            @if($review->is_approved)
                                <span class="status-badge status-confirmed">Approved</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                            <p class="text-muted" style="font-size:.8125rem;margin-top:.375rem;">
                                {{ $review->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted" style="padding:2rem;">
                    No reviews from this user yet
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right: User Info --}}
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="text-align:center;">
                <div style="width:5rem;height:5rem;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="fa-solid fa-user" style="font-size:2rem;color:#fff;"></i>
                </div>
                <h3 style="margin-bottom:.25rem;">{{ $user->name }}</h3>
                <p class="text-muted" style="font-size:.875rem;">{{ $user->email }}</p>
                <span class="status-badge {{ $user->isAdmin() ? 'status-confirmed' : 'status-completed' }}" style="margin-top:.5rem;">
                    {{ $user->isAdmin() ? 'Admin' : 'Customer' }}
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Contact Info</h4></div>
            <div class="card-body">
                @if($user->phone)
                <p style="margin-bottom:.75rem;"><strong><i class="fa-solid fa-phone text-primary"></i></strong> {{ $user->phone }}</p>
                @endif
                @if($user->city || $user->country)
                <p style="margin-bottom:.75rem;"><strong><i class="fa-solid fa-location-dot text-primary"></i></strong>
                    {{ implode(', ', array_filter([$user->city, $user->country])) }}
                </p>
                @endif
                @if($user->address)
                <p style="margin-bottom:.75rem;"><strong><i class="fa-solid fa-map text-primary"></i></strong> {{ $user->address }}</p>
                @endif
                <hr style="margin:.875rem 0;border-color:var(--gray-300);">
                <p style="font-size:.875rem;color:var(--gray-500);">
                    <i class="fa-solid fa-calendar-plus"></i>
                    Joined {{ $user->created_at->format('M d, Y') }}
                </p>
                <p style="font-size:.875rem;color:var(--gray-500);margin-top:.375rem;">
                    <i class="fa-solid fa-clock"></i>
                    Last updated {{ $user->updated_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
