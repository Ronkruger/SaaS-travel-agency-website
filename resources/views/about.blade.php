@extends('layouts.app')
@section('title', 'About Us')

@section('content')

{{-- Hero --}}
<div style="background:linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 100%);padding:72px 0 56px;text-align:center">
    <div class="container">
        <div style="width:88px;height:88px;background:#0A2D74;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 8px 24px rgba(10,45,116,.25)">
            <svg width="56" height="56" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <text x="20" y="28.5" text-anchor="middle" font-family="'Poppins',sans-serif" font-size="22" font-weight="900" fill="#ffffff">D</text>
            </svg>
        </div>
        <h1 style="font-size:2.8rem;font-weight:800;color:#0A2D74;margin:0 0 12px">Discover Group</h1>
        <p style="font-size:1.1rem;color:#374151;margin:0 0 12px">Delivering Exceptional Business Solutions Since 2008</p>
        <p style="color:#F5A623;font-weight:600;font-size:.95rem"><i class="fas fa-map-marker-alt" style="margin-right:6px"></i>Quezon City, Philippines</p>

        <div style="display:flex;justify-content:center;align-items:center;gap:12px;margin-top:32px">
            <button onclick="document.getElementById('scroll-down').scrollIntoView({behavior:'smooth'})"
                style="background:none;border:2px solid #d1d5db;border-radius:50%;width:44px;height:44px;cursor:pointer;color:#6b7280;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>
</div>

{{-- Stats --}}
<div id="scroll-down" style="background:#fff;padding:48px 0;border-bottom:1px solid #f1f5f9">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:32px;text-align:center">
            <div>
                <div style="width:64px;height:64px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                    <i class="fas fa-calendar-alt" style="color:#F5A623;font-size:1.4rem"></i>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#0A2D74">2008</div>
                <div style="color:#6b7280;font-size:.9rem;margin-top:4px">Established</div>
            </div>
            <div>
                <div style="width:64px;height:64px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                    <i class="fas fa-award" style="color:#F5A623;font-size:1.4rem"></i>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#0A2D74">{{ date('Y') - 2008 }}+</div>
                <div style="color:#6b7280;font-size:.9rem;margin-top:4px">Years Experience</div>
            </div>
            <div>
                <div style="width:64px;height:64px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                    <i class="fas fa-users" style="color:#F5A623;font-size:1.4rem"></i>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#0A2D74">32K+</div>
                <div style="color:#6b7280;font-size:.9rem;margin-top:4px">Happy Subscribers</div>
            </div>
            <div>
                <div style="width:64px;height:64px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                    <i class="fas fa-heart" style="color:#F5A623;font-size:1.4rem"></i>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#0A2D74">100%</div>
                <div style="color:#6b7280;font-size:.9rem;margin-top:4px">Personalized</div>
            </div>
        </div>
    </div>
</div>

