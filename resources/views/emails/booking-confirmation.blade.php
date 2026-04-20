<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isInstallment ? 'Payment Received' : 'Subscription Confirmed' }}</title>
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
        .alert-info    { background:#eff6ff; border:1px solid #93c5fd; color:#1e40af; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:24px 0 10px; }
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; border-bottom:1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; }
        .detail-row .value { font-weight:600; text-align:right; }
        table.schedule { width:100%; border-collapse:collapse; font-size:13px; margin-top:4px; }
        table.schedule th { background:#f1f5f9; padding:8px 10px; text-align:left; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em; border-bottom:2px solid #e2e8f0; }
        table.schedule td { padding:8px 10px; border-bottom:1px solid #f1f5f9; }
        table.schedule tr:last-child td { border-bottom:none; }
        .badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; }
        .badge-paid    { background:#dcfce7; color:#166534; }
        .badge-pending { background:#fef9c3; color:#854d0e; }
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
            <img src="{{ $emailLogo }}" alt="{{ $brandName ?? "Your Agency" }}" style="max-height:48px;width:auto;margin-bottom:6px">
        @else
            <h1>{{ $brandName ?? "Your Agency" }}</h1>
        @endif
        <p>{{ $isInstallment ? '✅ Payment Received' : '🎉 Subscription Confirmed!' }}</p>
    </div>

    <div class="body">
        <p class="greeting">Hi <strong>{{ $booking->contact_name }}</strong>,</p>

        @if($isInstallment)
        <div class="alert alert-success">
            <strong>Payment received!</strong> Your installment payment of
            <strong>₱{{ number_format($amountPaid ?? 0, 2) }}</strong>
            for <strong>{{ $termLabel }}</strong> on subscription
            <strong>{{ $booking->booking_number }}</strong> has been successfully processed.
        </div>
        @else
        <div class="alert alert-success">
            <strong>Your subscription is confirmed!</strong> Full payment has been received for subscription
            <strong>{{ $booking->booking_number }}</strong>. We look forward to hosting you!
        </div>
        @endif

        {{-- Booking Details --}}
        <div class="section-title">Subscription Details</div>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Subscription Number</span>
                <span class="value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Plan</span>
                <span class="value">{{ $booking->tour->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Start Date</span>
                <span class="value">{{ $booking->tour_date->format('F d, Y') }}</span>
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
                <span class="label">Payment Method</span>
                <span class="value" style="text-transform:capitalize">
                    @if($booking->payment_method === 'xendit') Online (Xendit)
                    @elseif($booking->payment_method === 'installment') Installment
                    @else Cash / Office @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount</span>
                <span class="value" style="color:#1e3a5f">₱{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Installment schedule summary --}}
        @if($booking->payment_method === 'installment' && !empty($booking->installment_schedule))
        @php
            $schedule = $booking->installment_schedule;
            $paidCount   = collect($schedule)->where('status', 'paid')->count();
            $pendingCount = collect($schedule)->where('status', 'pending')->count();
            $paidAmount   = collect($schedule)->where('status', 'paid')->sum('amount');
            $remaining    = collect($schedule)->where('status', 'pending')->sum('amount');
        @endphp
        <div class="section-title">Payment Progress</div>
        <div class="detail-box" style="margin-bottom:16px">
            <div class="detail-row">
                <span class="label">Terms Paid</span>
                <span class="value" style="color:#16a34a">{{ $paidCount }} of {{ count($schedule) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount Paid So Far</span>
                <span class="value" style="color:#16a34a">₱{{ number_format($paidAmount, 2) }}</span>
            </div>
            @if($pendingCount > 0)
            <div class="detail-row">
                <span class="label">Remaining Balance</span>
                <span class="value" style="color:#b45309">₱{{ number_format($remaining, 2) }} ({{ $pendingCount }} term{{ $pendingCount != 1 ? 's' : '' }})</span>
            </div>
            @endif
        </div>

        @if($pendingCount > 0)
        <div class="section-title">Upcoming Payments</div>
        <table class="schedule">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Due Date</th>
                    <th style="text-align:right">Amount</th>
                    <th style="text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedule as $term)
                <tr>
                    <td>{{ $term['term'] === 0 ? 'Down Payment' : 'Month ' . $term['term'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($term['due_date'])->format('M d, Y') }}</td>
                    <td style="text-align:right">₱{{ number_format($term['amount'], 2) }}</td>
                    <td style="text-align:center">
                        @if($term['status'] === 'paid')
                            <span class="badge badge-paid">Paid</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endif

        <hr class="divider">

        <p style="font-size:14px;color:#4b5563;margin:0 0 8px">
            You can view your subscription details and pay upcoming installment terms anytime from your account.
        </p>

        {{-- CTA button --}}
        <a href="{{ config('app.url') }}/bookings/{{ $booking->id }}" class="btn">
            View My Subscription
        </a>

        <hr class="divider">

        <p style="font-size:13px;color:#6b7280;margin:0">
            Questions? Reply to this email or contact us at
            <a href="mailto:{{ config('mail.from.address') }}" style="color:#1e3a5f">{{ config('mail.from.address') }}</a>.
        </p>
    </div>

    <div class="footer">
        <p style="margin:0 0 8px;background:#f3f4f6;border-radius:6px;padding:8px 12px;font-size:11px;color:#6b7280;display:inline-block">
            ⚠️ This is an automated, system-generated email — please do not reply directly to this message.
            For assistance, contact us at <a href="mailto:{{ config('mail.from.address', 'bookings@discovergroup.com') }}">{{ config('mail.from.address', 'bookings@discovergroup.com') }}</a>.
        </p>
        <p style="margin:6px 0 4px">© {{ date('Y') }} {{ $brandName ?? "Your Agency" }}. All rights reserved.</p>
        <p style="margin:0">
            <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
        </p>
    </div>

</div>
</div>
</body>
</html>
