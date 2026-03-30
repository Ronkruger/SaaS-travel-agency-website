<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report — {{ $startOfMonth->format('F Y') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px 32px; font-family: 'Segoe UI', Arial, sans-serif; color: #1a202c; font-size: 13px; background: #fff; }

        /* Header */
        .report-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1e3a5f; padding-bottom: 14px; margin-bottom: 20px; }
        .report-header h1 { margin: 0; font-size: 20px; color: #1e3a5f; }
        .report-header .meta { text-align: right; font-size: 11px; color: #6b7280; }
        .report-header .meta strong { display: block; font-size: 14px; color: #1a202c; margin-bottom: 2px; }

        /* Section */
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; margin: 18px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }

        /* KPI grid */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 4px; }
        .kpi-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; }
        .kpi-card .label { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: .06em; }
        .kpi-card .value { font-size: 18px; font-weight: 800; color: #1e3a5f; line-height: 1.2; margin: 3px 0 1px; }
        .kpi-card .sub { font-size: 10px; color: #9ca3af; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        thead th { background: #f1f5f9; padding: 7px 10px; text-align: left; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child td { border-bottom: none; }
        tfoot td { padding: 7px 10px; border-top: 2px solid #e2e8f0; font-weight: 700; background: #f8fafc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #16a34a; }
        .text-muted { color: #9ca3af; }

        /* Two-column layout */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* Badge */
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-paid    { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }

        /* Footer */
        .report-footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #9ca3af; display: flex; justify-content: space-between; }

        /* Print controls */
        .print-bar { background: #1e3a5f; color: #fff; padding: 10px 20px; margin: -24px -32px 24px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .print-bar button { background: #fff; color: #1e3a5f; border: none; border-radius: 6px; padding: 6px 16px; font-weight: 700; cursor: pointer; font-size: 13px; }
        .print-bar .hint { font-size: 11px; color: #93c5fd; }

        @media print {
            .print-bar { display: none; }
            body { padding: 16px 20px; }
        }
    </style>
</head>
<body>

{{-- Print bar (hidden when printing) --}}
<div class="print-bar">
    <span>
        <strong>{{ $startOfMonth->format('F Y') }} Performance Report</strong>
        <span class="hint"> — Use Ctrl+P / Cmd+P to save as PDF</span>
    </span>
    <button onclick="window.print()"><i>🖨</i> Print / Save PDF</button>
</div>

{{-- Report Header --}}
<div class="report-header">
    <div>
        <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Discover Group</div>
        <h1>Monthly Performance Report</h1>
        <div style="font-size:13px;color:#374151;font-weight:600">{{ $startOfMonth->format('F Y') }}</div>
    </div>
    <div class="meta">
        <strong>{{ $startOfMonth->format('F 1, Y') }} – {{ $startOfMonth->copy()->endOfMonth()->format('F d, Y') }}</strong>
        Generated: {{ now()->format('M d, Y H:i') }}<br>
        Prepared by: Discover Group Admin
    </div>
</div>

{{-- KPI Cards --}}
<div class="section-title">Summary</div>
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="label">Total Bookings</div>
        <div class="value">{{ number_format($monthSummary['total_bookings']) }}</div>
        <div class="sub">{{ $monthSummary['cancelled'] }} cancelled</div>
    </div>
    <div class="kpi-card">
        <div class="label">Gross Revenue</div>
        <div class="value text-green">₱{{ number_format($monthSummary['total_revenue'], 0) }}</div>
        <div class="sub">{{ $monthSummary['new_bookings'] }} active bookings</div>
    </div>
    <div class="kpi-card">
        <div class="label">Avg. per Booking</div>
        @php $avg = $monthSummary['new_bookings'] > 0 ? $monthSummary['total_revenue'] / $monthSummary['new_bookings'] : 0; @endphp
        <div class="value" style="color:#7c3aed">₱{{ number_format($avg, 0) }}</div>
        <div class="sub">average booking value</div>
    </div>
    <div class="kpi-card">
        <div class="label">New Customers</div>
        <div class="value" style="color:#64748b">{{ number_format($monthSummary['new_users']) }}</div>
        <div class="sub">registered this month</div>
    </div>
</div>
<div class="kpi-grid" style="margin-top:8px">
    <div class="kpi-card" style="border-left:3px solid #0ea5e9">
        <div class="label">Online Revenue (Xendit)</div>
        <div class="value" style="font-size:15px;color:#0ea5e9">₱{{ number_format($monthSummary['online_revenue'], 0) }}</div>
    </div>
    <div class="kpi-card" style="border-left:3px solid #f59e0b">
        <div class="label">Cash / Office Revenue</div>
        <div class="value" style="font-size:15px;color:#f59e0b">₱{{ number_format($monthSummary['cash_revenue'], 0) }}</div>
    </div>
    <div class="kpi-card" style="border-left:3px solid #16a34a">
        <div class="label">Collection Rate</div>
        @php $collected = $monthSummary['online_revenue'] + $monthSummary['cash_revenue']; $rate = $monthSummary['total_revenue'] > 0 ? ($collected / $monthSummary['total_revenue'] * 100) : 0; @endphp
        <div class="value" style="font-size:15px;color:#16a34a">{{ number_format($rate, 1) }}%</div>
        <div class="sub">₱{{ number_format($collected, 0) }} collected</div>
    </div>
    <div class="kpi-card" style="border-left:3px solid #ef4444">
        <div class="label">Cancellation Rate</div>
        @php $cancelRate = $monthSummary['total_bookings'] > 0 ? ($monthSummary['cancelled'] / $monthSummary['total_bookings'] * 100) : 0; @endphp
        <div class="value" style="font-size:15px;color:#ef4444">{{ number_format($cancelRate, 1) }}%</div>
        <div class="sub">{{ $monthSummary['cancelled'] }} of {{ $monthSummary['total_bookings'] }}</div>
    </div>
</div>

{{-- Two column: Top Tours + Payment Breakdown --}}
<div class="two-col" style="margin-top:16px">
    <div>
        <div class="section-title">Top Tours This Month</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tour</th>
                    <th class="text-right">Bookings</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topTours as $i => $tb)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>{{ Str::limit($tb->tour->title ?? 'N/A', 30) }}</td>
                    <td class="text-right">{{ $tb->booking_count }}</td>
                    <td class="text-right text-green">₱{{ number_format($tb->revenue, 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center">No bookings this month</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <div class="section-title">Payment Method Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th class="text-right">Bookings</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentBreakdown as $pm)
                <tr>
                    <td style="text-transform:capitalize">
                        @if($pm->payment_method === 'xendit') Online (Xendit)
                        @elseif($pm->payment_method === 'cash') Cash / Office
                        @else Installment @endif
                    </td>
                    <td class="text-right">{{ $pm->count }}</td>
                    <td class="text-right">₱{{ number_format($pm->revenue, 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center">No data</td></tr>
                @endforelse
            </tbody>
            @if($paymentBreakdown->isNotEmpty())
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="text-right">{{ $paymentBreakdown->sum('count') }}</td>
                    <td class="text-right text-green">₱{{ number_format($paymentBreakdown->sum('revenue'), 0) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Daily Breakdown Table --}}
<div class="section-title" style="margin-top:18px">Day-by-Day Breakdown</div>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Day</th>
            <th class="text-right">Bookings</th>
            <th class="text-right">Revenue (PHP)</th>
            <th>Sales Activity</th>
        </tr>
    </thead>
    <tbody>
        @php $maxRev = collect($dailySales)->max('revenue') ?: 1; @endphp
        @foreach($dailySales as $day)
        <tr style="{{ $day['bookings'] > 0 ? 'background:#f0fdf4' : '' }}">
            <td>{{ \Carbon\Carbon::parse($day['date'])->format('M d, Y') }}</td>
            <td class="text-muted">{{ \Carbon\Carbon::parse($day['date'])->format('D') }}</td>
            <td class="text-right">{{ $day['bookings'] > 0 ? $day['bookings'] : '—' }}</td>
            <td class="text-right {{ $day['revenue'] > 0 ? 'text-green' : 'text-muted' }}">
                {{ $day['revenue'] > 0 ? '₱'.number_format($day['revenue'], 0) : '—' }}
            </td>
            <td style="width:120px">
                @if($day['revenue'] > 0)
                <div style="background:#1e3a5f;height:8px;border-radius:4px;width:{{ round($day['revenue'] / $maxRev * 100) }}%;min-width:4px"></div>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Monthly Total</td>
            <td class="text-right">{{ collect($dailySales)->sum('bookings') }}</td>
            <td class="text-right text-green">₱{{ number_format(collect($dailySales)->sum('revenue'), 0) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

{{-- Footer --}}
<div class="report-footer">
    <span>Discover Group — Confidential. For internal use only.</span>
    <span>{{ config('app.url') }} · Generated {{ now()->format('M d, Y H:i') }}</span>
</div>

</body>
</html>
