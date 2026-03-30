@extends('layouts.admin')
@section('title', 'Booking ' . $booking->booking_number)

@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}">Bookings</a> / {{ $booking->booking_number }}
@endsection

@section('content')
<div class="page-title-row">
    <h2>Booking #{{ $booking->booking_number }}</h2>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

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
                @php $schedule = $booking->installment_schedule ?? []; @endphp
                @if(count($schedule))
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
                        @foreach($schedule as $term)
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
                            <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format(collect($schedule)->sum('amount'), 2) }}</td>
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
        <!-- Tour Info -->
        <div class="card mb-4">
            <div class="card-header"><h4>Tour</h4></div>
            <div class="card-body">
                <img src="{{ cdn_url($booking->tour->main_image) }}"
                     alt="{{ $booking->tour->title }}" class="img-fluid rounded mb-3">
                <h5>{{ $booking->tour->title }}</h5>
                <p><i class="fas fa-map-marker-alt"></i> {{ $booking->tour->destination }}, {{ $booking->tour->country }}</p>
                <p><i class="fas fa-clock"></i> {{ $booking->tour->duration_days }}D / {{ $booking->tour->duration_nights }}N</p>
                <a href="{{ route('admin.tours.edit', $booking->tour) }}" class="btn btn-sm btn-outline">
                    Edit Tour
                </a>
            </div>
        </div>

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
