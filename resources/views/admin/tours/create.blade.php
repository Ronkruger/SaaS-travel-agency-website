@extends('layouts.admin')
@section('title', 'Add New Tour')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
    <a href="{{ route('admin.tours.index') }}">Tours</a> / Add New
@endsection

@push('styles')
<link href='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css' rel='stylesheet' />
<link href='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css' rel='stylesheet' />
<style>
.tour-form-header{padding:.875rem 1.5rem}
.tour-tabs-nav{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:0;overflow-x:auto;-webkit-overflow-scrolling:touch}
.tour-tab-btn{padding:.5rem 1rem;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;cursor:pointer;font-size:.875rem;color:#475569;font-weight:500;transition:all .2s}
.tour-tab-btn{flex-shrink:0;white-space:nowrap}
.tour-tab-btn:hover{background:#e2e8f0;color:#1e293b}
.tour-tab-btn.active{background:var(--primary,#0e7490);color:#fff;border-color:var(--primary,#0e7490)}
.tour-tab-panel{display:none}.tour-tab-panel.active{display:block}
.repeatable-row{border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:.75rem;background:#fafafa;position:relative}
.repeatable-row .remove-row{position:absolute;top:.5rem;right:.5rem;background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;line-height:1;padding:0}
.form-row-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.form-row-2{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
.d-flex{display:flex}.align-items-center{align-items:center}.gap-2{gap:.5rem}
#tourFormSidebar{position:sticky;top:calc(var(--admin-header-height,70px) + 1rem)}
.itinerary-days-scroll{max-height:360px;overflow-y:auto;padding-right:.35rem;border-radius:8px}
.itinerary-days-scroll::-webkit-scrollbar{width:8px}
.itinerary-days-scroll::-webkit-scrollbar-thumb{background:#94a3b8;border-radius:999px}
.stops-map-sticky{position:sticky;top:calc(var(--admin-header-height,70px) + 1rem);z-index:30}
@media(min-width:992px){.itinerary-days-scroll{max-height:440px}}
@media(min-width:1280px){.itinerary-days-scroll{max-height:520px}}
@media(max-width:1200px){#tourFormSidebar{position:static}}
@media(max-width:992px){.stops-map-sticky{position:static}}
@media(max-width:768px){.form-row-3,.form-row-2{grid-template-columns:1fr}}
/* ── Toggle label (publish sidebar) ─────── */
.toggle-label{display:flex;align-items:center;gap:.625rem;cursor:pointer;font-weight:600;color:var(--gray-700);font-size:.9375rem}
.toggle-label input[type=checkbox]{width:1.1rem;height:1.1rem;accent-color:var(--primary);flex-shrink:0}
.toggle-label .toggle-slider{display:none}/* hide fancy slider when used as simple checkbox label */
/* ── Sticky save bar ─────────────────────── */
#tourStickyBar{position:fixed;bottom:0;left:var(--admin-sidebar-width,260px);right:0;background:#fff;border-top:2px solid var(--primary,#0e7490);padding:.875rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;z-index:90;box-shadow:0 -4px 16px rgba(0,0,0,.1)}
.dep-status-pill{display:inline-flex;align-items:center;padding:.3rem .8rem;border-radius:50px;font-size:.8rem;font-weight:700;white-space:nowrap}
.dep-status-open{background:#d1fae5;color:#065f46}
.dep-status-low{background:#fef3c7;color:#92400e}
.dep-status-full{background:#fee2e2;color:#dc2626}
#tourStickyBar .sticky-checks{display:flex;gap:1.5rem;align-items:center}
#tourStickyBar .sticky-checks label{display:flex;align-items:center;gap:.4rem;font-size:.9rem;font-weight:600;color:var(--gray-700);cursor:pointer}
#tourStickyBar .sticky-checks input[type=checkbox]{width:1rem;height:1rem;accent-color:var(--primary)}
@media(max-width:900px){#tourStickyBar{left:0}}
@media(max-width:600px){#tourStickyBar{padding:.75rem 1rem;flex-wrap:wrap}#tourStickyBar .sticky-checks{flex-wrap:wrap;gap:.75rem}}
/* extra bottom padding so sticky bar doesn't overlap form */
.form-layout{padding-bottom:5rem}
/* ── AI Smart Paste ──────────────────────── */
.ai-paste-toggle{display:flex;align-items:center;gap:.875rem;cursor:pointer;padding:.25rem 0;-webkit-user-select:none;user-select:none}
.ai-paste-icon{width:2.25rem;height:2.25rem;background:linear-gradient(135deg,#7c3aed,#a78bfa);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ai-paste-icon i{color:#fff;font-size:.875rem}
#btnParsePaste{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;border:none;border-radius:var(--radius,6px);font-size:.9375rem;font-weight:600;cursor:pointer;transition:opacity .2s;font-family:inherit}
#btnParsePaste:hover{opacity:.88}#btnParsePaste:disabled{opacity:.55;cursor:default}
.ai-preview-item{display:flex;gap:.75rem;align-items:flex-start;padding:.3rem 0;border-bottom:1px solid #d1fae5;font-size:.875rem}
.ai-preview-item:last-child{border-bottom:none}
.ai-preview-label{font-weight:700;color:#065f46;min-width:140px;flex-shrink:0}
.ai-preview-val{color:#1e293b;word-break:break-word}
@media(max-width:480px){
    .ai-preview-item{flex-direction:column;gap:.2rem}
    .ai-preview-label{min-width:0}
    .tour-form-header{padding:.625rem 1rem}
}
</style>
@endpush

@section('content')

<form action="{{ route('admin.tours.store') }}" method="POST" enctype="multipart/form-data" id="tourForm">
@csrf

<!-- ── AI Smart Paste ─────────────────────────────────────────────── -->
<div class="card mb-4" id="aiSmartPasteCard">
    <div style="padding:1.25rem 1.5rem">
        <div class="ai-paste-toggle" onclick="toggleSmartPaste()" role="button" tabindex="0"
             onkeydown="if(event.key==='Enter'||event.key===' ')toggleSmartPaste()">
            <div class="ai-paste-icon"><i class="fas fa-magic"></i></div>
            <div style="flex:1">
                <div style="font-weight:700;color:#1e293b;font-size:.9375rem">AI Smart Paste &mdash; Auto-fill from Tour Text</div>
                <div style="font-size:.8125rem;color:#64748b;margin-top:.15rem">Paste your tour brief text and let the form fill itself automatically</div>
            </div>
            <span id="aiPasteChevron" style="color:#7c3aed;font-weight:600;font-size:.875rem;white-space:nowrap">
                Open <i class="fas fa-chevron-down"></i>
            </span>
        </div>
        <div id="aiSmartPasteBody" style="display:none;border-top:1px solid #e2e8f0;margin-top:1rem;padding-top:1rem">
            <p style="font-size:.875rem;color:#475569;margin-bottom:.75rem">
                Paste your raw tour description below (dates, prices, optional tours, countries, freebies, downpayment rules &mdash; all supported).
            </p>
            <textarea id="aiPasteInput" class="form-control" rows="9"
                style="font-family:monospace;font-size:.8125rem;background:#fafafa;line-height:1.6"
                placeholder="Route A Preferred (15 days)&#10;Links for 2026: https://bit.ly/EXAMPLE1 , and https://bit.ly/EXAMPLE2&#10;Links for 2027: https://bit.ly/EXAMPLE3&#10;Travel Date: May 13 &#8211; 27, 2026 (Php 170,000)&#10;             May 25 &#8211; June 8, 2026 (Php 170,000)&#10;Optional Tours:&#10;Day 4: Disneyland Paris Tour (Php 1,500)&#10;Cash Freebies:&#10;&#8364;50 Shopping Allowance&#10;Down Payment: 30,000"></textarea>
            <div style="margin-top:.75rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                <button type="button" id="btnParsePaste" onclick="runSmartPaste()">
                    <i class="fas fa-magic"></i> Parse &amp; Auto-fill
                </button>
                <button type="button" class="btn btn-outline" onclick="clearSmartPaste()">Clear</button>
            </div>
            <div id="aiParsePreview" style="display:none;margin-top:1rem"></div>
        </div>
    </div>
</div>

<div class="form-layout">
    <!-- Main Column -->
    <div class="form-main">

        <!-- Page Title + Tab Navigation -->
        <div class="card mb-3">
            <div class="card-body tour-form-header">
                <div class="page-title-row" style="margin-bottom:.75rem">
                    <h2 class="page-title">Add New Tour</h2>
                    <a href="{{ route('admin.tours.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="tour-tabs-nav" role="tablist">
                    <button type="button" class="tour-tab-btn active" data-tab="basic"><i class="fas fa-info-circle"></i> Basic Info</button>
                    <button type="button" class="tour-tab-btn" data-tab="pricing"><i class="fas fa-tag"></i> Pricing</button>
                    <button type="button" class="tour-tab-btn" data-tab="dates"><i class="fas fa-calendar"></i> Travel Dates</button>
                    <button type="button" class="tour-tab-btn" data-tab="content"><i class="fas fa-images"></i> Content</button>
                    <button type="button" class="tour-tab-btn" data-tab="itinerary"><i class="fas fa-list-ol"></i> Itinerary</button>
                    <button type="button" class="tour-tab-btn" data-tab="stops"><i class="fas fa-map-marker-alt"></i> Stops</button>
                    <button type="button" class="tour-tab-btn" data-tab="booking"><i class="fas fa-bookmark"></i> Booking</button>
                    <button type="button" class="tour-tab-btn" data-tab="extras"><i class="fas fa-gift"></i> Extras</button>
                </div>
            </div>
        </div>

        <!-- ── TAB 1: BASIC INFO ─────────────────────────────────────── -->
        <div class="tour-tab-panel active card mb-4" id="tab-basic">
            <div class="card-header"><h4>Basic Information</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label>Tour Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                        class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Custom URL Slug</label>
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <span class="text-muted" style="white-space:nowrap;font-size:.875rem">{{ url('/tours') }}/</span>
                        <input type="text" name="slug" id="slugInput" class="form-control" value="{{ old('slug') }}"
                            placeholder="auto-generated-from-title" pattern="[a-z0-9\-]+" style="font-family:monospace">
                    </div>
                    <small class="text-muted">Lowercase letters, numbers, hyphens. Leave blank to auto-generate.</small>
                    <div style="margin-top:.3rem;font-size:.8rem;color:green">
                        <i class="fas fa-link"></i> <span id="slugPreview">{{ url('/tours/') }}<em>..your-slug..</em></span>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Tour Line / Brand</label>
                        <input type="text" name="line" class="form-control" value="{{ old('line') }}"
                            placeholder="e.g. Island Luxury, Adventure Series">
                    </div>
                    <div class="form-group">
                        <label>Continent</label>
                        <select name="continent" class="form-control">
                            <option value="">Select continent...</option>
                            @foreach(['Africa','Antarctica','Asia','Europe','North America','Oceania','South America'] as $c)
                                <option value="{{ $c }}" {{ old('continent') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Duration (days) *</label>
                        <input type="number" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror"
                            value="{{ old('duration_days', 1) }}" min="1" required>
                        @error('duration_days')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group d-flex align-items-center" style="padding-top:1.75rem">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                            <input type="checkbox" name="guaranteed_departure" value="1" {{ old('guaranteed_departure') ? 'checked' : '' }}>
                            Guaranteed Departure
                        </label>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Booking PDF URL</label>
                        <input type="text" name="booking_pdf_url" class="form-control" value="{{ old('booking_pdf_url') }}"
                            placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Video URL</label>
                        <input type="text" name="video_url" class="form-control" value="{{ old('video_url') }}"
                            placeholder="YouTube / Vimeo URL">
                    </div>
                    <div class="form-group">
                        <label>Facebook Post URL</label>
                        <input type="text" name="facebook_post_url" class="form-control" value="{{ old('facebook_post_url') }}"
                            placeholder="https://facebook.com/...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Summary</label>
                    <textarea name="summary" class="form-control" rows="4"
                        placeholder="Full description shown on the tour detail page...">{{ old('summary') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Short Description <small class="text-muted">(max 500 chars, used in listing cards)</small></label>
                    <textarea name="short_description" class="form-control" rows="2" maxlength="500"
                        placeholder="Brief one-liner for listing cards...">{{ old('short_description') }}</textarea>
                </div>

            </div>
        </div>

        <!-- ── TAB 2: PRICING ─────────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-pricing">
            <div class="card-header"><h4>Pricing</h4></div>
            <div class="card-body">

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Regular Price / Person (₱)</label>
                        <input type="number" name="regular_price_per_person" class="form-control"
                            value="{{ old('regular_price_per_person') }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Promo Price / Person (₱) <small class="text-muted">(leave blank = no promo)</small></label>
                        <input type="number" name="promo_price_per_person" class="form-control"
                            value="{{ old('promo_price_per_person') }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Base Price / Day (₱)</label>
                        <input type="number" name="base_price_per_day" class="form-control"
                            value="{{ old('base_price_per_day') }}" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group d-flex align-items-center" style="padding-top:1.75rem">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                            <input type="checkbox" name="is_sale_enabled" value="1" {{ old('is_sale_enabled') ? 'checked' : '' }}>
                            Sale Currently Active
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Sale End Date</label>
                        <input type="date" name="sale_end_date" class="form-control" value="{{ old('sale_end_date') }}">
                    </div>
                </div>

            </div>
        </div>

        <!-- ── TAB 3: TRAVEL DATES ────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-dates">
            <div class="card-header"><h4>Travel Dates</h4></div>
            <div class="card-body">

                <h5>Travel Window (overall availability window)</h5>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="travel_window_start" class="form-control"
                            value="{{ old('travel_window_start') }}">
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="travel_window_end" class="form-control"
                            value="{{ old('travel_window_end') }}">
                    </div>
                </div>

                <hr>
                <h5>Specific Departure Dates</h5>
                <div id="departureDatesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addDepartureDate()">
                    <i class="fas fa-plus"></i> Add Departure Date
                </button>

            </div>
        </div>

        <!-- ── TAB 4: CONTENT & MEDIA ─────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-content">
            <div class="card-header"><h4>Content &amp; Media</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label>Main Image <small class="text-muted">(used as hero/thumbnail)</small></label>
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Gallery Images <small class="text-muted">(select multiple)</small></label>
                    <input type="file" name="gallery_image_files[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="form-group">
                    <label>Related Images <small class="text-muted">(e.g. destination or activity photos)</small></label>
                    <input type="file" name="related_image_files[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="form-group">
                    <label>Video File <small class="text-muted">(mp4, mov, webm — max 100MB)</small></label>
                    <input type="file" name="video_file" class="form-control" accept="video/*">
                </div>

                <div class="form-group">
                    <label>Highlights <small class="text-muted">(one per line)</small></label>
                    <textarea name="highlights" class="form-control" rows="6"
                        placeholder="Overwater villa accommodation&#10;Guided snorkeling&#10;Sunset dolphin cruise">{{ old('highlights') }}</textarea>
                </div>

            </div>
        </div>

        <!-- ── TAB 5: ITINERARY ───────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-itinerary">
            <div class="card-header"><h4>Day-by-Day Itinerary</h4></div>
            <div class="card-body">
                <div id="itineraryContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addItineraryDay()">
                    <i class="fas fa-plus"></i> Add Day
                </button>
            </div>
        </div>

        <!-- ── TAB 6: STOPS & GEOGRAPHY ──────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-stops">
            <div class="card-header"><h4>Route Map &amp; Day-by-Day Itinerary</h4></div>
            <div class="card-body">

                {{-- Mapbox Route Map --}}
                <div id="adminStopsMap"
                     class="stops-map-sticky"
                     style="height:420px;border-radius:10px;margin-bottom:1.5rem;background:#e8edf3;"
                     data-mapbox-token="{{ config('ai.mapbox_token') }}"></div>

                <h5>Itinerary Days <small class="text-muted">(in travel order)</small></h5>
                <div class="itinerary-days-scroll">
                    <div id="fullStopsContainer"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="addFullStop()">
                    <i class="fas fa-plus"></i> Add Day
                </button>

                <hr class="mt-4">
                <h5>Additional Geographical Info</h5>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Starting Point</label>
                        <input type="text" name="ai_starting_point" class="form-control" value="{{ old('ai_starting_point') }}"
                            placeholder="e.g. Narita International Airport, Tokyo">
                    </div>
                    <div class="form-group">
                        <label>Ending Point</label>
                        <input type="text" name="ai_ending_point" class="form-control" value="{{ old('ai_ending_point') }}"
                            placeholder="e.g. Kansai International Airport, Osaka">
                    </div>
                </div>

                <div class="form-group">
                    <label>Countries Visited <small class="text-muted">(one per line)</small></label>
                    <textarea name="ai_countries_visited" class="form-control" rows="3"
                        placeholder="Japan&#10;France&#10;Italy">{{ old('ai_countries_visited') }}</textarea>
                </div>

                <h6 class="mt-3">Main Cities by Country</h6>
                <p class="text-muted" style="font-size:.85rem">For each country, list its main cities (comma-separated).</p>
                <div id="mainCitiesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addMainCitiesRow()">
                    <i class="fas fa-plus"></i> Add Country → Cities
                </button>

                <h6 class="mt-3">Featured Countries <small class="text-muted">(name + image)</small></h6>
                <div id="aiCountriesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addNameImagePair('aiCountriesContainer','ai_countries')">
                    <i class="fas fa-plus"></i> Add Country
                </button>

                <h6 class="mt-3">Featured Cities to Visit <small class="text-muted">(name + image)</small></h6>
                <div id="aiCitiesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addNameImagePair('aiCitiesContainer','ai_cities_to_visit')">
                    <i class="fas fa-plus"></i> Add City
                </button>

            </div>
        </div>

        <!-- ── TAB 7: BOOKING ─────────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-booking">
            <div class="card-header"><h4>Booking</h4></div>
            <div class="card-body">

                <h5>Flipbook / Presentation Links</h5>
                <p class="text-muted" style="font-size:.85rem">Add flipbook or presentation URLs for each year (e.g. Canva, Issuu, or bit.ly links).</p>
                <div id="bookingLinksContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addBookingLinkYear()">
                    <i class="fas fa-plus"></i> Add Year Group
                </button>

                <hr class="mt-4">
                <h5>Down Payment Settings</h5>
                <div class="form-group">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                        <input type="checkbox" name="allows_downpayment" value="1" id="allowsDownpayment"
                            {{ old('allows_downpayment') ? 'checked' : '' }}>
                        Allow booking with a down payment
                    </label>
                </div>

                <div class="form-row-2" id="downpaymentFields" style="{{ old('allows_downpayment') ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label>Fixed Down Payment Amount (₱)</label>
                        <input type="number" name="fixed_downpayment_amount" class="form-control"
                            value="{{ old('fixed_downpayment_amount') }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Balance Due (days before travel)</label>
                        <input type="number" name="balance_due_days_before_travel" class="form-control"
                            value="{{ old('balance_due_days_before_travel') }}" min="0">
                    </div>
                </div>

                <hr class="mt-4">
                <h5>Installment / Payment Terms</h5>
                <p class="text-muted" style="font-size:.85rem">Allow clients to pay in monthly installments (e.g. ₱16,000/month for 10 months). Set both fields to enable cash installment booking.</p>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Max Payment Terms (months, 1–15)</label>
                        <input type="number" name="installment_months" class="form-control"
                            value="{{ old('installment_months') }}" min="1" max="15"
                            placeholder="e.g. 10">
                        <small class="text-muted">Leave blank to disable installment option.</small>
                    </div>
                    <div class="form-group">
                        <label>Monthly Installment Amount (₱)</label>
                        <input type="number" name="monthly_installment_amount" class="form-control"
                            value="{{ old('monthly_installment_amount') }}" step="0.01" min="0"
                            placeholder="e.g. 16000">
                        <small class="text-muted">Fixed amount per monthly term.</small>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── TAB 8: EXTRAS ─────────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-extras">
            <div class="card-header"><h4>Extras</h4></div>
            <div class="card-body">

                <h5>Optional Tours / Excursions</h5>
                <p class="text-muted" style="font-size:.85rem">Add optional paid excursions guests can add to their trip.</p>
                <div id="optionalToursContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addOptionalTour()">
                    <i class="fas fa-plus"></i> Add Optional Tour
                </button>

                <hr class="mt-4">
                <h5>Cash &amp; Freebies</h5>
                <p class="text-muted" style="font-size:.85rem">Add any included cash allowances or free perks.</p>
                <div id="cashFreebiesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addCashFreebie()">
                    <i class="fas fa-plus"></i> Add Freebie
                </button>

            </div>
        </div>

    </div><!-- /form-main -->

    <!-- Sidebar -->
    <div class="form-sidebar" id="tourFormSidebar">
        <div class="card mb-3">
            <div class="card-header"><h4>Publish</h4></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="is_active" id="isActiveSide" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        Active (visible to public)
                    </label>
                </div>
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="is_featured" id="isFeaturedSide" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        Featured on Homepage
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-3">
                    <i class="fas fa-save"></i> Create Tour
                </button>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-outline btn-block mt-2">Cancel</a>
            </div>
        </div>
    </div>

</div><!-- /form-layout -->

<!-- ── Sticky Save Bar ─────────────────────────────────────────────── -->
<div id="tourStickyBar">
    <div class="sticky-checks">
        <label>
            <input type="checkbox" id="stickyActive" onchange="document.getElementById('isActiveSide').checked=this.checked" checked>
            Active
        </label>
        <label>
            <input type="checkbox" id="stickyFeatured" onchange="document.getElementById('isFeaturedSide').checked=this.checked">
            Featured
        </label>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <a href="{{ route('admin.tours.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding:.625rem 1.75rem;font-size:1rem">
            <i class="fas fa-save"></i> Create Tour
        </button>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script src='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js'></script>
<script src="{{ asset('js/admin-tour-form.js') }}"></script>
<script>
// Tab switching
document.querySelectorAll('.tour-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tour-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tour-tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        // Init Mapbox map when Stops tab is opened
        if (btn.dataset.tab === 'stops') {
            setTimeout(initStopsMap, 50);
        }
    });
});

// Slug preview
const titleInput = document.getElementById('title');
const slugInput  = document.getElementById('slugInput');
const preview    = document.getElementById('slugPreview');
function slugify(s) {
    return s.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
}
function updateSlugPreview() {
    const slug = slugInput.value || slugify(titleInput.value);
    preview.innerHTML = '{{ url("/tours/") }}' + '<em>' + (slug || '..your-slug..') + '</em>';
}
titleInput.addEventListener('input', updateSlugPreview);
slugInput.addEventListener('input', updateSlugPreview);

// Down payment toggle
const dpCheck = document.getElementById('allowsDownpayment');
if (dpCheck) dpCheck.addEventListener('change', function() {
    document.getElementById('downpaymentFields').style.display = this.checked ? '' : 'none';
});

// Sync sticky bar checkboxes with sidebar on load
(function(){
    var sa = document.getElementById('stickyActive');
    var sf = document.getElementById('stickyFeatured');
    var ia = document.getElementById('isActiveSide');
    var iff = document.getElementById('isFeaturedSide');
    if(sa && ia) { sa.checked = ia.checked; ia.addEventListener('change', function(){ sa.checked = ia.checked; }); }
    if(sf && iff) { sf.checked = iff.checked; iff.addEventListener('change', function(){ sf.checked = iff.checked; }); }
})();
</script>
@endpush
