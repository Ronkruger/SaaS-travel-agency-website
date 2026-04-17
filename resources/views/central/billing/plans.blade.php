@extends('central.layouts.app')
@section('title', 'Choose a Plan')

@push('styles')
<style>
.plans-hero { background: linear-gradient(135deg, #0A2D74, #1a47a0); color: #fff; padding: 3rem 2rem; text-align: center; }
.plans-hero h1 { font-size: 2rem; font-weight: 800; margin-bottom: .5rem; }
.plans-hero p { opacity: .85; }
.page { max-width: 1100px; margin: 0 auto; padding: 3rem 2rem; }
.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
.plan-card { background: #fff; border: 2px solid #e5e7eb; border-radius: 16px; padding: 2rem; display: flex; flex-direction: column; transition: all .25s; }
.plan-card:hover { border-color: #0A2D74; transform: translateY(-3px); box-shadow: 0 16px 48px rgba(10,45,116,.12); }
.plan-card.popular { border-color: #0A2D74; position: relative; }
.popular-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #F5A623; color: #fff; font-size: .75rem; font-weight: 700; padding: .25rem 1rem; border-radius: 20px; white-space: nowrap; }
.plan-name { font-size: 1.1rem; font-weight: 800; color: #0A2D74; margin-bottom: .4rem; }
.plan-price { font-size: 2.2rem; font-weight: 900; color: #0A2D74; }
.plan-price sup { font-size: 1rem; vertical-align: top; margin-top: .5rem; }
.plan-price span { font-size: .85rem; font-weight: 500; color: #6b7280; }
.plan-desc { color: #6b7280; font-size: .85rem; margin: .8rem 0 1.2rem; }
.plan-features { flex: 1; margin-bottom: 1.5rem; }
.plan-feature { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: #374151; padding: .3rem 0; }
.plan-feature i { color: #059669; width: 16px; }
.plan-feature.no i { color: #9ca3af; }
.btn-plan { display: block; text-align: center; padding: .75rem; border-radius: 10px; font-weight: 700; font-size: .9rem; transition: all .2s; background: #0A2D74; color: #fff; border: none; cursor: pointer; text-decoration: none; }
.btn-plan:hover { background: #1a47a0; }
.btn-plan.current { background: #d1fae5; color: #065f46; cursor: default; }
</style>
@endpush

@section('content')
<div class="plans-hero">
    <h1>Choose Your Plan</h1>
    <p>Upgrade or switch your subscription at any time.</p>
</div>

<div class="page">
    @if(session('error'))
        <div style="background:#fee2e2;color:#991b1b;padding:.9rem 1.2rem;border-radius:12px;margin-bottom:1.5rem">{{ session('error') }}</div>
    @endif

    <div class="plans-grid">
        @foreach($plans as $plan)
        @php $isCurrent = $currentPlan === $plan->slug; @endphp
        <div class="plan-card {{ $plan->slug === 'professional' ? 'popular' : '' }}">
            @if($plan->slug === 'professional')
                <div class="popular-badge">Most Popular</div>
            @endif
            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-price">
                <sup>$</sup>{{ number_format($plan->monthly_price) }}<span>/mo</span>
            </div>
            <div class="plan-desc">{{ $plan->description }}</div>
            <ul class="plan-features" style="list-style:none;padding:0;margin:0">
                <li class="plan-feature"><i class="fas fa-check"></i>
                    {{ $plan->max_tours < 0 ? 'Unlimited' : $plan->max_tours }} Tours
                </li>
                <li class="plan-feature"><i class="fas fa-check"></i>
                    {{ $plan->max_admin_users < 0 ? 'Unlimited' : $plan->max_admin_users }} Staff accounts
                </li>
                @if($plan->has_diy_builder)
                <li class="plan-feature"><i class="fas fa-check"></i> AI Tour Builder</li>
                @endif
                @if($plan->has_advanced_reports)
                <li class="plan-feature"><i class="fas fa-check"></i> Advanced Reports</li>
                @endif
                @if($plan->has_custom_domain)
                <li class="plan-feature"><i class="fas fa-check"></i> Custom Domain</li>
                @endif
                @if($plan->has_priority_support)
                <li class="plan-feature"><i class="fas fa-check"></i> Priority Support</li>
                @endif
            </ul>

            @if($isCurrent)
                <span class="btn-plan current"><i class="fas fa-check"></i> Current Plan</span>
            @elseif($plan->monthly_price == 0)
                <span class="btn-plan" style="background:#f3f4f6;color:#6b7280;cursor:default">Trial Only</span>
            @else
                <form method="POST" action="{{ route('central.billing.subscribe', $plan->slug) }}">
                    @csrf
                    <button type="submit" class="btn-plan">
                        {{ $currentPlan === 'trial' ? 'Start' : 'Switch to' }} {{ $plan->name }}
                    </button>
                </form>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
