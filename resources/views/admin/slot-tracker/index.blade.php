@extends('layouts.admin')
@section('title', 'Slot Tracker')

@section('breadcrumb')
    Slot Tracker
@endsection

@push('styles')
<style>
.slot-stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.slot-stat { background:#fff; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.25rem 1rem; text-align:center; }
.slot-stat__num { font-size:2rem; font-weight:700; line-height:1; }
.slot-stat__lbl { font-size:.75rem; color:#64748b; margin-top:.25rem; text-transform:uppercase; letter-spacing:.06em; }
.slot-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.slot-table th { background:#1e293b; color:#e2e8f0; padding:.6rem .9rem; text-align:left; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; }
.slot-table td { padding:.65rem .9rem; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
.slot-table tr:hover { background:#f8fafc; }
.slot-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .7rem; border-radius:2rem; font-size:.75rem; font-weight:600; }
.badge-active    { background:#dcfce7; color:#166534; }
.badge-sold_out  { background:#fee2e2; color:#991b1b; }
.badge-cancelled { background:#f1f5f9; color:#475569; }
.badge-warning   { background:#fef9c3; color:#854d0e; }
.seats-bar { width:120px; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; display:inline-block; vertical-align:middle; margin-right:.5rem; }
.seats-bar__fill { height:100%; border-radius:4px; }
.filter-tabs { display:flex; gap:.5rem; margin-bottom:1.25rem; }
.filter-tab { padding:.4rem 1rem; border-radius:.5rem; font-size:.85rem; font-weight:500; text-decoration:none; color:#475569; background:#f1f5f9; }
.filter-tab.active { background:#0f172a; color:#fff; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2><i class="fas fa-layer-group" style="color:#3b82f6"></i> Slot Tracker</h2>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('admin.tours.index') }}" class="btn btn-outline">
            <i class="fas fa-map-marked-alt"></i> Manage Tours
        </a>
    </div>
</div>

{{-- Summary Stats --}}
<div class="slot-stat-grid">
    <div class="slot-stat">
        <div class="slot-stat__num" style="color:#3b82f6">{{ $stats['total_schedules'] }}</div>
        <div class="slot-stat__lbl">Schedules</div>
    </div>
    <div class="slot-stat">
        <div class="slot-stat__num" style="color:#0f172a">{{ number_format($stats['total_seats']) }}</div>
        <div class="slot-stat__lbl">Total Seats</div>
    </div>
    <div class="slot-stat">
        <div class="slot-stat__num" style="color:#f59e0b">{{ number_format($stats['total_booked']) }}</div>
        <div class="slot-stat__lbl">Occupied</div>
    </div>
    <div class="slot-stat">
        <div class="slot-stat__num" style="color:#16a34a">{{ number_format($stats['total_available']) }}</div>
        <div class="slot-stat__lbl">Available</div>
    </div>
    <div class="slot-stat">
        <div class="slot-stat__num" style="color:#dc2626">{{ $stats['overbooked_count'] }}</div>
        <div class="slot-stat__lbl">Overbooked</div>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="filter-tabs">
    <a href="{{ route('admin.slot-tracker.index', ['filter'=>'upcoming']) }}" class="filter-tab {{ $filter==='upcoming' ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i> Upcoming
    </a>
    <a href="{{ route('admin.slot-tracker.index', ['filter'=>'all']) }}" class="filter-tab {{ $filter==='all' ? 'active' : '' }}">
        <i class="fas fa-list"></i> All
    </a>
    <a href="{{ route('admin.slot-tracker.index', ['filter'=>'past']) }}" class="filter-tab {{ $filter==='past' ? 'active' : '' }}">
        <i class="fas fa-history"></i> Past
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        @if($schedules->isEmpty())
            <div style="text-align:center;padding:3rem;color:#94a3b8">
                <i class="fas fa-calendar-times" style="font-size:2.5rem;margin-bottom:.75rem;display:block"></i>
                No schedules found for this filter.
            </div>
        @else
        <div style="overflow-x:auto">
        <table class="slot-table">
            <thead>
                <tr>
                    <th>Tour</th>
                    <th>Departure</th>
                    <th>Return</th>
                    <th style="text-align:center">Total Seats</th>
                    <th style="text-align:center">Occupied</th>
                    <th style="text-align:center">Available</th>
                    <th>Capacity</th>
                    <th style="text-align:center">Pending</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $sched)
                @php
                    $pct       = $sched->available_seats > 0 ? ($sched->booked_seats / $sched->available_seats) * 100 : 100;
                    $barColor  = $pct >= 100 ? '#dc2626' : ($pct >= 80 ? '#f59e0b' : '#16a34a');
                    $available = max(0, $sched->available_seats - $sched->booked_seats);
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.tours.schedules.index', $sched->tour) }}" style="font-weight:600;color:#1e40af;text-decoration:none">
                            {{ $sched->tour->title }}
                        </a>
                        @if($sched->tour->line)
                            <br><small style="color:#94a3b8;font-size:.75rem">{{ $sched->tour->line }}</small>
                        @endif
                    </td>
                    <td style="white-space:nowrap;font-weight:500">
                        {{ \Carbon\Carbon::parse($sched->departure_date)->format('M d, Y') }}
                    </td>
                    <td style="white-space:nowrap">
                        {{ $sched->return_date ? \Carbon\Carbon::parse($sched->return_date)->format('M d, Y') : '—' }}
                    </td>
                    <td style="text-align:center;font-weight:600">{{ $sched->available_seats }}</td>
                    <td style="text-align:center;font-weight:700;color:{{ $pct >= 100 ? '#dc2626' : '#f59e0b' }}">
                        {{ $sched->booked_seats }}
                    </td>
                    <td style="text-align:center;font-weight:700;color:{{ $available > 0 ? '#16a34a' : '#dc2626' }}">
                        {{ $available }}
                    </td>
                    <td>
                        <span class="seats-bar">
                            <span class="seats-bar__fill" style="width:{{ min(100,$pct) }}%;background:{{ $barColor }}"></span>
                        </span>
                        <small style="color:#64748b">{{ round($pct) }}%</small>
                    </td>
                    <td style="text-align:center">
                        @if($sched->pending_count > 0)
                            <a href="{{ route('admin.bookings.index', ['status'=>'pending']) }}"
                               style="background:#fef9c3;color:#854d0e;padding:.2rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600;text-decoration:none">
                                {{ $sched->pending_count }} pending
                            </a>
                        @else
                            <span style="color:#cbd5e1;font-size:.8rem">—</span>
                        @endif
                    </td>
                    <td>
                        @if($sched->booked_seats > $sched->available_seats)
                            <span class="slot-badge badge-sold_out"><i class="fas fa-exclamation-triangle"></i> OVERBOOKED</span>
                        @elseif($sched->status === 'sold_out' || $available === 0)
                            <span class="slot-badge badge-sold_out"><i class="fas fa-times-circle"></i> SOLD OUT</span>
                        @elseif($sched->status === 'cancelled')
                            <span class="slot-badge badge-cancelled">CANCELLED</span>
                        @elseif($pct >= 80)
                            <span class="slot-badge badge-warning"><i class="fas fa-exclamation-circle"></i> ALMOST FULL</span>
                        @else
                            <span class="slot-badge badge-active"><i class="fas fa-check-circle"></i> SLOTS AVAILABLE</span>
                        @endif
                    </td>
                    <td style="max-width:200px;font-size:.8rem;color:#64748b">
                        {{ $sched->notes ? \Illuminate\Support\Str::limit($sched->notes, 60) : '—' }}
                    </td>
                    <td style="white-space:nowrap">
                        <a href="{{ route('admin.tours.schedules.index', $sched->tour) }}"
                           class="btn btn-xs btn-outline" title="Manage Slots">
                            <i class="fas fa-cog"></i> Manage
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>
@endsection
