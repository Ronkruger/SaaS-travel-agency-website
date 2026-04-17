@extends('layouts.admin')
@section('title', $tour->title . ' — Usage Monitor')

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => true, 'filterCount' => 0, 'cols' => 7, 'rows' => 6])
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.tours.index') }}">Tours</a> /
    <a href="{{ route('admin.tours.edit', $tour) }}">{{ $tour->title }}</a> /
    Slot Tracker
@endsection

@push('styles')
<style>
.schedule-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: #fff;
    padding: 1.25rem 1.5rem;
    border-radius: .75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
}
.schedule-header h3 { margin:0; font-size:1.25rem; }
.schedule-header small { color:#94a3b8; font-size:.8rem; }
.sched-stat-row { display:flex; gap:1.5rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.sched-stat { background:#fff; border:1px solid #e2e8f0; border-radius:.5rem; padding:.75rem 1.25rem; min-width:110px; text-align:center; }
.sched-stat__n { font-size:1.5rem; font-weight:700; }
.sched-stat__l { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em; }
.slot-card { border:1px solid #e2e8f0; border-radius:.75rem; margin-bottom:1.25rem; overflow:hidden; }
.slot-card-head { padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; cursor:pointer; }
.slot-card-head:hover { background:#f8fafc; }
.slot-card-body { border-top:1px solid #e2e8f0; }
.slot-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .75rem; border-radius:2rem; font-size:.75rem; font-weight:700; }
.badge-active    { background:#dcfce7; color:#166534; }
.badge-sold_out  { background:#fee2e2; color:#991b1b; }
.badge-overbooked{ background:#fecaca; color:#7f1d1d; border:1px solid #f87171; }
.badge-cancelled { background:#f1f5f9; color:#475569; }
.badge-warning   { background:#fef9c3; color:#854d0e; }
.seats-progress { flex:1; max-width:200px; }
.seats-bar { height:10px; background:#e2e8f0; border-radius:5px; overflow:hidden; }
.seats-bar__fill { height:100%; border-radius:5px; transition:width .3s; }
.booking-mini-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.booking-mini-table th { background:#f8fafc; color:#475569; padding:.45rem .9rem; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid #e2e8f0; }
.booking-mini-table td { padding:.6rem .9rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.booking-mini-table tr:last-child td { border-bottom:none; }
.status-pill { display:inline-block; padding:.15rem .6rem; border-radius:2rem; font-size:.75rem; font-weight:600; }
.status-pending   { background:#fef9c3; color:#854d0e; }
.status-confirmed { background:#dcfce7; color:#166534; }
.status-cancelled { background:#fee2e2; color:#991b1b; }
.status-completed { background:#dbeafe; color:#1e40af; }
.status-refunded  { background:#f3e8ff; color:#6b21a8; }
.edit-form { display:none; background:#f8fafc; border-top:1px solid #e2e8f0; padding:1.25rem 1.5rem; }
.edit-form.open { display:block; }
.form-row-inline { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:.75rem; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <div>
        <h2><i class="fas fa-layer-group" style="color:#3b82f6"></i> Usage Monitor</h2>
        <p style="margin:.25rem 0 0;color:#64748b;font-size:.9rem">{{ $tour->title }}</p>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="{{ route('admin.slot-tracker.index') }}" class="btn btn-outline">
            <i class="fas fa-layer-group"></i> All Slots
        </a>
        <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-outline">
            <i class="fas fa-edit"></i> Edit Tour
        </a>
        <button onclick="document.getElementById('addSlotForm').classList.toggle('open')" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Schedule Slot
        </button>
    </div>
</div>

{{-- Summary row --}}
@php
    $totalSeats    = $schedules->sum('available_seats');
    $totalBooked   = $schedules->sum('booked_seats');
    $totalAvail    = $schedules->sum(fn($s) => max(0, $s->available_seats - $s->booked_seats));
    $totalPending  = $schedules->sum('pending_count');
    $totalConfirmed= $schedules->sum('confirmed_count');
@endphp
<div class="sched-stat-row">
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#3b82f6">{{ $schedules->count() }}</div>
        <div class="sched-stat__l">Schedules</div>
    </div>
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#0f172a">{{ $totalSeats }}</div>
        <div class="sched-stat__l">Total Licenses</div>
    </div>
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#f59e0b">{{ $totalBooked }}</div>
        <div class="sched-stat__l">Occupied</div>
    </div>
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#16a34a">{{ $totalAvail }}</div>
        <div class="sched-stat__l">Available</div>
    </div>
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#ca8a04">{{ $totalPending }}</div>
        <div class="sched-stat__l">Pending</div>
    </div>
    <div class="sched-stat">
        <div class="sched-stat__n" style="color:#16a34a">{{ $totalConfirmed }}</div>
        <div class="sched-stat__l">Confirmed</div>
    </div>
</div>

{{-- Add Slot Form --}}
<div id="addSlotForm" class="card mb-4" style="display:none">
    <div class="card-header" style="background:#f0fdf4;border-color:#86efac">
        <h4 style="color:#166534"><i class="fas fa-plus-circle"></i> Add New Period Slot</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.tours.schedules.store', $tour) }}" method="POST">
            @csrf
            <div class="form-row-inline">
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="departure_date" class="form-control" required value="{{ old('departure_date') }}">
                </div>
                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="return_date" class="form-control" value="{{ old('return_date') }}">
                </div>
                <div class="form-group">
                    <label>Total Licenses *</label>
                    <input type="number" name="available_seats" class="form-control" min="1" max="500" required value="{{ old('available_seats', 40) }}">
                </div>
                <div class="form-group">
                    <label>Price Override (₱)</label>
                    <input type="number" name="price_override" class="form-control" min="0" step="0.01" placeholder="Leave blank for tour default" value="{{ old('price_override') }}">
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="sold_out">Sold Out</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:.75rem">
                <label>Notes</label>
                <input type="text" name="notes" class="form-control" maxlength="500" placeholder="e.g. TOUR IS DONE, TO TRANSFER TO ANOTHER DATE" value="{{ old('notes') }}">
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Slot</button>
                <button type="button" onclick="document.getElementById('addSlotForm').style.display='none'" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Schedule Cards --}}
@forelse($schedules as $sched)
@php
    $pct       = $sched->available_seats > 0 ? ($sched->booked_seats / $sched->available_seats) * 100 : 100;
    $available = max(0, $sched->available_seats - $sched->booked_seats);
    $barColor  = $sched->booked_seats > $sched->available_seats ? '#7f1d1d'
               : ($pct >= 100 ? '#dc2626' : ($pct >= 80 ? '#f59e0b' : '#16a34a'));
    $headBg    = $sched->booked_seats > $sched->available_seats ? '#fff1f2'
               : ($pct >= 80 ? '#fffbeb' : '#f0fdf4');
    $bookings  = $scheduleBookings[$sched->id] ?? collect();
@endphp
<div class="slot-card">
    {{-- Card Header --}}
    <div class="slot-card-head" style="background:{{ $headBg }}"
         onclick="toggleSlot({{ $sched->id }})">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <div>
                <div style="font-weight:700;font-size:1rem">
                    {{ \Carbon\Carbon::parse($sched->departure_date)->format('M d, Y') }}
                    @if($sched->return_date)
                        &ndash; {{ \Carbon\Carbon::parse($sched->return_date)->format('M d, Y') }}
                    @endif
                </div>
                @if($sched->price_override)
                    <small style="color:#64748b">Price: ₱{{ number_format($sched->price_override, 2) }}/person</small>
                @endif
            </div>
            {{-- Seat numbers --}}
            <div style="display:flex;gap:1.5rem;font-size:.875rem">
                <span><strong style="color:#0f172a">{{ $sched->available_seats }}</strong> <span style="color:#94a3b8">total</span></span>
                <span><strong style="color:#f59e0b">{{ $sched->booked_seats }}</strong> <span style="color:#94a3b8">occupied</span></span>
                <span><strong style="color:{{ $available > 0 ? '#16a34a' : '#dc2626' }}">{{ $available }}</strong> <span style="color:#94a3b8">available</span></span>
            </div>
            {{-- Progress bar --}}
            <div class="seats-progress">
                <div class="seats-bar">
                    <div class="seats-bar__fill" style="width:{{ min(100,$pct) }}%;background:{{ $barColor }}"></div>
                </div>
                <div style="font-size:.7rem;color:#94a3b8;margin-top:.2rem">{{ round($pct) }}% occupied</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
            {{-- Status badge --}}
            @if($sched->booked_seats > $sched->available_seats)
                <span class="slot-badge badge-overbooked"><i class="fas fa-exclamation-triangle"></i> OVERBOOKED</span>
            @elseif($sched->status === 'sold_out' || $available === 0)
                <span class="slot-badge badge-sold_out"><i class="fas fa-times-circle"></i> SOLD OUT</span>
            @elseif($sched->status === 'cancelled')
                <span class="slot-badge badge-cancelled">CANCELLED</span>
            @elseif($pct >= 80)
                <span class="slot-badge badge-warning"><i class="fas fa-exclamation-circle"></i> ALMOST FULL</span>
            @else
                <span class="slot-badge badge-active"><i class="fas fa-check-circle"></i> SLOTS AVAILABLE</span>
            @endif
            {{-- Pending bookings badge --}}
            @if($sched->pending_count > 0)
                <span style="background:#fef3c7;color:#92400e;padding:.25rem .75rem;border-radius:2rem;font-size:.75rem;font-weight:600">
                    <i class="fas fa-clock"></i> {{ $sched->pending_count }} pending
                </span>
            @endif
            {{-- Actions --}}
            <button onclick="event.stopPropagation();toggleEdit({{ $sched->id }})"
                    class="btn btn-xs btn-outline" title="Edit Slot">
                <i class="fas fa-edit"></i>
            </button>
            <form action="{{ route('admin.tours.schedules.destroy', [$tour, $sched]) }}" method="POST" style="display:inline"
                  onsubmit="return confirm('Delete this slot? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-xs" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5" title="Delete Slot">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            <i class="fas fa-chevron-down" id="chevron-{{ $sched->id }}" style="color:#94a3b8;font-size:.8rem;transition:transform .2s"></i>
        </div>
    </div>

    {{-- Notes banner --}}
    @if($sched->notes)
    <div style="background:#fffbeb;padding:.5rem 1.25rem;border-top:1px solid #fde68a;font-size:.85rem;color:#92400e">
        <i class="fas fa-sticky-note"></i> {{ $sched->notes }}
    </div>
    @endif

    {{-- Edit Form (hidden by default) --}}
    <div class="edit-form" id="edit-{{ $sched->id }}">
        <h5 style="margin-bottom:1rem;color:#334155"><i class="fas fa-edit"></i> Edit Period Slot</h5>
        <form action="{{ route('admin.tours.schedules.update', [$tour, $sched]) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-row-inline">
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="departure_date" class="form-control" required
                           value="{{ $sched->departure_date->format('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="return_date" class="form-control"
                           value="{{ $sched->return_date ? $sched->return_date->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label>Total Licenses *</label>
                    <input type="number" name="available_seats" class="form-control" min="1" max="500" required
                           value="{{ $sched->available_seats }}">
                </div>
                <div class="form-group">
                    <label>Used Licenses *</label>
                    <input type="number" name="booked_seats" class="form-control" min="0" required
                           value="{{ $sched->booked_seats }}">
                </div>
                <div class="form-group">
                    <label>Price Override (₱)</label>
                    <input type="number" name="price_override" class="form-control" min="0" step="0.01"
                           placeholder="Leave blank for tour default"
                           value="{{ $sched->price_override }}">
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        @foreach(['active' => 'Active', 'sold_out' => 'Sold Out', 'cancelled' => 'Cancelled'] as $val => $label)
                            <option value="{{ $val }}" {{ $sched->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:.75rem">
                <label>Notes</label>
                <input type="text" name="notes" class="form-control" maxlength="500"
                       placeholder="e.g. TOUR IS DONE, TO TRANSFER TO ANOTHER DATE"
                       value="{{ $sched->notes }}">
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" onclick="toggleEdit({{ $sched->id }})" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Bookings list (collapsible) --}}
    <div class="slot-card-body" id="slot-body-{{ $sched->id }}" style="display:none">
        @if($bookings->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.875rem">
                <i class="fas fa-inbox" style="display:block;font-size:1.5rem;margin-bottom:.5rem"></i>
                No bookings yet for this slot.
            </div>
        @else
        <div style="overflow-x:auto">
        <table class="booking-mini-table">
            <thead>
                <tr>
                    <th>Sub #</th>
                    <th>Client Name</th>
                    <th style="text-align:center">PAX</th>
                    <th>Payment Terms</th>
                    <th style="text-align:right">Rate/Person</th>
                    <th style="text-align:right">Total</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Payment</th>
                    <th>Booked On</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $bk)
                <tr>
                    <td style="font-family:monospace;font-size:.8rem">
                        <a href="{{ route('admin.bookings.show', $bk) }}" style="color:#1e40af;text-decoration:none;font-weight:600">
                            {{ $bk->booking_number }}
                        </a>
                    </td>
                    <td style="font-weight:500">{{ $bk->contact_name }}</td>
                    <td style="text-align:center">{{ $bk->total_guests }}</td>
                    <td>
                        @if($bk->payment_method === 'installment')
                            <span style="background:#ede9fe;color:#5b21b6;padding:.2rem .5rem;border-radius:.75rem;font-size:.75rem">Instalment</span>
                        @elseif($bk->payment_method === 'cash')
                            <span style="background:#dbeafe;color:#1e40af;padding:.2rem .5rem;border-radius:.75rem;font-size:.75rem">
                                {{ $bk->downpayment_amount > 0 ? 'Downpayment' : 'Full Cash' }}
                            </span>
                        @else
                            <span style="background:#dcfce7;color:#166534;padding:.2rem .5rem;border-radius:.75rem;font-size:.75rem">Xendit</span>
                        @endif
                    </td>
                    <td style="text-align:right">₱{{ number_format($bk->price_per_adult, 2) }}</td>
                    <td style="text-align:right;font-weight:600">₱{{ number_format($bk->total_amount, 2) }}</td>
                    <td style="text-align:center">
                        <span class="status-pill status-{{ $bk->status }}">{{ ucfirst($bk->status) }}</span>
                    </td>
                    <td style="text-align:center">
                        <span class="status-pill status-{{ $bk->payment_status === 'paid' ? 'confirmed' : ($bk->payment_status === 'partial' ? 'pending' : 'cancelled') }}">
                            {{ ucfirst($bk->payment_status) }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#64748b;white-space:nowrap">
                        {{ $bk->created_at->format('M d, Y') }}
                    </td>
                    <td style="white-space:nowrap">
                        <a href="{{ route('admin.bookings.show', $bk) }}"
                           class="btn btn-xs btn-outline" title="View Booking">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.bookings.transfer', $bk) }}"
                           class="btn btn-xs btn-warning" title="Transfer">
                            <i class="fas fa-exchange-alt"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700;font-size:.875rem">
                    <td colspan="2" style="padding:.6rem .9rem">Totals</td>
                    <td style="padding:.6rem .9rem;text-align:center">{{ $bookings->sum('total_guests') }}</td>
                    <td colspan="2"></td>
                    <td style="padding:.6rem .9rem;text-align:right">₱{{ number_format($bookings->sum('total_amount'), 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body" style="text-align:center;padding:3rem;color:#94a3b8">
        <i class="fas fa-calendar-plus" style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i>
        <p>No schedule slots yet for this tour.</p>
        <button onclick="document.getElementById('addSlotForm').style.display='block';document.getElementById('addSlotForm').classList.add('open')"
                class="btn btn-primary">
            <i class="fas fa-plus"></i> Add First Slot
        </button>
    </div>
</div>
@endforelse

@push('scripts')
<script>
function toggleSlot(id) {
    const body    = document.getElementById('slot-body-' + id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen  = body.style.display !== 'none';
    body.style.display   = isOpen ? 'none'    : 'block';
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}
function toggleEdit(id) {
    const form = document.getElementById('edit-' + id);
    form.classList.toggle('open');
}
// Auto-open slots with pending bookings
document.addEventListener('DOMContentLoaded', function () {
    @foreach($schedules as $sched)
        @if($sched->pending_count > 0)
            toggleSlot({{ $sched->id }});
        @endif
    @endforeach
});
</script>
@endpush
@endsection
