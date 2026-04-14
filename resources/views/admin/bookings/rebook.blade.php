@extends('layouts.admin')
@section('title', 'Rebook ' . $booking->booking_number)

@section('skeleton')
    @include('admin.partials.skeleton-form')
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Bookings</a>
    / <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->booking_number }}</a>
    / Rebook
@endsection

@push('styles')
<style>
.rebook-summary { display:grid; grid-template-columns:1fr 60px 1fr; gap:1rem; align-items:center; margin-bottom:1.5rem; }
.rebook-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.25rem; }
.rebook-box h5 { margin:0 0 .5rem; font-size:.85rem; text-transform:uppercase; color:#64748b; letter-spacing:.05em; }
.rebook-box .value { font-weight:600; font-size:1rem; color:#0f172a; }
.rebook-box .sub { font-size:.8rem; color:#94a3b8; margin-top:.25rem; }
.rebook-arrow { text-align:center; font-size:1.5rem; color:#7c3aed; }
.schedule-info { background:#f5f3ff; border:1px solid #c4b5fd; border-radius:.5rem; padding:.75rem 1rem; margin-top:.75rem; font-size:.85rem; display:none; }
.schedule-info .info-row { display:flex; justify-content:space-between; margin-bottom:.25rem; }
.schedule-info .info-row:last-child { margin-bottom:0; }
.schedule-info .label { color:#64748b; }
.schedule-info .val { font-weight:600; color:#0f172a; }
.schedule-info .val.warn { color:#ea580c; }
.schedule-info .val.danger { color:#dc2626; }
.rebook-notice { background:#fffbeb; border:1px solid #fde68a; border-radius:.75rem; padding:1rem 1.25rem; margin-bottom:1.5rem; font-size:.9rem; color:#78350f; display:flex; gap:.75rem; align-items:flex-start; }
.rebook-notice i { margin-top:.1rem; flex-shrink:0; color:#d97706; }
.cancel-toggle { display:flex; align-items:center; gap:.75rem; padding:1rem 1.25rem; border:2px solid #e2e8f0; border-radius:.75rem; cursor:pointer; transition:border-color .2s,background .2s; margin-top:.5rem; }
.cancel-toggle:has(input:checked) { border-color:#fca5a5; background:#fff5f5; }
.cancel-toggle input { width:1.1rem; height:1.1rem; accent-color:#dc2626; cursor:pointer; }
.cancel-toggle .toggle-label { font-weight:600; color:#1f2937; }
.cancel-toggle .toggle-sub { font-size:.8rem; color:#6b7280; margin-top:.2rem; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2 class="page-title"><i class="fas fa-redo" style="color:#7c3aed"></i> Rebook Client</h2>
    <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
</div>

<div class="rebook-notice">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>What does Rebook do?</strong>
        A <em>new</em> booking will be created for the same client with the same passenger details on the new tour / departure date.
        The original booking (<strong>{{ $booking->booking_number }}</strong>) can optionally be cancelled at the same time.
    </div>
</div>

{{-- Summary --}}
<div class="rebook-summary">
    <div class="rebook-box">
        <h5>Original Booking</h5>
        <div class="value">{{ $booking->tour?->title ?? '—' }}</div>
        <div class="sub">
            {{ $booking->tour_date?->format('M d, Y') ?? '—' }}
            &bull; {{ $booking->total_guests }} pax
            &bull; <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
        </div>
    </div>
    <div class="rebook-arrow"><i class="fas fa-arrow-right"></i></div>
    <div class="rebook-box" style="border-color:#7c3aed;">
        <h5>New Booking</h5>
        <div class="value" id="newTourLabel">Select below…</div>
        <div class="sub" id="newScheduleLabel">&nbsp;</div>
    </div>
</div>

{{-- Client snapshot --}}
<div class="card mb-4">
    <div class="card-header"><h4><i class="fas fa-user"></i> Client Details (will be copied)</h4></div>
    <div class="card-body">
        <div class="form-row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem">
            <div>
                <label style="font-size:.8rem;color:#64748b">Booking #</label>
                <div style="font-weight:600">{{ $booking->booking_number }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Client</label>
                <div style="font-weight:600">{{ $booking->contact_name ?? $booking->user?->name ?? '—' }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Email</label>
                <div style="font-weight:600">{{ $booking->contact_email ?? '—' }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Phone</label>
                <div style="font-weight:600">{{ $booking->contact_phone ?? '—' }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">PAX</label>
                <div style="font-weight:600">{{ $booking->adults }} adults@if($booking->children > 0), {{ $booking->children }} children@endif</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Payment Terms</label>
                <div style="font-weight:600">{{ ucfirst($booking->payment_method ?? '—') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Rebook form --}}
<form action="{{ route('admin.bookings.rebook.execute', $booking) }}" method="POST"
      onsubmit="return confirmRebook()">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h4>New Tour & Schedule</h4></div>
        <div class="card-body">

            <div class="form-group">
                <label>Tour *</label>
                <select name="tour_id" id="tourSelect" class="form-control @error('tour_id') is-invalid @enderror" required>
                    <option value="">— Select Tour —</option>
                    @foreach($tours as $tour)
                        <option value="{{ $tour->id }}"
                            {{ (old('tour_id', $booking->tour_id) == $tour->id) ? 'selected' : '' }}>
                            {{ $tour->title }}
                        </option>
                    @endforeach
                </select>
                @error('tour_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Departure Schedule *</label>
                <select name="schedule_id" id="scheduleSelect"
                        class="form-control @error('schedule_id') is-invalid @enderror"
                        required disabled>
                    <option value="">— Select tour first —</option>
                </select>
                @error('schedule_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            {{-- Seat availability preview --}}
            <div class="schedule-info" id="scheduleInfo">
                <div class="info-row">
                    <span class="label">Available Seats</span>
                    <span class="val" id="infoAvailable">—</span>
                </div>
                <div class="info-row">
                    <span class="label">Already Booked</span>
                    <span class="val" id="infoBooked">—</span>
                </div>
                <div class="info-row">
                    <span class="label">Remaining After Rebook</span>
                    <span class="val" id="infoRemaining">—</span>
                </div>
                <div class="info-row">
                    <span class="label">Price Override</span>
                    <span class="val" id="infoPrice">—</span>
                </div>
            </div>

            <div class="form-group" style="margin-top:1rem">
                <label>New Booking Status *</label>
                <select name="new_status" class="form-control" style="max-width:220px">
                    <option value="pending" {{ old('new_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ old('new_status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Reason for Rebooking (optional)</label>
                <textarea name="reason" class="form-control" rows="3"
                          placeholder="e.g. Tour cancelled due to typhoon, client emergency…">{{ old('reason') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Cancel original option --}}
    <div class="card mb-4">
        <div class="card-header"><h4>Original Booking</h4></div>
        <div class="card-body">
            <p style="margin:0 0 .75rem;font-size:.9rem;color:#6b7280">
                The original booking will have a note added linking it to the new booking.
                You can also cancel it immediately.
            </p>
            <label class="cancel-toggle">
                <input type="checkbox" name="cancel_original" value="1"
                       {{ old('cancel_original') ? 'checked' : '' }}>
                <div>
                    <div class="toggle-label" style="color:#dc2626">
                        <i class="fas fa-times-circle"></i>
                        Cancel original booking ({{ $booking->booking_number }})
                    </div>
                    <div class="toggle-sub">
                        This will set the original booking status to "Cancelled" and free its seats.
                        Leave unchecked to keep the original booking as-is.
                    </div>
                </div>
            </label>
        </div>
    </div>

    <div style="display:flex;gap:.75rem;margin-bottom:2rem">
        <button type="submit" class="btn btn-primary" id="submitBtn"
                style="background:#7c3aed;border-color:#7c3aed" disabled>
            <i class="fas fa-redo"></i> Create New Booking
        </button>
        <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const tourSelect     = document.getElementById('tourSelect');
    const scheduleSelect = document.getElementById('scheduleSelect');
    const scheduleInfo   = document.getElementById('scheduleInfo');
    const submitBtn      = document.getElementById('submitBtn');
    const pax            = {{ $booking->total_guests }};
    let schedulesCache   = {};

    // Auto-load schedules for pre-selected tour (same tour as original)
    if (tourSelect.value) {
        loadSchedules(tourSelect.value);
    }

    tourSelect.addEventListener('change', function () {
        const tourId = this.value;
        scheduleSelect.innerHTML = '<option value="">— Loading… —</option>';
        scheduleSelect.disabled = true;
        submitBtn.disabled = true;
        scheduleInfo.style.display = 'none';
        document.getElementById('newTourLabel').textContent =
            this.options[this.selectedIndex].text || 'Select below…';
        document.getElementById('newScheduleLabel').innerHTML = '&nbsp;';

        if (!tourId) {
            scheduleSelect.innerHTML = '<option value="">— Select tour first —</option>';
            return;
        }

        loadSchedules(tourId);
    });

    function loadSchedules(tourId) {
        if (schedulesCache[tourId]) {
            renderSchedules(schedulesCache[tourId]);
            return;
        }

        fetch('{{ route("admin.bookings.schedules-for-tour") }}?tour_id=' + tourId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            schedulesCache[tourId] = data;
            renderSchedules(data);
        })
        .catch(() => {
            scheduleSelect.innerHTML = '<option value="">— Error loading schedules —</option>';
        });
    }

    function renderSchedules(schedules) {
        scheduleSelect.innerHTML = '<option value="">— Select Schedule —</option>';
        schedules.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.label + '  (' + s.remaining + ' seats left)';
            opt.dataset.available = s.available_seats;
            opt.dataset.booked    = s.booked_seats;
            opt.dataset.remaining = s.remaining;
            opt.dataset.price     = s.price_override;
            opt.dataset.label     = s.label;
            scheduleSelect.appendChild(opt);
        });
        scheduleSelect.disabled = false;
    }

    scheduleSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            scheduleInfo.style.display = 'none';
            submitBtn.disabled = true;
            document.getElementById('newScheduleLabel').innerHTML = '&nbsp;';
            return;
        }

        const available = parseInt(opt.dataset.available);
        const booked    = parseInt(opt.dataset.booked);
        const remaining = available - booked - pax;
        const price     = opt.dataset.price;

        document.getElementById('infoAvailable').textContent = available;
        document.getElementById('infoBooked').textContent    = booked;

        const remEl = document.getElementById('infoRemaining');
        remEl.textContent = remaining;
        remEl.className   = 'val' + (remaining < 0 ? ' danger' : remaining < 5 ? ' warn' : '');

        document.getElementById('infoPrice').textContent =
            price && price !== 'null'
                ? '₱' + parseFloat(price).toLocaleString(undefined, { minimumFractionDigits: 2 })
                : 'Use tour default';

        scheduleInfo.style.display = 'block';
        submitBtn.disabled = false;

        document.getElementById('newScheduleLabel').textContent =
            opt.dataset.label + ' • ' + (available - booked) + ' seats remaining';
    });
})();

function confirmRebook() {
    const cancelOriginal = document.querySelector('input[name="cancel_original"]').checked;
    const msg = cancelOriginal
        ? 'This will create a new booking AND cancel the original booking ({{ $booking->booking_number }}). Proceed?'
        : 'This will create a new booking for the same client. The original booking will be kept. Proceed?';
    return confirm(msg);
}
</script>
@endpush
