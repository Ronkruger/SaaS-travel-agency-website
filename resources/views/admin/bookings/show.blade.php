@extends('layouts.admin')
@section('title', 'Booking ' . $booking->booking_number)

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Bookings</a> / {{ $booking->booking_number }}
@endsection

@push('styles')
<style>
.approve-bar { border-radius:.75rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.approve-bar--pending { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:2px solid #86efac; }
.approve-bar__info { display:flex; align-items:center; gap:.75rem; }
.approve-bar__actions { display:flex; gap:.75rem; flex-wrap:wrap; }
.btn-approve { background:#16a34a; color:#fff; border:none; padding:.6rem 1.5rem; border-radius:.5rem; font-weight:700; font-size:.95rem; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; }
.btn-approve:hover { background:#15803d; }
.btn-reject { background:#fff; color:#991b1b; border:2px solid #fca5a5; padding:.6rem 1.25rem; border-radius:.5rem; font-weight:600; font-size:.875rem; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; }
.btn-reject:hover { background:#fee2e2; }
.slot-avail-card { border:1px solid #e2e8f0; border-radius:.75rem; overflow:hidden; margin-bottom:1rem; }
.slot-avail-head { background:#1e293b; color:#fff; padding:.75rem 1rem; font-weight:600; font-size:.875rem; display:flex; align-items:center; gap:.5rem; }
.slot-avail-body { padding:1rem; }
.slot-row { display:flex; justify-content:space-between; align-items:center; padding:.35rem 0; border-bottom:1px solid #f1f5f9; font-size:.875rem; }
.slot-row:last-child { border-bottom:none; }
.seats-mini-bar { width:100%; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:.5rem; }
.seats-mini-fill { height:100%; border-radius:4px; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2>Booking #{{ $booking->booking_number }}</h2>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
        {{-- PDF Actions --}}
        <a href="{{ route('admin.bookings.pdf.preview', $booking) }}" target="_blank"
           class="btn btn-outline" title="Preview PDF">
            <i class="fas fa-file-pdf" style="color:#dc2626"></i> Preview PDF
        </a>
        <a href="{{ route('admin.bookings.pdf.download', $booking) }}"
           class="btn btn-outline" title="Download PDF">
            <i class="fas fa-download"></i> Download PDF
        </a>
        <form action="{{ route('admin.bookings.pdf.email', $booking) }}" method="POST" style="display:inline"
              onsubmit="return confirm('Send booking confirmation PDF to {{ $booking->contact_email }}?')">
            @csrf
            <button type="submit" class="btn btn-outline" title="Email PDF to client">
                <i class="fas fa-envelope"></i> Email PDF
            </button>
        </form>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- ── PENDING APPROVAL BAR ──────────────────────────────────────── --}}
@if($booking->status === 'pending')
<div class="approve-bar approve-bar--pending">
    <div class="approve-bar__info">
        <i class="fas fa-clock" style="font-size:1.5rem;color:#ca8a04"></i>
        <div>
            <div style="font-weight:700;font-size:1rem;color:#166534">This booking is waiting for approval</div>
            <div style="font-size:.85rem;color:#4b5563">
                {{ $booking->contact_name }} &bull; {{ $booking->total_guests }} pax &bull;
                {{ $booking->tour?->title ?? 'Unknown Tour' }} &bull;
                {{ $booking->tour_date?->format('M d, Y') ?? '—' }}
            </div>
        </div>
    </div>
    <div class="approve-bar__actions">
        <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="confirmed">
            <button type="submit" class="btn-approve"
                    onclick="return confirm('Confirm this booking? A confirmation email will be sent to the client.')">
                <i class="fas fa-check-circle"></i> Approve & Confirm
            </button>
        </form>
        <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn-reject"
                    onclick="return confirm('Cancel this booking? This action cannot be undone.')">
                <i class="fas fa-times-circle"></i> Reject / Cancel
            </button>
        </form>
    </div>
</div>
@endif

<div class="booking-admin-layout">
    <div>
        <!-- Status -->
        <div class="card mb-4">
            <div class="card-header"><h4>Update Booking Status</h4></div>
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

        {{-- ── XENDIT ONLINE PAYMENTS ─────────────────────────────── --}}
        @if($booking->payment_method === 'xendit')
        <div class="card mb-4" style="border:2px solid #bfdbfe">
            <div class="card-header" style="background:#eff6ff">
                <h4 style="color:#1e40af"><i class="fas fa-credit-card"></i> Xendit Online Payments</h4>
            </div>
            <div class="card-body">

                {{-- Overall status + manual override --}}
                <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" class="inline-form mb-4">
                    @csrf @method('PATCH')
                    <label style="font-weight:600;margin-right:.5rem">Overall Payment Status:</label>
                    <select name="payment_status" class="form-control" style="max-width:180px">
                        @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partially Paid', 'paid' => 'Fully Paid'] as $val => $label)
                            <option value="{{ $val }}" {{ $booking->payment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>

                @php $xenditPayments = $booking->payments->where('method', 'xendit')->sortByDesc('paid_at'); @endphp

                @if($xenditPayments->count())
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                    <thead>
                        <tr style="background:#dbeafe;color:#1e3a8a;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em">
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Transaction ID</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Amount</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Channel</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Gateway ID</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Notes</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Paid At</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #bfdbfe">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($xenditPayments as $pmt)
                        <tr style="border-bottom:1px solid #e2e8f0">
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.8rem">{{ $pmt->transaction_id }}</td>
                            <td style="padding:.6rem .75rem;font-weight:600">₱{{ number_format($pmt->amount, 2) }}</td>
                            <td style="padding:.6rem .75rem">
                                @php $channel = $pmt->gateway_response['payment_channel'] ?? $pmt->gateway_response['payment_method'] ?? '—'; @endphp
                                {{ strtoupper($channel) }}
                            </td>
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.8rem">{{ $pmt->gateway_transaction_id ?? '—' }}</td>
                            <td style="padding:.6rem .75rem">{{ $pmt->notes ?? '—' }}</td>
                            <td style="padding:.6rem .75rem">
                                {{ $pmt->paid_at ? $pmt->paid_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td style="padding:.6rem .75rem;text-align:center">
                                @if($pmt->status === 'completed')
                                    <span style="background:#dcfce7;color:#166534;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                                        <i class="fas fa-check"></i> Paid
                                    </span>
                                @else
                                    <span style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem">{{ ucfirst($pmt->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @else
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:1rem;color:#854d0e;font-size:.9rem">
                    <i class="fas fa-hourglass-half"></i>
                    <strong>No Xendit payment received yet.</strong>
                    Payment will be automatically recorded here once the client completes payment online.
                    If the client has paid but this is empty, the Xendit webhook may have been delayed —
                    you can manually update the Overall Payment Status above.
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── XENDIT ONLINE PAYMENTS ─────────────────────────────── --}}
        @if($booking->payment_method === 'xendit')
        <div class="card mb-4" style="border:2px solid #bfdbfe">
            <div class="card-header" style="background:#eff6ff">
                <h4 style="color:#1e40af"><i class="fas fa-credit-card"></i> Xendit Online Payments</h4>
            </div>
            <div class="card-body">

                {{-- Overall status + manual override --}}
                <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" class="inline-form mb-4">
                    @csrf @method('PATCH')
                    <label style="font-weight:600;margin-right:.5rem">Overall Payment Status:</label>
                    <select name="payment_status" class="form-control" style="max-width:180px">
                        @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partially Paid', 'paid' => 'Fully Paid'] as $val => $label)
                            <option value="{{ $val }}" {{ $booking->payment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>

                @php $xenditPayments = $booking->payments->sortByDesc('paid_at'); @endphp

                @if($xenditPayments->count())
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                    <thead>
                        <tr style="background:#dbeafe;color:#1e3a8a;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em">
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Transaction ID</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Amount</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Channel</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Gateway ID</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Paid At</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #bfdbfe">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($xenditPayments as $pmt)
                        <tr style="border-bottom:1px solid #e2e8f0">
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.8rem">{{ $pmt->transaction_id }}</td>
                            <td style="padding:.6rem .75rem;font-weight:600">₱{{ number_format($pmt->amount, 2) }}</td>
                            <td style="padding:.6rem .75rem">
                                @php $channel = $pmt->gateway_response['payment_channel'] ?? $pmt->gateway_response['payment_method'] ?? strtoupper($pmt->method); @endphp
                                {{ strtoupper($channel) }}
                            </td>
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.8rem">{{ $pmt->gateway_transaction_id ?? '—' }}</td>
                            <td style="padding:.6rem .75rem">{{ $pmt->paid_at ? $pmt->paid_at->format('M d, Y H:i') : '—' }}</td>
                            <td style="padding:.6rem .75rem;text-align:center">
                                @if($pmt->status === 'completed')
                                    <span style="background:#dcfce7;color:#166534;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                                        <i class="fas fa-check"></i> Paid
                                    </span>
                                @else
                                    <span style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem">{{ ucfirst($pmt->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @else
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:1rem;color:#854d0e;font-size:.9rem">
                    <i class="fas fa-hourglass-half"></i>
                    <strong>No Xendit payment received yet.</strong>
                    Payment will automatically appear here once the client completes the Xendit payment online.
                    If the client has paid but nothing shows here, the webhook may have been delayed —
                    you can manually update the Overall Payment Status above.
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(in_array($booking->payment_method, ['cash', 'installment']))
        {{-- ── CASH PAYMENT MANAGEMENT ────────────────────────────── --}}
        <div class="card mb-4" style="border:2px solid #86efac">
            <div class="card-header" style="background:#f0fdf4">
                <h4 style="color:#166534">
                    @if($booking->payment_method === 'installment')
                        <i class="fas fa-calendar-alt"></i> Installment / Payment Terms
                    @else
                        <i class="fas fa-money-bill-wave"></i> Cash / Office Payment
                    @endif
                </h4>
            </div>
            <div class="card-body">

                {{-- Overall payment status update --}}
                <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" class="inline-form mb-4">
                    @csrf @method('PATCH')
                    <label style="font-weight:600;margin-right:.5rem">Overall Payment Status:</label>
                    <select name="payment_status" class="form-control" style="max-width:180px">
                        @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partially Paid', 'paid' => 'Fully Paid'] as $val => $label)
                            <option value="{{ $val }}" {{ $booking->payment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>

                @if($booking->downpayment_amount > 0)
                <div style="background:#fefce8;border:1px solid #fde047;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1rem;font-size:.9rem">
                    <i class="fas fa-exclamation-circle" style="color:#ca8a04"></i>
                    <strong>Down Payment:</strong> ₱{{ number_format($booking->downpayment_amount, 2) }}
                </div>
                @endif

                {{-- Per-term schedule --}}
                @php $installTerms = $booking->installment_schedule ?? []; @endphp
                @if(count($installTerms))
                <h5 style="margin-bottom:.75rem">Installment Schedule</h5>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                    <thead>
                        <tr style="background:#f1f5f9;color:#475569;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em">
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e2e8f0">Term</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e2e8f0">Due Date</th>
                            <th style="padding:.5rem .75rem;text-align:right;border-bottom:2px solid #e2e8f0">Amount</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e2e8f0">Status</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e2e8f0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($installTerms as $term)
                        <tr style="border-bottom:1px solid #e2e8f0{{ $term['type'] === 'downpayment' ? ';background:#f0fdf4' : '' }}">
                            <td style="padding:.6rem .75rem">
                                @if($term['type'] === 'downpayment')
                                    <strong>Down Payment</strong>
                                @else
                                    Month {{ $term['term'] }}
                                @endif
                            </td>
                            <td style="padding:.6rem .75rem">{{ \Carbon\Carbon::parse($term['due_date'])->format('M d, Y') }}</td>
                            <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format($term['amount'], 2) }}</td>
                            <td style="padding:.6rem .75rem;text-align:center">
                                @if($term['status'] === 'paid')
                                    <span style="background:#dcfce7;color:#166534;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                                        <i class="fas fa-check"></i> Paid
                                        @if(!empty($term['paid_at']))
                                            <br><small>{{ \Carbon\Carbon::parse($term['paid_at'])->format('M d') }}</small>
                                        @endif
                                    </span>
                                @else
                                    <span style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem">Pending</span>
                                @endif
                            </td>
                            <td style="padding:.6rem .75rem;text-align:center">
                                <form action="{{ route('admin.bookings.installment-term', [$booking, $term['term']]) }}" method="POST" style="display:inline">
                                    @csrf @method('PATCH')
                                    @if($term['status'] === 'paid')
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="btn btn-xs btn-ghost" style="color:#6b7280">
                                            <i class="fas fa-undo"></i> Undo
                                        </button>
                                    @else
                                        <input type="hidden" name="status" value="paid">
                                        <button type="submit" class="btn btn-xs btn-primary" style="background:#16a34a;border-color:#16a34a">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700;background:#f8fafc">
                            <td colspan="2" style="padding:.6rem .75rem">Total</td>
                            <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format(collect($installTerms)->sum('amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
                @endif
            </div>
        </div>
        @endif

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
                    <div class="detail-item"><span>Payment Method</span>
                        <strong>
                            @if($booking->payment_method === 'installment') 📅 Installment
                            @elseif($booking->payment_method === 'cash') 🏢 Cash / Office
                            @else 💳 Xendit Online
                            @endif
                        </strong>
                    </div>
                    <div class="detail-item"><span>Payment Status</span>
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
        <!-- Slot Availability -->
        @if($schedule)
        @php
            $slotPct    = $schedule->available_seats > 0 ? ($schedule->booked_seats / $schedule->available_seats) * 100 : 100;
            $slotAvail  = max(0, $schedule->available_seats - $schedule->booked_seats);
            $slotColor  = $schedule->booked_seats > $schedule->available_seats ? '#dc2626'
                        : ($slotPct >= 80 ? '#f59e0b' : '#16a34a');
        @endphp
        <div class="slot-avail-card">
            <div class="slot-avail-head">
                <i class="fas fa-layer-group"></i> Slot Availability
                @if($booking->tour)
                <a href="{{ route('admin.tours.schedules.index', $booking->tour) }}"
                   style="margin-left:auto;color:#93c5fd;font-size:.8rem;font-weight:400;text-decoration:none">
                    <i class="fas fa-external-link-alt"></i> Manage Slots
                </a>
                @endif
            </div>
            <div class="slot-avail-body">
                <div class="slot-row">
                    <span style="color:#64748b">Total Seats</span>
                    <strong>{{ $schedule->available_seats }}</strong>
                </div>
                <div class="slot-row">
                    <span style="color:#64748b">Occupied</span>
                    <strong style="color:#f59e0b">{{ $schedule->booked_seats }}</strong>
                </div>
                <div class="slot-row">
                    <span style="color:#64748b">Available</span>
                    <strong style="color:{{ $slotAvail > 0 ? '#16a34a' : '#dc2626' }}">{{ $slotAvail }}</strong>
                </div>
                @if($schedule->notes)
                <div style="margin-top:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:.5rem .75rem;font-size:.8rem;color:#92400e">
                    <i class="fas fa-sticky-note"></i> {{ $schedule->notes }}
                </div>
                @endif
                <div class="seats-mini-bar" style="margin-top:.75rem">
                    <div class="seats-mini-fill" style="width:{{ min(100,$slotPct) }}%;background:{{ $slotColor }}"></div>
                </div>
                <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;text-align:right">
                    {{ round($slotPct) }}% occupied
                </div>
                @if($schedule->booked_seats > $schedule->available_seats)
                <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:.5rem;padding:.5rem .75rem;margin-top:.75rem;font-size:.8rem;color:#991b1b;font-weight:600">
                    <i class="fas fa-exclamation-triangle"></i> OVERBOOKED — {{ abs($slotAvail) }} over capacity
                </div>
                @elseif($slotAvail === 0)
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:.5rem .75rem;margin-top:.75rem;font-size:.8rem;color:#854d0e">
                    <i class="fas fa-exclamation-circle"></i> No seats remaining
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Tour Info -->
        @if($booking->tour)
        <div class="card mb-4">
            <div class="card-header"><h4>Tour</h4></div>
            <div class="card-body">
                @if($booking->tour->main_image)
                <img src="{{ cdn_url($booking->tour->main_image) }}"
                     alt="{{ $booking->tour->title }}" class="img-fluid rounded mb-3">
                @endif
                <h5>{{ $booking->tour->title }}</h5>
                @if($booking->tour->destination)
                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                @endif
                @if($booking->tour->duration_days)
                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }}D / {{ $booking->tour->duration_nights }}N</p>
                @endif
                <a href="{{ route('admin.tours.edit', $booking->tour) }}" class="btn btn-sm btn-outline">
                    Edit Tour
                </a>
            </div>
        </div>
        @endif

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
