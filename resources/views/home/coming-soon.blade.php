@extends('layouts.app')
@section('title', $brandName ?? 'Coming Soon')

@section('content')
<section style="min-height:70vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%)">
    <div class="container" style="text-align:center;max-width:600px;padding:60px 24px">
        @if(!empty($brandLogoUrl))
        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" style="max-height:80px;margin-bottom:24px">
        @else
        <div style="width:80px;height:80px;background:linear-gradient(135deg,#0A2D74,#1a4fa0);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px">
            <span style="color:#fff;font-size:2rem;font-weight:800">{{ strtoupper(substr($brandName ?? 'T', 0, 1)) }}</span>
        </div>
        @endif

        <h1 style="font-size:2.2rem;font-weight:800;color:#0A2D74;margin:0 0 12px">{{ $brandName ?? 'Our Site' }}</h1>

        @if(!empty($brandTagline))
        <p style="font-size:1.1rem;color:#6b7280;margin:0 0 32px">{{ $brandTagline }}</p>
        @else
        <p style="font-size:1.1rem;color:#6b7280;margin:0 0 32px">We're building something amazing. Check back soon!</p>
        @endif

        <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(0,0,0,.06);padding:32px;margin-bottom:32px">
            <div style="width:56px;height:56px;background:#FEF3C7;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fas fa-hard-hat" style="color:#F59E0B;font-size:1.4rem"></i>
            </div>
            <h3 style="font-size:1.1rem;font-weight:700;margin:0 0 8px;color:#374151">Site Under Construction</h3>
            <p style="color:#9ca3af;font-size:.9rem;margin:0;line-height:1.6">
                Our website is currently being set up. Browse our tours or get in touch with us directly.
            </p>
        </div>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            @if(isset($currentTenant) && $currentTenant->email)
            <a href="mailto:{{ $currentTenant->email }}" class="btn btn-primary" style="gap:8px">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
            @endif
            <a href="{{ route('tours.index') }}" class="btn btn-outline" style="gap:8px">
                <i class="fas fa-compass"></i> Browse Tours
            </a>
        </div>
    </div>
</section>
@endsection
