@extends('central.layouts.app')
@section('title', 'Launch Your Business Online')

@push('styles')
<style>
    /* Hero */
    .hero { padding: 7rem 0 5rem; background: linear-gradient(135deg, #0A2D74 0%, #1a47a0 50%, #0e3a8a 100%); color: #fff; text-align: center; position: relative; overflow: hidden; }
    .hero::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
    .hero-badge { display: inline-block; background: rgba(245,166,35,.2); color: var(--accent); border: 1px solid rgba(245,166,35,.4); padding: .4rem 1.2rem; border-radius: 20px; font-size: .85rem; font-weight: 600; margin-bottom: 1.5rem; }
    .hero h1 { font-size: clamp(2.2rem, 5vw, 3.8rem); font-weight: 800; line-height: 1.15; margin-bottom: 1.5rem; }
    .hero h1 span { color: var(--accent); }
    .hero p { font-size: 1.15rem; color: rgba(255,255,255,.85); max-width: 560px; margin: 0 auto 2.5rem; }
    .hero-cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .hero-stats { display: flex; gap: 3rem; justify-content: center; margin-top: 4rem; padding-top: 3rem; border-top: 1px solid rgba(255,255,255,.15); flex-wrap: wrap; }
    .hero-stat { text-align: center; }
    .hero-stat .num { font-size: 2.2rem; font-weight: 800; color: var(--accent); }
    .hero-stat .label { font-size: .85rem; color: rgba(255,255,255,.7); margin-top: .2rem; }

    /* Logos */
    .logos-bar { padding: 2.5rem 0; background: var(--bg-alt); border-bottom: 1px solid var(--border); text-align: center; }
    .logos-bar p { color: var(--text-muted); font-size: .85rem; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 1.5rem; }
    .logos-list { display: flex; gap: 3rem; justify-content: center; align-items: center; flex-wrap: wrap; opacity: .5; }
    .logos-list span { font-size: 1.1rem; font-weight: 700; color: var(--text-muted); }

    /* Features */
    .features { padding: 6rem 0; }
    .section-label { font-size: .85rem; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: .12em; margin-bottom: .8rem; }
    .section-title { font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 800; color: var(--primary); line-height: 1.2; margin-bottom: 1rem; }
    .section-sub { color: var(--text-muted); font-size: 1.05rem; max-width: 560px; }
    .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3.5rem; }
    .feature-card { background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; transition: all .3s; }
    .feature-card:hover { border-color: var(--primary); box-shadow: var(--shadow); transform: translateY(-3px); }
    .feature-icon { width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; margin-bottom: 1.2rem; color: #fff; font-size: 1.3rem; }
    .feature-card h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: .5rem; }
    .feature-card p { color: var(--text-muted); font-size: .9rem; line-height: 1.6; }

    /* How it works */
    .how-it-works { padding: 6rem 0; background: var(--bg-alt); }
    .steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; margin-top: 3.5rem; }
    .step { text-align: center; padding: 2rem 1.5rem; }
    .step-num { width: 52px; height: 52px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; margin: 0 auto 1.2rem; }
    .step h3 { font-weight: 700; margin-bottom: .5rem; }
    .step p { color: var(--text-muted); font-size: .9rem; }

    /* Pricing preview */
    .pricing-section { padding: 6rem 0; }
    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 3rem; }
    .plan-card { border: 2px solid var(--border); border-radius: var(--radius); padding: 2rem; transition: all .3s; background: var(--bg); }
    .plan-card.popular { border-color: var(--primary); box-shadow: var(--shadow); position: relative; }
    .plan-card.popular::before { content: 'Most Popular'; position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--primary); color: #fff; padding: .3rem 1rem; border-radius: 20px; font-size: .78rem; font-weight: 700; white-space: nowrap; }
    .plan-name { font-size: 1.1rem; font-weight: 700; color: var(--primary); margin-bottom: .3rem; }
    .plan-price { font-size: 2.5rem; font-weight: 800; color: var(--text); margin: .5rem 0; }
    .plan-price span { font-size: 1rem; font-weight: 500; color: var(--text-muted); }
    .plan-desc { color: var(--text-muted); font-size: .9rem; margin-bottom: 1.5rem; }
    .plan-features { list-style: none; display: flex; flex-direction: column; gap: .6rem; margin-bottom: 2rem; }
    .plan-features li { display: flex; gap: .6rem; align-items: flex-start; font-size: .9rem; }
    .plan-features li i { color: #10b981; margin-top: .2rem; flex-shrink: 0; }

    /* CTA banner */
    .cta-banner { padding: 5rem 0; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; text-align: center; }
    .cta-banner h2 { font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 800; margin-bottom: 1rem; }
    .cta-banner p { color: rgba(255,255,255,.85); font-size: 1.05rem; margin-bottom: 2.5rem; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<section class="hero">
    <div class="container" style="position:relative;z-index:1">
        <div class="hero-badge">🚀 &nbsp; The #1 Business SaaS Platform</div>
        <h1>Launch Your <span>Business</span><br>Online in Minutes</h1>
        <p>Everything you need to manage plans, subscriptions, payments, and customers — powered by AI and delivered on your own subdomain.</p>
        <div class="hero-cta">
            <a href="{{ route('central.register') }}" class="btn btn-accent btn-lg">Start Free 14-Day Trial</a>
            <a href="{{ route('central.pricing') }}" class="btn btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.3)">View Pricing</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><div class="num">500+</div><div class="label">Businesses</div></div>
            <div class="hero-stat"><div class="num">50K+</div><div class="label">Subscriptions Processed</div></div>
            <div class="hero-stat"><div class="num">99.9%</div><div class="label">Uptime SLA</div></div>
            <div class="hero-stat"><div class="num">24/7</div><div class="label">Support</div></div>
        </div>
    </div>
</section>

{{-- Logos bar --}}
<div class="logos-bar">
    <div class="container">
        <p>Trusted by agencies worldwide</p>
        <div class="logos-list">
            <span>Sunrise Travels</span>
            <span>Paradise Tours</span>
            <span>Globe Trekkers</span>
            <span>Horizon Escapes</span>
            <span>Elite Voyages</span>
        </div>
    </div>
</div>

{{-- Features --}}
<section class="features">
    <div class="container">
        <div class="text-center">
            <div class="section-label">Everything you need</div>
            <h2 class="section-title">A complete platform for<br>modern businesses</h2>
            <p class="section-sub" style="margin:0 auto">From plan management to AI-powered custom builders, we have everything your business needs to grow.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Plan Management</h3>
                <p>Create and manage unlimited plans with rich descriptions, galleries, schedules, and real-time availability.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Subscription System</h3>
                <p>Full-featured subscription engine with installment payments, cancellations, PDFs, email confirmations, and more.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-robot"></i></div>
                <h3>AI Plan Builder</h3>
                <p>Let customers build custom plans with AI assistance. Collaborative editing, smart routing, and instant pricing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                <h3>Payment Processing</h3>
                <p>Accept payments via Xendit with installment plans, credit balance, coupon discounts, and payment reminders.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                <h3>Analytics & Reports</h3>
                <p>Detailed revenue reports, subscription trends, customer analytics, and exportable CSV reports for your accountant.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
                <h3>Staff Management</h3>
                <p>Granular role-based permissions for your admin staff. Manage plans, subscriptions, or finances independently.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-palette"></i></div>
                <h3>White-Label Branding</h3>
                <p>Upload your logo, customize your brand colors, and present a fully branded experience to your customers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-envelope-open-text"></i></div>
                <h3>Email Automation</h3>
                <p>Automated subscription confirmations, payment reminders, plan PDFs, and custom email tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-globe"></i></div>
                <h3>Your Own Subdomain</h3>
                <p>Get <code>youragency.toursaas.com</code> instantly. Upgrade to a custom domain on Professional and above.</p>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="text-center">
            <div class="section-label">Simple setup</div>
            <h2 class="section-title">Live in 5 minutes</h2>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Sign Up Free</h3>
                <p>Create your account with your agency name. No credit card required to start your 14-day trial.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Customize Your Brand</h3>
                <p>Upload your logo, set your brand colors, and configure your payment gateway credentials.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>Add Your Plans</h3>
                <p>Create plan packages with pricing, schedules, images, and details. Takes just minutes per plan.</p>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <h3>Go Live &amp; Grow</h3>
                <p>Share your branded URL with customers and start accepting subscriptions and payments immediately.</p>
            </div>
        </div>
    </div>
</section>

{{-- Pricing preview --}}
<section class="pricing-section">
    <div class="container">
        <div class="text-center">
            <div class="section-label">Transparent pricing</div>
            <h2 class="section-title">Plans for every agency size</h2>
            <p class="section-sub" style="margin:0 auto">Start free, upgrade when you're ready. No hidden fees.</p>
        </div>
        <div class="plans-grid">
            @foreach($plans as $i => $plan)
            <div class="plan-card {{ $i === 1 ? 'popular' : '' }}">
                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-price">
                    @if($plan->monthly_price == 0) Free
                    @else ${{ number_format($plan->monthly_price) }}<span>/mo</span>
                    @endif
                </div>
                <div class="plan-desc">{{ $plan->description }}</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> {{ $plan->max_tours < 0 ? 'Unlimited plans' : $plan->max_tours . ' plans' }}</li>
                    <li><i class="fas fa-check"></i> {{ $plan->max_admin_users < 0 ? 'Unlimited staff' : $plan->max_admin_users . ' staff accounts' }}</li>
                    @if($plan->has_diy_builder)<li><i class="fas fa-check"></i> AI Plan Builder</li>@endif
                    @if($plan->has_advanced_reports)<li><i class="fas fa-check"></i> Advanced analytics</li>@endif
                    @if($plan->has_custom_domain)<li><i class="fas fa-check"></i> Custom domain</li>@endif
                    @if($plan->has_priority_support)<li><i class="fas fa-check"></i> Priority support</li>@endif
                </ul>
                <a href="{{ route('central.register') }}?plan={{ $plan->slug }}" class="btn {{ $i === 1 ? 'btn-primary' : 'btn-outline' }}" style="width:100%;justify-content:center">
                    {{ $plan->monthly_price == 0 ? 'Start Free Trial' : 'Get Started' }}
                </a>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('central.pricing') }}" style="color:var(--primary);font-weight:600">View full comparison <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

{{-- CTA Banner --}}
<section class="cta-banner">
    <div class="container">
        <h2>Ready to grow your business?</h2>
        <p>Join hundreds of businesses already using TourSaaS to manage their operations.</p>
        <a href="{{ route('central.register') }}" class="btn btn-accent btn-lg">Start Your Free Trial Today</a>
    </div>
</section>

@endsection
