@extends('central.platform.layouts.admin')
@section('title', ($tenant->company_name ?? $tenant->name) . ' — Tenant')
@section('page-title', $tenant->company_name ?? $tenant->name)

@section('topbar-actions')
<div style="display:flex;gap:.5rem">
    <a href="{{ route('platform.tenants.edit', $tenant) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
    <form method="POST" action="{{ route('platform.tenants.impersonate', $tenant) }}" style="display:inline">
        @csrf
        <button class="btn btn-sm btn-accent"><i class="fas fa-eye"></i> Impersonate</button>
    </form>
    <a href="{{ route('platform.tenants.index') }}" class="btn btn-sm" style="background:var(--bg);border:2px solid var(--border)"><i class="fas fa-arrow-left"></i> Back</a>
</div>
@endsection

@section('content')
@php
    $domain = $tenant->domains()->first();
    $status = $tenant->subscriptionStatus();
@endphp
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <div class="card">
        <div class="card-header"><h3>Tenant Information</h3></div>
        <div class="card-body">
            @php
            $rows = [
                'Tenant ID' => $tenant->id,
                'Name' => $tenant->name,
                'Email' => $tenant->email,
                'Company' => $tenant->company_name ?? '—',
                'Phone' => $tenant->company_phone ?? '—',
                'Address' => $tenant->company_address ?? '—',
                'Subdomain' => $domain ? $domain->domain : '—',
                'Plan' => ucfirst($tenant->plan),
                'Status' => ucfirst($status),
                'Trial ends' => $tenant->trial_ends_at ? $tenant->trial_ends_at->format('M d, Y H:i') : '—',
                'Subscription ends' => $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('M d, Y H:i') : '—',
                'Stripe Customer' => $tenant->stripe_customer_id ?? '—',
                'Stripe Subscription' => $tenant->stripe_subscription_id ?? '—',
                'Active' => $tenant->is_active ? 'Yes' : 'No',
                'Joined' => $tenant->created_at->format('M d, Y H:i'),
            ];
            @endphp
            @foreach($rows as $key => $val)
            <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.88rem">
                <span style="color:var(--text-muted)">{{ $key }}</span>
                <span style="font-weight:600">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.2rem">
        <div class="card">
            <div class="card-header"><h3>Quick Actions</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.7rem">
                @if($domain)
                <a href="http://{{ $domain->domain }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i> Open Agency Site
                </a>
                <a href="http://{{ $domain->domain }}/admin/dashboard" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-cog"></i> Open Agency Admin
                </a>
                @endif
                <a href="{{ route('platform.tenants.edit', $tenant) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit Tenant
                </a>
                <form method="POST" action="{{ route('platform.tenants.toggle-status', $tenant) }}">
                    @csrf
                    <button class="btn btn-sm {{ $tenant->is_active ? 'btn-danger' : 'btn-success' }}" style="width:100%">
                        {{ $tenant->is_active ? '⏸ Suspend Agency' : '▶ Activate Agency' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" onsubmit="return confirm('PERMANENTLY DELETE tenant {{ $tenant->id }} and ALL their data? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" style="width:100%">
                        <i class="fas fa-trash"></i> Delete Tenant & Data
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Subscription Status</h3></div>
            <div class="card-body">
                <span class="badge {{ $status === 'active' ? 'badge-success' : ($status === 'trial' ? 'badge-info' : ($status === 'inactive' ? 'badge-danger' : 'badge-warning')) }}" style="font-size:.9rem;padding:.4rem 1rem">
                    {{ ucfirst($status) }}
                </span>
                <p style="font-size:.85rem;color:var(--text-muted);margin-top:.8rem">
                    Plan: <strong>{{ ucfirst($tenant->plan) }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
