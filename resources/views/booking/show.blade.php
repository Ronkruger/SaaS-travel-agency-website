@extends('layouts.app')
@section('title', 'Booking #' . $booking->booking_number)

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Booking Details</h1>
        <p>{{ $booking->booking_number }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="booking-detail-layout">
            <div class="booking-detail-main">
                <!-- Booking Status -->
                <div class="card mb-4">
                    <div class="card-body status-card">
                        <div class="status-info">
                            <div class="status-dot status-dot--{{ $booking->status }}"></div>
                            <div>
                                <span class="status-badge status-{{ $booking->status }} status-lg">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <p class="mt-1 text-muted">Booked on {{ $booking->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="payment-info">
                            <span class="payment-badge payment-{{ $booking->payment_status }}">
                                Payment: {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tour Info -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-map-marked-alt"></i> Tour Information</h4></div>
                    <div class="card-body">
                        <div class="tour-detail-row">
                            <img src="{{ cdn_url($booking->tour->main_image) }}"
                                 alt="{{ $booking->tour->title }}" class="tour-thumb-sm">
                            <div>
                                <h4><a href="{{ route('tours.show', $booking->tour->slug) }}">
                                    {{ $booking->tour->title }}
                                </a></h4>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }} Days / {{ $booking->tour->duration_nights }} Nights</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-clipboard-list"></i> Booking Details</h4></div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Booking Number</span>
                                <strong>{{ $booking->booking_number }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Tour Date</span>
                                <strong>{{ $booking->tour_date->format('M d, Y') }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Adults</span>
                                <strong>{{ $booking->adults }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Children</span>
                                <strong>{{ $booking->children }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Infants</span>
                                <strong>{{ $booking->infants }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Total Guests</span>
                                <strong>{{ $booking->total_guests }}</strong>
                            </div>
                        </div>

                        @if($booking->special_requests)
                            <div class="mt-3">
                                <strong>Special Requests:</strong>
                                <p class="mt-1 text-muted">{{ $booking->special_requests }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card mb-4">
                    <div class="card-header"><h4><i class="fas fa-user"></i> Contact Information</h4></div>
                    <div class="card-body">
                        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem">
                            <div class="detail-item" style="min-width:0">
                                <span>Name</span>
                                <strong style="word-break:break-word">{{ $booking->contact_name }}</strong>
                            </div>
                            <div class="detail-item" style="min-width:0">
                                <span>Email</span>
                                <strong style="word-break:break-all">{{ $booking->contact_email }}</strong>
                            </div>
                            <div class="detail-item" style="min-width:0">
                                <span>Phone</span>
                                <strong style="word-break:break-word">{{ $booking->contact_phone }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if($booking->payment_method === 'installment' && !empty($booking->installment_schedule))
                @php
                    $sched        = $booking->installment_schedule;
                    $paidItems    = collect($sched)->where('status', 'paid');
                    $pendingItems = collect($sched)->where('status', '!=', 'paid');
                    $remaining    = $pendingItems->sum('amount');
                    $nextDue      = $pendingItems->first();
                @endphp
                <!-- Installment Payment Schedule -->
                <div class="card mb-4">
                    <div class="card-header" style="background:#faf5ff;border-bottom:1px solid #e9d5ff">
                        <h4 style="color:#6d28d9"><i class="fas fa-calendar-alt"></i> Installment Payment Schedule</h4>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                        <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                        @endif
                        @if(session('info'))
                        <div class="alert alert-info mb-3"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
                        @endif
                        @error('error')
                        <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                        @enderror

                        {{-- Summary bar --}}
                        <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem">
                            <div style="flex:1;min-width:120px;background:#f0fdf4;border:1px solid #86efac;border-radius:.625rem;padding:.75rem 1rem;text-align:center">
                                <div style="font-size:1.2rem;font-weight:800;color:#166534">{{ $paidItems->count() }}/{{ count($sched) }}</div>
                                <div style="font-size:.78rem;color:#15803d">Terms Paid</div>
                            </div>
                            <div style="flex:1;min-width:120px;background:#faf5ff;border:1px solid #d8b4fe;border-radius:.625rem;padding:.75rem 1rem;text-align:center">
                                <div style="font-size:1.2rem;font-weight:800;color:#6d28d9">₱{{ number_format($remaining, 2) }}</div>
                                <div style="font-size:.78rem;color:#7c3aed">Remaining Balance</div>
                            </div>
                            @if($nextDue)
                            <div style="flex:1;min-width:120px;background:#fffbeb;border:1px solid #fcd34d;border-radius:.625rem;padding:.75rem 1rem;text-align:center">
                                <div style="font-size:1rem;font-weight:700;color:#92400e">{{ \Carbon\Carbon::parse($nextDue['due_date'])->format('M d, Y') }}</div>
                                <div style="font-size:.78rem;color:#b45309">Next Due Date</div>
                            </div>
                            @endif
                        </div>

                        {{-- Schedule table --}}
                        <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                            <thead>
                                <tr style="background:#f5f3ff;color:#6d28d9;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em">
                                    <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e9d5ff">Term</th>
                                    <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e9d5ff">Due Date</th>
                                    <th style="padding:.5rem .75rem;text-align:right;border-bottom:2px solid #e9d5ff">Amount</th>
                                    <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e9d5ff">Status</th>
                                    <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e9d5ff">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($sched as $term)
                            <tr style="border-bottom:1px solid #f3e8ff{{ $term['status'] === 'paid' ? ';background:#f0fdf4' : ($term === $nextDue ? ';background:#fffbeb' : '') }}">
                                <td style="padding:.6rem .75rem">
                                    @if($term['type'] === 'downpayment') <strong>Down Payment</strong>
                                    @else Month {{ $term['term'] }} @endif
                                </td>
                                <td style="padding:.6rem .75rem">{{ \Carbon\Carbon::parse($term['due_date'])->format('M d, Y') }}</td>
                                <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format($term['amount'], 2) }}</td>
                                <td style="padding:.6rem .75rem;text-align:center">
                                    @if($term['status'] === 'paid')
                                        <span style="background:#dcfce7;color:#166534;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600"><i class="fas fa-check"></i> Paid</span>
                                        @if(!empty($term['paid_at']))
                                            <div style="font-size:.73rem;color:#6b7280;margin-top:.2rem">{{ \Carbon\Carbon::parse($term['paid_at'])->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem">Pending</span>
                                    @endif
                                </td>
                                <td style="padding:.5rem .75rem;text-align:center">
                                    @if($term['status'] !== 'paid' && $term === $nextDue)
                                    <form method="POST" action="{{ parse_url(route('checkout.installment.pay', [$booking, $term['term']]), PHP_URL_PATH) }}" class="pay-form" style="display:inline">
                                        @csrf
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:.3rem">
                                            <button type="submit" class="pay-submit-btn"
                                                style="background:#6d28d9;color:#fff;border:none;border-radius:.4rem;padding:.35rem .85rem;font-size:.82rem;cursor:pointer;white-space:nowrap;font-weight:600">
                                                <i class="fas fa-credit-card"></i> Pay ₱{{ number_format($term['amount'], 0) }}
                                            </button>
                                            <input type="number" name="custom_amount" min="1" step="1"
                                                placeholder="Or custom ₱"
                                                style="width:120px;padding:.25rem .4rem;font-size:.75rem;border:1px solid #d8b4fe;border-radius:.35rem;text-align:right">
                                        </div>
                                    </form>
                                    @elseif($term['status'] !== 'paid')
                                        <span style="font-size:.8rem;color:#94a3b8">—</span>
                                    @else
                                        <span style="color:#86efac;font-size:.85rem"><i class="fas fa-check-circle"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:700;background:#f5f3ff">
                                    <td colspan="2" style="padding:.6rem .75rem">Total</td>
                                    <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format(collect($sched)->sum('amount'), 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>

                        {{-- Pay remaining balance --}}
                        @if($remaining > 0 && $paidItems->count() > 0)
                        <div style="margin-top:1.25rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:.75rem;padding:1rem 1.25rem">
                            <strong style="font-size:.9rem"><i class="fas fa-wallet" style="color:#0284c7"></i> Pay Remaining Balance</strong>
                            <p style="margin:.35rem 0 .75rem;font-size:.85rem;color:#374151">
                                Outstanding: <strong>₱{{ number_format($remaining, 2) }}</strong> across {{ $pendingItems->count() }} pending term(s).
                            </p>
                            <form method="POST" action="{{ parse_url(route('checkout.pay-balance', $booking), PHP_URL_PATH) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center" class="pay-form">
                                @csrf
                                <div style="display:flex;align-items:center;border:1px solid #93c5fd;border-radius:.5rem;overflow:hidden;background:#fff">
                                    <span style="padding:.45rem .75rem;background:#e0f2fe;color:#0369a1;font-weight:700;font-size:.9rem;border-right:1px solid #93c5fd">₱</span>
                                    <input type="number" name="custom_amount" min="1" step="1"
                                        placeholder="{{ number_format($remaining, 0) }}"
                                        style="padding:.45rem .75rem;border:none;outline:none;font-size:.9rem;width:150px">
                                </div>
                                <button type="submit" class="pay-submit-btn"
                                    style="background:#0284c7;color:#fff;border:none;border-radius:.5rem;padding:.5rem 1.25rem;font-size:.875rem;font-weight:600;cursor:pointer">
                                    <i class="fas fa-credit-card"></i> Pay via Xendit
                                </button>
                            </form>
                            <p style="margin:.5rem 0 0;font-size:.75rem;color:#6b7280">Leave blank to pay the full remaining balance of ₱{{ number_format($remaining, 2) }}</p>
                        </div>
                        @endif

                    </div>
                </div>
                @endif
            </div>

            <!-- Price Summary -->
            <aside class="booking-detail-sidebar">
                <div class="card">
                    <div class="card-header"><h4><i class="fas fa-receipt"></i> Price Summary</h4></div>
                    <div class="card-body">
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>{{ $booking->adults }} Adult(s) × ₱{{ number_format($booking->price_per_adult, 2) }}</span>
                                <span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span>
                            </div>
                            @if($booking->children > 0)
                                <div class="price-row">
                                    <span>{{ $booking->children }} Child(ren) × ₱{{ number_format($booking->price_per_child, 2) }}</span>
                                    <span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Subtotal</span>
                                <span>₱{{ number_format($booking->subtotal, 2) }}</span>
                            </div>
                            @if($booking->discount_amount > 0)
                                <div class="price-row text-green">
                                    <span>Discount</span>
                                    <span>−₱{{ number_format($booking->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Tax (10%)</span>
                                <span>₱{{ number_format($booking->tax_amount, 2) }}</span>
                            </div>
                            <div class="price-row price-row--total">
                                <strong>Total</strong>
                                <strong>₱{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                        </div>

                        @if($booking->payment_status === 'unpaid')
                            <a href="{{ route('checkout.show', $booking) }}" class="btn btn-primary btn-block mt-3">
                                <i class="fas fa-credit-card"></i> Complete Payment
                            </a>
                        @endif

                        @if($booking->isConfirmed())
                            <a href="{{ route('booking.pdf.download', $booking) }}"
                               class="btn btn-outline-primary btn-block mt-2">
                                <i class="fas fa-file-pdf"></i> Download Confirmation PDF
                            </a>
                        @endif

                        @if($booking->isCancellable())
                            <form action="{{ route('booking.cancel', $booking) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block mt-2">
                                    <i class="fas fa-times-circle"></i> Cancel Booking
                                </button>
                            </form>
                        @endif

                        @if($booking->payment)
                            <div class="payment-details mt-3">
                                <strong>Payment Details</strong>
                                <p>Method: {{ ucfirst($booking->payment->method) }}</p>
                                <p>Transaction: {{ $booking->payment->transaction_id }}</p>
                                @if($booking->payment->paid_at)
                                    <p>Paid: {{ $booking->payment->paid_at->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($booking->status === 'completed' && !$booking->review)
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x text-yellow mb-2"></i>
                            <p>How was your tour?</p>
                            <a href="{{ route('tours.show', $booking->tour->slug) }}#tab-reviews"
                               class="btn btn-warning btn-block">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Duplicate-submit guard for installment pay buttons
document.querySelectorAll('.pay-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (form.dataset.submitting === '1') { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        var btn = form.querySelector('.pay-submit-btn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting…'; }
    });
    window.addEventListener('pageshow', function() {
        form.dataset.submitting = '0';
        var btn = form.querySelector('.pay-submit-btn');
        if (btn) { btn.disabled = false; }
    });
});
</script>
@endpush
