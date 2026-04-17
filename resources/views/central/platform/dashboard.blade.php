@extends('central.platform.layouts.admin')
@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-building"></i></div>
        <div>
            <div class="stat-num">{{ $stats['total_tenants'] }}</div>
            <div class="stat-label">Total Agencies</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-num">{{ $stats['active_tenants'] }}</div>
            <div class="stat-label">Active Agencies</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-num">{{ $stats['trial_tenants'] }}</div>
            <div class="stat-label">On Trial</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <div class="stat-num">{{ $stats['expired_tenants'] }}</div>
            <div class="stat-label">Expired</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

{{-- Recent agencies --}}
<div class="card">
    <div class="card-header">
        <h3>Recent Agencies</h3>
        <a href="{{ route('platform.tenants.index') }}" class="btn btn-sm btn-outline">View all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Agency</th>
                    <th>Subdomain</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTenants as $tenant)
                @php $domain = $tenant->domains()->first(); @endphp
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $tenant->company_name ?? $tenant->name }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">{{ $tenant->email }}</div>
                    </td>
                    <td>
                        @if($domain)
                            <a href="http://{{ $domain->domain }}" target="_blank" style="color:var(--primary);font-size:.85rem">
                                {{ $domain->domain }}
                            </a>
                        @else —
                        @endif
                    </td>
                    <td><span class="badge badge-info" style="text-transform:capitalize">{{ $tenant->plan }}</span></td>
                    <td>
                        @php $status = $tenant->subscriptionStatus(); @endphp
                        <span class="badge {{ $status === 'active' ? 'badge-success' : ($status === 'trial' ? 'badge-info' : ($status === 'inactive' ? 'badge-danger' : 'badge-warning')) }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td style="font-size:.85rem;color:var(--text-muted)">{{ $tenant->created_at->format('M d') }}</td>
                    <td>
                        <a href="{{ route('platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Plan breakdown --}}
<div class="card">
    <div class="card-header"><h3>Agencies by Plan</h3></div>
    <div class="card-body">
        @foreach($stats['plans'] as $planStat)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border)">
            <span style="font-weight:600;text-transform:capitalize">{{ $planStat['name'] }}</span>
            <span class="badge badge-info">{{ $planStat['count'] }} agencies</span>
        </div>
        @endforeach
        <div style="margin-top:1.2rem">
            <div style="font-size:.85rem;color:var(--text-muted)">New this month</div>
            <div style="font-size:1.8rem;font-weight:800;color:var(--primary)">{{ $stats['new_this_month'] }}</div>
        </div>
    </div>
</div>

</div>
@endsection
