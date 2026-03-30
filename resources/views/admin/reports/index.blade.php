@extends('layouts.admin')
@section('title', 'Monthly Performance Report')

@section('content')
<div class="admin-page-header">
    <div>
        <h1><i class="fas fa-chart-bar"></i> Monthly Performance Report</h1>
        <p class="text-muted">Sales, bookings, and revenue analytics</p>
    </div>
</div>

{{-- Month / Year Filter --}}
<form method="GET" action="{{ route('admin.reports.index') }}" class="card mb-4" style="padding:1.25rem 1.5rem">
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end">
        <div>
            <label style="font-size:.82rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.25rem">Month</label>
            <select name="month" class="form-control" style="min-width:130px">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.82rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.25rem">Year</label>
            <select name="year" class="form-control" style="min-width:100px">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:.5rem 1.25rem">
            <i class="fas fa-filter"></i> Apply
        </button>
        <span style="font-size:.9rem;color:#6b7280;align-self:center">
            Showing: <strong>{{ $startOfMonth->format('F Y') }}</strong>
        </span>
    </div>
</form>

{{-- KPI Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1.25rem;margin-bottom:2rem">
    <div class="card" style="padding:1.25rem;border-left:4px solid #1e3a5f">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">Total Bookings</div>
        <div style="font-size:2rem;font-weight:800;color:#1e3a5f;line-height:1.2;margin:.25rem 0">{{ number_format($monthSummary['total_bookings']) }}</div>
        <div style="font-size:.8rem;color:#6b7280">{{ $monthSummary['cancelled'] }} cancelled</div>
    </div>
    <div class="card" style="padding:1.25rem;border-left:4px solid #16a34a">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">Gross Revenue</div>
        <div style="font-size:1.65rem;font-weight:800;color:#16a34a;line-height:1.2;margin:.25rem 0">₱{{ number_format($monthSummary['total_revenue'], 0) }}</div>
        <div style="font-size:.8rem;color:#6b7280">from {{ $monthSummary['new_bookings'] }} active bookings</div>
    </div>
    <div class="card" style="padding:1.25rem;border-left:4px solid #0ea5e9">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">Online Revenue</div>
        <div style="font-size:1.65rem;font-weight:800;color:#0ea5e9;line-height:1.2;margin:.25rem 0">₱{{ number_format($monthSummary['online_revenue'], 0) }}</div>
        <div style="font-size:.8rem;color:#6b7280">Xendit payments</div>
    </div>
    <div class="card" style="padding:1.25rem;border-left:4px solid #f59e0b">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">Cash / Office</div>
        <div style="font-size:1.65rem;font-weight:800;color:#f59e0b;line-height:1.2;margin:.25rem 0">₱{{ number_format($monthSummary['cash_revenue'], 0) }}</div>
        <div style="font-size:.8rem;color:#6b7280">paid cash bookings</div>
    </div>
    <div class="card" style="padding:1.25rem;border-left:4px solid #7c3aed">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">Avg. per Booking</div>
        @php $avg = $monthSummary['new_bookings'] > 0 ? $monthSummary['total_revenue'] / $monthSummary['new_bookings'] : 0; @endphp
        <div style="font-size:1.65rem;font-weight:800;color:#7c3aed;line-height:1.2;margin:.25rem 0">₱{{ number_format($avg, 0) }}</div>
        <div style="font-size:.8rem;color:#6b7280">average booking value</div>
    </div>
    <div class="card" style="padding:1.25rem;border-left:4px solid #64748b">
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">New Customers</div>
        <div style="font-size:2rem;font-weight:800;color:#64748b;line-height:1.2;margin:.25rem 0">{{ number_format($monthSummary['new_users']) }}</div>
        <div style="font-size:.8rem;color:#6b7280">registered this month</div>
    </div>
</div>

{{-- Charts row --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:2rem">

    {{-- Daily Sales Bar Chart --}}
    <div class="card" style="padding:1.5rem">
        <h5 style="margin:0 0 1.25rem;font-size:1rem">
            <i class="fas fa-chart-bar" style="color:#1e3a5f"></i>
            Daily Sales — {{ $startOfMonth->format('F Y') }}
        </h5>
        <canvas id="dailyChart" height="120"></canvas>
    </div>

    {{-- Payment Method Donut --}}
    <div class="card" style="padding:1.5rem">
        <h5 style="margin:0 0 1.25rem;font-size:1rem">
            <i class="fas fa-chart-pie" style="color:#7c3aed"></i>
            Payment Methods
        </h5>
        <canvas id="paymentChart" height="180"></canvas>
        <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.375rem">
            @foreach($paymentBreakdown as $pm)
            <div style="display:flex;justify-content:space-between;font-size:.85rem">
                <span style="text-transform:capitalize">
                    @if($pm->payment_method === 'xendit') 💳 Online (Xendit)
                    @elseif($pm->payment_method === 'cash') 🏢 Cash / Office
                    @else 📅 Installment
                    @endif
                </span>
                <span><strong>{{ $pm->count }}</strong> · ₱{{ number_format($pm->revenue, 0) }}</span>
            </div>
            @endforeach
            @if($paymentBreakdown->isEmpty())
                <p class="text-muted" style="font-size:.85rem;margin:0">No bookings this month</p>
            @endif
        </div>
    </div>
</div>

{{-- 12-month revenue trend --}}
<div class="card" style="padding:1.5rem;margin-bottom:2rem">
    <h5 style="margin:0 0 1.25rem;font-size:1rem">
        <i class="fas fa-chart-line" style="color:#16a34a"></i>
        12-Month Revenue Trend
    </h5>
    <canvas id="trendChart" height="80"></canvas>
</div>

{{-- Bottom row: daily table + top tours --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem">

    {{-- Daily sales table --}}
    <div class="card" style="overflow:hidden">
        <div style="padding:1rem 1.25rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:.9rem">
            <i class="fas fa-table" style="color:#1e3a5f"></i>
            Day-by-Day Breakdown — {{ $startOfMonth->format('F Y') }}
        </div>
        <div style="overflow-y:auto;max-height:420px">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                <thead style="position:sticky;top:0;background:#f1f5f9">
                    <tr>
                        <th style="padding:.5rem .875rem;text-align:left;border-bottom:2px solid #e2e8f0;color:#475569">Date</th>
                        <th style="padding:.5rem .875rem;text-align:right;border-bottom:2px solid #e2e8f0;color:#475569">Bookings</th>
                        <th style="padding:.5rem .875rem;text-align:right;border-bottom:2px solid #e2e8f0;color:#475569">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalB = 0; $totalR = 0; @endphp
                    @foreach($dailySales as $day)
                    @php $totalB += $day['bookings']; $totalR += $day['revenue']; @endphp
                    <tr style="border-bottom:1px solid #f1f5f9{{ $day['bookings'] > 0 ? ';background:#f0fdf4' : '' }}">
                        <td style="padding:.45rem .875rem;color:{{ $day['bookings'] > 0 ? '#166534' : '#94a3b8' }}">
                            {{ \Carbon\Carbon::parse($day['date'])->format('D, M d') }}
                        </td>
                        <td style="padding:.45rem .875rem;text-align:right;font-weight:{{ $day['bookings'] > 0 ? '700' : '400' }}">
                            {{ $day['bookings'] > 0 ? $day['bookings'] : '—' }}
                        </td>
                        <td style="padding:.45rem .875rem;text-align:right;color:{{ $day['revenue'] > 0 ? '#166534' : '#94a3b8' }};font-weight:{{ $day['revenue'] > 0 ? '600' : '400' }}">
                            {{ $day['revenue'] > 0 ? '₱'.number_format($day['revenue'], 0) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;border-top:2px solid #e2e8f0">
                        <td style="padding:.5rem .875rem">Total</td>
                        <td style="padding:.5rem .875rem;text-align:right">{{ $totalB }}</td>
                        <td style="padding:.5rem .875rem;text-align:right;color:#16a34a">₱{{ number_format($totalR, 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Top tours --}}
    <div class="card" style="overflow:hidden">
        <div style="padding:1rem 1.25rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:.9rem">
            <i class="fas fa-trophy" style="color:#f59e0b"></i>
            Top Tours This Month
        </div>
        @if($topTours->isEmpty())
            <div style="padding:2rem;text-align:center;color:#94a3b8">No bookings this month</div>
        @else
        <table style="width:100%;border-collapse:collapse;font-size:.875rem">
            <thead style="background:#f1f5f9">
                <tr>
                    <th style="padding:.5rem .875rem;text-align:left;border-bottom:2px solid #e2e8f0;color:#475569">#</th>
                    <th style="padding:.5rem .875rem;text-align:left;border-bottom:2px solid #e2e8f0;color:#475569">Tour</th>
                    <th style="padding:.5rem .875rem;text-align:right;border-bottom:2px solid #e2e8f0;color:#475569">Bookings</th>
                    <th style="padding:.5rem .875rem;text-align:right;border-bottom:2px solid #e2e8f0;color:#475569">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topTours as $i => $tb)
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:.5rem .875rem;color:#94a3b8;font-weight:700">{{ $i+1 }}</td>
                    <td style="padding:.5rem .875rem">
                        <div style="font-weight:600;font-size:.85rem">{{ Str::limit($tb->tour->title ?? 'N/A', 32) }}</div>
                        <div style="font-size:.78rem;color:#6b7280">{{ $tb->guests }} guests</div>
                    </td>
                    <td style="padding:.5rem .875rem;text-align:right;font-weight:700">{{ $tb->booking_count }}</td>
                    <td style="padding:.5rem .875rem;text-align:right;color:#16a34a;font-weight:600">₱{{ number_format($tb->revenue, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#6b7280';

// ── Daily sales bar chart ─────────────────────────────────────────────────
const dailyData = @json($dailySales);

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: dailyData.map(d => {
            const dt = new Date(d.date + 'T00:00:00');
            return dt.getDate();
        }),
        datasets: [
            {
                label: 'Revenue (₱)',
                data: dailyData.map(d => d.revenue),
                backgroundColor: 'rgba(30,58,95,.75)',
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'Bookings',
                data: dailyData.map(d => d.bookings),
                type: 'line',
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,.12)',
                tension: 0.35,
                pointRadius: 3,
                yAxisID: 'y2',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.yAxisID === 'y'
                        ? ` ₱${ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:0})}`
                        : ` ${ctx.parsed.y} booking${ctx.parsed.y !== 1 ? 's' : ''}`,
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y:  { position: 'left',  grid: { color: '#f1f5f9' }, ticks: { callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } },
            y2: { position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } },
        }
    }
});

// ── Payment method donut ──────────────────────────────────────────────────
const pmData = @json($paymentBreakdown);
if (pmData.length) {
    const pmColors = { xendit: '#1e3a5f', cash: '#16a34a', installment: '#7c3aed' };
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: pmData.map(p => p.payment_method === 'xendit' ? 'Online' : p.payment_method === 'cash' ? 'Cash' : 'Installment'),
            datasets: [{
                data: pmData.map(p => p.count),
                backgroundColor: pmData.map(p => pmColors[p.payment_method] ?? '#94a3b8'),
                borderWidth: 2, borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} bookings` } } }
        }
    });
} else {
    document.getElementById('paymentChart').closest('.card').querySelector('canvas').style.display = 'none';
}

// ── 12-month trend line ───────────────────────────────────────────────────
const trendData = @json($revenueTrend);
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendData.map(t => t.label),
        datasets: [
            {
                label: 'Revenue (₱)',
                data: trendData.map(t => t.revenue),
                borderColor: '#1e3a5f',
                backgroundColor: 'rgba(30,58,95,.08)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'Bookings',
                data: trendData.map(t => t.bookings),
                borderColor: '#16a34a',
                backgroundColor: 'transparent',
                tension: 0.4,
                borderDash: [5,4],
                pointRadius: 3,
                yAxisID: 'y2',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
        scales: {
            x: { grid: { display: false } },
            y:  { position: 'left',  grid: { color: '#f1f5f9' }, ticks: { callback: v => '₱' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } },
            y2: { position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } },
        }
    }
});
</script>
@endpush
@endsection
