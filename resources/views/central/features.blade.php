@extends('central.layouts.app')
@section('title', 'Features')

@push('styles')
<style>
.hero { background: linear-gradient(135deg, #0A2D74 0%, #1a47a0 100%); color: #fff; padding: 5rem 2rem; text-align: center; }
.hero h1 { font-size: 2.8rem; font-weight: 900; margin-bottom: 1rem; }
.hero p { font-size: 1.15rem; opacity: .85; max-width: 600px; margin: 0 auto; }
section { padding: 5rem 2rem; max-width: 1100px; margin: 0 auto; }
.section-label { color: #F5A623; font-weight: 700; font-size: .85rem; text-transform: uppercase; letter-spacing: .12em; margin-bottom: .6rem; }
.section-title { font-size: 2rem; font-weight: 800; color: #0A2D74; margin-bottom: 1rem; }
.section-sub { color: #6b7280; max-width: 580px; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem; }
.feature-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 2rem; transition: all .25s; }
.feature-box:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(10,45,116,.1); border-color: #0A2D74; }
.feature-icon { width: 52px; height: 52px; background: linear-gradient(135deg, #0A2D74, #1a47a0); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; margin-bottom: 1.2rem; }
.feature-title { font-size: 1.05rem; font-weight: 800; color: #0A2D74; margin-bottom: .5rem; }
.feature-desc { color: #6b7280; font-size: .88rem; line-height: 1.6; }
.cta-section { background: linear-gradient(135deg, #F5A623, #e08e0b); padding: 5rem 2rem; text-align: center; }
.cta-section h2 { font-size: 2.2rem; font-weight: 900; color: #fff; margin-bottom: 1rem; }
.cta-section p { color: rgba(255,255,255,.9); margin-bottom: 2rem; }
.btn-cta { display: inline-block; background: #0A2D74; color: #fff; padding: .9rem 2.5rem; border-radius: 50px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all .2s; }
.btn-cta:hover { background: #1a47a0; transform: translateY(-2px); }
</style>
@endpush

@section('content')
<div class="hero">
    <h1>Everything You Need to Run<br>Your Business Online</h1>
    <p>TourSaaS gives you a complete, ready-made subscription platform — branded for your business, live in minutes.</p>
</div>

<section>
    <div class="section-label">Core Platform</div>
    <div class="section-title">Built for businesses, by design</div>
    <p class="section-sub">All the tools you need — plans, subscriptions, payments, staff, and more — in one SaaS platform.</p>
    <div class="features-grid">
        @php
        $features = [
            ['fas fa-map-marked-alt', 'Plan Management', 'Create and manage plan listings with rich descriptions, pricing tiers, and availability calendars.'],
            ['fas fa-calendar-check', 'Online Subscription System', 'Customers can browse, select options, fill in details, and checkout — all without leaving your site.'],
            ['fas fa-users', 'Staff Management', 'Add admin accounts for your agents. Control who can manage plans, handle subscriptions, or view reports.'],
            ['fas fa-credit-card', 'Payment Processing', 'Integrated with Xendit for local payments. Accept credit cards, e-wallets, and bank transfers.'],
            ['fas fa-chart-line', 'Reports & Analytics', 'Track revenue, subscription trends, plan popularity, and customer behaviour from a clean dashboard.'],
            ['fas fa-robot', 'AI Plan Builder', 'Let customers design their own custom plan using the AI-powered DIY builder (Professional+).'],
            ['fas fa-envelope', 'Automated Emails', 'Subscription confirmations, reminders, and receipts sent automatically to customers and your team.'],
            ['fas fa-globe', 'Your Own Subdomain', 'Each business gets a dedicated subdomain (yourbusiness.toursaas.com) — fully isolated from other tenants.'],
            ['fas fa-lock', 'Secure & Isolated', 'Every tenant runs in a separate database. Your customer data never mixes with other agencies.'],
            ['fas fa-paint-brush', 'White-label Branding', 'Upload your logo, set your colors, and make the platform feel like your own product.'],
            ['fas fa-mobile-alt', 'Mobile Responsive', 'Your booking site works beautifully on phones, tablets, and desktops out of the box.'],
            ['fas fa-headset', 'Priority Support', 'Get dedicated support from our team on Professional and Enterprise plans.'],
        ];
        @endphp
        @foreach($features as [$icon, $title, $desc])
        <div class="feature-box">
            <div class="feature-icon"><i class="{{ $icon }}"></i></div>
            <div class="feature-title">{{ $title }}</div>
            <div class="feature-desc">{{ $desc }}</div>
        </div>
        @endforeach
    </div>
</section>

<div class="cta-section">
    <h2>Ready to launch your business?</h2>
    <p>Start your free 14-day trial today. No credit card required.</p>
    <a href="{{ route('central.register') }}" class="btn-cta">Start Free Trial →</a>
</div>
@endsection