{{-- Our Story tabs --}}
<section style="padding:64px 0;background:#f8fafc">
    <div class="container">
        <div style="text-align:center;margin-bottom:36px">
            <h2 style="font-size:2rem;font-weight:800;color:#0A2D74;margin:0 0 10px">Our Story</h2>
            <p style="color:#6b7280;margin:0">Discover what makes us the trusted service partner for thousands of satisfied clients</p>
        </div>

        {{-- Tab buttons --}}
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:40px">
            <button class="about-tab active" data-tab="journey"
                style="padding:10px 28px;border-radius:50px;font-weight:600;font-size:.9rem;cursor:pointer;border:2px solid #e5e7eb;background:#fff;color:#374151">
                ✨ Our Journey
            </button>
            <button class="about-tab" data-tab="offer"
                style="padding:10px 28px;border-radius:50px;font-weight:600;font-size:.9rem;cursor:pointer;border:2px solid #e5e7eb;background:#fff;color:#374151">
                🏛 What We Offer
            </button>
            <button class="about-tab" data-tab="values"
                style="padding:10px 28px;border-radius:50px;font-weight:600;font-size:.9rem;cursor:pointer;border:2px solid #e5e7eb;background:#fff;color:#374151">
                🤍 Our Values
            </button>
        </div>

        {{-- Tab: Journey --}}
        <div id="tab-journey" class="about-tab-panel">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:32px">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06)">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                        <div style="width:48px;height:48px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-calendar-alt" style="color:#F5A623;font-size:1.1rem"></i>
                        </div>
                        <h3 style="margin:0;font-size:1.15rem;font-weight:700">Founded in May 2008</h3>
                    </div>
                    <p style="color:#374151;line-height:1.8;margin:0 0 12px">Discover Group of Travel Services Inc. began with a simple vision: to make travel accessible, enjoyable, and truly unforgettable for every Filipino traveler.</p>
                    <p style="color:#374151;line-height:1.8;margin:0">What started as a small travel agency in Quezon City has grown into a trusted name in the Philippine travel industry, serving thousands of satisfied customers across 15+ years.</p>
                </div>
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06)">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                        <div style="width:48px;height:48px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-map-marker-alt" style="color:#F5A623;font-size:1.1rem"></i>
                        </div>
                        <h3 style="margin:0;font-size:1.15rem;font-weight:700">Our Headquarters</h3>
                    </div>
                    <p style="color:#374151;line-height:1.8;margin:0 0 12px">Located at the 22nd floor of The Upper Class Tower on Quezon Avenue, Diliman, our modern office serves as the hub for all our travel operations.</p>
                    <p style="color:#374151;line-height:1.8;margin:0">Our strategic location in Metro Manila allows us to serve clients nationwide while maintaining close partnerships with international travel networks.</p>
                </div>
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);grid-column:1/-1">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                        <div style="width:48px;height:48px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-award" style="color:#F5A623;font-size:1.1rem"></i>
                        </div>
                        <h3 style="margin:0;font-size:1.15rem;font-weight:700">Accredited &amp; Trusted</h3>
                    </div>
                    <p style="color:#374151;line-height:1.8;margin:0">As a DOT-accredited travel agency since 2022, we uphold the highest standards of service quality and professionalism. Our dedicated team of 11–50 travel experts combines personalized attention with industry expertise to ensure every journey exceeds expectations.</p>
                </div>
            </div>
        </div>

        {{-- Tab: What We Offer --}}
        <div id="tab-offer" class="about-tab-panel" style="display:none">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:28px">
                @php
                    $services = [
                        ['icon'=>'fa-plane','title'=>'International & Local Plans','desc'=>'From global adventures to local experiences'],
                        ['icon'=>'fa-file-alt','title'=>'Visa Assistance','desc'=>'Specializing in Japan visa processing'],
                        ['icon'=>'fa-globe','title'=>'Airline Ticketing','desc'=>'Best rates and flexible booking options'],
                        ['icon'=>'fa-briefcase','title'=>'Corporate Travel','desc'=>'Meetings, events, and team building'],
                        ['icon'=>'fa-plane-arrival','title'=>'Airport Assistance','desc'=>'Seamless arrival and departure support'],
                        ['icon'=>'fa-users','title'=>'Group Packages','desc'=>'Customized itineraries for any group size'],
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

        {{-- Tab: Our Values --}}
        <div id="tab-values" class="about-tab-panel" style="display:none">
            <div style="max-width:700px;margin:0 auto;display:flex;flex-direction:column;gap:28px">
                <div style="display:flex;align-items:flex-start;gap:20px">
                    <div style="width:56px;height:56px;background:#3b82f6;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-bullseye" style="color:#fff;font-size:1.2rem"></i>
                    </div>
                    <div>
                        <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Customer-Centric</h4>
                        <p style="margin:0;color:#374151;line-height:1.7">Every itinerary is tailored to your preferences, interests, and budget. Your dream journey is our priority.</p>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:20px">
                    <div style="width:56px;height:56px;background:linear-gradient(135deg,#ec4899,#f43f5e);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-heart" style="color:#fff;font-size:1.2rem"></i>
                    </div>
                    <div>
                        <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Collaborative Approach</h4>
                        <p style="margin:0;color:#374151;line-height:1.7">We work closely with you to understand your vision and craft experiences that exceed expectations.</p>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:20px">
                    <div style="width:56px;height:56px;background:#22c55e;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-chart-line" style="color:#fff;font-size:1.2rem"></i>
                    </div>
                    <div>
                        <h4 style="margin:0 0 8px;font-size:1.05rem;font-weight:700">Quality &amp; Excellence</h4>
                        <p style="margin:0;color:#374151;line-height:1.7">DOT-accredited since 2022, we maintain the highest standards in travel services and customer care.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Our Mission --}}
<section style="background:#0A2D74;padding:64px 0;text-align:center">
    <div class="container" style="max-width:640px">
        <div style="width:56px;height:56px;background:rgba(245,166,35,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
            <i class="fas fa-star" style="color:#F5A623;font-size:1.3rem"></i>
        </div>
        <h2 style="color:#fff;font-size:1.9rem;font-weight:800;margin:0 0 20px">Our Mission</h2>
        <p style="color:#93c5fd;line-height:1.8;margin:0 0 32px;font-size:1.05rem">To provide every Filipino traveler with seamless, personalized, and value-packed travel experiences — from visa processing to door-to-door trip management.</p>
        <a href="{{ route('tours.index') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#F5A623;color:#fff;padding:13px 32px;border-radius:10px;font-weight:700;text-decoration:none;font-size:.95rem">
            <i class="fas fa-compass"></i> Explore Our Plans
        </a>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function(){
    const tabs = document.querySelectorAll('.about-tab');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => {
                b.style.background = '#fff';
                b.style.color      = '#374151';
                b.style.borderColor= '#e5e7eb';
                b.classList.remove('active');
            });
            document.querySelectorAll('.about-tab-panel').forEach(p => p.style.display = 'none');
            btn.style.background  = '#F5A623';
            btn.style.color       = '#fff';
            btn.style.borderColor = '#F5A623';
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).style.display = 'block';
        });
    });
    // Set initial active style
    const activeBtn = document.querySelector('.about-tab.active');
    if (activeBtn) {
        activeBtn.style.background  = '#F5A623';
        activeBtn.style.color       = '#fff';
        activeBtn.style.borderColor = '#F5A623';
    }
})();
</script>
@endpush
