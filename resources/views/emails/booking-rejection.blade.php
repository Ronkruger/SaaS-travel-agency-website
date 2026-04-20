<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Update — {{ $booking->booking_number }}</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:600px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#7f1d1d; padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:-.3px; }
        .header p { color:#fca5a5; margin:6px 0 0; font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .alert { border-radius:8px; padding:14px 18px; margin-bottom:24px; font-size:14px; line-height:1.5; }
        .alert-error  { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; }
        .alert-fund   { background:#f0fdf4; border:1px solid #86efac; color:#166534; }
        .alert-info   { background:#eff6ff; border:1px solid #93c5fd; color:#1e40af; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:24px 0 10px; }
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; border-bottom:1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; }
        .detail-row .value { font-weight:600; text-align:right; }
        .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; padding:20px 40px 32px; }
        .footer a { color:#1e3a5f; text-decoration:none; }
        .options-grid { display:table; width:100%; border-spacing:0; }
        .option-box { background:#f8fafc; border:2px solid #e2e8f0; border-radius:10px; padding:20px; margin-bottom:14px; }
        .option-box h3 { margin:0 0 6px; font-size:15px; color:#0f172a; }
        .option-box p { margin:0; font-size:13px; color:#64748b; line-height:1.5; }
        .btn { display:inline-block; text-decoration:none; padding:11px 28px; border-radius:8px; font-size:14px; font-weight:600; margin-top:12px; }
        .btn-primary { background:#1e3a5f; color:#fff !important; }
        .btn-secondary { background:#0e7490; color:#fff !important; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    {{-- Header --}}
    <div class="header">
        @php $emailLogo = \App\Models\Setting::logoUrl('logo_path'); @endphp
        @if($emailLogo)
            <img src="{{ $emailLogo }}" alt="{{ $brandName ?? "Your Agency" }}" style="max-height:48px;width:auto;margin-bottom:6px;filter:brightness(0) invert(1)">
        @else
            <h1>{{ $brandName ?? "Your Agency" }}</h1>
        @endif
        <p>❌ Subscription Cancelled / Rejected</p>
    </div>

    <div class="body">
        <p class="greeting">Hi <strong>{{ $booking->contact_name }}</strong>,</p>

        <div class="alert alert-error">
            <strong>Your subscription has been cancelled.</strong>
            We regret to inform you that subscription <strong>{{ $booking->booking_number }}</strong>
            for <strong>{{ $booking->tour?->title ?? 'your plan' }}</strong>
            ({{ $booking->tour_date?->format('F d, Y') ?? '—' }}) could not be processed.
            @if($reason)
                <br><br><strong>Reason:</strong> {{ $reason }}
            @endif
        </div>

        {{-- Booking Details --}}
        <div class="section-title">Subscription Details</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Subscription Number</span>
                <span class="value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Plan</span>
                <span class="value">{{ $booking->tour?->title ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Departure Date</span>
                <span class="value">{{ $booking->tour_date?->format('F d, Y') ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests</span>
                <span class="value">{{ $booking->total_guests }} pax</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount</span>
                <span class="value">₱{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Travel Fund Notice (if applicable) --}}
        @if($travelFundAdded && $travelFundAmount > 0)
        <div class="alert alert-fund">
            <strong>💰 Travel Fund Credit Added!</strong><br>
            ₱{{ number_format($travelFundAmount, 2) }} has been added to your Travel Fund balance.
            You can use this credit on your next booking. Please contact us to apply your Travel Fund.
        </div>
        @endif

        <hr class="divider">

        {{-- Options --}}
        <div class="section-title">What would you like to do?</div>

        <div class="option-box" style="border-color:#0e7490">
            <h3>🔄 Rebook on a Different Date</h3>
            <p>
                Your reservation details are saved. Contact our team and we'll help you find
                a new departure date that suits your schedule — your passenger information
                will carry over automatically.
            </p>
            <a href="mailto:{{ config('mail.from.address', 'info@discovergroup.com') }}?subject=Rebook%20Request%20—%20{{ $booking->booking_number }}&body=Hi%2C%20I%20would%20like%20to%20rebook%20my%20cancelled%20booking%20{{ $booking->booking_number }}.%20Please%20assist%20me."
               class="btn btn-secondary">
                Contact Us to Rebook
            </a>
        </div>

        @if(!$travelFundAdded)
        <div class="option-box" style="border-color:#7c3aed">
            <h3>💰 Move Payment to Travel Fund</h3>
            <p>
                If you have made any payments, you may request to convert them into a
                Travel Fund credit usable on future bookings. Please contact our team
                for assistance.
            </p>
            <a href="mailto:{{ config('mail.from.address', 'info@discovergroup.com') }}?subject=Travel%20Fund%20Request%20—%20{{ $booking->booking_number }}&body=Hi%2C%20I%20would%20like%20to%20move%20my%20payment%20for%20booking%20{{ $booking->booking_number }}%20to%20a%20Travel%20Fund%20credit."
               class="btn" style="background:#7c3aed;color:#fff !important;display:inline-block;text-decoration:none;padding:11px 28px;border-radius:8px;font-size:14px;font-weight:600;margin-top:12px">
                Request Travel Fund
            </a>
        </div>
        @endif

        <hr class="divider">

        <p style="font-size:14px;color:#64748b;line-height:1.6">
            We sincerely apologize for any inconvenience. Our team is here to help you plan
            your next adventure. Feel free to reach out anytime.
        </p>
    </div>

    <div class="footer">
        <p style="margin:0 0 8px;background:#f3f4f6;border-radius:6px;padding:8px 12px;font-size:11px;color:#6b7280;display:inline-block">
            ⚠️ This is an automated, system-generated email — please do not reply directly to this message.
            For assistance, contact us at <a href="mailto:{{ config('mail.from.address', 'bookings@discovergroup.com') }}">{{ config('mail.from.address', 'bookings@discovergroup.com') }}</a>.
        </p>
        <p style="margin:6px 0 4px">© {{ date('Y') }} {{ $brandName ?? "Your Agency" }}. All rights reserved.</p>
        <p style="margin-top:4px">
            <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
        </p>
    </div>
</div>
</div>
</body>
</html>
