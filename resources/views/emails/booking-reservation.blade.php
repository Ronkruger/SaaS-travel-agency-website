<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Received — {{ $booking->booking_number }}</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:600px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }

        /* Header */
        .header { background:#1e3a5f; padding:0; }
        .header-top { padding:28px 40px 0; text-align:center; }
        .header-top h1 { color:#fff; margin:0; font-size:20px; font-weight:700; letter-spacing:-.3px; }
        .header-top p { color:#93c5fd; margin:4px 0 0; font-size:13px; }
        .header-banner { background:linear-gradient(135deg,#16a34a,#15803d); margin-top:20px; padding:20px 40px; text-align:center; }
        .header-banner .icon { font-size:36px; display:block; margin-bottom:8px; }
        .header-banner h2 { color:#fff; margin:0; font-size:22px; font-weight:800; }
        .header-banner p { color:#bbf7d0; margin:6px 0 0; font-size:14px; }

        /* Body */
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .alert { border-radius:8px; padding:14px 18px; margin-bottom:24px; font-size:14px; line-height:1.6; }
        .alert-warning { background:#fffbeb; border:1px solid #fcd34d; color:#92400e; }
        .alert-info    { background:#eff6ff; border:1px solid #93c5fd; color:#1e40af; }

        /* Section label */
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:0 0 10px; }

        /* Detail box */
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:24px; border:1px solid #e2e8f0; }
        .detail-row { display:flex; justify-content:space-between; align-items:flex-start; padding:7px 0; font-size:14px; border-bottom:1px solid #e2e8f0; gap:12px; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; white-space:nowrap; flex-shrink:0; }
        .detail-row .value { font-weight:600; text-align:right; }

        /* Price breakdown */
        .price-box { background:#f0fdf4; border-radius:8px; border:1px solid #86efac; padding:16px 20px; margin-bottom:24px; }
        .price-row { display:flex; justify-content:space-between; font-size:14px; padding:5px 0; color:#374151; }
        .price-row.total { border-top:2px solid #86efac; margin-top:8px; padding-top:10px; font-size:16px; font-weight:800; color:#1e3a5f; }

        /* Installment table */
        table.schedule { width:100%; border-collapse:collapse; font-size:13px; margin-top:4px; }
        table.schedule th { background:#f1f5f9; padding:8px 10px; text-align:left; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em; border-bottom:2px solid #e2e8f0; }
        table.schedule td { padding:8px 10px; border-bottom:1px solid #f1f5f9; color:#374151; }
        table.schedule tr:last-child td { border-bottom:none; }

        /* Payment method badge */
        .pm-badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; }
        .pm-xendit      { background:#dbeafe; color:#1e40af; }
        .pm-installment { background:#ede9fe; color:#5b21b6; }
        .pm-cash        { background:#dcfce7; color:#166534; }

        /* Steps */
        .steps { display:flex; gap:0; margin-bottom:24px; }
        .step { flex:1; text-align:center; padding:12px 8px; font-size:12px; }
        .step .num { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; margin:0 auto 6px; }
        .step.done .num  { background:#16a34a; color:#fff; }
        .step.next .num  { background:#1e3a5f; color:#fff; }
        .step.later .num { background:#e2e8f0; color:#94a3b8; }
        .step.done .label-s  { color:#16a34a; font-weight:600; }
        .step.next .label-s  { color:#1e3a5f; font-weight:700; }
        .step.later .label-s { color:#9ca3af; }
        .step-line { width:2px; background:#e2e8f0; align-self:stretch; margin-top:14px; display:none; }

        .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
        .btn { display:block; width:fit-content; margin:0 auto; background:#1e3a5f; color:#fff !important; text-decoration:none; padding:13px 36px; border-radius:8px; font-size:15px; font-weight:700; text-align:center; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; padding:20px 40px 32px; }
        .footer a { color:#1e3a5f; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            @php $emailLogo = \App\Models\Setting::logoUrl('logo_path'); @endphp
            @if($emailLogo)
                <img src="{{ $emailLogo }}" alt="Discover Group" style="max-height:48px;width:auto;margin-bottom:6px">
            @else
                <h1>Discover Group</h1>
            @endif
            <p>Your adventure awaits</p>
        </div>
        <div class="header-banner">
            <span class="icon">🎉</span>
            <h2>Reservation Received!</h2>
            <p>We've got your slot. Complete your payment to confirm.</p>
        </div>
    </div>

    <div class="body">

        <p class="greeting">Hi <strong>{{ $booking->contact_name }}</strong>,</p>
        <p style="font-size:14px;color:#4b5563;margin:0 0 24px;line-height:1.6">
            Thank you for choosing Discover Group! Your reservation for
            <strong>{{ $booking->tour->title }}</strong> has been received with booking number
            <strong style="color:#1e3a5f">{{ $booking->booking_number }}</strong>.
        </p>

        {{-- Next Step Alert --}}
        @if($booking->payment_method === 'xendit')
        <div class="alert alert-info">
            <strong>⏳ Complete your payment.</strong> Click the button below or visit your booking page to pay securely via Xendit (Credit Card, GCash, Maya, GrabPay, BPI, BDO).
        </div>
        @elseif($booking->payment_method === 'installment')
        <div class="alert alert-info">
            <strong>📅 Installment plan activated.</strong> Pay each monthly term through your booking page using any payment method on Xendit. Your slot is reserved!
        </div>
        @else
        <div class="alert alert-warning">
            <strong>🏢 Cash payment.</strong> Our team will contact you at <strong>{{ $booking->contact_phone }}</strong> to coordinate your cash payment. Please pay within <strong>3 business days</strong> to secure your slot.
        </div>
        @endif

        {{-- What's Next steps --}}
        <div class="section-title">What Happens Next</div>
        <div class="steps">
            <div class="step done">
                <div class="num">✓</div>
                <div class="label-s">Reservation<br>Submitted</div>
            </div>
            <div class="step next">
                <div class="num">2</div>
                <div class="label-s">Complete<br>Payment</div>
            </div>
            <div class="step later">
                <div class="num">3</div>
                <div class="label-s">Booking<br>Confirmed</div>
            </div>
            <div class="step later">
                <div class="num">4</div>
                <div class="label-s">Go on Your<br>Adventure!</div>
            </div>
        </div>

        {{-- Tour & Booking Info --}}
        <div class="section-title">Reservation Summary</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Booking Number</span>
                <span class="value" style="color:#1e3a5f">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tour</span>
                <span class="value">{{ $booking->tour->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Departure Date</span>
                <span class="value">{{ $booking->tour_date->format('l, F d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests</span>
                <span class="value">
                    {{ $booking->adults }} Adult{{ $booking->adults != 1 ? 's' : '' }}
                    @if($booking->children > 0), {{ $booking->children }} Child{{ $booking->children != 1 ? 'ren' : '' }}@endif
                    @if($booking->infants > 0), {{ $booking->infants }} Infant{{ $booking->infants != 1 ? 's' : '' }}@endif
                </span>
            </div>
            <div class="detail-row">
                <span class="label">Contact</span>
                <span class="value">{{ $booking->contact_email }}<br><span style="font-weight:400;color:#6b7280">{{ $booking->contact_phone }}</span></span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Method</span>
                <span class="value">
                    @if($booking->payment_method === 'xendit')
                        <span class="pm-badge pm-xendit">Online (Xendit)</span>
                    @elseif($booking->payment_method === 'installment')
                        <span class="pm-badge pm-installment">Installment — {{ $booking->installment_months }} months</span>
                    @else
                        <span class="pm-badge pm-cash">Cash / Office</span>
                    @endif
                </span>
            </div>
            @if($booking->special_requests)
            <div class="detail-row">
                <span class="label">Special Requests</span>
                <span class="value" style="font-weight:400;font-style:italic">{{ $booking->special_requests }}</span>
            </div>
            @endif
        </div>

        {{-- Price Breakdown --}}
        <div class="section-title">Price Breakdown</div>
        <div class="price-box">
            <div class="price-row">
                <span>{{ $booking->adults }} Adult{{ $booking->adults != 1 ? 's' : '' }} × ₱{{ number_format($booking->price_per_adult, 2) }}</span>
                <span>₱{{ number_format($booking->adults * $booking->price_per_adult, 2) }}</span>
            </div>
            @if($booking->children > 0)
            <div class="price-row">
                <span>{{ $booking->children }} Child{{ $booking->children != 1 ? 'ren' : '' }} × ₱{{ number_format($booking->price_per_child, 2) }}</span>
                <span>₱{{ number_format($booking->children * $booking->price_per_child, 2) }}</span>
            </div>
            @endif
            <div class="price-row">
                <span>Travel Tax (₱1,620/person)</span>
                <span>₱{{ number_format($booking->tax_amount, 2) }}</span>
            </div>
            <div class="price-row total">
                <span>Total Due</span>
                <span>₱{{ number_format($booking->total_amount, 2) }}</span>
            </div>
            @if($booking->payment_method === 'installment')
            <div class="price-row" style="font-size:13px;color:#5b21b6;margin-top:4px">
                <span>Monthly installment</span>
                <span>₱{{ number_format(collect($booking->installment_schedule)->where('type','installment')->first()['amount'] ?? 0, 2) }}/mo × {{ $booking->installment_months }} months</span>
            </div>
            @endif
        </div>

        {{-- Installment Schedule --}}
        @if($booking->payment_method === 'installment' && !empty($booking->installment_schedule))
        <div class="section-title">Your Payment Schedule</div>
        <table class="schedule" style="margin-bottom:24px">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Due Date</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->installment_schedule as $term)
                <tr>
                    <td>{{ $term['term'] === 0 ? 'Down Payment' : 'Month ' . $term['term'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($term['due_date'])->format('M d, Y') }}</td>
                    <td style="text-align:right">₱{{ number_format($term['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- CTA --}}
        @if($booking->payment_method !== 'cash')
        <a href="{{ config('app.url') }}/checkout/{{ $booking->id }}" class="btn">
            Complete Payment Now
        </a>
        @else
        <a href="{{ config('app.url') }}/bookings/{{ $booking->id }}" class="btn">
            View My Booking
        </a>
        @endif

        <hr class="divider">

        <p style="font-size:13px;color:#6b7280;margin:0;line-height:1.6">
            Need help? Reply to this email or reach us at
            <a href="mailto:{{ config('mail.from.address') }}" style="color:#1e3a5f">{{ config('mail.from.address') }}</a>.<br>
            Please note your booking slot is <strong>not guaranteed</strong> until payment is completed.
        </p>
    </div>

    <div class="footer">
        <p style="margin:0 0 4px">© {{ date('Y') }} Discover Group. All rights reserved.</p>
        <p style="margin:0"><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
    </div>

</div>
</div>
</body>
</html>
