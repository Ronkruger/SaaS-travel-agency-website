@extends('central.layouts.app')
@section('title', 'My Agency Billing')

@push('styles')
<style>
    .billing-page { padding: 3rem 0 5rem; }
    .billing-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem; }
    .billing-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--primary); }
    .card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.8rem; }
    .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: var(--bg-alt); border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); }
    .stat-card .label { font-size: .85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem; }
    .stat-card .value { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
    .stat-card .sub { font-size: .85rem; color: var(--text-muted); margin-top: .3rem; }
    .section-title { font-size: 1.2rem; font-weight: 700; color: var(--text); margin-bottom: 1.2rem; padding-bottom: .8rem; border-bottom: 1px solid var(--border); }
    .info-row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid var(--border); font-size: .9rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row .key { color: var(--text-muted); }
    .info-row .val { font-weight: 600; }
    .actions { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem; }
</style>
@endpush

@section('content')
<div class="billing-page">
    <div class="container">
        <div class="billing-header">
            <div>
                <h1>{{ $tenant->company_name ?? $tenant->name }}</h1>
                <p style="color:var(--text-muted)">Manage your subscription, billing and account settings.</p>
            </div>
            <div>
                @php $domain = $tenant->domains()->first(); @endphp
                @if($domain)
                <a href="{{ request()->secure() ? 'https' : 'http' }}://{{ $domain->domain }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i> Open My Agency
                </a>
                @endif
            </div>
        </div>

        <div class="card-grid">
            <div class="stat-card">
                <div class="label">Current Plan</div>
                <div class="value" style="text-transform:capitalize">{{ $tenant->plan }}</div>
                <div class="sub">
                    <span class="badge {{ $tenant->subscriptionStatus() === 'active' ? 'badge-success' : ($tenant->subscriptionStatus() === 'trial' ? 'badge-info' : 'badge-warning') }}">
                        {{ ucfirst($tenant->subscriptionStatus()) }}
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="label">Subdomain</div>
                <div class="value" style="font-size:1.1rem">
                    @if($domain)
                        <code>{{ $domain->domain }}</code>
                    @else
                        <span style="color:var(--text-muted)">Not configured</span>
                    @endif
                </div>
            </div>
            <div class="stat-card">
                <div class="label">Trial / Subscription Ends</div>
                <div class="value" style="font-size:1.1rem">
                    @if($tenant->subscription_ends_at)
                        {{ $tenant->subscription_ends_at->format('M d, Y') }}
                    @elseif($tenant->trial_ends_at)
                        {{ $tenant->trial_ends_at->format('M d, Y') }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start" class="card-grid">
            {{-- Account info --}}
            <div class="card">
                <div class="section-title">Account Information</div>
                <div class="info-row"><span class="key">Name</span><span class="val">{{ $tenant->name }}</span></div>
                <div class="info-row"><span class="key">Email</span><span class="val">{{ $tenant->email }}</span></div>
                <div class="info-row"><span class="key">Company</span><span class="val">{{ $tenant->company_name ?? '—' }}</span></div>
                <div class="info-row"><span class="key">Phone</span><span class="val">{{ $tenant->company_phone ?? '—' }}</span></div>
                <div class="info-row"><span class="key">Member since</span><span class="val">{{ $tenant->created_at->format('M d, Y') }}</span></div>
            </div>

            {{-- Subscription actions --}}
            <div class="card">
                <div class="section-title">Subscription</div>
                <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.2rem">
                    @if($tenant->subscriptionStatus() === 'trial')
                        You are on a free trial. Upgrade to continue after your trial ends.
                    @elseif($tenant->subscriptionStatus() === 'active')
                        Your subscription is active. You have full access to all plan features.
                    @else
                        Your subscription has expired. Please renew to restore access.
                    @endif
                </p>
                <div class="actions">
                    <a href="{{ route('central.billing.plans') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-up"></i> {{ $tenant->subscriptionStatus() === 'expired' ? 'Renew' : 'Upgrade' }} Plan
                    </a>
                    <a href="{{ route('central.billing.invoices') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-file-invoice"></i> Invoices
                    </a>
                    @if($tenant->stripe_subscription_id)
                    <form method="POST" action="{{ route('central.billing.cancel') }}" onsubmit="return confirm('Cancel subscription at end of billing period?')">
                        @csrf
                        <button class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border:none;cursor:pointer">Cancel Subscription</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
