@extends('layouts.app')

@section('title', 'Payment Confirmed — ' . ($itinerary->tour_name ?? 'Custom Tour'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/diy.css') }}">
<style>
.confirmation-container { max-width: 640px; margin: 3rem auto; padding: 0 1rem; text-align: center; }
.confirmation-card { background: #fff; border-radius: 1rem; box-shadow: 0 4px 24px rgba(0,0,0,.08); padding: 3rem 2rem; }
.confirmation-icon { width: 80px; height: 80px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; color: #16a34a; }
.confirmation-card h1 { font-size: 1.75rem; font-weight: 800; color: #166534; margin: 0 0 .5rem; }
.confirmation-card .subtitle { color: #64748b; font-size: 1rem; margin-bottom: 2rem; }
.confirmation-details { background: #f8fafc; border-radius: .75rem; padding: 1.25rem; margin-bottom: 2rem; text-align: left; }
.confirmation-details .detail-row { display: flex; justify-content: space-between; padding: .5rem 0; font-size: .9rem; }
.confirmation-details .detail-row + .detail-row { border-top: 1px solid #e2e8f0; }
.confirmation-details .detail-row .label { color: #64748b; }
.confirmation-details .detail-row .value { font-weight: 600; }
.confirmation-actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
.confirmation-note { margin-top: 1.5rem; font-size: .85rem; color: #94a3b8; line-height: 1.6; }
@media (max-width: 480px) {
    .confirmation-card { padding: 2rem 1.25rem; }
}
</style>
@endpush

@section('content')
<div class="confirmation-container">
    <div class="confirmation-card">
        @if($quote && $quote->status === 'accepted')
            <div class="confirmation-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Confirmed!</h1>
            <p class="subtitle">Your DIY tour has been booked successfully</p>

            <div class="confirmation-details">
                <div class="detail-row">
                    <span class="label">Tour</span>
                    <span class="value">{{ $itinerary->tour_name ?? 'Custom Tour' }}</span>
                </div>
                @php
                    $latestPayment = $quote->payments->where('status', 'completed')->sortByDesc('paid_at')->first();
                @endphp
                @if($latestPayment)
                <div class="detail-row">
                    <span class="label">Amount Paid</span>
                    <span class="value" style="color:#16a34a">₱{{ number_format($latestPayment->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Transaction ID</span>
                    <span class="value" style="font-family:monospace;font-size:.8rem">{{ $latestPayment->transaction_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Paid On</span>
                    <span class="value">{{ $latestPayment->paid_at?->format('M d, Y h:i A') ?? '—' }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Quote Price (per person)</span>
                    <span class="value">₱{{ number_format($quote->quoted_price_php, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value" style="color:#16a34a"><i class="fas fa-check-circle"></i> Confirmed</span>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="{{ route('diy.quote', $session->session_token) }}" class="btn btn-outline">
                    <i class="fas fa-file-alt"></i> View Full Itinerary
                </a>
                <a href="{{ route('diy.my-tours') }}" class="btn btn-primary">
                    <i class="fas fa-suitcase"></i> My Tours
                </a>
            </div>

            <p class="confirmation-note">
                Our team will reach out to you within 24 hours to finalize your tour details.<br>
                A confirmation email has been sent to <strong>{{ auth()->user()->email }}</strong>.
            </p>
        @else
            <div class="confirmation-icon" style="background:#fef9c3;color:#ca8a04">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <h1 style="color:#854d0e">Payment Processing</h1>
            <p class="subtitle">Your payment is being verified. This usually takes a few seconds.</p>

            <div class="confirmation-actions">
                <a href="{{ route('diy.quote', $session->session_token) }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Quote
                </a>
            </div>

            <p class="confirmation-note">
                If your payment was successful, this page will update automatically.<br>
                You can also check <a href="{{ route('diy.my-tours') }}">My Tours</a> for the latest status.
            </p>

            <script>
                // Auto-refresh every 5 seconds while waiting for webhook
                setTimeout(function() { location.reload(); }, 5000);
            </script>
        @endif
    </div>
</div>
@endsection
