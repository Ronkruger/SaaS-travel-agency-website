<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Booking Confirmation — {{ $booking->booking_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10pt;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.5;
    }

    /* ── Layout ─────────────────────────── */
    .page { padding: 0; }

    /* ── Header ─────────────────────────── */
    .header {
        background-color: {{ $settings['accent_color'] }};
        color: #ffffff;
        padding: 22px 30px 18px;
    }
    .header-inner { display: table; width: 100%; }
    .header-logo-cell { display: table-cell; vertical-align: middle; width: 120px; }
    .header-logo-cell img { max-width: 110px; max-height: 50px; filter: brightness(0) invert(1); }
    .header-text-cell { display: table-cell; vertical-align: middle; padding-left: 18px; }
    .header-company { font-size: 18pt; font-weight: bold; letter-spacing: 0.03em; }
    .header-tagline { font-size: 9pt; color: rgba(255,255,255,0.8); margin-top: 2px; }
    .header-doc-title {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        padding-right: 4px;
    }
    .header-doc-title span {
        font-size: 11pt;
        font-weight: bold;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border: 2px solid rgba(255,255,255,0.6);
        border-radius: 4px;
        padding: 5px 12px;
        display: inline-block;
    }

    /* ── Booking number bar ──────────────── */
    .booking-bar {
        background: #f8fafc;
        border-bottom: 3px solid {{ $settings['accent_color'] }};
        padding: 10px 30px;
        display: table;
        width: 100%;
    }
    .booking-bar td { display: table-cell; font-size: 9pt; color: #64748b; }
    .booking-bar td strong { color: #1e293b; font-size: 10pt; }
    .booking-bar td:last-child { text-align: right; }

    /* ── Body content ───────────────────── */
    .content { padding: 22px 30px; }

    /* ── Route / Travel Date highlight ──── */
    .route-box {
        background: {{ $settings['accent_color'] }};
        color: #fff;
        border-radius: 6px;
        padding: 14px 20px;
        margin-bottom: 18px;
        display: table;
        width: 100%;
    }
    .route-box-cell { display: table-cell; vertical-align: middle; }
    .route-box-cell:last-child { text-align: right; }
    .route-name { font-size: 15pt; font-weight: bold; letter-spacing: 0.04em; }
    .route-sub  { font-size: 9pt; color: rgba(255,255,255,0.8); margin-top: 2px; }
    .travel-date { font-size: 13pt; font-weight: bold; }
    .travel-date-lbl { font-size: 8pt; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.06em; }

    /* ── Info table ──────────────────────── */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .info-table th {
        background: #f1f5f9;
        color: #475569;
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 6px 10px;
        text-align: left;
        border: 1px solid #e2e8f0;
    }
    .info-table td { padding: 8px 10px; border: 1px solid #e2e8f0; font-size: 10pt; vertical-align: middle; }
    .info-table td.label { background: #f8fafc; color: #64748b; font-size: 8.5pt; width: 36%; }
    .info-table td.value { font-weight: 600; color: #0f172a; }

    /* ── Two-column layout ───────────────── */
    .two-col { display: table; width: 100%; margin-bottom: 18px; border-spacing: 12px 0; }
    .col-left  { display: table-cell; width: 48%; vertical-align: top; }
    .col-right { display: table-cell; width: 48%; vertical-align: top; padding-left: 14px; }

    /* ── Section head ────────────────────── */
    .section-head {
        background: {{ $settings['accent_color'] }};
        color: #fff;
        padding: 6px 12px;
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        border-radius: 4px 4px 0 0;
        margin-bottom: 0;
    }
    .section-box { border: 1px solid #e2e8f0; border-radius: 0 0 4px 4px; overflow: hidden; margin-bottom: 18px; }

    /* ── Payment pills ───────────────────── */
    .pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 8.5pt;
        font-weight: 700;
    }
    .pill-paid      { background: #dcfce7; color: #166534; }
    .pill-partial   { background: #fef9c3; color: #854d0e; }
    .pill-unpaid    { background: #fee2e2; color: #991b1b; }
    .pill-confirmed { background: #dbeafe; color: #1e40af; }
    .pill-pending   { background: #fef9c3; color: #854d0e; }
    .pill-completed { background: #dcfce7; color: #166534; }
    .pill-cancelled { background: #fee2e2; color: #991b1b; }

    /* ── Payment history table ───────────── */
    .payment-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
    .payment-table th {
        background: #f1f5f9;
        color: #475569;
        padding: 6px 10px;
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .payment-table td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .payment-table td.amount { text-align: right; font-weight: 700; }
    .payment-table tfoot td { background: #f8fafc; font-weight: 700; font-size: 10pt; }

    /* ── Price summary ───────────────────── */
    .price-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
    .price-table td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
    .price-table td:last-child { text-align: right; font-weight: 600; }
    .price-table tr.total td { border-top: 2px solid {{ $settings['accent_color'] }}; font-size: 11pt; font-weight: 700; color: {{ $settings['accent_color'] }}; }

    /* ── Status bar ──────────────────────── */
    .status-bar {
        text-align: center;
        padding: 14px;
        border-radius: 6px;
        margin-bottom: 18px;
        font-size: 13pt;
        font-weight: bold;
        letter-spacing: 0.1em;
    }
    .status-confirmed { background: #dcfce7; color: #166534; border: 2px solid #86efac; }
    .status-pending   { background: #fef9c3; color: #854d0e; border: 2px solid #fde047; }
    .status-completed { background: #dbeafe; color: #1e40af; border: 2px solid #93c5fd; }
    .status-cancelled { background: #fee2e2; color: #991b1b; border: 2px solid #fca5a5; }
    .status-refunded  { background: #f3e8ff; color: #6b21a8; border: 2px solid #d8b4fe; }

    /* ── Footer ──────────────────────────── */
    .footer {
        background: #1e293b;
        color: #fff;
        padding: 16px 30px;
        margin-top: 20px;
    }
    .footer-inner { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; font-size: 8pt; color: #94a3b8; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 8pt; color: #94a3b8; }
    .footer-right strong { color: #fff; }
    .footer-disclaimer { margin-top: 8px; padding-top: 8px; border-top: 1px solid #334155; font-size: 7.5pt; color: #94a3b8; }

    .watermark-stripe {
        height: 4px;
        background: linear-gradient(to right, {{ $settings['accent_color'] }}, #60a5fa, #a78bfa);
    }
</style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-inner">
            {{-- Logo --}}
            <div class="header-logo-cell">
                @if(!empty($settings['logo_url']) && (str_starts_with($settings['logo_url'],'http://') || str_starts_with($settings['logo_url'],'https://')))
                    <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['company_name'] }}">
                @else
                    <div style="font-size:22pt;font-weight:900;letter-spacing:-1px">DG</div>
                @endif
            </div>
            {{-- Company name --}}
            <div class="header-text-cell">
                <div class="header-company">{{ strtoupper($settings['company_name']) }}</div>
                <div class="header-tagline">{{ $settings['tagline'] }}</div>
            </div>
            {{-- Document title --}}
            <div class="header-doc-title">
                <span>{{ $settings['header_text'] }}</span>
            </div>
        </div>
    </div>

    <div class="watermark-stripe"></div>

    {{-- ── BOOKING NUMBER BAR ───────────────────────────────────── --}}
    <div class="booking-bar">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td style="font-size:9pt;color:#64748b">
                    Booking Number: <strong style="font-size:11pt;color:#1e293b;font-family:monospace">{{ $booking->booking_number }}</strong>
                </td>
                <td style="font-size:9pt;color:#64748b;text-align:center">
                    Booked On: <strong>{{ $booking->created_at->format('F d, Y') }}</strong>
                </td>
                <td style="font-size:9pt;color:#64748b;text-align:right">
                    Issued: <strong>{{ now()->format('F d, Y') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── CONTENT ──────────────────────────────────────────────── --}}
    <div class="content">

        {{-- Status banner --}}
        <div class="status-bar status-{{ $booking->status }}">
            @if($booking->status === 'confirmed') ✓ BOOKING CONFIRMED
            @elseif($booking->status === 'completed') ✓ TOUR COMPLETED
            @elseif($booking->status === 'cancelled') ✗ BOOKING CANCELLED
            @elseif($booking->status === 'refunded') ↩ REFUNDED
            @else ⏳ PENDING CONFIRMATION
            @endif
        </div>

        {{-- Route & Date highlight --}}
        <div class="route-box">
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="vertical-align:middle">
                        <div class="route-name">{{ strtoupper($booking->tour->title) }}</div>
                        @if($booking->tour->line)
                            <div class="route-sub">{{ $booking->tour->line }}</div>
                        @endif
                    </td>
                    <td style="vertical-align:middle;text-align:right">
                        <div style="font-size:8pt;color:rgba(255,255,255,0.75);text-transform:uppercase;letter-spacing:.06em">Travel Date</div>
                        <div style="font-size:13pt;font-weight:bold">{{ $booking->tour_date->format('M d, Y') }}</div>
                        @if($booking->schedule && $booking->schedule->return_date)
                            <div style="font-size:9pt;color:rgba(255,255,255,0.8)">
                                — {{ $booking->schedule->return_date->format('M d, Y') }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Two columns: Client Details + Payment Summary --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:18px">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:10px">
                    {{-- Client Details --}}
                    <div class="section-head">Client Information</div>
                    <div class="section-box">
                        <table style="width:100%;border-collapse:collapse;font-size:9.5pt">
                            <tr>
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;width:45%;font-size:8.5pt">Client Name</td>
                                <td style="padding:7px 10px;font-weight:700">{{ $booking->contact_name }}</td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Email</td>
                                <td style="padding:7px 10px">{{ $booking->contact_email }}</td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Phone</td>
                                <td style="padding:7px 10px">{{ $booking->contact_phone }}</td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">PAX</td>
                                <td style="padding:7px 10px;font-weight:700;font-size:11pt">
                                    {{ $booking->total_guests }}
                                    <span style="font-size:8.5pt;font-weight:400;color:#64748b">
                                        ({{ $booking->adults }} adult{{ $booking->adults != 1 ? 's' : '' }}
                                        @if($booking->children > 0), {{ $booking->children }} child{{ $booking->children != 1 ? 'ren' : '' }}@endif
                                        @if($booking->infants > 0), {{ $booking->infants }} infant{{ $booking->infants != 1 ? 's' : '' }}@endif)
                                    </span>
                                </td>
                            </tr>
                            @if($booking->special_requests)
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Special Requests</td>
                                <td style="padding:7px 10px;font-size:8.5pt">{{ $booking->special_requests }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:10px">
                    {{-- Price Summary --}}
                    <div class="section-head">Payment Summary</div>
                    <div class="section-box">
                        <table style="width:100%;border-collapse:collapse;font-size:9.5pt">
                            <tr>
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;width:55%;font-size:8.5pt;">Package Rate/Person</td>
                                <td style="padding:7px 10px;font-weight:700;text-align:right">₱{{ number_format($booking->price_per_adult, 2) }}</td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Payment Terms</td>
                                <td style="padding:7px 10px;font-weight:600;text-align:right">
                                    @if($booking->payment_method === 'installment') Instalment
                                    @elseif($booking->payment_method === 'cash' && $booking->downpayment_amount > 0) Downpayment
                                    @elseif($booking->payment_method === 'cash') Full Cash
                                    @else Online (Xendit)
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Subtotal</td>
                                <td style="padding:7px 10px;text-align:right">₱{{ number_format($booking->subtotal, 2) }}</td>
                            </tr>
                            @if($booking->tax_amount > 0)
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Travel Tax</td>
                                <td style="padding:7px 10px;text-align:right">₱{{ number_format($booking->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr style="border-top:2px solid {{ $settings['accent_color'] }}">
                                <td style="padding:9px 10px;background:#f8fafc;font-weight:700;font-size:10.5pt">TOTAL AMOUNT</td>
                                <td style="padding:9px 10px;font-weight:700;font-size:12pt;text-align:right;color:{{ $settings['accent_color'] }}">
                                    ₱{{ number_format($booking->total_amount, 2) }}
                                </td>
                            </tr>
                            <tr style="border-top:1px solid #f1f5f9">
                                <td style="padding:7px 10px;background:#f8fafc;color:#64748b;font-size:8.5pt">Payment Status</td>
                                <td style="padding:7px 10px;text-align:right">
                                    <span class="pill pill-{{ $booking->payment_status }}">
                                        {{ ucfirst($booking->payment_status === 'paid' ? 'Fully Paid' : ($booking->payment_status === 'partial' ? 'Partial' : 'Unpaid')) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── PAYMENT HISTORY (admin can toggle this) ──────────── --}}
        @if(!empty($settings['show_payments']))

        {{-- Installment schedule --}}
        @if(!empty($booking->installment_schedule) && count($booking->installment_schedule))
        <div class="section-head">Instalment / Payment Schedule</div>
        <div class="section-box" style="margin-bottom:18px">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Due Date</th>
                        <th style="text-align:right">Amount</th>
                        <th style="text-align:center">Status</th>
                        <th style="text-align:right">Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->installment_schedule as $term)
                    <tr style="{{ $term['status'] === 'paid' ? 'background:#f0fdf4' : '' }}">
                        <td style="font-weight:600">
                            @if($term['type'] === 'downpayment') Down Payment
                            @else Month {{ $term['term'] }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($term['due_date'])->format('M d, Y') }}</td>
                        <td class="amount">₱{{ number_format($term['amount'], 2) }}</td>
                        <td style="text-align:center">
                            <span class="pill {{ $term['status'] === 'paid' ? 'pill-paid' : 'pill-pending' }}">
                                {{ ucfirst($term['status']) }}
                            </span>
                        </td>
                        <td style="text-align:right;color:#64748b;font-size:8.5pt">
                            {{ !empty($term['paid_at']) ? \Carbon\Carbon::parse($term['paid_at'])->format('M d, Y') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="padding:8px 10px;background:#f8fafc">Total</td>
                        <td style="padding:8px 10px;text-align:right;background:#f8fafc">
                            ₱{{ number_format(collect($booking->installment_schedule)->sum('amount'), 2) }}
                        </td>
                        <td colspan="2" style="background:#f8fafc"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Xendit payment records --}}
        @php $paidPayments = $booking->payments->where('status','completed')->sortBy('paid_at'); @endphp
        @if($paidPayments->count())
        <div class="section-head">Payment Records</div>
        <div class="section-box" style="margin-bottom:18px">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transaction ID</th>
                        <th>Method</th>
                        <th style="text-align:right">Amount</th>
                        <th style="text-align:right">Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paidPayments as $i => $pmt)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-family:monospace;font-size:8pt">{{ $pmt->transaction_id }}</td>
                        <td>{{ strtoupper($pmt->method ?? '—') }}</td>
                        <td class="amount">₱{{ number_format($pmt->amount, 2) }}</td>
                        <td style="text-align:right">
                            {{ $pmt->paid_at ? $pmt->paid_at->format('M d, Y') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @endif {{-- /show_payments --}}

        {{-- ── TERMS & CONDITIONS ───────────────────────────────── --}}
        <div class="section-head">Terms & Conditions</div>
        <div class="section-box">
            <div style="padding:10px 14px;font-size:8pt;color:#475569;line-height:1.7">
                <ul style="margin:0;padding-left:18px">
                    <li>This booking confirmation is subject to availability and final payment.</li>
                    <li>Full payment must be settled at least <strong>{{ $booking->tour->balance_due_days_before_travel ?? 30 }} days</strong> before the departure date.</li>
                    <li>Cancellations made less than 30 days before departure may incur penalties.</li>
                    <li>The company reserves the right to reschedule or cancel trips due to force majeure events.</li>
                    <li>Travel insurance is the responsibility of the traveler.</li>
                    <li>Valid identification documents must be presented at check-in.</li>
                </ul>
            </div>
        </div>

    </div>{{-- /content --}}

    {{-- ── FOOTER ───────────────────────────────────────────────── --}}
    <div class="footer">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td style="vertical-align:middle;font-size:8pt;color:#94a3b8">
                    <strong style="color:#fff;font-size:10pt">{{ $settings['company_name'] }}</strong><br>
                    @if(!empty($settings['contact_address'])) {{ $settings['contact_address'] }}<br>@endif
                    @if(!empty($settings['contact_email'])) {{ $settings['contact_email'] }}@endif
                    @if(!empty($settings['contact_phone'])) &nbsp;|&nbsp; {{ $settings['contact_phone'] }}@endif
                </td>
                <td style="vertical-align:middle;text-align:right;font-size:8pt;color:#94a3b8">
                    @if(!empty($settings['facebook_url']))
                        Facebook: <strong style="color:#93c5fd">{{ $settings['facebook_url'] }}</strong><br>
                    @endif
                    <span style="font-size:7.5pt">Generated: {{ now()->format('F d, Y h:i A') }}</span>
                </td>
            </tr>
        </table>
        <div class="footer-disclaimer">
            {{ $settings['footer_text'] }}
        </div>
    </div>

</div>
</body>
</html>
