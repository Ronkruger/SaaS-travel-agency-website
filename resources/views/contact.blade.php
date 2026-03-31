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

        {{-- 4-card row --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-bottom:56px">
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-phone" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Phone</h4>
                <p style="margin:0;color:#374151;font-size:.95rem">02 8554 6954</p>
            </div>
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-envelope" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Email</h4>
                <p style="margin:0;font-size:.95rem"><a href="mailto:inquiry@discovergrp.com" style="color:#0A2D74">inquiry@discovergrp.com</a></p>
            </div>
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-map-marker-alt" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Office</h4>
                <p style="margin:0;color:#374151;font-size:.9rem;line-height:1.6">22nd Floor, The Upper Class Tower<br>Quezon Ave cor. Sct. Reyes St<br>Diliman, Quezon City, 1103</p>
            </div>
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:36px 24px;text-align:center">
                <div style="width:64px;height:64px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-clock" style="color:#fff;font-size:1.4rem"></i>
                </div>
                <h4 style="margin:0 0 8px;font-size:1rem;font-weight:700">Business Hours</h4>
                <p style="margin:0;color:#374151;font-size:.9rem;line-height:1.7">Monday - Friday: 9AM - 6PM<br>Saturday: 9AM - 2PM</p>
            </div>
        </div>

        {{-- Contact by Department --}}
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.7rem;font-weight:700;margin:0 0 8px">Contact by Department</h2>
            <p style="color:#6b7280;margin:0">Reach out to the right team for your needs</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-bottom:56px">
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:28px 24px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                    <div style="width:48px;height:48px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-users" style="color:#fff;font-size:1.1rem"></i>
                    </div>
                    <h4 style="margin:0;font-size:1rem;font-weight:700">Sales</h4>
                </div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px">
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0995-674-3860</li>
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0919-394-6919</li>
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0962-440-2835</li>
                </ul>
            </div>
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:28px 24px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                    <div style="width:48px;height:48px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-file-alt" style="color:#fff;font-size:1.1rem"></i>
                    </div>
                    <h4 style="margin:0;font-size:1rem;font-weight:700">Visa</h4>
                </div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px">
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0960-312-3656</li>
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0962-373-6463</li>
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0962-373-6465</li>
                </ul>
            </div>
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.07);padding:28px 24px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                    <div style="width:48px;height:48px;background:#F5A623;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-headset" style="color:#fff;font-size:1.1rem"></i>
                    </div>
                    <h4 style="margin:0;font-size:1rem;font-weight:700">Customer Relations</h4>
                </div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px">
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0961-605-2958</li>
                    <li style="font-size:.9rem;color:#374151"><i class="fas fa-phone" style="width:16px;color:#F5A623"></i> 0968-737-4685</li>
                </ul>
            </div>
        </div>

        {{-- Email Addresses --}}
        <div style="text-align:center;margin-bottom:56px">
            <h3 style="font-size:1.1rem;font-weight:700;margin:0 0 14px">Email Addresses</h3>
            <div style="display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap">
                <a href="mailto:inquiry@discovergrp.com" style="color:#F5A623;text-decoration:none;font-size:.95rem"><i class="fas fa-envelope" style="margin-right:6px"></i>inquiry@discovergrp.com</a>
                <span style="color:#d1d5db">|</span>
                <a href="mailto:traveldesk@discovergrp.com" style="color:#F5A623;text-decoration:none;font-size:.95rem"><i class="fas fa-envelope" style="margin-right:6px"></i>traveldesk@discovergrp.com</a>
            </div>
        </div>

        {{-- Follow Us --}}
        <div style="text-align:center;margin-bottom:64px">
            <h3 style="font-size:1.1rem;font-weight:700;margin:0 0 16px">Follow Us</h3>
            <div style="display:flex;align-items:center;justify-content:center;gap:16px">
                <a href="https://www.facebook.com/discovergrp" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:8px;background:#1877f2;color:#fff;padding:11px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.95rem">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://www.instagram.com/discover_grp/" target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff;padding:11px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.95rem">
                    <i class="fab fa-instagram"></i> Instagram
                </a>
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
