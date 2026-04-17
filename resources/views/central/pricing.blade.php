@extends('central.layouts.app')
@section('title', 'Pricing')

@push('styles')
<style>
    .page-hero { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; padding: 5rem 0 3rem; text-align: center; }
    .page-hero h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: .8rem; }
    .page-hero p { color: rgba(255,255,255,.85); font-size: 1.1rem; }

    .pricing-section { padding: 5rem 0; }
    .billing-toggle { display: flex; gap: .5rem; align-items: center; justify-content: center; margin: 2rem 0 3rem; }
    .billing-toggle span { font-weight: 600; color: var(--text-muted); }
    .billing-toggle .badge { font-size: .75rem; }
    .toggle-btn { position: relative; width: 50px; height: 26px; background: var(--border); border-radius: 13px; cursor: pointer; border: none; transition: background .3s; }
    .toggle-btn.active { background: var(--primary); }
    .toggle-btn::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: transform .3s; box-shadow: 0 2px 4px rgba(0,0,0,.2); }
    .toggle-btn.active::after { transform: translateX(24px); }

    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
    .plan-card { border: 2px solid var(--border); border-radius: var(--radius); padding: 2.5rem 2rem; background: var(--bg); transition: all .3s; position: relative; }
    .plan-card.popular { border-color: var(--primary); box-shadow: 0 8px 40px rgba(10,45,116,.15); }
    .plan-badge { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--primary); color: #fff; padding: .3rem 1rem; border-radius: 20px; font-size: .78rem; font-weight: 700; white-space: nowrap; }
    .plan-name { font-size: 1rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .08em; }
    .plan-price { font-size: 3rem; font-weight: 800; color: var(--primary); margin: .8rem 0 .3rem; line-height: 1; }
    .plan-price .period { font-size: 1rem; font-weight: 500; color: var(--text-muted); }
    .plan-price .yearly-note { display: block; font-size: .8rem; color: #10b981; font-weight: 600; margin-top: .3rem; }
    .plan-desc { color: var(--text-muted); font-size: .9rem; margin: .5rem 0 1.5rem; }
    .plan-features { list-style: none; display: flex; flex-direction: column; gap: .7rem; margin-bottom: 2.5rem; }
    .plan-features li { display: flex; gap: .7rem; font-size: .9rem; align-items: flex-start; }
    .plan-features li i.check { color: #10b981; flex-shrink: 0; margin-top: .15rem; }
    .plan-features li i.times { color: #d1d5db; flex-shrink: 0; margin-top: .15rem; }
    .plan-features li .dimmed { color: var(--text-muted); }

    /* Comparison table */
    .compare-section { padding: 4rem 0 6rem; }
    .compare-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    .compare-table th { padding: 1rem; text-align: center; font-weight: 700; color: var(--primary); border-bottom: 2px solid var(--border); }
    .compare-table th:first-child { text-align: left; }
    .compare-table td { padding: .9rem 1rem; border-bottom: 1px solid var(--border); text-align: center; }
    .compare-table td:first-child { text-align: left; font-weight: 500; color: var(--text); }
    .compare-table tr:hover td { background: var(--bg-alt); }
    .compare-table .section-row td { background: var(--bg-alt); font-weight: 700; color: var(--text-muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .1em; padding: .5rem 1rem; }
    .compare-table .fa-check { color: #10b981; }
    .compare-table .fa-times { color: #d1d5db; }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="container">
        <h1>Simple, Transparent Pricing</h1>
        <p>Start free. Scale as you grow. Cancel anytime.</p>
    </div>
</div>

<section class="pricing-section">
    <div class="container">
        <div class="billing-toggle">
            <span>Monthly</span>
            <button class="toggle-btn" id="billingToggle" onclick="toggleBilling()"></button>
            <span>Yearly <span class="badge badge-success">Save 17%</span></span>
        </div>

        <div class="plans-grid">
            @foreach($plans as $plan)
            <div class="plan-card {{ $plan->slug === 'professional' ? 'popular' : '' }}">
                @if($plan->slug === 'professional')
                    <div class="plan-badge">Most Popular</div>
                @endif
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-price">
                    @if($plan->monthly_price == 0)
                        Free
                        <span class="period">&nbsp;</span>
                    @else
                        <span class="monthly-price">${{ number_format($plan->monthly_price) }}</span>
                        <span class="yearly-price" style="display:none">${{ number_format($plan->yearly_price / 12, 0) }}</span>
                        <span class="period">/mo</span>
                        <span class="yearly-note" style="display:none">Billed ${{ number_format($plan->yearly_price) }}/year</span>
                    @endif
                </div>
                <div class="plan-desc">{{ $plan->description }}</div>
                <ul class="plan-features">
                    <li>
                        <i class="fas fa-check check"></i>
                        {{ $plan->max_tours < 0 ? 'Unlimited tours' : $plan->max_tours . ' tours' }}
                    </li>
                    <li>
                        <i class="fas fa-check check"></i>
                        {{ $plan->max_bookings_per_month < 0 ? 'Unlimited bookings/mo' : number_format($plan->max_bookings_per_month) . ' bookings/month' }}
                    </li>
                    <li>
                        <i class="fas fa-check check"></i>
                        {{ $plan->max_admin_users < 0 ? 'Unlimited staff' : $plan->max_admin_users . ' staff accounts' }}
                    </li>
                    <li>
                        <i class="fas {{ $plan->has_diy_builder ? 'fa-check check' : 'fa-times times' }}"></i>
                        <span class="{{ !$plan->has_diy_builder ? 'dimmed' : '' }}">AI Tour Builder</span>
                    </li>
                    <li>
                        <i class="fas {{ $plan->has_custom_domain ? 'fa-check check' : 'fa-times times' }}"></i>
                        <span class="{{ !$plan->has_custom_domain ? 'dimmed' : '' }}">Custom domain</span>
                    </li>
                    <li>
                        <i class="fas {{ $plan->has_advanced_reports ? 'fa-check check' : 'fa-times times' }}"></i>
                        <span class="{{ !$plan->has_advanced_reports ? 'dimmed' : '' }}">Advanced analytics</span>
                    </li>
                    <li>
                        <i class="fas {{ $plan->has_priority_support ? 'fa-check check' : 'fa-times times' }}"></i>
                        <span class="{{ !$plan->has_priority_support ? 'dimmed' : '' }}">Priority support</span>
                    </li>
                </ul>
                <a href="{{ route('central.register') }}?plan={{ $plan->slug }}"
                   class="btn {{ $plan->slug === 'professional' ? 'btn-primary' : 'btn-outline' }}"
                   style="width:100%;justify-content:center">
                    {{ $plan->monthly_price == 0 ? 'Start Free Trial' : 'Get Started' }}
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Feature comparison table --}}
<section class="compare-section">
    <div class="container">
        <h2 class="section-title text-center mb-4">Full Feature Comparison</h2>
        <div style="overflow-x:auto">
        <table class="compare-table">
            <thead>
                <tr>
                    <th style="width:35%">Feature</th>
                    @foreach($plans as $plan)
                    <th>{{ $plan->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="section-row"><td colspan="{{ $plans->count() + 1 }}">Tour Management</td></tr>
                <tr>
                    <td>Tours</td>
                    @foreach($plans as $plan)
                    <td>{{ $plan->max_tours < 0 ? 'Unlimited' : $plan->max_tours }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Tour Schedules</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Category Management</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Image Galleries (Cloudinary CDN)</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>

                <tr class="section-row"><td colspan="{{ $plans->count() + 1 }}">Bookings & Payments</td></tr>
                <tr>
                    <td>Bookings per month</td>
                    @foreach($plans as $plan)
                    <td>{{ $plan->max_bookings_per_month < 0 ? 'Unlimited' : number_format($plan->max_bookings_per_month) }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Xendit Payment Gateway</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Installment Payments</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Coupon / Discount Codes</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Travel Fund Credits</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>PDF Booking Confirmations</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>CSV Import / Bulk Actions</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>

                <tr class="section-row"><td colspan="{{ $plans->count() + 1 }}">AI & Advanced Features</td></tr>
                <tr>
                    <td>AI Tour Builder (DIY)</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_diy_builder ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_diy_builder ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>
                <tr>
                    <td>Advanced Analytics & Reports</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_advanced_reports ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_advanced_reports ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>
                <tr>
                    <td>Email Marketing</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_email_marketing ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_email_marketing ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>
                <tr>
                    <td>API Access</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_api_access ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_api_access ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>

                <tr class="section-row"><td colspan="{{ $plans->count() + 1 }}">Infrastructure</td></tr>
                <tr>
                    <td>Staff Accounts</td>
                    @foreach($plans as $plan)
                    <td>{{ $plan->max_admin_users < 0 ? 'Unlimited' : $plan->max_admin_users }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Custom Domain</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_custom_domain ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_custom_domain ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>
                <tr>
                    <td>White-label Branding</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Auth0 SSO</td>
                    @foreach($plans as $plan)<td><i class="fas fa-check"></i></td>@endforeach
                </tr>
                <tr>
                    <td>Priority Support</td>
                    @foreach($plans as $plan)
                    <td><i class="fas {{ $plan->has_priority_support ? 'fa-check' : 'fa-times' }}" style="color:{{ $plan->has_priority_support ? '#10b981' : '#d1d5db' }}"></i></td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</section>

@push('scripts')
<script>
let yearlyBilling = false;
function toggleBilling() {
    yearlyBilling = !yearlyBilling;
    document.getElementById('billingToggle').classList.toggle('active', yearlyBilling);
    document.querySelectorAll('.monthly-price').forEach(el => el.style.display = yearlyBilling ? 'none' : 'inline');
    document.querySelectorAll('.yearly-price').forEach(el => el.style.display = yearlyBilling ? 'inline' : 'none');
    document.querySelectorAll('.yearly-note').forEach(el => el.style.display = yearlyBilling ? 'block' : 'none');
}
</script>
@endpush

@endsection
