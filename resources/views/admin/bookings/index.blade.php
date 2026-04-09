@extends('layouts.admin')
@section('title', 'Bookings')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Bookings
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>All Bookings</h2>
        <p>Manage tour reservations</p>
    </div>
    <div>
        <form action="{{ route('admin.bookings.destroy-all') }}" method="POST"
              onsubmit="return confirm('⚠️ DELETE ALL {{ $bookings->total() }} BOOKINGS?\n\nThis will permanently remove every booking and reset all slot counts.\n\nThis cannot be undone. Are you sure?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Delete All Bookings ({{ $bookings->total() }})
            </button>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="filter-row">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Booking #, name, email..." class="form-control">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                @foreach(['pending','confirmed','cancelled','completed','refunded'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <select name="payment_method" class="form-control">
                <option value="">All Methods</option>
                <option value="xendit"       {{ request('payment_method') === 'xendit'       ? 'selected' : '' }}>💳 Online (Xendit)</option>
                <option value="cash"         {{ request('payment_method') === 'cash'         ? 'selected' : '' }}>🏢 Cash / Office</option>
                <option value="installment"  {{ request('payment_method') === 'installment'  ? 'selected' : '' }}>📅 Installment</option>
            </select>
            <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Tour</th>
                    <th>Date</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><code>{{ $booking->booking_number }}</code></td>
                        <td>
                            <strong>{{ $booking->contact_name }}</strong><br>
                            <small class="text-muted">{{ $booking->contact_email }}</small>
                        </td>
                        <td>{{ Str::limit($booking->tour->title, 30) }}</td>
                        <td>{{ $booking->tour_date->format('M d, Y') }}</td>
                        <td>{{ $booking->total_guests }}</td>
                        <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                        <td><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                        <td>
                            <span class="payment-badge payment-{{ $booking->payment_status }}">{{ ucfirst($booking->payment_status) }}</span>
                            @if($booking->payment_method === 'cash')
                                <br><small style="color:#16a34a;font-size:.75rem">🏢 Cash</small>
                            @elseif($booking->payment_method === 'installment')
                                <br><small style="color:#7c3aed;font-size:.75rem">📅 Installment</small>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="btn btn-xs btn-outline">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST"
                                      onsubmit="return confirm('Permanently delete booking {{ $booking->booking_number }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $bookings->links() }}</div>
</div>
@endsection
