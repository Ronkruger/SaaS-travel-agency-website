@extends('central.platform.layouts.admin')
@section('title', 'All Agencies')
@section('page-title', 'Agencies')

@section('topbar-actions')
<a href="{{ route('central.register') }}" target="_blank" class="btn btn-primary btn-sm">
    <i class="fas fa-plus"></i> New Agency
</a>
@endsection

@section('content')

{{-- Filters --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body" style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, company..." style="padding:.55rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem;flex:1;min-width:200px">
            <select name="plan" style="padding:.55rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                <option value="">All Plans</option>
                <option value="trial" {{ request('plan') === 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="starter" {{ request('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                <option value="professional" {{ request('plan') === 'professional' ? 'selected' : '' }}>Professional</option>
                <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
            <select name="status" style="padding:.55rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['search','plan','status']))
                <a href="{{ route('platform.tenants.index') }}" class="btn btn-sm" style="background:var(--bg);border:2px solid var(--border)">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Agency</th>
                    <th>Subdomain</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Expiry</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                @php
                    $domain = $tenant->domains()->first();
                    $status = $tenant->subscriptionStatus();
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $tenant->company_name ?? $tenant->name }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">{{ $tenant->email }}</div>
                    </td>
                    <td>
                        @if($domain)
                            <a href="http://{{ $domain->domain }}" target="_blank" style="color:var(--primary);font-size:.85rem">
                                {{ $domain->domain }} <i class="fas fa-external-link-alt" style="font-size:.7rem"></i>
                            </a>
                        @else <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td><span class="badge badge-info" style="text-transform:capitalize">{{ $tenant->plan }}</span></td>
                    <td>
                        <span class="badge {{ $status === 'active' ? 'badge-success' : ($status === 'trial' ? 'badge-info' : ($status === 'inactive' ? 'badge-danger' : 'badge-warning')) }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td style="font-size:.85rem;color:var(--text-muted)">
                        @if($tenant->subscription_ends_at)
                            {{ $tenant->subscription_ends_at->format('M d, Y') }}
                        @elseif($tenant->trial_ends_at)
                            Trial: {{ $tenant->trial_ends_at->format('M d, Y') }}
                        @else —
                        @endif
                    </td>
                    <td style="font-size:.85rem;color:var(--text-muted)">{{ $tenant->created_at->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                            <a href="{{ route('platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">View</a>
                            <form method="POST" action="{{ route('platform.tenants.toggle-status', $tenant) }}">
                                @csrf
                                <button class="btn btn-sm {{ $tenant->is_active ? 'btn-danger' : 'btn-success' }}">
                                    {{ $tenant->is_active ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem">No agencies found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@endsection
