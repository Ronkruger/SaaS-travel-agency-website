@extends('layouts.app')

@section('title', 'Checkout — ' . ($itinerary->tour_name ?? 'Custom Tour'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/diy.css') }}">
<style>
.checkout-container { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
.checkout-card { background: #fff; border-radius: 1rem; box-shadow: 0 4px 24px rgba(0,0,0,.08); overflow: hidden; }
.checkout-header { background: linear-gradient(135deg, #0a2d74, #1e4fa3); padding: 2rem; color: #fff; }
.checkout-header h1 { font-size: 1.5rem; font-weight: 800; margin: 0 0 .25rem; }
.checkout-header p { opacity: .85; margin: 0; font-size: .9rem; }
.checkout-body { padding: 2rem; }
.checkout-summary { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 1.5rem; }
.checkout-line { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; font-size: .95rem; }
.checkout-line + .checkout-line { border-top: 1px solid #f1f5f9; }
.checkout-line.total { font-size: 1.15rem; font-weight: 800; color: #0a2d74; border-top: 2px solid #0a2d74; padding-top: .75rem; }
.checkout-line .label { color: #64748b; }
.payment-type-tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
.payment-type-tab { flex: 1; padding: .75rem; border: 2px solid #e2e8f0; border-radius: .75rem; text-align: center; cursor: pointer; transition: all .2s; background: #fff; font-weight: 600; }
.payment-type-tab.active { border-color: #0a2d74; background: #eff6ff; color: #0a2d74; }
.payment-type-tab:hover:not(.active) { border-color: #94a3b8; }
.pax-input { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: .75rem; }
.pax-input label { font-weight: 600; white-space: nowrap; }
.pax-input input { width: 80px; text-align: center; font-size: 1.1rem; font-weight: 700; border: 2px solid #e2e8f0; border-radius: .5rem; padding: .4rem; }
.pay-btn { width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 700; border: none; border-radius: .75rem; background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem; transition: transform .15s; }
.pay-btn:hover { transform: translateY(-1px); }
.pay-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.secure-note { display: flex; align-items: center; gap: .5rem; margin-top: 1rem; font-size: .8rem; color: #64748b; justify-content: center; }
.quote-validity-warn { background: #fef9c3; border: 1px solid #fde047; padding: .75rem 1rem; border-radius: .5rem; font-size: .85rem; color: #854d0e; margin-bottom: 1.5rem; }
.already-paid { background: #dcfce7; border: 1px solid #86efac; padding: 1rem; border-radius: .75rem; color: #166534; font-weight: 600; text-align: center; }
@media (max-width: 480px) {
    .checkout-body { padding: 1.25rem; }
    .payment-type-tabs { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="checkout-container">
    <div class="checkout-card">
        <div class="checkout-header">
            <h1>{{ $itinerary->tour_name ?? 'Your Custom Tour' }}</h1>
            <p>Complete your payment to confirm your booking</p>
        </div>

        <div class="checkout-body">
            @if(session('error'))
            <div style="background:#fee2e2;border:1px solid #fca5a5;padding:.75rem 1rem;border-radius:.5rem;color:#991b1b;margin-bottom:1rem;font-size:.9rem">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif
            @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;padding:.75rem 1rem;border-radius:.5rem;color:#991b1b;margin-bottom:1rem;font-size:.9rem">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
            @endif

            @if($totalPaid > 0)
            <div class="already-paid mb-3">
                <i class="fas fa-check-circle"></i> ₱{{ number_format($totalPaid, 2) }} already paid
            </div>
            @endif

            @if($quote->valid_until)
            <div class="quote-validity-warn">
                <i class="fas fa-clock"></i>
                This quote is valid until <strong>{{ $quote->valid_until->format('F j, Y') }}</strong>.
                @if($quote->valid_until->diffInDays(now()) <= 3)
                    Expires soon!
                @endif
            </div>
            @endif

            {{-- Quote summary --}}
            <div class="checkout-summary">
                <div class="checkout-line">
                    <span class="label">Tour</span>
                    <span>{{ $itinerary->tour_name ?? 'Custom Tour' }}</span>
                </div>
                <div class="checkout-line">
                    <span class="label">Price per person</span>
                    <span style="font-weight:700">₱{{ number_format($quote->quoted_price_php, 2) }}</span>
                </div>
                <div class="checkout-line">
                    <span class="label">Group size</span>
                    <span id="displayPax">{{ $groupSize }} person{{ $groupSize > 1 ? 's' : '' }}</span>
                </div>
            </div>

            {{-- Payment type selection --}}
            <form action="{{ route('diy.checkout.process', $session->session_token) }}" method="POST" id="checkoutForm">
                @csrf

                <h4 style="font-size:.9rem;font-weight:700;color:#475569;margin-bottom:.5rem">How would you like to pay?</h4>
                <div class="payment-type-tabs">
                    <div class="payment-type-tab active" data-type="per_person" onclick="setPaymentType('per_person')">
                        <i class="fas fa-user"></i><br>
                        Per Person<br>
                        <small style="font-weight:400;color:#64748b">₱{{ number_format($quote->quoted_price_php, 2) }}</small>
                    </div>
                    <div class="payment-type-tab" data-type="group" onclick="setPaymentType('group')">
                        <i class="fas fa-users"></i><br>
                        Full Group<br>
                        <small style="font-weight:400;color:#64748b" id="groupPriceLabel">₱{{ number_format($totalGroupPrice, 2) }}</small>
                    </div>
                </div>

                <input type="hidden" name="payment_type" id="paymentType" value="per_person">

                <div class="pax-input" id="paxSection">
                    <label for="paxCount"><i class="fas fa-users"></i> Number of travelers:</label>
                    <input type="number" name="pax_count" id="paxCount" value="{{ $groupSize }}" min="1" max="50" onchange="updateTotal()">
                </div>

                {{-- Total display --}}
                <div class="checkout-summary">
                    <div class="checkout-line total">
                        <span>Total to pay</span>
                        <span id="totalAmount">₱{{ number_format($quote->quoted_price_php, 2) }}</span>
                    </div>
                </div>

                <button type="submit" class="pay-btn" id="payBtn">
                    <i class="fas fa-lock"></i> Pay Now
                </button>

                <div class="secure-note">
                    <i class="fas fa-shield-alt"></i>
                    Secured by Xendit · GCash, BPI, BDO, Credit Card accepted
                </div>
            </form>

            <div style="text-align:center;margin-top:1.5rem">
                <a href="{{ route('diy.quote', $session->session_token) }}" style="color:#64748b;font-size:.85rem">
                    <i class="fas fa-arrow-left"></i> Back to Quote
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var pricePerPerson = {{ (float) $quote->quoted_price_php }};
var currentType = 'per_person';

function setPaymentType(type) {
    currentType = type;
    document.getElementById('paymentType').value = type;
    document.querySelectorAll('.payment-type-tab').forEach(function(tab) {
        tab.classList.toggle('active', tab.dataset.type === type);
    });
    updateTotal();
}

function updateTotal() {
    var pax = parseInt(document.getElementById('paxCount').value) || 1;
    var total;

    if (currentType === 'per_person') {
        total = pricePerPerson;
        document.getElementById('displayPax').textContent = '1 person (of ' + pax + ')';
    } else {
        total = pricePerPerson * pax;
        document.getElementById('displayPax').textContent = pax + ' person' + (pax > 1 ? 's' : '');
    }

    document.getElementById('totalAmount').textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('groupPriceLabel').textContent = '₱' + (pricePerPerson * pax).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.getElementById('checkoutForm').addEventListener('submit', function() {
    document.getElementById('payBtn').disabled = true;
    document.getElementById('payBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});
</script>
@endpush
