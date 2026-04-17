<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmed</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:600px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#1e3a5f; padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:-.3px; }
        .header p { color:#93c5fd; margin:6px 0 0; font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .alert { border-radius:8px; padding:14px 18px; margin-bottom:24px; font-size:14px; line-height:1.5; }
        .alert-success { background:#f0fdf4; border:1px solid #86efac; color:#166534; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:24px 0 10px; }
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; border-bottom:1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; }
        .detail-row .value { font-weight:600; text-align:right; }
        .highlight-amount { font-size:26px; font-weight:800; color:#1e3a5f; text-align:center; margin:0 0 20px; }
        .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; padding:20px 40px 32px; }
        .footer a { color:#1e3a5f; text-decoration:none; }
        .btn { display:block; width:fit-content; margin:24px auto 0; background:#1e3a5f; color:#fff !important; text-decoration:none; padding:12px 32px; border-radius:8px; font-size:15px; font-weight:600; text-align:center; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    {{-- Header --}}
    <div class="header">
        @php $emailLogo = \App\Models\Setting::logoUrl('logo_path'); @endphp
        @if($emailLogo)
            <img src="{{ $emailLogo }}" alt="Discover Group" style="max-height:48px;width:auto;margin-bottom:6px">
        @else
            <h1>Discover Group</h1>
        @endif
        <p>🎉 Payment Confirmed!</p>
    </div>

    <div class="body">
        @php
            $clientName = $session->user?->name ?? 'Traveler';
            $itinerary  = $session->latestItinerary;
            $tourName   = $itinerary?->tour_name ?? 'Your Custom Tour';
        @endphp

        <p class="greeting">Hi <strong>{{ $clientName }}</strong>,</p>

        <div class="alert alert-success">
            <strong>Your payment has been confirmed!</strong> Your custom plan
            <strong>{{ $tourName }}</strong> is now confirmed. We'll be in touch with next steps.
        </div>

        <p class="highlight-amount">₱{{ number_format($payment->amount, 2) }}</p>

        {{-- Payment Details --}}
        <div class="section-title">Payment Details</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Transaction ID</span>
                <span class="value" style="font-family:monospace;font-size:12px">{{ $payment->transaction_id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount Paid</span>
                <span class="value">₱{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Method</span>
                <span class="value">{{ strtoupper($payment->method) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $payment->paid_at?->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        {{-- Tour Summary --}}
        @if($itinerary)
        <div class="section-title">Plan Summary</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Plan Name</span>
                <span class="value">{{ $tourName }}</span>
            </div>
            @php $pricing = $itinerary->pricing_data ?? []; @endphp
            @if(!empty($pricing['total_days']))
            <div class="detail-row">
                <span class="label">Duration</span>
                <span class="value">{{ $pricing['total_days'] }} {{ Str::plural('day', $pricing['total_days']) }}</span>
            </div>
            @endif
            @if(!empty($pricing['group_size']))
            <div class="detail-row">
                <span class="label">Group Size</span>
                <span class="value">{{ $pricing['group_size'] }} {{ Str::plural('person', $pricing['group_size']) }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="label">Quoted Price (per person)</span>
                <span class="value">₱{{ number_format($quote->quoted_price_php, 2) }}</span>
            </div>
        </div>
        @endif

        <hr class="divider">

        <p style="font-size:14px;color:#475569;text-align:center;margin:0">
            Our team will follow up with your detailed plan and preparation guide.
            If you have any questions, feel free to reach out.
        </p>

        <a href="{{ route('diy.confirmation', $session->session_token) }}" class="btn">View Confirmation</a>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Discover Group. All rights reserved.</p>
        <p>
            <a href="{{ route('contact') }}">Contact Us</a> &middot;
            <a href="{{ route('home') }}">Visit Website</a>
        </p>
    </div>

</div>
</div>
</body>
</html>
