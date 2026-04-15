<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Payment Receipt — {{ $payment->transaction_id }}</title>
<style>
    @page { size: A4 portrait; margin: 30px 0 100px 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10pt;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.5;
    }

    .header {
        background-color: {{ $settings['accent_color'] }};
        color: #ffffff;
        padding: 28px 40px 24px;
    }
    .header-inner { display: table; width: 100%; }
    .header-logo-cell { display: table-cell; vertical-align: middle; width: 1%; padding-right: 16px; }
    .header-logo-cell img { max-width: 110px; max-height: 50px; filter: brightness(0) invert(1); }
    .header-text-cell { display: table-cell; vertical-align: middle; }
    .header-company { font-size: 18pt; font-weight: bold; letter-spacing: 0.04em; }
    .header-tagline { font-size: 9pt; color: rgba(255,255,255,0.8); margin-top: 3px; }
    .header-doc-title {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        padding-right: 4px;
    }
    .header-doc-title span {
        font-size: 11pt;
        font-weight: bold;
        letter-spacing: 0.05em;
        border: 2px solid rgba(255,255,255,0.6);
        border-radius: 4px;
        padding: 7px 14px;
        display: inline-block;
    }

    .ref-bar {
        background: #f8fafc;
        border-bottom: 3px solid {{ $settings['accent_color'] }};
        padding: 10px 40px;
        display: table;
        width: 100%;
    }
    .ref-bar td { display: table-cell; font-size: 9pt; color: #64748b; }
    .ref-bar td strong { color: #1e293b; font-size: 10pt; }
    .ref-bar td:last-child { text-align: right; }

    .content { padding: 24px 40px; }

    .amount-box {
        background: {{ $settings['accent_color'] }};
        color: #fff;
        border-radius: 6px;
        padding: 20px 28px;
        margin-bottom: 24px;
        text-align: center;
    }
    .amount-label { font-size: 9pt; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.8); }
    .amount-value { font-size: 26pt; font-weight: bold; margin-top: 4px; }
    .amount-status {
        display: inline-block;
        margin-top: 8px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 20px;
        padding: 3px 16px;
        font-size: 9pt;
        font-weight: bold;
        letter-spacing: 0.04em;
    }

    .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .details-table td { padding: 9px 14px; font-size: 9.5pt; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .details-table .label { color: #64748b; font-weight: 600; width: 180px; text-transform: uppercase; font-size: 8pt; letter-spacing: 0.05em; }
    .details-table .value { color: #1e293b; font-weight: 500; }

    .section-title {
        font-size: 11pt;
        font-weight: bold;
        color: {{ $settings['accent_color'] }};
        border-bottom: 2px solid {{ $settings['accent_color'] }};
        padding-bottom: 6px;
        margin-bottom: 14px;
        margin-top: 24px;
    }

    .note-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 5px;
        padding: 12px 16px;
        font-size: 9pt;
        color: #1e40af;
        margin-top: 20px;
    }

    .watermark {
        position: fixed;
        top: 340px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 64pt;
        color: rgba(0,0,0,0.03);
        font-weight: bold;
        letter-spacing: 0.1em;
        transform: rotate(-30deg);
        z-index: -1;
    }

    .footer {
        position: fixed;
        bottom: -100px;
        left: 0;
        right: 0;
        height: 90px;
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
        padding: 12px 40px;
        font-size: 8pt;
        color: #94a3b8;
    }
    .footer-inner { display: table; width: 100%; }
    .footer-inner td { display: table-cell; vertical-align: middle; }
    .footer-inner td:last-child { text-align: right; }
</style>
</head>
<body>

<div class="watermark">PAID</div>

{{-- ── Header ────────────────────────────────────── --}}
<div class="header">
    <div class="header-inner">
        <div class="header-logo-cell">
            @if(!empty($settings['logo_url']))
                <img src="{{ $settings['logo_url'] }}" alt="Logo">
            @endif
        </div>
        <div class="header-text-cell">
            <div class="header-company">{{ $settings['company_name'] }}</div>
            <div class="header-tagline">{{ $settings['tagline'] }}</div>
        </div>
        <div class="header-doc-title">
            <span>PAYMENT RECEIPT</span>
        </div>
    </div>
</div>

{{-- ── Reference bar ─────────────────────────────── --}}
<div class="ref-bar">
    <table style="width:100%"><tr>
        <td><strong>{{ $payment->transaction_id }}</strong></td>
        <td style="text-align:center">Booking: <strong>{{ $booking->booking_number }}</strong></td>
        <td style="text-align:right">Date: <strong>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}</strong></td>
    </tr></table>
</div>

{{-- ── Content ───────────────────────────────────── --}}
<div class="content">

    {{-- Amount highlight --}}
    <div class="amount-box">
        <div class="amount-label">Amount Paid</div>
        <div class="amount-value">₱{{ number_format($payment->amount, 2) }}</div>
        <div class="amount-status">✓ PAYMENT {{ strtoupper($payment->status) }}</div>
    </div>

    {{-- Payment Details --}}
    <div class="section-title">Payment Details</div>
    <table class="details-table">
        <tr>
            <td class="label">Description</td>
            <td class="value">{{ $payment->notes ?: 'Payment for ' . $booking->booking_number }}</td>
        </tr>
        <tr>
            <td class="label">Payment Channel</td>
            <td class="value">{{ $channel }}</td>
        </tr>
        @if($payment->gateway_transaction_id)
        <tr>
            <td class="label">Xendit Reference</td>
            <td class="value" style="font-family:monospace;font-size:9pt">{{ $payment->gateway_transaction_id }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">System Transaction ID</td>
            <td class="value" style="font-family:monospace;font-size:9pt">{{ $payment->transaction_id }}</td>
        </tr>
        <tr>
            <td class="label">Payment Date</td>
            <td class="value">{{ $payment->paid_at ? $payment->paid_at->format('F d, Y — h:i A') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">Currency</td>
            <td class="value">{{ $payment->currency }}</td>
        </tr>
    </table>

    {{-- Booking Info --}}
    <div class="section-title">Booking Information</div>
    <table class="details-table">
        <tr>
            <td class="label">Booking Number</td>
            <td class="value">{{ $booking->booking_number }}</td>
        </tr>
        <tr>
            <td class="label">Client Name</td>
            <td class="value">{{ $booking->contact_name }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="value">{{ $booking->contact_email }}</td>
        </tr>
        @if($booking->tour)
        <tr>
            <td class="label">Tour</td>
            <td class="value">{{ $booking->tour->title }}</td>
        </tr>
        <tr>
            <td class="label">Travel Date</td>
            <td class="value">{{ $booking->tour_date ? \Carbon\Carbon::parse($booking->tour_date)->format('F d, Y') : '—' }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Total Booking Amount</td>
            <td class="value">₱{{ number_format($booking->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Overall Payment Status</td>
            <td class="value">
                @if($booking->payment_status === 'paid')
                    Fully Paid
                @elseif($booking->payment_status === 'partial')
                    Partially Paid
                @else
                    {{ ucfirst($booking->payment_status) }}
                @endif
            </td>
        </tr>
    </table>

    <div class="note-box">
        This receipt confirms the payment recorded above. Please keep it for your records.
        For questions, contact us at {{ $settings['contact_email'] ?: $settings['company_name'] }}.
    </div>
</div>

{{-- ── Footer ────────────────────────────────────── --}}
<div class="footer">
    <div class="footer-inner">
        <table style="width:100%"><tr>
            <td>{{ $settings['company_name'] }} {{ $settings['tagline'] ? '— ' . $settings['tagline'] : '' }}</td>
            <td style="text-align:right">Generated {{ now()->format('M d, Y h:i A') }}</td>
        </tr></table>
    </div>
</div>

</body>
</html>
