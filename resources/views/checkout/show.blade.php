@extends('layouts.app')
@section('title', 'Checkout — ' . $booking->booking_number)

@section('content')
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-credit-card"></i> Secure Checkout</h1>
        <p>Complete your payment for {{ $booking->tour->title }}</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Progress Steps -->
        <div class="checkout-steps">
            <div class="checkout-step completed"><i class="fas fa-check"></i> Booking Details</div>
            <div class="checkout-step-divider"><i class="fas fa-chevron-right"></i></div>
            <div class="checkout-step active"><i class="fas fa-credit-card"></i> Payment</div>
            <div class="checkout-step-divider"><i class="fas fa-chevron-right"></i></div>
            <div class="checkout-step"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <div class="checkout-layout">
            <!-- Payment Form -->
            <div class="checkout-form">

                @if(in_array($booking->payment_method, ['cash', 'installment']))
                {{-- ── CASH / INSTALLMENT VIEW ────────────────────────────── --}}
                <div class="card mb-4">
                    <div class="card-header" style="background:#f0fdf4">
                        <h4>
                            @if($booking->payment_method === 'installment')
                                <i class="fas fa-calendar-alt" style="color:#7c3aed"></i> Installment Payment Schedule
                            @else
                                <i class="fas fa-money-bill-wave" style="color:#16a34a"></i> Full Cash Payment
                            @endif
                        </h4>
                    </div>
                    <div class="card-body">

                        @if($booking->payment_method === 'installment')
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem">
                            <p style="margin:0;font-size:.925rem">
                                <i class="fas fa-check-circle" style="color:#16a34a"></i>
                                <strong>Booking confirmed!</strong> Please follow the payment schedule below.
                                Our team will contact you to coordinate payments.
                            </p>
                        </div>
                        @else
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem">
                            <p style="margin:0;font-size:.925rem">
                                <i class="fas fa-check-circle" style="color:#16a34a"></i>
                                <strong>Booking confirmed!</strong> Please follow the payment schedule below.
                                Our team will contact you to coordinate payments.
                            </p>
                        </div>
                        @endif

                        @if($booking->downpayment_amount > 0)
                        <div style="background:#fefce8;border:1px solid #fde047;border-radius:.75rem;padding:.875rem 1.125rem;margin-bottom:1.25rem">
                            <strong><i class="fas fa-exclamation-circle" style="color:#ca8a04"></i> Down Payment Required</strong>
                            <p style="margin:.4rem 0 0;font-size:.9rem">
                                Please send <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong>
                                within 7 days to secure your slot.
                            </p>
                        </div>
                        @endif

                        {{-- Installment schedule table --}}
                        @php $schedule = $booking->installment_schedule ?? []; @endphp
                        @if(count($schedule))
                        <h5 style="margin-bottom:.75rem">Payment Schedule</h5>

                        @if(session('success'))
                        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1rem;font-size:.9rem">
                            <i class="fas fa-check-circle" style="color:#16a34a"></i> {{ session('success') }}
                        </div>
                        @endif
                        @if(session('info'))
                        <div style="background:#dbeafe;border:1px solid #93c5fd;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1rem;font-size:.9rem">
                            <i class="fas fa-info-circle" style="color:#1d4ed8"></i> {{ session('info') }}
                        </div>
                        @endif
                        @error('error')
                        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1rem;font-size:.9rem">
                            <i class="fas fa-exclamation-circle" style="color:#dc2626"></i> {{ $message }}
                        </div>
                        @enderror

                        <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                            <thead>
                                <tr style="background:#f1f5f9;color:#475569;font-size:.82rem;text-transform:uppercase;letter-spacing:.04em">
                                    <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e2e8f0">Term</th>
                                    <th style="padding:.5rem .75rem;text-align:left;border-bottom:2px solid #e2e8f0">Due Date</th>
                                    <th style="padding:.5rem .75rem;text-align:right;border-bottom:2px solid #e2e8f0">Amount</th>
                                    <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e2e8f0">Status</th>
                                    @if($booking->payment_method === 'installment')
                                    <th style="padding:.5rem .75rem;text-align:center;border-bottom:2px solid #e2e8f0">Action</th>
                                    @endif
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
                                            </span>
                                        @else
                                            <span style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem">Pending</span>
                                        @endif
                                    </td>
                                    @if($booking->payment_method === 'installment')
                                    <td style="padding:.5rem .75rem;text-align:center">
                                        @if($term['status'] === 'paid')
                                            <span style="color:#86efac;font-size:.85rem"><i class="fas fa-check-circle"></i></span>
                                        @else
                                            <form method="POST" action="{{ route('checkout.installment.pay', [$booking, $term['term']]) }}" style="display:inline">
                                                @csrf
                                                <div style="display:flex;flex-direction:column;align-items:center;gap:.3rem">
                                                    <button type="submit"
                                                        style="background:#1e3a5f;color:#fff;border:none;border-radius:.4rem;padding:.3rem .75rem;font-size:.82rem;cursor:pointer;white-space:nowrap"
                                                        onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing…';this.form.submit()">
                                                        <i class="fas fa-credit-card"></i>
                                                        Pay ₱{{ number_format($term['amount'], 0) }}
                                                    </button>
                                                    <input type="number" name="custom_amount" min="1" step="1"
                                                        placeholder="Or custom ₱"
                                                        style="width:120px;padding:.25rem .4rem;font-size:.75rem;border:1px solid #cbd5e1;border-radius:.35rem;text-align:right"
                                                        title="Enter a different amount (e.g. ₱30,000 covers 2 months)">
                                                </div>
                                            </form>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:700;background:#f8fafc">
                                    <td colspan="2" style="padding:.6rem .75rem">Total</td>
                                    <td style="padding:.6rem .75rem;text-align:right">₱{{ number_format(collect($schedule)->sum('amount'), 2) }}</td>
                                    <td></td>
                                    @if($booking->payment_method === 'installment')<td></td>@endif
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                        @endif

                        {{-- Pay remaining balance block --}}
                        @php
                            $pendingTerms = collect($schedule)->where('status', '!=', 'paid');
                            $remainingBalance = $pendingTerms->sum('amount');
                        @endphp
                        @if($booking->payment_method === 'installment' && $remainingBalance > 0)
                        <div style="margin-top:1.25rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:.75rem;padding:1rem 1.25rem">
                            <strong style="font-size:.9rem"><i class="fas fa-wallet" style="color:#0284c7"></i> Pay Remaining Balance</strong>
                            <p style="margin:.35rem 0 .75rem;font-size:.85rem;color:#374151">
                                Outstanding: <strong>₱{{ number_format($remainingBalance, 2) }}</strong> across {{ $pendingTerms->count() }} pending term(s).
                                Enter a custom amount below — it will automatically cover as many months as possible.
                            </p>
                            <form method="POST" action="{{ route('checkout.pay-balance', $booking) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                                @csrf
                                <div style="display:flex;align-items:center;border:1px solid #93c5fd;border-radius:.5rem;overflow:hidden;background:#fff">
                                    <span style="padding:.45rem .75rem;background:#e0f2fe;color:#0369a1;font-weight:700;font-size:.9rem;border-right:1px solid #93c5fd">₱</span>
                                    <input type="number" name="custom_amount" min="1" step="1"
                                        placeholder="{{ number_format($remainingBalance, 0) }}"
                                        style="padding:.45rem .75rem;border:none;outline:none;font-size:.9rem;width:150px">
                                </div>
                                <button type="submit"
                                    style="background:#0284c7;color:#fff;border:none;border-radius:.5rem;padding:.5rem 1.25rem;font-size:.875rem;font-weight:600;cursor:pointer"
                                    onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing…';this.form.submit()">
                                    <i class="fas fa-credit-card"></i> Pay via Xendit
                                </button>
                            </form>
                            <p style="margin:.5rem 0 0;font-size:.75rem;color:#6b7280">Leave blank to pay the full remaining balance of ₱{{ number_format($remainingBalance, 2) }}</p>
                        </div>
                        @endif

                        <div style="margin-top:1.5rem;padding:1rem;background:#f8fafc;border-radius:.75rem;font-size:.875rem;color:#374151">
                            <strong><i class="fas fa-university"></i> Bank Transfer Details</strong><br>
                            <span class="text-muted">Our team will send you bank account details via email at <strong>{{ $booking->contact_email }}</strong>.</span>
                        </div>

                        <a href="{{ route('booking.show', $booking) }}" class="btn btn-primary btn-lg btn-block mt-4">
                            <i class="fas fa-check-circle"></i> View My Booking
                        </a>
                    </div>
                </div>

                @else
                {{-- ── XENDIT ONLINE PAYMENT VIEW ──────────────────────────── --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-credit-card"></i> Secure Payment via Xendit</h4>
                    </div>
                    <div class="card-body text-center py-4">
                        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:1rem; margin-bottom:1.5rem;">
                            <span class="badge-pm"><i class="fas fa-credit-card"></i> Credit Card</span>
                            <span class="badge-pm"><i class="fas fa-university"></i> BPI / BDO</span>
                            <span class="badge-pm"><img src="https://www.gcash.com/favicon.ico" style="width:14px;height:14px"> GCash</span>
                            <span class="badge-pm"><i class="fas fa-wallet"></i> GrabPay</span>
                            <span class="badge-pm"><i class="fas fa-mobile-alt"></i> Maya</span>
                        </div>
                        <p class="text-muted mb-4">
                            You will be redirected to Xendit's secure payment page to complete your booking.<br>
                            Choose any payment method you prefer there.
                        </p>
                        <form action="{{ route('checkout.process', $booking) }}" method="POST">
                            @csrf
                            @error('error')
                                <div class="alert alert-danger mb-3">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                            <div class="secure-note mb-3">
                                <i class="fas fa-shield-alt text-green"></i>
                                Encrypted &amp; secured by Xendit
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="payBtn"
                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Redirecting to Xendit...'; this.form.submit();">
                                <i class="fas fa-lock"></i> Pay ₱{{ number_format($booking->total_amount, 2) }} via Xendit
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <!-- Order Summary -->
            <aside class="checkout-summary">
                <div class="card">
                    <div class="card-header"><h4><i class="fas fa-receipt"></i> Order Summary</h4></div>
                    <div class="card-body">
                        <h5>{{ $booking->tour->title }}</h5>
                        <p class="text-muted">
                            <i class="fas fa-calendar"></i> {{ $booking->tour_date->format('M d, Y') }}
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-users"></i> {{ $booking->total_guests }} guest(s)
                        </p>

                        <div class="price-breakdown mt-3">
                            <div class="price-row">
                                <span>{{ $booking->adults }} Adult(s)</span>
                                <span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span>
                            </div>
                            @if($booking->children > 0)
                                <div class="price-row">
                                    <span>{{ $booking->children }} Child(ren)</span>
                                    <span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span>
                                </div>
                            @endif
                            <div class="price-row">
                                <span>Travel Tax</span>
                                <span>₱{{ number_format($booking->tax_amount, 2) }}</span>
                            </div>
                            <div class="price-row price-row--total">
                                <strong>Total Due</strong>
                                <strong class="text-primary">₱{{ number_format($booking->total_amount, 2) }}</strong>
                            </div>
                        </div>

                        <div class="checkout-guarantees mt-3">
                            <div><i class="fas fa-check text-green"></i> Free cancellation (48h)</div>
                            <div><i class="fas fa-check text-green"></i> Instant confirmation</div>
                            <div><i class="fas fa-check text-green"></i> Secure payment</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Payment method switching
document.querySelectorAll('input[name=payment_method]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method-option').forEach(el => el.classList.remove('active'));
        this.closest('.payment-method-option').classList.add('active');

        const method = this.value;
        document.getElementById('cardDetails').style.display    = ['credit_card', 'debit_card'].includes(method) ? 'block' : 'none';
        document.getElementById('paypalDetails').style.display  = method === 'paypal' ? 'block' : 'none';
        document.getElementById('bankDetails').style.display    = method === 'bank_transfer' ? 'block' : 'none';
    });
});

// Card number formatting
document.getElementById('cardNumber')?.addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
});

// Expiry formatting
document.getElementById('cardExpiry')?.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.slice(0, 2) + ' / ' + v.slice(2);
    this.value = v.slice(0, 7);
});

// Prevent form double submit
document.getElementById('paymentForm').addEventListener('submit', function() {
    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});
</script>
@endpush
