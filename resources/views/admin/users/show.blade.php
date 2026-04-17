@extends('layouts.admin')
@section('title', 'User Detail — Admin')

@section('skeleton')
    @include('admin.partials.skeleton-detail')
@endsection

@push('styles')
<style>
.tf-balance-card { background:linear-gradient(135deg,#7c3aed,#4f46e5); border-radius:.875rem; padding:1.5rem; color:#fff; margin-bottom:1rem; }
.tf-balance-card .tf-label { font-size:.8rem; opacity:.8; margin-bottom:.25rem; }
.tf-balance-card .tf-amount { font-size:2rem; font-weight:800; }
.tf-row { display:flex; justify-content:space-between; align-items:center; padding:.6rem 0; border-bottom:1px solid #f1f5f9; font-size:.875rem; }
.tf-row:last-child { border-bottom:none; }
.tf-credit { color:#16a34a; font-weight:700; }
.tf-debit  { color:#dc2626; font-weight:700; }
</style>
@endpush

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

    {{-- Left: Subscriptions, Credit History & Reviews --}}
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h3>Subscription History</h3>
                <span class="badge badge-primary">{{ $user->bookings->count() }} subscriptions</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sub #</th>
                            <th>Plan</th>
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
                                No subscriptions found for this user
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Travel Fund History --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-wallet" style="color:#7c3aed"></i> Credit History</h3>
                <span style="font-weight:700;color:{{ $travelFundBalance >= 0 ? '#16a34a' : '#dc2626' }}">
                    Balance: ₱{{ number_format($travelFundBalance, 2) }}
                </span>
            </div>
            <div class="card-body" style="padding:0">
                @if($user->travelFunds->count())
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Subscription</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->travelFunds->sortByDesc('created_at') as $tf)
                            <tr>
                                <td style="white-space:nowrap">{{ $tf->created_at->format('M d, Y') }}</td>
                                <td>{{ $tf->description ?? '—' }}</td>
                                <td>
                                    @if($tf->booking)
                                        <a href="{{ route('admin.bookings.show', $tf->booking) }}" class="btn btn-xs btn-ghost">
                                            {{ $tf->booking->booking_number }}
                                        </a>
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    @if($tf->type === 'credit')
                                        <span style="background:#dcfce7;color:#166534;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                                            <i class="fas fa-plus"></i> Credit
                                        </span>
                                    @else
                                        <span style="background:#fee2e2;color:#991b1b;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                                            <i class="fas fa-minus"></i> Debit
                                        </span>
                                    @endif
                                </td>
                                <td class="{{ $tf->type === 'credit' ? 'tf-credit' : 'tf-debit' }}">
                                    {{ $tf->type === 'credit' ? '+' : '-' }}₱{{ number_format($tf->amount, 2) }}
                                </td>
                                <td style="font-size:.8rem;color:#6b7280">{{ $tf->adminUser?->name ?? 'System' }}</td>
                                <td>
                                    <form action="{{ route('admin.travel-fund.destroy', $tf) }}" method="POST" style="display:inline"
                                          onsubmit="return confirm('Remove this travel fund entry?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-ghost" style="color:#dc2626" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted" style="padding:2rem">
                    <i class="fas fa-wallet" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                    No credit entries yet
                </div>
                @endif
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

    {{-- Right: User Info + Credit Balance Management --}}
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="text-align:center;">
                <div style="width:5rem;height:5rem;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="fa-solid fa-user" style="font-size:2rem;color:#fff;"></i>
                </div>
                <h3 style="margin-bottom:.25rem;">{{ $user->name }}</h3>
                <p class="text-muted" style="font-size:.875rem;">{{ $user->email }}</p>
                <div style="display:flex;gap:.375rem;justify-content:center;flex-wrap:wrap;margin-top:.5rem">
                    <span class="status-badge {{ $user->isAdmin() ? 'status-confirmed' : 'status-completed' }}">
                        {{ $user->isAdmin() ? 'Admin' : 'Customer' }}
                    </span>
                    @if($user->auth0_id)
                        <span style="background:#ede9fe;color:#5b21b6;padding:.2rem .6rem;border-radius:1rem;font-size:.75rem;font-weight:600;">
                            <i class="fas fa-shield-alt"></i> Auth0
                        </span>
                    @else
                        <span style="background:#dbeafe;color:#1e40af;padding:.2rem .6rem;border-radius:1rem;font-size:.75rem;font-weight:600;">
                            <i class="fas fa-envelope"></i> Email Sign-up
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Travel Fund Balance Card --}}
        <div class="tf-balance-card">
            <div class="tf-label"><i class="fas fa-wallet"></i> Credit Balance</div>
            <div class="tf-amount">₱{{ number_format($travelFundBalance, 2) }}</div>
            <div style="font-size:.8rem;opacity:.7;margin-top:.5rem">
                {{ $user->travelFunds->count() }} transaction{{ $user->travelFunds->count() != 1 ? 's' : '' }}
            </div>
        </div>

        {{-- Add Travel Fund --}}
        <div class="card mb-4">
            <div class="card-header"><h4><i class="fas fa-plus-circle" style="color:#7c3aed"></i> Add / Deduct Credits</h4></div>
            <div class="card-body">
                <form action="{{ route('admin.travel-fund.store', $user) }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label style="font-size:.8rem;color:#6b7280;font-weight:600">Type</label>
                        <select name="type" class="form-control" style="margin-top:.25rem">
                            <option value="credit">➕ Credit (Add)</option>
                            <option value="debit">➖ Debit (Deduct)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label style="font-size:.8rem;color:#6b7280;font-weight:600">Amount (₱) *</label>
                        <input type="number" name="amount" step="0.01" min="1" class="form-control"
                               placeholder="0.00" style="margin-top:.25rem" required>
                    </div>
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label style="font-size:.8rem;color:#6b7280;font-weight:600">Description *</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="e.g. Credits from subscription DG-2026-001234"
                               maxlength="255" style="margin-top:.25rem" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;background:#7c3aed;border-color:#7c3aed">
                        <i class="fas fa-save"></i> Save
                    </button>
                </form>
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
                <p style="font-size:.875rem;color:var(--gray-500);margin-top:.375rem;">
                    @if($user->auth0_id)
                        <i class="fas fa-shield-alt" style="color:#7c3aed"></i>
                        Registered via <strong>Auth0</strong>
                        <br><span style="font-size:.75rem;word-break:break-all;opacity:.6;">{{ $user->auth0_id }}</span>
                    @else
                        <i class="fas fa-envelope" style="color:#1e40af"></i>
                        Registered via <strong>Email / Password</strong>
                    @endif
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
