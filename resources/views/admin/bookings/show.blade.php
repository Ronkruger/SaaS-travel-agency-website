@extends('layouts.admin')
@section('title', 'Subscription ' . $booking->booking_number)

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Subscriptions</a> / {{ $booking->booking_number }}
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-detail')
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

/* Mobile responsive for booking show */
@media (max-width: 768px) {
    .booking-admin-layout { grid-template-columns: 1fr !important; }
    .approve-bar { flex-direction: column; text-align: center; }
    .approve-bar__info { flex-direction: column; text-align: center; }
    .approve-bar__actions { justify-content: center; }
    .inline-form { flex-direction: column; }
    .inline-form .form-control { width: 100%; }
    .slot-avail-card { overflow-x: auto; }
}
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2>Subscription #{{ $booking->booking_number }}</h2>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
        {{-- PDF Actions --}}
        <a href="{{ route('admin.bookings.pdf.preview', $booking) }}" target="_blank"
           class="btn btn-outline" title="Preview PDF">
            <i class="fas fa-file-pdf" style="color:#dc2626"></i> Preview PDF
        </a>
        <a href="{{ route('admin.bookings.transfer', $booking) }}" class="btn btn-warning" title="Transfer to another tour">
            <i class="fas fa-exchange-alt"></i> Transfer
        </a>
        <a href="{{ route('admin.bookings.rebook', $booking) }}" class="btn btn-outline"
           title="Rebook — create new booking for this client on a different date"
           style="color:#7c3aed;border-color:#7c3aed">
            <i class="fas fa-redo"></i> Rebook
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
        @if($booking->payment_method === 'installment')
        <form action="{{ route('admin.bookings.send-payment-reminder', $booking) }}" method="POST" style="display:inline"
              onsubmit="return confirm('Send a payment reminder email to {{ $booking->contact_email }}?')">
            @csrf
            <button type="submit" class="btn btn-outline" title="Send next pending installment reminder"
                style="color:#ca8a04;border-color:#fde047">
                <i class="fas fa-bell"></i> Send Reminder
            </button>
        </form>
        @endif
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
            <div class="card-header"><h4>Update Subscription Status</h4></div>
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

        {{-- ── PAYMENT RECEIPTS (all methods) ────────────────────────────── --}}
        <div id="payments" class="card mb-4" style="border:2px solid #bfdbfe">
            <div class="card-header" style="background:#eff6ff;display:flex;align-items:center;justify-content:space-between">
                <h4 style="color:#1e40af;margin:0"><i class="fas fa-receipt"></i> Payment Receipts</h4>
                {{-- Manual override for all payment methods --}}
                <form action="{{ route('admin.bookings.payment-status', $booking) }}" method="POST" style="display:flex;align-items:center;gap:.5rem;margin:0">
                    @csrf @method('PATCH')
                    <label style="font-weight:600;font-size:.875rem;white-space:nowrap">Overall Status:</label>
                    <select name="payment_status" class="form-control" style="max-width:160px;font-size:.875rem">
                        @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partially Paid', 'paid' => 'Fully Paid'] as $val => $label)
                            <option value="{{ $val }}" {{ $booking->payment_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </form>
            </div>
            <div class="card-body">
                @php $allPayments = $booking->payments->sortByDesc('paid_at'); @endphp
                @if($allPayments->count())
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead>
                        <tr style="background:#dbeafe;color:#1e3a8a;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">For</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Amount</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Channel</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Xendit ID</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">System Txn</th>
                            <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #bfdbfe">Paid At</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #bfdbfe">Status</th>
                            <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #bfdbfe">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allPayments as $pmt)
                        @php $channel = strtoupper($pmt->gateway_response['payment_channel'] ?? $pmt->gateway_response['payment_method'] ?? $pmt->method); @endphp
                        <tr style="border-bottom:1px solid #e2e8f0">
                            <td style="padding:.6rem .75rem;font-weight:600">{{ $pmt->notes ?: '—' }}</td>
                            <td style="padding:.6rem .75rem;font-weight:700;color:#166534">₱{{ number_format($pmt->amount, 2) }}</td>
                            <td style="padding:.6rem .75rem">{{ $channel }}</td>
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.78rem;color:#374151">
                                {{ $pmt->gateway_transaction_id ?? '—' }}
                            </td>
                            <td style="padding:.6rem .75rem;font-family:monospace;font-size:.78rem;color:#6b7280">
                                {{ $pmt->transaction_id }}
                            </td>
                            <td style="padding:.6rem .75rem;white-space:nowrap">
                                {{ $pmt->paid_at ? $pmt->paid_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td style="padding:.6rem .75rem;text-align:center">
                                @if($pmt->status === 'completed')
                                    <span style="background:#dcfce7;color:#166534;padding:.2rem .65rem;border-radius:1rem;font-size:.78rem;font-weight:700">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span style="background:#fef9c3;color:#854d0e;padding:.2rem .65rem;border-radius:1rem;font-size:.78rem">{{ ucfirst($pmt->status) }}</span>
                                @endif
                            </td>
                            <td style="padding:.6rem .75rem;text-align:center;white-space:nowrap">
                                <a href="{{ route('admin.payments.receipt.preview', $pmt) }}" target="_blank" title="View Receipt" style="color:#2563eb;margin-right:.4rem"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.payments.receipt.download', $pmt) }}" title="Download Receipt" style="color:#2563eb"><i class="fas fa-download"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @else
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:1rem;color:#854d0e;font-size:.875rem">
                    <i class="fas fa-hourglass-half"></i>
                    <strong>No payments recorded yet.</strong>
                    @if($booking->payment_method === 'xendit' || $booking->payment_method === 'installment')
                        Payments will appear here automatically once processed via Xendit.
                    @else
                        Use "Mark Paid" below to record cash payments.
                    @endif
                </div>
                @endif
            </div>
        </div>

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
                                        @if(!empty($term['custom_amount']) && $term['custom_amount'] != $term['amount'])
                                            <br><small style="color:#2563eb">₱{{ number_format($term['custom_amount'], 2) }} paid</small>
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
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:.35rem">
                                            <button type="submit" class="btn btn-xs btn-primary" style="background:#16a34a;border-color:#16a34a;white-space:nowrap">
                                                <i class="fas fa-check"></i> Mark Paid
                                            </button>
                                            <div style="display:flex;align-items:center;gap:.3rem">
                                                <input type="number" name="custom_amount" min="1" step="0.01"
                                                    placeholder="Custom ₱"
                                                    style="width:110px;padding:.25rem .4rem;font-size:.75rem;border:1px solid #d1d5db;border-radius:.35rem;text-align:right"
                                                    title="Leave blank to use scheduled amount ₱{{ number_format($term['amount'], 2) }}">
                                            </div>
                                        </div>
                                    @endif
                                </form>
                                @if(($term['status'] ?? '') !== 'paid')
                                @php $termOrdinal = $term['term']; @endphp
                                <form action="{{ route('admin.bookings.send-payment-reminder', $booking) }}" method="POST" style="display:inline;margin-top:.3rem"
                                      onsubmit="return confirm('Send payment reminder for Term {{ $termOrdinal }} to {{ $booking->contact_email }}?')"
                                      >
                                    @csrf
                                    <input type="hidden" name="term" value="{{ $term['term'] }}">
                                    <button type="submit" class="btn btn-xs btn-ghost" style="color:#ca8a04;white-space:nowrap;margin-top:.25rem" title="Send reminder email for this term">
                                        <i class="fas fa-bell"></i> Remind
                                    </button>
                                </form>
                                @if(!empty($term['xendit_invoice_id']))
                                <form action="{{ route('admin.bookings.resync-xendit', [$booking, $term['term']]) }}" method="POST" style="display:inline;margin-top:.3rem"
                                      onsubmit="return confirm('Fetch this term\'s payment status from Xendit and mark as paid if complete?')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-ghost" style="color:#7c3aed;white-space:nowrap;margin-top:.25rem" title="Re-check payment status from Xendit (use when webhook failed)">
                                        <i class="fas fa-sync-alt"></i> Resync
                                    </button>
                                </form>
                                @endif
                                @endif
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

        {{-- ── 2ND PAYMENT STATUS (only show when not fully paid) ── --}}
        @if($booking->payment_status !== 'paid')
        <div class="card mb-4" style="border:2px solid #c4b5fd">
            <div class="card-header" style="background:#f5f3ff">
                <h4 style="color:#5b21b6"><i class="fas fa-file-invoice-dollar"></i> 2nd Payment Status</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.bookings.second-payment-status', $booking) }}" method="POST" id="secondPaymentForm">
                    @csrf @method('PATCH')

                    @php
                        $presetOptions = [
                            ''                    => '— Select Status —',
                            'Confirmed Start' => 'Confirmed Start',
                            'Travel Fund'         => 'Travel Fund',
                            'Float'               => 'Float',
                            'Floating'            => 'Floating',
                            'Pending'             => 'Pending',
                            'Pending Refund'      => 'Pending Refund',
                            'Refund'              => 'Refund',
                            'Refund, But Working to TF' => 'Refund, But Working to TF',
                            'Refund But Working On It'  => 'Refund But Working On It',
                            'No BC'               => 'No BC',
                            '__custom__'          => '✏️ Custom (type your own)',
                        ];
                        $currentVal = $booking->second_payment_status;
                        $isCustom = $currentVal && !array_key_exists($currentVal, $presetOptions);
                    @endphp

                    <div style="display:flex;gap:.75rem;align-items:flex-start;flex-wrap:wrap">
                        <div style="flex:1;min-width:200px">
                            <select id="secondPaymentSelect" class="form-control" style="margin-bottom:.5rem"
                                    onchange="toggleCustomField(this)">
                                @foreach($presetOptions as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ (!$isCustom && $currentVal === $val) ? 'selected' : '' }}
                                        {{ ($isCustom && $val === '__custom__') ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="text" id="secondPaymentCustom" name="second_payment_status"
                                   class="form-control" placeholder="Type custom status..."
                                   value="{{ $isCustom ? $currentVal : ($currentVal ?? '') }}"
                                   style="display:{{ $isCustom ? 'block' : 'none' }};margin-top:.5rem">
                        </div>

                        <button type="submit" class="btn btn-primary" style="white-space:nowrap">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>

                    @if($currentVal)
                    <div style="margin-top:.75rem;padding:.5rem .75rem;border-radius:.5rem;font-size:.9rem;
                        {{ in_array($currentVal, ['Confirmed Start']) ? 'background:#dcfce7;color:#166534;border:1px solid #86efac' :
                           (in_array($currentVal, ['Pending', 'Floating', 'Float', 'Pending Refund']) ? 'background:#fef9c3;color:#854d0e;border:1px solid #fde047' :
                           (str_contains(strtolower($currentVal), 'refund') ? 'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5' :
                           'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe')) }}">
                        <strong>Current:</strong> {{ $currentVal }}
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <script>
        function toggleCustomField(select) {
            var customInput = document.getElementById('secondPaymentCustom');
            if (select.value === '__custom__') {
                customInput.style.display = 'block';
                customInput.value = '';
                customInput.focus();
            } else {
                customInput.style.display = 'none';
                customInput.value = select.value;
            }
        }
        document.getElementById('secondPaymentForm').addEventListener('submit', function() {
            var select = document.getElementById('secondPaymentSelect');
            var customInput = document.getElementById('secondPaymentCustom');
            if (select.value !== '__custom__') {
                customInput.value = select.value;
            }
        });
        </script>
        @endif

        <!-- Subscription Info -->
        <div class="card mb-4">
            <div class="card-header"><h4>Subscription Information</h4></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span>Sub #</span><strong>{{ $booking->booking_number }}</strong></div>
                    <div class="detail-item"><span>Start Date</span><strong>{{ $booking->tour_date->format('M d, Y') }}</strong></div>
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
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem">
                    <div class="detail-item" style="min-width:0"><span>Name: </span><strong style="word-break:break-word">{{ $booking->contact_name }}</strong></div>
                    <div class="detail-item" style="min-width:0;grid-column:span 2"><span>Email: </span><strong style="word-break:break-all">{{ $booking->contact_email }}</strong></div>
                    <div class="detail-item" style="min-width:0"><span>Phone: </span><strong style="word-break:break-word">{{ $booking->contact_phone }}</strong></div>
                    @if($booking->user_id)
                    <div class="detail-item">
                        <span>Account</span>
                        <a href="{{ route('admin.users.show', $booking->user_id) }}" class="btn btn-xs btn-outline">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── TRAVEL FUND (cancelled bookings with a linked user) ── --}}
        @if(in_array($booking->status, ['cancelled', 'refunded']) && $booking->user_id)
        <div class="card mb-4" style="border:2px solid #c4b5fd">
            <div class="card-header" style="background:#faf5ff">
                <h4 style="color:#7c3aed"><i class="fas fa-wallet"></i> Credit Balance</h4>
                @php
                    $existingFund = \App\Models\TravelFund::where('booking_id', $booking->id)->where('type', 'credit')->exists();
                @endphp
                @if($existingFund)
                    <span style="background:#dcfce7;color:#166534;padding:.25rem .75rem;border-radius:1rem;font-size:.8rem;font-weight:600">
                        <i class="fas fa-check"></i> Already Added
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if($existingFund)
                    <p style="font-size:.9rem;color:#6b7280;margin:0">
                        A credit balance credit from this booking has already been issued to the client.
                        <a href="{{ route('admin.users.show', $booking->user_id) }}" style="color:#7c3aed">View client funds &rarr;</a>
                    </p>
                @else
                    <p style="font-size:.875rem;color:#6b7280;margin:0 0 1rem">
                        Move the client's paid amount to their Credit Balance.
                        A notification email will be sent to <strong>{{ $booking->contact_email }}</strong>.
                    </p>
                    <form action="{{ route('admin.bookings.travel-fund', $booking) }}" method="POST"
                          onsubmit="return confirm('Move ₱' + document.getElementById('tfAmount').value + ' to this client\'s Travel Fund and notify them by email?')">
                        @csrf
                        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
                            <div style="flex:1;min-width:140px">
                                <label style="font-size:.8rem;color:#6b7280;font-weight:600">Amount (₱)</label>
                                <input type="number" id="tfAmount" name="amount" step="0.01" min="1"
                                       class="form-control" style="margin-top:.25rem"
                                       value="{{ number_format($booking->total_amount, 2, '.', '') }}"
                                       placeholder="0.00" required>
                            </div>
                            <div style="flex:2;min-width:200px">
                                <label style="font-size:.8rem;color:#6b7280;font-weight:600">Note</label>
                                <input type="text" name="description" class="form-control" style="margin-top:.25rem"
                                       value="Credits from cancelled subscription {{ $booking->booking_number }}"
                                       maxlength="255">
                            </div>
                            <button type="submit" class="btn btn-primary"
                                    style="background:#7c3aed;border-color:#7c3aed;white-space:nowrap">
                                <i class="fas fa-wallet"></i> Move to Credit Balance
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        @endif
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
                    <span style="color:#64748b">Total Licenses</span>
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
                    <i class="fas fa-exclamation-circle"></i> No licenses remaining
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Plan Info -->
        @if($booking->tour)
        <div class="card mb-4">
            <div class="card-header"><h4>Plan</h4></div>
            <div class="card-body">
                @if($booking->tour->main_image)
                <img src="{{ cdn_url($booking->tour->main_image) }}"
                     alt="{{ $booking->tour->title }}" class="img-fluid rounded mb-3"
                     style="max-height:200px;width:100%;object-fit:cover">
                @endif
                <h5>{{ $booking->tour->title }}</h5>
                @if($booking->tour->destination)
                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                @endif
                @if($booking->tour->duration_days)
                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }}D / {{ $booking->tour->duration_nights }}N</p>
                @endif
                <a href="{{ route('admin.tours.edit', $booking->tour) }}" class="btn btn-sm btn-outline">
                    Edit Plan
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

{{-- ── INTERNAL NOTES ─────────────────────────────────────────────── --}}
<div class="card mt-4">
    <div class="card-header" style="background:#fafafa;display:flex;align-items:center;justify-content:space-between">
        <h4 style="margin:0"><i class="fas fa-sticky-note" style="color:#f59e0b"></i> Internal Notes
            @if($booking->notes->count())
                <span style="background:#f59e0b;color:#fff;font-size:.72rem;padding:.1rem .5rem;border-radius:1rem;margin-left:.4rem;font-weight:700">
                    {{ $booking->notes->count() }}
                </span>
            @endif
        </h4>
        <small style="color:#94a3b8">Visible to admins only</small>
    </div>
    <div class="card-body">
        {{-- Add note form --}}
        <form action="{{ route('admin.bookings.notes.store', $booking) }}" method="POST"
              style="display:flex;gap:.75rem;align-items:flex-start;margin-bottom:1.25rem">
            @csrf
            <textarea name="note" rows="2" class="form-control" placeholder="Add an internal note…" required
                      style="flex:1;resize:vertical;min-height:60px;max-height:160px"></textarea>
            <div style="display:flex;flex-direction:column;gap:.4rem">
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;padding:.5rem 1rem">
                    <i class="fas fa-plus"></i> Add Note
                </button>
                <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#6b7280;cursor:pointer">
                    <input type="checkbox" name="is_pinned" value="1"> Pin
                </label>
            </div>
        </form>

        {{-- Notes list --}}
        @forelse($booking->notes as $note)
        <div style="border:1px solid {{ $note->is_pinned ? '#fde68a' : '#e2e8f0' }};border-radius:.6rem;padding:.75rem 1rem;margin-bottom:.65rem;background:{{ $note->is_pinned ? '#fffbeb' : '#fff' }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem">
                <div style="flex:1">
                    @if($note->is_pinned)
                        <span style="color:#d97706;font-size:.72rem;font-weight:700;margin-right:.4rem">
                            <i class="fas fa-thumbtack"></i> PINNED
                        </span>
                    @endif
                    <span style="font-size:.9rem;color:#1e293b;white-space:pre-wrap">{{ $note->note }}</span>
                </div>
                <div style="display:flex;gap:.4rem;flex-shrink:0">
                    <form action="{{ route('admin.bookings.notes.pin', [$booking, $note]) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-xs btn-ghost"
                                title="{{ $note->is_pinned ? 'Unpin' : 'Pin' }}"
                                style="color:{{ $note->is_pinned ? '#d97706' : '#94a3b8' }}">
                            <i class="fas fa-thumbtack"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.bookings.notes.destroy', [$booking, $note]) }}" method="POST"
                          onsubmit="return confirm('Delete this note?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-ghost" title="Delete" style="color:#ef4444">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top:.35rem;font-size:.75rem;color:#94a3b8">
                {{ $note->adminUser?->name ?? 'Admin' }} &bull; {{ $note->created_at->format('M d, Y H:i') }}
            </div>
        </div>
        @empty
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:1rem 0;margin:0">
                No notes yet. Use notes to track follow-ups, client requests, or special arrangements.
            </p>
        @endforelse
    </div>
</div>
@push('scripts')
<script>
// Scroll to #payments section when notification deep-links here
if (window.location.hash === '#payments') {
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('payments');
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.style.transition = 'box-shadow .4s';
                el.style.boxShadow = '0 0 0 3px #3b82f6';
                setTimeout(function () { el.style.boxShadow = ''; }, 2000);
            }, 200);
        }
    });
}
</script>
@endpush
@endsection
