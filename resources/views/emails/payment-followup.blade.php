<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Payment Reminder</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:600px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#1e3a5f; padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; }
        .header p { color:#93c5fd; margin:6px 0 0; font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .alert { border-radius:8px; padding:14px 18px; margin-bottom:24px; font-size:14px; line-height:1.5; }
        .alert-warning { background:#fffbeb; border:1px solid #fcd34d; color:#92400e; }
        .alert-urgent  { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; margin:24px 0 10px; }
        .detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; border-bottom:1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#6b7280; }
        .detail-row .value { font-weight:600; text-align:right; }
        .due-amount { font-size:28px; font-weight:800; color:#1e3a5f; text-align:center; margin:0 0 8px; }
        .due-label  { font-size:13px; color:#6b7280; text-align:center; margin-bottom:24px; }
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
        <p>Installment Payment Reminder</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Hi <strong>{{ $booking->contact_name }}</strong>,</p>

        @if($isManual ?? false)
            <div class="alert alert-warning">
                <strong>📋 Payment Reminder from DiscoverGRP</strong><br>
                Our team is reaching out to remind you about your upcoming installment payment.
                Please settle this at your earliest convenience to keep your booking active.
            </div>
        @elseif($daysUntilDue === 0)
            <div class="alert alert-urgent">
                <strong>🔔 Your installment payment is due today!</strong><br>
                Please settle this payment today to keep your booking active and avoid any issues.
            </div>
        @elseif($daysUntilDue <= 5)
            <div class="alert alert-warning">
                <strong>📅 Your installment payment is due in {{ $daysUntilDue }} day{{ $daysUntilDue > 1 ? 's' : '' }}.</strong><br>
                Please plan ahead and ensure your payment is completed before the due date.
            </div>
        @else
            <div class="alert alert-warning">
                <strong>📅 Friendly reminder: upcoming installment payment.</strong><br>
                Please plan ahead and ensure your payment is completed before the due date.
            </div>
        @endif

        {{-- Due Amount --}}
        <p class="due-amount">₱{{ number_format($term['amount'], 2) }}</p>
        <p class="due-label">
            {{ $term['type'] === 'downpayment' ? 'Downpayment' : 'Term ' . $term['term'] }} — due on
            <strong>{{ \Carbon\Carbon::parse($term['due_date'])->format('F d, Y') }}</strong>
        </p>

        {{-- Booking Details --}}
        <p class="section-title">Booking Details</p>
        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Booking #</span>
                <span class="value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tour</span>
                <span class="value">{{ $booking->tour?->title ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tour Date</span>
                <span class="value">{{ $booking->tour_date?->format('F d, Y') ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests</span>
                <span class="value">{{ $booking->total_guests }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount</span>
                <span class="value">₱{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Installment Schedule Summary --}}
        @if(!empty($booking->installment_schedule))
        <p class="section-title">Full Installment Schedule</p>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f1f5f9">
                    <th style="padding:8px 10px;text-align:left;color:#475569;font-size:11px;text-transform:uppercase;border-bottom:2px solid #e2e8f0">Term</th>
                    <th style="padding:8px 10px;text-align:left;color:#475569;font-size:11px;text-transform:uppercase;border-bottom:2px solid #e2e8f0">Due Date</th>
                    <th style="padding:8px 10px;text-align:right;color:#475569;font-size:11px;text-transform:uppercase;border-bottom:2px solid #e2e8f0">Amount</th>
                    <th style="padding:8px 10px;text-align:center;color:#475569;font-size:11px;text-transform:uppercase;border-bottom:2px solid #e2e8f0">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->installment_schedule as $row)
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:8px 10px">
                        {{ $row['type'] === 'downpayment' ? 'Downpayment' : 'Term ' . $row['term'] }}
                    </td>
                    <td style="padding:8px 10px">{{ \Carbon\Carbon::parse($row['due_date'])->format('M d, Y') }}</td>
                    <td style="padding:8px 10px;text-align:right">₱{{ number_format($row['amount'], 2) }}</td>
                    <td style="padding:8px 10px;text-align:center">
                        @if(($row['status'] ?? '') === 'paid')
                            <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600">Paid</span>
                        @else
                            <span style="background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <hr class="divider">

        <p style="font-size:14px;color:#374151;margin:0">
            If you have already made this payment or have questions, please contact us and reference your booking number
            <strong>{{ $booking->booking_number }}</strong>.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>© {{ date('Y') }} Discover Group. All rights reserved.</p>
        <p>This is an automated reminder. Please do not reply to this email.</p>
    </div>
</div>
</div>
</body>
</html>
