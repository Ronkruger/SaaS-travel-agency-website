@extends('layouts.admin')
@section('title', 'Activity Log')

@section('breadcrumb')
    Activity Log
@endsection

@push('styles')
<style>
.log-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.log-table th { background:#1e293b; color:#e2e8f0; padding:.55rem .9rem; text-align:left; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; }
.log-table td { padding:.6rem .9rem; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.log-table tr:hover td { background:#f8fafc; }
.log-action { display:inline-block; font-family:monospace; font-size:.75rem; background:#f1f5f9; color:#475569; padding:.15rem .5rem; border-radius:.3rem; }
.log-action.booking  { background:#dbeafe; color:#1e40af; }
.log-action.user     { background:#dcfce7; color:#166534; }
.log-action.tour     { background:#fef9c3; color:#854d0e; }
.log-action.note     { background:#ede9fe; color:#5b21b6; }
.log-action.settings { background:#fee2e2; color:#991b1b; }
.log-changes { font-size:.78rem; color:#64748b; margin-top:.3rem; }
.log-changes span { display:inline-block; background:#f8fafc; border:1px solid #e2e8f0; border-radius:.25rem; padding:.1rem .4rem; margin:.1rem .15rem; }
.log-changes .from { color:#dc2626; text-decoration:line-through; }
.log-changes .to   { color:#16a34a; }
.filter-bar { background:#fff; border:1px solid #e2e8f0; border-radius:.75rem; padding:1rem 1.25rem; margin-bottom:1.25rem; }
.filter-bar form { display:flex; flex-wrap:wrap; gap:.75rem; align-items:flex-end; }
</style>
@endpush

@section('content')
<div class="page-title-row">
    <h2><i class="fas fa-history" style="color:#6366f1"></i> Activity Log</h2>
</div>

{{-- Filters --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.activity-log.index') }}">
        <div>
            <label style="font-size:.78rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.2rem">Action</label>
            <input type="text" name="action" class="form-control" style="min-width:160px"
                   placeholder="e.g. booking" value="{{ request('action') }}">
        </div>
        <div>
            <label style="font-size:.78rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.2rem">Subject</label>
            <select name="subject" class="form-control" style="min-width:130px">
                <option value="">All</option>
                @foreach(['Booking','User','Tour','TravelFund'] as $s)
                    <option value="{{ $s }}" {{ request('subject') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.2rem">Admin</label>
            <select name="admin_id" class="form-control" style="min-width:140px">
                <option value="">All Admins</option>
                @foreach($admins as $a)
                    <option value="{{ $a->id }}" {{ request('admin_id') == $a->id ? 'selected' : '' }}>
                        {{ $a->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;font-weight:600;color:#6b7280;display:block;margin-bottom:.2rem">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request()->hasAny(['action','subject','admin_id','date']))
            <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline" style="padding:.45rem 1rem">
                <i class="fas fa-times"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        @if($logs->isEmpty())
            <div style="text-align:center;padding:3rem;color:#94a3b8">
                <i class="fas fa-history" style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i>
                No activity logged yet.
            </div>
        @else
        <div style="overflow-x:auto">
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width:160px">When</th>
                    <th style="width:140px">Admin</th>
                    <th style="width:220px">Action</th>
                    <th>Description</th>
                    <th style="width:100px">IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                @php
                    $module = explode('.', $log->action)[0] ?? 'other';
                @endphp
                <tr>
                    <td style="white-space:nowrap;color:#64748b;font-size:.8rem">
                        {{ $log->created_at->format('M d, Y') }}<br>
                        <span style="color:#94a3b8">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if($log->adminUser)
                            <span style="font-weight:600;font-size:.85rem">{{ $log->adminUser->name }}</span>
                        @else
                            <span style="color:#94a3b8;font-size:.8rem">System</span>
                        @endif
                    </td>
                    <td>
                        <span class="log-action {{ $module }}">{{ $log->action }}</span>
                        @if($log->subject_type && $log->subject_id)
                            <br><small style="color:#94a3b8;font-size:.72rem">
                                {{ $log->subject_type }} #{{ $log->subject_id }}
                            </small>
                        @endif
                    </td>
                    <td>
                        <span>{{ $log->description }}</span>
                        @if($log->changes)
                            <div class="log-changes">
                                @foreach($log->changes as $field => $value)
                                    @if(is_array($value) && isset($value['from'], $value['to']))
                                        <span>{{ $field }}:</span>
                                        <span class="from">{{ $value['from'] }}</span>
                                        <span style="color:#94a3b8">&rarr;</span>
                                        <span class="to">{{ $value['to'] }}</span>
                                    @elseif(!is_array($value))
                                        <span>{{ $field }}: {{ $value }}</span>
                                    @else
                                        @if(isset($value['from']))
                                            <span class="from">{{ $value['from'] }}</span>
                                            <span style="color:#94a3b8">&rarr;</span>
                                        @endif
                                        @if(isset($value['to']))
                                            <span class="to">{{ $value['to'] }}</span>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="font-family:monospace;font-size:.75rem;color:#94a3b8">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div style="padding:1rem 1.25rem">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
