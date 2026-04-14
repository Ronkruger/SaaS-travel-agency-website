@extends('layouts.admin')
@section('title', 'Dashboard')

@section('breadcrumb')
    <span>Dashboard</span>
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-dashboard')
@endsection

@section('content')
<div class="page-title">
    <h2>Dashboard <span class="live-dot" id="liveDot" title="Live data — updates every 30 s"></span></h2>
    <p>Welcome back, {{ auth()->user()->name }}</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card--blue">
        <div class="stat-icon"><i class="fas fa-map-marked-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-active-tours">{{ $stats['active_tours'] }}</span>
            <span class="stat-label">Active Tours</span>
        </div>
    </div>
    <div class="stat-card stat-card--green">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-total-bookings">{{ $stats['total_bookings'] }}</span>
            <span class="stat-label">Total Bookings</span>
        </div>
    </div>
    <div class="stat-card stat-card--orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-pending-bookings">{{ $stats['pending_bookings'] }}</span>
            <span class="stat-label">Pending Bookings</span>
        </div>
    </div>
    <div class="stat-card stat-card--purple">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-total-revenue">₱{{ number_format($stats['total_revenue'], 0) }}</span>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-total-users">{{ $stats['total_users'] }}</span>
            <span class="stat-label">Registered Users</span>
        </div>
    </div>
    <div class="stat-card stat-card--yellow">
        <div class="stat-icon"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="stat-pending-reviews">{{ $stats['pending_reviews'] }}</span>
            <span class="stat-label">Pending Reviews</span>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card mt-4">
    <div class="card-header" style="flex-wrap:wrap;gap:.5rem">
        <h4><i class="fas fa-chart-line"></i> Revenue Chart</h4>
        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
            <label style="font-size:.82rem;color:#6b7280;margin:0">From</label>
            <input type="date" id="chart-from" class="form-control" style="width:140px;padding:.3rem .6rem;font-size:.85rem">
            <label style="font-size:.82rem;color:#6b7280;margin:0">To</label>
            <input type="date" id="chart-to"   class="form-control" style="width:140px;padding:.3rem .6rem;font-size:.85rem">
            <button id="chart-apply" class="btn btn-outline btn-sm"><i class="fas fa-sync-alt"></i> Apply</button>
            <button id="chart-reset" class="btn btn-ghost btn-sm">Last 12 months</button>
            <span id="chart-total" style="font-size:.85rem;font-weight:700;color:#1e3a5f;margin-left:.5rem"></span>
        </div>
    </div>
    <div class="card-body" style="padding:1.25rem">
        <canvas id="revenueChart" style="max-height:260px"></canvas>
    </div>
</div>

