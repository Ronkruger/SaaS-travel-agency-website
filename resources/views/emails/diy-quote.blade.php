<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Official Quote — Discover Group</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:600px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#1e3a5f; padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:-.3px; }
        .header p { color:#93c5fd; margin:6px 0 0; font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .alert-info { background:#eff6ff; border:1px solid #93c5fd; color:#1e40af; border-radius:8px; padding:14px 18px; margin-bottom:24px; font-size:14px; line-height:1.5; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:24px 0 10px; }
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; border-bottom:1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; }
        .detail-row .value { font-weight:600; text-align:right; }
        .highlight-amount { font-size:32px; font-weight:800; color:#1e3a5f; text-align:center; margin:0 0 4px; }
        .highlight-label { text-align:center; font-size:13px; color:#6b7280; margin:0 0 24px; }
        .terms-box { background:#fefce8; border:1px solid #fde68a; border-radius:8px; padding:14px 18px; font-size:13px; color:#78350f; line-height:1.6; margin-bottom:20px; }
        .btn { display:block; width:fit-content; margin:24px auto 0; background:#1e3a5f; color:#fff !important; text-decoration:none; padding:13px 36px; border-radius:8px; font-size:15px; font-weight:600; text-align:center; }
        .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; padding:20px 40px 32px; }
        .footer a { color:#1e3a5f; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    {{-- Header --}}
    <div class="header">
        <h1>Discover Group</h1>
        <p>📋 Your Official Quote is Ready</p>
    </div>

    <div class="body">

        @php
            $user     = $session->user;
            $itinerary = $session->latestItinerary;
            $tourName  = $itinerary?->tour_name ?? 'Your Custom Tour';
            $prefs     = $itinerary?->user_preferences ?? [];
            $quoteUrl  = url('/diy/' . $session->session_token . '/quote');
        @endphp

        <p class="greeting">Hi <strong>{{ $user?->name ?? 'Traveler' }}</strong>,</p>

        <div class="alert-info">
            We've reviewed your custom itinerary and prepared an official quote for
            <strong>{{ $tourName }}</strong>. Please review the details below and
            accept via the link at the bottom of this email.
        </div>

        {{-- Price highlight --}}
        <p class="highlight-amount">₱{{ number_format($quote->quoted_price_php, 2) }}</p>
        <p class="highlight-label">per person</p>

        {{-- Quote Details --}}
        <div class="section-title">Quote Details</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Tour Name</span>
                <span class="value">{{ $tourName }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Duration</span>
                <span class="value">{{ $prefs['duration'] ?? '—' }} days</span>
            </div>
            <div class="detail-row">
                <span class="label">Group Size</span>
                <span class="value">{{ $prefs['group_size'] ?? '—' }} {{ ($prefs['group_size'] ?? 1) == 1 ? 'person' : 'people' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Travel Month</span>
                <span class="value">{{ ucfirst($prefs['travel_month'] ?? '—') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Price per Person</span>
                <span class="value" style="color:#1e3a5f">₱{{ number_format($quote->quoted_price_php, 2) }}</span>
            </div>
            @isset($prefs['group_size'])
            <div class="detail-row">
                <span class="label">Total Group Price</span>
                <span class="value" style="color:#1e3a5f">
                    ₱{{ number_format($quote->quoted_price_php * (int) $prefs['group_size'], 2) }}
                </span>
            </div>
            @endisset
            <div class="detail-row">
                <span class="label">Quote Valid Until</span>
                <span class="value" style="color:#dc2626">{{ $quote->valid_until->format('F d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status</span>
                <span class="value" style="text-transform:capitalize">{{ $quote->status }}</span>
            </div>
        </div>

        {{-- Terms & Conditions --}}
        @if($quote->terms_conditions)
        <div class="section-title">Terms &amp; Conditions</div>
        <div class="terms-box">
            {!! nl2br(e($quote->terms_conditions)) !!}
        </div>
        @endif

        <hr class="divider">

        <p style="font-size:14px;color:#374151;text-align:center;margin:0 0 4px">
            To review your full itinerary and accept this quote, click the button below.
        </p>
        <p style="font-size:12px;color:#9ca3af;text-align:center;margin:0 0 0">
            This quote expires on <strong>{{ $quote->valid_until->format('F d, Y') }}</strong>.
        </p>

        <a href="{{ $quoteUrl }}" class="btn">View &amp; Accept Quote</a>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <p style="margin:0 0 6px">Questions? Reply to this email or contact us at
            <a href="mailto:bookings@discovergroup.com">bookings@discovergroup.com</a>
        </p>
        <p style="margin:0">© {{ date('Y') }} Discover Group. All rights reserved.</p>
    </div>

</div>
</div>
</body>
</html>
