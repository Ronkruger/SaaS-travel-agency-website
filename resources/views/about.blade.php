@extends('layouts.app')
@section('title', 'About Us')

@section('content')

{{-- Hero --}}
<div style="background:linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 100%);padding:72px 0 56px;text-align:center">
    <div class="container">
        @if($brandLogoUrl)
            <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" style="max-height:80px;width:auto;margin:0 auto 24px;display:block">
        @else
            <div style="width:88px;height:88px;background:#0A2D74;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 8px 24px rgba(10,45,116,.25)">
                <span style="font-family:'Poppins',sans-serif;font-size:38px;font-weight:900;color:#fff">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
            </div>
        @endif
        <h1 style="font-size:2.8rem;font-weight:800;color:#0A2D74;margin:0 0 12px">{{ $brandName }}</h1>
        @if($brandTagline)
            <p style="font-size:1.1rem;color:#374151;margin:0 0 12px">{{ $brandTagline }}</p>
        @endif
        @if($currentTenant->company_address ?? false)
            <p style="color:#F5A623;font-weight:600;font-size:.95rem"><i class="fas fa-map-marker-alt" style="margin-right:6px"></i>{{ $currentTenant->company_address }}</p>
        @endif
    </div>
</div>

{{-- What We Offer --}}
<section style="padding:64px 0;background:#f8fafc">
    <div class="container">
        <div style="text-align:center;margin-bottom:36px">
            <h2 style="font-size:2rem;font-weight:800;color:#0A2D74;margin:0 0 10px">What We Offer</h2>
            <p style="color:#6b7280;margin:0">Comprehensive travel services tailored to your needs</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:28px">
            @php
                $services = [
                    ['icon'=>'fa-plane','title'=>'International & Local Plans','desc'=>'From global adventures to local getaways, we have the perfect package for you.'],
                    ['icon'=>'fa-file-alt','title'=>'Visa Assistance','desc'=>'Expert guidance through the visa application process for any destination.'],
                    ['icon'=>'fa-globe','title'=>'Airline Ticketing','desc'=>'Best rates and flexible booking options for all major airlines.'],
                    ['icon'=>'fa-briefcase','title'=>'Corporate Travel','desc'=>'Professional arrangements for meetings, events, and team building.'],
                    ['icon'=>'fa-plane-arrival','title'=>'Airport Assistance','desc'=>'Seamless arrival and departure support for worry-free travel.'],
                    ['icon'=>'fa-users','title'=>'Group Packages','desc'=>'Customized itineraries designed for groups of any size.'],
                ];
            @endphp
            @foreach($services as $s)
            <div style="background:#fff;border-radius:16px;padding:28px 24px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center">
                <div style="width:64px;height:64px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas {{ $s['icon'] }}" style="color:#F5A623;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">{{ $s['title'] }}</h4>
                <p style="margin:0;color:#6b7280;font-size:.9rem;line-height:1.6">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Our Values --}}
<section style="padding:64px 0;background:#fff">
    <div class="container">
        <div style="text-align:center;margin-bottom:36px">
            <h2 style="font-size:2rem;font-weight:800;color:#0A2D74;margin:0 0 10px">Our Values</h2>
        </div>
        <div style="max-width:700px;margin:0 auto;display:flex;flex-direction:column;gap:28px">
            <div style="display:flex;align-items:flex-start;gap:20px">
                <div style="width:56px;height:56px;background:#3b82f6;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-bullseye" style="color:#fff;font-size:1.2rem"></i>
                </div>
                <div>
                    <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Customer-Centric</h4>
                    <p style="margin:0;color:#374151;line-height:1.7">Every itinerary is tailored to your preferences, interests, and budget.</p>
                </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:20px">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#ec4899,#f43f5e);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-heart" style="color:#fff;font-size:1.2rem"></i>
                </div>
                <div>
                    <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Personalized Experience</h4>
                    <p style="margin:0;color:#374151;line-height:1.7">We craft experiences that exceed expectations, every time.</p>
                </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:20px">
                <div style="width:56px;height:56px;background:#22c55e;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-chart-line" style="color:#fff;font-size:1.2rem"></i>
                </div>
                <div>
                    <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Quality & Excellence</h4>
                    <p style="margin:0;color:#374151;line-height:1.7">We maintain the highest standards in travel services and customer care.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section style="background:#0A2D74;padding:64px 0;text-align:center">
    <div class="container" style="max-width:640px">
        <h2 style="color:#fff;font-size:1.9rem;font-weight:800;margin:0 0 20px">Ready to Explore?</h2>
        <p style="color:#93c5fd;line-height:1.8;margin:0 0 32px;font-size:1.05rem">Browse our travel packages and plan your next unforgettable adventure.</p>
        <a href="{{ route('tours.index') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#F5A623;color:#fff;padding:13px 32px;border-radius:10px;font-weight:700;text-decoration:none;font-size:.95rem">
            <i class="fas fa-compass"></i> Explore Our Plans
        </a>
    </div>
</section>

@endsection