<div class="dashboard-grid mt-4">
    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-calendar-check"></i> Recent Bookings</h4>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-scroll-outer" id="bookingsScrollOuter">
        <div class="card-body p-0 table-responsive" id="bookingsScrollEl">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="recentBookingsBody">
                    @foreach($recentBookings as $booking)
                        <tr data-booking="{{ $booking->booking_number }}">
                            <td><code>{{ $booking->booking_number }}</code></td>
                            <td>{{ $booking->user->name ?? $booking->contact_name ?? '—' }}</td>
                            <td>{{ Str::limit($booking->tour->title ?? '', 30) }}</td>
                            <td>{{ $booking->tour_date->format('M d, Y') }}</td>
                            <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                            <td><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-xs btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>

    <!-- Top Tours & Revenue -->
    <div>
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="fas fa-trophy"></i> Top Tours</h4>
            </div>
            <div class="card-body p-0">
                @foreach($topTours as $tour)
                    <div class="top-item">
                        <img src="{{ cdn_url($tour->main_image) }}" alt="{{ $tour->title }}" class="top-item-img">
                        <div class="top-item-info">
                            <strong>{{ Str::limit($tour->title, 35) }}</strong>
                            <span>{{ $tour->total_bookings }} bookings</span>
                        </div>
                        <div class="top-item-rating">
                            <i class="fas fa-star text-yellow"></i>
                            {{ number_format($tour->average_rating, 1) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($stats['pending_reviews'] > 0)
            <div class="card alert-card">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x text-yellow mb-2"></i>
                    <h5>{{ $stats['pending_reviews'] }} Pending Reviews</h5>
                    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning">
                        Review Them
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" integrity="sha512-ZwR1/gSZM3ai6vCdI+LVF1zSq/5HznD3oD+sCoJrzXJ+yKqtkPsqkOOm1L7jCoed7I2yFexoyiD1SgY3XHGQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
    'use strict';

    const STATS_URL    = '{{ route('admin.live.stats') }}';
    const BOOKINGS_URL = '{{ route('admin.live.bookings') }}';
    const CHART_URL    = '{{ route('admin.live.revenue-chart') }}';
    const INTERVAL_MS  = 30000; // 30 seconds

    const dot = document.getElementById('liveDot');

    function flash(el) {
        el.classList.remove('stat-flash');
        // Force reflow so the animation restarts
        void el.offsetWidth;
        el.classList.add('stat-flash');
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(value)) {
            el.textContent = value;
            flash(el);
        }
    }

    function fmt(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 0 });
    }

    function statusBadge(status) {
        return `<span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }

    async function refreshStats() {
        try {
            const res  = await fetch(STATS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            setText('stat-active-tours',     data.active_tours);
            setText('stat-total-bookings',   data.total_bookings);
            setText('stat-pending-bookings', data.pending_bookings);
            setText('stat-total-revenue',    fmt(data.total_revenue));
            setText('stat-total-users',      data.total_users);
            setText('stat-pending-reviews',  data.pending_reviews);
        } catch (_) { /* network hiccup — ignore */ }
    }

    async function refreshBookings() {
        try {
            const res  = await fetch(BOOKINGS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const rows = await res.json();

            const tbody  = document.getElementById('recentBookingsBody');
            if (!tbody) return;

            // Build a set of current booking numbers already rendered
            const existing = new Set([...tbody.querySelectorAll('tr[data-booking]')].map(r => r.dataset.booking));

            let hasNew = false;
            rows.forEach(b => {
                if (!existing.has(b.booking_number)) hasNew = true;
            });

            if (!hasNew) return; // nothing changed

            tbody.innerHTML = rows.map(b => `
                <tr data-booking="${b.booking_number}">
                    <td><code>${b.booking_number}</code></td>
                    <td>${b.customer}</td>
                    <td>${b.tour}</td>
                    <td>${b.date}</td>
                    <td>${b.amount}</td>
                    <td>${statusBadge(b.status)}</td>
                    <td><a href="${b.url}" class="btn btn-xs btn-outline"><i class="fas fa-eye"></i></a></td>
                </tr>
            `).join('');

            flash(tbody);
        } catch (_) { /* network hiccup — ignore */ }
    }

    function pulse() {
        if (dot) dot.classList.toggle('live-dot--active');
    }

    async function tick() {
        pulse();
        await Promise.all([refreshStats(), refreshBookings()]);
        pulse();
    }

    // Horizontal scroll indicator for the bookings table
    (function () {
        const outer = document.getElementById('bookingsScrollOuter');
        const el    = document.getElementById('bookingsScrollEl');
        if (!outer || !el) return;
        function updateIndicator() {
            const overflows = el.scrollWidth > el.clientWidth + 2;
            const atEnd     = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
            outer.classList.toggle('has-overflow', overflows && !atEnd);
        }
        el.addEventListener('scroll', updateIndicator, { passive: true });
        window.addEventListener('resize', updateIndicator);
        updateIndicator();
    })();

    // Start polling
    setInterval(tick, INTERVAL_MS);

    // ── Revenue Chart ──────────────────────────────────────────────────
    let revenueChart = null;

    function initChart(labels, totals) {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;
        if (revenueChart) revenueChart.destroy();

        revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: totals,
                    backgroundColor: 'rgba(99, 102, 241, 0.25)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                    tension: 0.4,
                    type: 'bar',
                }, {
                    label: 'Trend',
                    data: totals,
                    type: 'line',
                    borderColor: 'rgba(16, 185, 129, 0.8)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + fmt(ctx.parsed.y),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => fmt(val),
                            maxTicksLimit: 6,
                            font: { size: 11 },
                        },
                        grid: { color: 'rgba(0,0,0,.04)' },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    }

    async function loadChart(fromDate, toDate) {
        const params = new URLSearchParams();
        if (fromDate) params.set('from_date', fromDate);
        if (toDate)   params.set('to_date',   toDate);

        try {
            const res  = await fetch(CHART_URL + '?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();

            initChart(data.labels, data.totals);

            const totalEl = document.getElementById('chart-total');
            if (totalEl) totalEl.textContent = 'Total: ' + fmt(data.sum);

            // Sync inputs to actual resolved range
            const fromEl = document.getElementById('chart-from');
            const toEl   = document.getElementById('chart-to');
            if (fromEl && !fromEl.value) fromEl.value = data.from;
            if (toEl   && !toEl.value)   toEl.value   = data.to;
        } catch (_) {}
    }

    // Default: last 12 months
    (function () {
        const today = new Date();
        const from  = new Date(today.getFullYear(), today.getMonth() - 11, 1);
        document.getElementById('chart-from').value = from.toISOString().split('T')[0];
        document.getElementById('chart-to').value   = today.toISOString().split('T')[0];
    })();

    loadChart(
        document.getElementById('chart-from').value,
        document.getElementById('chart-to').value
    );

    document.getElementById('chart-apply').addEventListener('click', function () {
        loadChart(
            document.getElementById('chart-from').value,
            document.getElementById('chart-to').value
        );
    });

    document.getElementById('chart-reset').addEventListener('click', function () {
        const today = new Date();
        const from  = new Date(today.getFullYear(), today.getMonth() - 11, 1);
        document.getElementById('chart-from').value = from.toISOString().split('T')[0];
        document.getElementById('chart-to').value   = today.toISOString().split('T')[0];
        loadChart(
            document.getElementById('chart-from').value,
            document.getElementById('chart-to').value
        );
    });
    // ── End Revenue Chart ───────────────────────────────────────────────

})();
</script>
@endpush
