@extends('layouts.admin')
@section('title', 'Transfer Booking ' . $booking->booking_number)

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Bookings</a>
    / <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->booking_number }}</a>
    / Transfer
@endsection

@push('styles')
<style>
.transfer-summary { display:grid; grid-template-columns:1fr 60px 1fr; gap:1rem; align-items:center; margin-bottom:1.5rem; }
.transfer-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.25rem; }
.transfer-box h5 { margin:0 0 .5rem; font-size:.85rem; text-transform:uppercase; color:#64748b; letter-spacing:.05em; }
.transfer-box .value { font-weight:600; font-size:1rem; color:#0f172a; }
.transfer-box .sub { font-size:.8rem; color:#94a3b8; margin-top:.25rem; }
.transfer-arrow { text-align:center; font-size:1.5rem; color:var(--primary,#0e7490); }
.schedule-info { background:#f0fdfa; border:1px solid #99f6e4; border-radius:.5rem; padding:.75rem 1rem; margin-top:.75rem; font-size:.85rem; display:none; }
.schedule-info .info-row { display:flex; justify-content:space-between; margin-bottom:.25rem; }
.schedule-info .info-row:last-child { margin-bottom:0; }
.schedule-info .label { color:#64748b; }
.schedule-info .val { font-weight:600; color:#0f172a; }
.schedule-info .val.warn { color:#ea580c; }
.schedule-info .val.danger { color:#dc2626; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2 class="page-title"><i class="fas fa-exchange-alt"></i> Transfer Booking</h2>
    <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
</div>

{{-- Current booking summary --}}
<div class="transfer-summary">
    <div class="transfer-box">
        <h5>Current Tour</h5>
        <div class="value">{{ $booking->tour?->title ?? '—' }}</div>
        <div class="sub">
            {{ $booking->tour_date?->format('M d, Y') ?? '—' }}
            @if($booking->schedule)
                &bull; {{ $booking->schedule->booked_seats }}/{{ $booking->schedule->available_seats }} seats booked
            @endif
        </div>
    </div>
    <div class="transfer-arrow"><i class="fas fa-arrow-right"></i></div>
    <div class="transfer-box" style="border-color:var(--primary,#0e7490)">
        <h5>New Tour</h5>
        <div class="value" id="newTourLabel">Select below…</div>
        <div class="sub" id="newScheduleLabel">&nbsp;</div>
    </div>
</div>

{{-- Booking details --}}
<div class="card mb-4">
    <div class="card-header"><h4>Booking Details</h4></div>
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
                <label style="font-size:.8rem;color:#64748b">PAX</label>
                <div style="font-weight:600">{{ $booking->total_guests }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Payment Terms</label>
                <div style="font-weight:600">{{ ucfirst($booking->payment_method ?? '—') }}</div>
            </div>
            <div>
                <label style="font-size:.8rem;color:#64748b">Current Rate</label>
                <div style="font-weight:600">₱{{ number_format($booking->price_per_adult, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Transfer form --}}
<form action="{{ route('admin.bookings.transfer.execute', $booking) }}" method="POST"
      onsubmit="return confirm('Are you sure you want to transfer this booking?')">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h4>Transfer To</h4></div>
        <div class="card-body">

            <div class="form-group">
                <label>Destination Tour *</label>
                <select name="tour_id" id="tourSelect" class="form-control @error('tour_id') is-invalid @enderror" required>
                    <option value="">— Select Tour —</option>
                    @foreach($tours as $tour)
                        <option value="{{ $tour->id }}"
                            {{ old('tour_id') == $tour->id ? 'selected' : '' }}>
                            {{ $tour->title }}
                        </option>
                    @endforeach
                </select>
                @error('tour_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Departure Schedule *</label>
                <select name="schedule_id" id="scheduleSelect" class="form-control @error('schedule_id') is-invalid @enderror" required disabled>
                    <option value="">— Select tour first —</option>
                </select>
                @error('schedule_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

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
                    <span class="label">Remaining After Transfer</span>
                    <span class="val" id="infoRemaining">—</span>
                </div>
                <div class="info-row">
                    <span class="label">Price Override</span>
                    <span class="val" id="infoPrice">—</span>
                </div>
            </div>

            <div class="form-group" style="margin-top:1rem">
                <label>Reason for Transfer (optional)</label>
                <textarea name="reason" class="form-control" rows="3"
                          placeholder="e.g. Overbooked on original tour">{{ old('reason') }}</textarea>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:.75rem;margin-bottom:2rem">
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-exchange-alt"></i> Confirm Transfer
        </button>
        <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const tourSelect     = document.getElementById('tourSelect');
    const scheduleSelect = document.getElementById('scheduleSelect');
    const scheduleInfo   = document.getElementById('scheduleInfo');
    const submitBtn      = document.getElementById('submitBtn');
    const pax            = {{ $booking->total_guests }};
    let schedulesCache   = {};

    tourSelect.addEventListener('change', function() {
        const tourId = this.value;
        scheduleSelect.innerHTML = '<option value="">— Loading… —</option>';
        scheduleSelect.disabled = true;
        submitBtn.disabled = true;
        scheduleInfo.style.display = 'none';
        document.getElementById('newTourLabel').textContent = this.options[this.selectedIndex].text || 'Select below…';
        document.getElementById('newScheduleLabel').innerHTML = '&nbsp;';

        if (!tourId) {
            scheduleSelect.innerHTML = '<option value="">— Select tour first —</option>';
            return;
        }

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
    });

    function renderSchedules(schedules) {
        scheduleSelect.innerHTML = '<option value="">— Select Schedule —</option>';
        schedules.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.label + '  (' + s.remaining + ' seats left)';
            opt.dataset.available = s.available_seats;
            opt.dataset.booked = s.booked_seats;
            opt.dataset.remaining = s.remaining;
            opt.dataset.price = s.price_override;
            opt.dataset.label = s.label;
            scheduleSelect.appendChild(opt);
        });
        scheduleSelect.disabled = false;
    }

    scheduleSelect.addEventListener('change', function() {
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
        document.getElementById('infoBooked').textContent = booked;

        const remEl = document.getElementById('infoRemaining');
        remEl.textContent = remaining;
        remEl.className = 'val' + (remaining < 0 ? ' danger' : remaining < 5 ? ' warn' : '');

        document.getElementById('infoPrice').textContent = price && price !== 'null'
            ? '₱' + parseFloat(price).toLocaleString(undefined, {minimumFractionDigits:2})
            : 'Use tour default';

        scheduleInfo.style.display = 'block';
        submitBtn.disabled = false;

        document.getElementById('newScheduleLabel').textContent =
            opt.dataset.label + ' • ' + (available - booked) + ' seats remaining';
    });
})();
</script>
@endpush
