@extends('layouts.admin')
@section('title', 'Tour Calendar')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Tour Calendar
@endsection

@push('styles')
<style>
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}
.cal-header-cell {
    background: #1e3a5f;
    color: #fff;
    text-align: center;
    padding: 8px 4px;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.cal-cell {
    background: #fff;
    min-height: 110px;
    padding: 6px;
    vertical-align: top;
    position: relative;
}
.cal-cell.other-month {
    background: #f9fafb;
}
.cal-cell.today {
    background: #eff6ff;
}
.cal-day-num {
    font-size: .78rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}
.cal-cell.today .cal-day-num {
    color: #2563eb;
}
.cal-event {
    border-radius: 4px;
    padding: 3px 6px;
    font-size: .7rem;
    margin-bottom: 2px;
    cursor: pointer;
    line-height: 1.3;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}
/* Occupancy color coding */
.occ-available { background: #dcfce7; color: #166534; border-left: 3px solid #22c55e; }
.occ-filling   { background: #fef3c7; color: #92400e; border-left: 3px solid #f59e0b; }
.occ-nearly    { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }
.occ-full      { background: #f3f4f6; color: #6b7280; border-left: 3px solid #9ca3af; text-decoration: line-through; }

/* Mobile responsive */
.cal-scroll-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -.5rem;
    padding: 0 .5rem;
}
.cal-scroll-wrap .cal-grid {
    min-width: 680px;
}
@media (max-width: 768px) {
    .cal-cell { min-height: 80px; padding: 4px; }
    .cal-event { font-size: .65rem; padding: 2px 4px; }
    .cal-header-cell { padding: 6px 2px; font-size: .68rem; }
    .cal-day-num { font-size: .72rem; }
}
</style>
@endpush

@section('content')
<div class="page-title-row">
    <div>
        <h2>Tour Availability Calendar</h2>
        <p>{{ $month->format('F Y') }} — Schedules color-coded by occupancy</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
        <a href="{{ route('admin.calendar.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
           class="btn btn-ghost btn-sm"><i class="fas fa-chevron-left"></i> Prev</a>
        <form action="{{ route('admin.calendar.index') }}" method="GET" style="display:inline">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control" style="width:160px;padding:.3rem .6rem"
                   onchange="this.form.submit()">
        </form>
        <a href="{{ route('admin.calendar.index', ['month' => now()->format('Y-m')]) }}"
           class="btn btn-outline btn-sm">Today</a>
        <a href="{{ route('admin.calendar.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
           class="btn btn-ghost btn-sm">Next <i class="fas fa-chevron-right"></i></a>
    </div>
</div>

{{-- Legend --}}
<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;font-size:.78rem">
    <span><span style="display:inline-block;width:12px;height:12px;background:#22c55e;border-radius:2px;margin-right:4px"></span> Available (&lt;60%)</span>
    <span><span style="display:inline-block;width:12px;height:12px;background:#f59e0b;border-radius:2px;margin-right:4px"></span> Filling (60–89%)</span>
    <span><span style="display:inline-block;width:12px;height:12px;background:#ef4444;border-radius:2px;margin-right:4px"></span> Nearly Full (90%+)</span>
    <span><span style="display:inline-block;width:12px;height:12px;background:#9ca3af;border-radius:2px;margin-right:4px"></span> Sold Out / Full</span>
</div>

@php
    $startOfGrid = $month->copy()->startOfMonth()->startOfWeek(0); // Sunday
    $endOfGrid   = $month->copy()->endOfMonth()->endOfWeek(6);     // Saturday
    $today       = now()->toDateString();
@endphp

<div class="cal-scroll-wrap">
<div class="cal-grid">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
        <div class="cal-header-cell">{{ $day }}</div>
    @endforeach

    @for($date = $startOfGrid->copy(); $date->lte($endOfGrid); $date->addDay())
        @php
            $key          = $date->format('Y-m-d');
            $daySchedules = $schedules[$key] ?? collect();
            $isOther      = $date->month !== $month->month;
            $isToday      = $key === $today;
        @endphp
        <div class="cal-cell {{ $isOther ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
            <div class="cal-day-num">{{ $date->day }}</div>
            @foreach($daySchedules as $schedule)
                @php
                    $pct = $schedule->available_seats > 0
                        ? ($schedule->booked_seats / $schedule->available_seats) * 100
                        : 100;
                    if ($pct >= 100)      $cls = 'occ-full';
                    elseif ($pct >= 90)   $cls = 'occ-nearly';
                    elseif ($pct >= 60)   $cls = 'occ-filling';
                    else                  $cls = 'occ-available';
                    $remaining = max(0, $schedule->available_seats - $schedule->booked_seats);
                @endphp
                <div class="cal-event {{ $cls }}"
                     title="{{ $schedule->tour->title }}: {{ $schedule->booked_seats }}/{{ $schedule->available_seats }} seats ({{ $remaining }} left)"
                     onclick="window.location='{{ route('admin.tours.schedules.index', $schedule->tour_id) }}'">
                    {{ Str::limit($schedule->tour->title, 20) }}
                    <span style="opacity:.7"> · {{ $remaining }}✓</span>
                </div>
            @endforeach
        </div>
    @endfor
</div>
</div>

{{-- Monthly Schedule List (below calendar) --}}
@if($schedules->isNotEmpty())
<div class="card mt-4">
    <div class="card-header">
        <h4><i class="fas fa-list"></i> All Schedules — {{ $month->format('F Y') }}</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Tour</th>
                    <th>Return</th>
                    <th>Seats</th>
                    <th>Booked</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules->flatten() as $schedule)
                    @php
                        $pct = $schedule->available_seats > 0
                            ? ($schedule->booked_seats / $schedule->available_seats) * 100 : 100;
                        if ($pct >= 100)    $barColor = '#ef4444';
                        elseif ($pct >= 90) $barColor = '#f97316';
                        elseif ($pct >= 60) $barColor = '#f59e0b';
                        else                $barColor = '#22c55e';
                    @endphp
                    <tr>
                        <td><strong>{{ $schedule->departure_date->format('D, M d') }}</strong></td>
                        <td>{{ $schedule->tour->title }}</td>
                        <td>{{ $schedule->return_date?->format('M d') ?? '—' }}</td>
                        <td>{{ $schedule->available_seats }}</td>
                        <td>{{ $schedule->booked_seats }}</td>
                        <td>
                            {{ max(0, $schedule->available_seats - $schedule->booked_seats) }}
                            <div style="width:60px;height:4px;background:#e5e7eb;border-radius:2px;margin-top:3px">
                                <div style="width:{{ min(100, $pct) }}%;height:100%;background:{{ $barColor }};border-radius:2px"></div>
                            </div>
                        </td>
                        <td><span class="status-badge status-{{ $schedule->status }}">{{ ucfirst(str_replace('_',' ',$schedule->status)) }}</span></td>
                        <td>
                            <a href="{{ route('admin.tours.schedules.index', $schedule->tour_id) }}" class="btn btn-xs btn-outline">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@endif
@endsection
