@extends('layouts.app')
@section('title', 'Contact Us')

@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);padding:64px 0 48px">
    <div class="container" style="text-align:center">
        <h1 style="color:#fff;font-size:2.4rem;margin:0 0 10px">Get in Touch</h1>
        <p style="color:#93c5fd;font-size:1.05rem;margin:0">Have questions about our tours? We're here to help you plan your perfect European adventure.</p>
    </div>
</div>

<section class="section" style="padding:56px 0">
    <div class="container">

        {{-- Contact info cards --}}
        @php
            $tenantEmail = $currentTenant->email ?? null;
            $tenantPhone = $currentTenant->company_phone ?? null;
            $tenantAddress = $currentTenant->company_address ?? null;
        @endphp
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-bottom:56px">
            @if($tenantPhone)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-phone" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Phone</h4>
                <p style="margin:0;color:#374151;font-size:.95rem">{{ $tenantPhone }}</p>
            </div>
            @endif
            @if($tenantEmail)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-envelope" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Email</h4>
                <p style="margin:0;font-size:.95rem"><a href="mailto:{{ $tenantEmail }}" style="color:#0A2D74">{{ $tenantEmail }}</a></p>
            </div>
            @endif
            @if($tenantAddress)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-map-marker-alt" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Office</h4>
                <p style="margin:0;color:#374151;font-size:.9rem;line-height:1.6">{{ $tenantAddress }}</p>
            </div>
            @endif
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-clock" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Business Hours</h4>
                <p style="margin:0;color:#374151;font-size:.9rem;line-height:1.7">Monday - Friday: 9AM - 6PM<br>Saturday: 9AM - 2PM</p>
            </div>
        </div>

        {{-- Send Us a Message --}}
        <div style="background:#f4f6f8;border-radius:20px;padding:56px 0">
            <div style="max-width:660px;margin:0 auto;padding:0 24px">
                <div style="text-align:center;margin-bottom:36px">
                    <h2 style="font-size:1.9rem;font-weight:700;margin:0 0 10px">Send Us a Message</h2>
                    <p style="color:#6b7280;margin:0">Fill out the form below and our team will get back to you within 24 hours.</p>
                </div>
                <div style="background:#fff;border-radius:16px;padding:36px 32px;box-shadow:0 2px 16px rgba(0,0,0,.07)">
                    @if(session('contact_success'))
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('contact_success') }}</div>
                    @endif
                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                            <div>
                                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Full Name <span style="color:#dc2626">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="John Doe" required>
                                @error('name')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Email Address <span style="color:#dc2626">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="john@example.com" required>
                                @error('email')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                            <div>
                                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+63 (9XX) XXX-XXXX">
                            </div>
                            <div>
                                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Subject <span style="color:#dc2626">*</span></label>
                                <select name="subject" class="form-control" required>
                                    <option value="">Select a subject</option>
                                    <option value="Tour Inquiry" {{ old('subject')=='Tour Inquiry'?'selected':'' }}>Tour Inquiry</option>
                                    <option value="Booking Support" {{ old('subject')=='Booking Support'?'selected':'' }}>Booking Support</option>
                                    <option value="Visa Assistance" {{ old('subject')=='Visa Assistance'?'selected':'' }}>Visa Assistance</option>
                                    <option value="Group Travel" {{ old('subject')=='Group Travel'?'selected':'' }}>Group Travel</option>
                                    <option value="Feedback" {{ old('subject')=='Feedback'?'selected':'' }}>Feedback</option>
                                    <option value="Other" {{ old('subject')=='Other'?'selected':'' }}>Other</option>
                                </select>
                                @error('subject')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div style="margin-bottom:24px">
                            <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Your Message <span style="color:#dc2626">*</span></label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Tell us about your travel plans or questions..." required>{{ old('message') }}</textarea>
                            @error('message')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                        <button type="submit"
                            style="width:100%;background:#F5A623;color:#fff;border:none;padding:14px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
