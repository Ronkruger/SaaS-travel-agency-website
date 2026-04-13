@extends('layouts.admin')
@section('title', 'Bookings')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Bookings
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => true, 'filterCount' => 3, 'cols' => 8, 'rows' => 10])
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>All Bookings</h2>
        <p>Manage tour reservations</p>
    </div>
    <div>
        @if(auth('admin')->user()->isSuperAdmin())
            <form action="{{ route('admin.bookings.destroy-all') }}" method="POST"
                  onsubmit="return confirm('⚠️ DELETE ALL {{ $bookings->total() }} BOOKINGS?\n\nThis will permanently remove every booking and reset all slot counts.\n\nThis cannot be undone. Are you sure?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete All Bookings ({{ $bookings->total() }})
                </button>
            </form>
        @endif
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
                                @if(auth('admin')->user()->isSuperAdmin())
                                    <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST"
                                          onsubmit="return confirm('Permanently delete booking {{ $booking->booking_number }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-xs btn-warning"
                                            onclick="openDeleteRequestModal('booking', {{ $booking->id }}, '{{ $booking->booking_number }}')"
                                            title="Request deletion">
                                        <i class="fas fa-hand-paper"></i>
                                    </button>
                                @endif
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

{{-- Deletion Request Modal (for non-super-admin staff) --}}
@unless(auth('admin')->user()->isSuperAdmin())
<div class="modal" id="deleteRequestModal">
    <div class="modal-backdrop" onclick="closeDeleteRequestModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-hand-paper text-warning"></i> Request Deletion</h4>
            <button class="modal-close" onclick="closeDeleteRequestModal()">×</button>
        </div>
        <form method="POST" action="{{ route('admin.deletion-requests.store') }}">
            @csrf
            <input type="hidden" name="type" id="dr-type">
            <input type="hidden" name="target_id" id="dr-target-id">
            <div class="modal-body">
                <p style="margin-bottom:1rem">
                    You are requesting deletion of <strong id="dr-label"></strong>.
                    A super administrator will review your request.
                </p>
                <div class="form-group">
                    <label>Reason for deletion <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3"
                              placeholder="Explain why this booking should be deleted…"
                              required maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteRequestModal()">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endunless

@endsection

@unless(auth('admin')->user()->isSuperAdmin())
@push('scripts')
<script>
function openDeleteRequestModal(type, id, label) {
    document.getElementById('dr-type').value = type;
    document.getElementById('dr-target-id').value = id;
    document.getElementById('dr-label').textContent = label;
    document.getElementById('deleteRequestModal').classList.add('open');
}
function closeDeleteRequestModal() {
    document.getElementById('deleteRequestModal').classList.remove('open');
}
</script>
@endpush
@endunless
