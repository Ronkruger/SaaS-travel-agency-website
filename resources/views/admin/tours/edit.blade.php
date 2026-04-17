@extends('layouts.admin')
@section('title', 'Edit Plan: ' . $tour->title)

@section('skeleton')
    @include('admin.partials.skeleton-form')
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
    <a href="{{ route('admin.tours.index') }}">Plans</a> / Edit
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
.existing-thumb{max-height:80px;border-radius:4px;border:1px solid #e2e8f0}
.existing-thumb-wrap{display:flex;align-items:center;gap:.75rem;padding:.5rem;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:6px;margin-bottom:.5rem}
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
/* ── Toggle label fix */
.toggle-label{display:flex;align-items:center;gap:.625rem;cursor:pointer;font-weight:600;color:var(--gray-700);font-size:.9375rem}
.toggle-label input[type=checkbox]{width:1.1rem;height:1.1rem;accent-color:var(--primary);flex-shrink:0}
.toggle-label .toggle-slider{display:none}
/* ── Sticky save bar */
#tourStickyBar{position:fixed;bottom:0;left:var(--admin-sidebar-width,260px);right:0;background:#fff;border-top:2px solid var(--primary,#0e7490);padding:.875rem 2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;z-index:90;box-shadow:0 -4px 16px rgba(0,0,0,.1)}
.dep-status-pill{display:inline-flex;align-items:center;padding:.3rem .8rem;border-radius:50px;font-size:.8rem;font-weight:700;white-space:nowrap}
.dep-status-open{background:#d1fae5;color:#065f46}
.dep-status-low{background:#fef3c7;color:#92400e}
.dep-status-full{background:#fee2e2;color:#dc2626}
#tourStickyBar .sticky-checks{display:flex;gap:1.5rem;align-items:center}
#tourStickyBar .sticky-checks label{display:flex;align-items:center;gap:.4rem;font-size:.9rem;font-weight:600;color:var(--gray-700);cursor:pointer}
#tourStickyBar .sticky-checks input[type=checkbox]{width:1rem;height:1rem;accent-color:var(--primary)}
@media(max-width:900px){#tourStickyBar{left:0}}
@media(max-width:600px){#tourStickyBar{padding:.75rem 1rem;flex-wrap:wrap}}
.form-layout{padding-bottom:5rem}
</style>
@endpush

@section('content')

<form action="{{ route('admin.tours.update', $tour) }}" method="POST" enctype="multipart/form-data" id="tourForm">
@csrf
@method('PUT')

<div class="form-layout">
    <!-- Main Column -->
    <div class="form-main">

        <!-- Page Title + Tab Navigation -->
        <div class="card mb-3">
            <div class="card-body tour-form-header">
                <div class="page-title-row" style="margin-bottom:.75rem">
                    <h2 class="page-title">Edit Plan: {{ $tour->title }}</h2>
                    <div style="display:flex;gap:.5rem">
                        <a href="{{ route('tours.show', $tour->slug) }}" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i> View</a>
                        <a href="{{ route('admin.tours.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="tour-tabs-nav" role="tablist">
                    <button type="button" class="tour-tab-btn active" data-tab="basic"><i class="fas fa-info-circle"></i> Basic Info</button>
                    <button type="button" class="tour-tab-btn" data-tab="pricing"><i class="fas fa-tag"></i> Pricing</button>
                    <button type="button" class="tour-tab-btn" data-tab="dates"><i class="fas fa-calendar"></i> Plan Dates</button>
                    <button type="button" class="tour-tab-btn" data-tab="content"><i class="fas fa-images"></i> Content</button>
                    <button type="button" class="tour-tab-btn" data-tab="itinerary"><i class="fas fa-list-ol"></i> Features</button>
                    <button type="button" class="tour-tab-btn" data-tab="stops"><i class="fas fa-map-marker-alt"></i> Milestones</button>
                    <button type="button" class="tour-tab-btn" data-tab="booking"><i class="fas fa-bookmark"></i> Enrollment</button>
                    <button type="button" class="tour-tab-btn" data-tab="extras"><i class="fas fa-gift"></i> Extras</button>
                </div>
            </div>
        </div>

        <!-- ── TAB 1: BASIC INFO ─────────────────────────────────────── -->
        <div class="tour-tab-panel active card mb-4" id="tab-basic">
            <div class="card-header"><h4>Basic Information</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label>Plan Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $tour->title) }}"
                        class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Custom URL Slug</label>
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <span class="text-muted" style="white-space:nowrap;font-size:.875rem">{{ url('/tours') }}/</span>
                        <input type="text" name="slug" id="slugInput" class="form-control" value="{{ old('slug', $tour->slug) }}"
                            placeholder="auto-generated-from-title" pattern="[a-z0-9\-]+" style="font-family:monospace">
                    </div>
                    <small class="text-muted">Lowercase letters, numbers, hyphens only.</small>
                    <div style="margin-top:.3rem;font-size:.8rem;color:green">
                        <i class="fas fa-link"></i> <span id="slugPreview">{{ url('/tours/' . $tour->slug) }}</span>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Plan Line / Brand</label>
                        <input type="text" name="line" class="form-control" value="{{ old('line', $tour->line) }}"
                            placeholder="e.g. Island Luxury, Adventure Series">
                    </div>
                    <div class="form-group">
                        <label>Region</label>
                        <select name="continent" class="form-control">
                            <option value="">Select region...</option>
                            @foreach(['Africa','Antarctica','Asia','Europe','North America','Oceania','South America'] as $c)
                                <option value="{{ $c }}" {{ old('continent', $tour->continent) == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Duration (days) *</label>
                        <input type="number" name="duration_days" class="form-control @error('duration_days') is-invalid @enderror"
                            value="{{ old('duration_days', $tour->duration_days) }}" min="1" required>
                        @error('duration_days')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group d-flex align-items-center" style="padding-top:1.75rem">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                            <input type="checkbox" name="guaranteed_departure" value="1"
                                {{ old('guaranteed_departure', $tour->guaranteed_departure) ? 'checked' : '' }}>
                            Guaranteed Start
                        </label>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Enrollment PDF URL</label>
                        <input type="text" name="booking_pdf_url" class="form-control"
                            value="{{ old('booking_pdf_url', $tour->booking_pdf_url) }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Video URL</label>
                        <input type="text" name="video_url" id="edit_video_url" class="form-control"
                            value="{{ old('video_url', $tour->video_url) }}" placeholder="YouTube / Vimeo / Google Drive URL"
                            oninput="updateEmbedPreview('edit_video_url','edit_video_preview','video')">
                        <div id="edit_video_preview" class="admin-embed-preview" style="margin-top:10px;{{ $tour->video_url ? '' : 'display:none' }}">
                            <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;background:#000">
                                <iframe id="edit_video_preview_frame"
                                    src="{{ video_embed_url($tour->video_url ?? '') }}"
                                    frameborder="0" allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:none"></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Facebook Post Embed</label>
                        <textarea name="facebook_post_url" id="edit_fb_url" class="form-control" rows="4"
                            placeholder='&lt;iframe src="https://www.facebook.com/plugins/post.php?..." ...&gt;&lt;/iframe&gt;'
                            oninput="updateFbEmbedPreview('edit_fb_url','edit_fb_preview')">{{ old('facebook_post_url', $tour->facebook_post_url) }}</textarea>
                        <small style="color:#6b7280">Paste the full Facebook embed code (from <a href="https://developers.facebook.com/docs/plugins/embedded-posts" target="_blank">Facebook Embedded Posts</a>).</small>
                        <div id="edit_fb_preview" class="admin-embed-preview" style="margin-top:10px;text-align:center;{{ $tour->facebook_post_url ? '' : 'display:none' }}">
                            <div id="edit_fb_preview_html">{!! $tour->facebook_post_url !!}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Summary</label>
                    <textarea name="summary" class="form-control" rows="4">{{ old('summary', $tour->summary) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Short Description <small class="text-muted">(max 500 chars)</small></label>
                    <textarea name="short_description" class="form-control" rows="2" maxlength="500">{{ old('short_description', $tour->short_description) }}</textarea>
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
                            value="{{ old('regular_price_per_person', $tour->regular_price_per_person) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Promo Price / Person (₱)</label>
                        <input type="number" name="promo_price_per_person" class="form-control"
                            value="{{ old('promo_price_per_person', $tour->promo_price_per_person) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Base Price / Day (₱)</label>
                        <input type="number" name="base_price_per_day" class="form-control"
                            value="{{ old('base_price_per_day', $tour->base_price_per_day) }}" step="0.01" min="0">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group d-flex align-items-center" style="padding-top:1.75rem">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                            <input type="checkbox" name="is_sale_enabled" value="1"
                                {{ old('is_sale_enabled', $tour->is_sale_enabled) ? 'checked' : '' }}>
                            Sale Currently Active
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Sale End Date</label>
                        <input type="date" name="sale_end_date" class="form-control"
                            value="{{ old('sale_end_date', $tour->sale_end_date?->format('Y-m-d')) }}">
                    </div>
                </div>

            </div>
        </div>

        <!-- ── TAB 3: TRAVEL DATES ────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-dates">
            <div class="card-header"><h4>Plan Dates</h4></div>
            <div class="card-body">

                <h5>Availability Window</h5>
                @php
                    $travelWindow = $tour->travel_window ?? [];
                @endphp
                <div class="form-row-2">
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="travel_window_start" class="form-control"
                            value="{{ old('travel_window_start', $travelWindow['start'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="travel_window_end" class="form-control"
                            value="{{ old('travel_window_end', $travelWindow['end'] ?? '') }}">
                    </div>
                </div>

                <hr>
                <h5>Specific Start Dates</h5>
                <div id="departureDatesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addDepartureDate()">
                    <i class="fas fa-plus"></i> Add Start Date
                </button>

            </div>
        </div>

        <!-- ── TAB 4: CONTENT & MEDIA ─────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-content">
            <div class="card-header"><h4>Content &amp; Media</h4></div>
            <div class="card-body">

                <div class="form-group">
                    <label>Main Image</label>
                    @if($tour->main_image)
                    <div class="existing-thumb-wrap">
                        <img src="{{ cdn_url($tour->main_image) }}" class="existing-thumb" alt="main">
                        <small class="text-muted">Current image</small>
                    </div>
                    @endif
                    <input type="file" name="main_image" class="form-control" accept="image/*">
                    <small class="text-muted">Upload a new file to replace the current one.</small>
                </div>

                <div class="form-group">
                    <label>Gallery Images</label>
                    @if(!empty($tour->gallery_images))
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem">
                        @foreach($tour->gallery_images as $gi)
                        <img src="{{ cdn_url($gi) }}" class="existing-thumb" alt="gallery">
                        @endforeach
                    </div>
                    @endif
                    <input type="file" name="gallery_image_files[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">Uploading new files will replace all current gallery images.</small>
                </div>

                <div class="form-group">
                    <label>Related Images</label>
                    @if(!empty($tour->related_images))
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem">
                        @foreach($tour->related_images as $ri)
                        <img src="{{ cdn_url($ri) }}" class="existing-thumb" alt="related">
                        @endforeach
                    </div>
                    @endif
                    <input type="file" name="related_image_files[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="form-group">
                    <label>Video File</label>
                    @if($tour->video_file)
                    <p class="text-muted" style="font-size:.85rem"><i class="fas fa-film"></i> {{ basename($tour->video_file) }}</p>
                    @endif
                    <input type="file" name="video_file" class="form-control" accept="video/*">
                </div>

                <div class="form-group">
                    <label>Highlights <small class="text-muted">(one per line)</small></label>
                    <textarea name="highlights" class="form-control" rows="6">{{ old('highlights', is_array($tour->highlights) ? implode("\n", $tour->highlights) : ($tour->highlights ?? '')) }}</textarea>
                </div>

            </div>
        </div>

        <!-- ── TAB 5: ITINERARY ───────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-itinerary">
            <div class="card-header"><h4>Features &amp; Deliverables</h4></div>
            <div class="card-body">
                <div id="itineraryContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addItineraryDay()">
                    <i class="fas fa-plus"></i> Add Day
                </button>
            </div>
        </div>

        <!-- ── TAB 6: STOPS & GEOGRAPHY ──────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-stops">
            <div class="card-header"><h4>Milestones &amp; Deliverables Map</h4></div>
            <div class="card-body">

                {{-- Mapbox Route Map --}}
                <div id="adminStopsMap"
                     class="stops-map-sticky"
                     style="height:420px;border-radius:10px;margin-bottom:1.5rem;background:#e8edf3;"
                     data-mapbox-token="{{ config('ai.mapbox_token') }}"></div>

                <h5>Milestones <small class="text-muted">(in order)</small></h5>
                <div class="itinerary-days-scroll">
                    <div id="fullStopsContainer"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="addFullStop()">
                    <i class="fas fa-plus"></i> Add Day
                </button>

                <hr class="mt-4">
                <h5>Additional Geographical Info</h5>
                @php $ai = $tour->additional_info ?? []; @endphp
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Starting Point</label>
                        <input type="text" name="ai_starting_point" class="form-control"
                            value="{{ old('ai_starting_point', $ai['starting_point'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Ending Point</label>
                        <input type="text" name="ai_ending_point" class="form-control"
                            value="{{ old('ai_ending_point', $ai['ending_point'] ?? '') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Countries Visited <small class="text-muted">(one per line)</small></label>
                    <textarea name="ai_countries_visited" class="form-control" rows="3">{{ old('ai_countries_visited', implode("\n", $ai['countries_visited'] ?? [])) }}</textarea>
                </div>

                <h6 class="mt-3">Main Cities by Country</h6>
                <div id="mainCitiesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addMainCitiesRow()">
                    <i class="fas fa-plus"></i> Add Country → Cities
                </button>

                <h6 class="mt-3">Featured Countries</h6>
                <div id="aiCountriesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addNameImagePair('aiCountriesContainer','ai_countries')">
                    <i class="fas fa-plus"></i> Add Country
                </button>

                <h6 class="mt-3">Featured Cities to Visit</h6>
                <div id="aiCitiesContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addNameImagePair('aiCitiesContainer','ai_cities_to_visit')">
                    <i class="fas fa-plus"></i> Add City
                </button>

            </div>
        </div>

        <!-- ── TAB 7: BOOKING ─────────────────────────────────────────── -->
        <div class="tour-tab-panel card mb-4" id="tab-booking">
            <div class="card-header"><h4>Enrollment</h4></div>
            <div class="card-body">

                <h5>Flipbook / Presentation Links</h5>
                <div id="bookingLinksContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addBookingLinkYear()">
                    <i class="fas fa-plus"></i> Add Year Group
                </button>

                <hr class="mt-4">
                <h5>Down Payment Settings</h5>
                <div class="form-group">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                        <input type="checkbox" name="allows_downpayment" value="1" id="allowsDownpayment"
                            {{ old('allows_downpayment', $tour->allows_downpayment) ? 'checked' : '' }}>
                        Allow booking with a down payment
                    </label>
                </div>

                <div class="form-row-2" id="downpaymentFields"
                    style="{{ old('allows_downpayment', $tour->allows_downpayment) ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label>Fixed Down Payment Amount (₱)</label>
                        <input type="number" name="fixed_downpayment_amount" class="form-control"
                            value="{{ old('fixed_downpayment_amount', $tour->fixed_downpayment_amount) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Balance Due (days before travel)</label>
                        <input type="number" name="balance_due_days_before_travel" class="form-control"
                            value="{{ old('balance_due_days_before_travel', $tour->balance_due_days_before_travel) }}" min="0">
                    </div>
                </div>

                <hr class="mt-4">
                <h5>Installment / Payment Terms</h5>
                <p class="text-muted" style="font-size:.85rem">Allow clients to pay in monthly installments (e.g. ₱16,000/month for 10 months). Set both fields to enable cash installment booking.</p>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Max Payment Terms (months, 1–15)</label>
                        <input type="number" name="installment_months" class="form-control"
                            value="{{ old('installment_months', $tour->installment_months) }}" min="1" max="15"
                            placeholder="e.g. 10">
                        <small class="text-muted">Leave blank to disable installment option.</small>
                    </div>
                    <div class="form-group">
                        <label>Monthly Installment Amount (₱)</label>
                        <input type="number" name="monthly_installment_amount" class="form-control"
                            value="{{ old('monthly_installment_amount', $tour->monthly_installment_amount) }}" step="0.01" min="0"
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

                <h5>Optional Add-ons</h5>
                <div id="optionalToursContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addOptionalTour()">
                    <i class="fas fa-plus"></i> Add Optional Add-on
                </button>

                <hr class="mt-4">
                <h5>Cash &amp; Freebies</h5>
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
                        <input type="checkbox" name="is_active" id="isActiveSide" value="1"
                            {{ old('is_active', $tour->is_active) ? 'checked' : '' }}>
                        Active (visible to public)
                    </label>
                </div>
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" name="is_featured" id="isFeaturedSide" value="1"
                            {{ old('is_featured', $tour->is_featured) ? 'checked' : '' }}>
                        Featured on Homepage
                    </label>
                </div>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-outline btn-block mt-2">Cancel</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h4>Actions</h4></div>
            <div class="card-body">
                <a href="{{ route('tours.show', $tour->slug) }}" target="_blank" class="btn btn-outline btn-block btn-sm">
                    <i class="fas fa-eye"></i> Preview Plan
                </a>
                <button type="button" class="btn btn-danger btn-block btn-sm mt-2"
                    onclick="if(confirm('Delete this tour? This cannot be undone.')) document.getElementById('deleteTourForm').submit()">
                    <i class="fas fa-trash"></i> Delete Plan
                </button>
            </div>
        </div>
    </div>

</div><!-- /form-layout -->

<!-- ── Sticky Save Bar ──────────────────────────────────── -->
<div id="tourStickyBar">
    <div class="sticky-checks">
        <label>
            <input type="checkbox" id="stickyActive" onchange="document.getElementById('isActiveSide').checked=this.checked" {{ old('is_active', $tour->is_active) ? 'checked' : '' }}>
            Active
        </label>
        <label>
            <input type="checkbox" id="stickyFeatured" onchange="document.getElementById('isFeaturedSide').checked=this.checked" {{ old('is_featured', $tour->is_featured) ? 'checked' : '' }}>
            Featured
        </label>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <a href="{{ route('admin.tours.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding:.625rem 1.75rem;font-size:1rem">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

</form>

{{-- Delete form is intentionally OUTSIDE the main form to prevent _method override --}}
<form id="deleteTourForm" action="{{ route('admin.tours.destroy', $tour) }}" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script src='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js'></script>
<script src="{{ global_asset('js/admin-tour-form.js') }}"></script>
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
    preview.innerHTML = '{{ url("/tours/") }}' + (slug || '...');
}
titleInput.addEventListener('input', updateSlugPreview);
slugInput.addEventListener('input', updateSlugPreview);

// Down payment toggle
const dpCheck = document.getElementById('allowsDownpayment');
if (dpCheck) dpCheck.addEventListener('change', function() {
    document.getElementById('downpaymentFields').style.display = this.checked ? '' : 'none';
});

// Sync sticky bar checkboxes with sidebar
(function(){
    var sa = document.getElementById('stickyActive');
    var sf = document.getElementById('stickyFeatured');
    var ia = document.getElementById('isActiveSide');
    var iff = document.getElementById('isFeaturedSide');
    if(sa && ia) ia.addEventListener('change', function(){ sa.checked = ia.checked; });
    if(sf && iff) iff.addEventListener('change', function(){ sf.checked = iff.checked; });
})();

// ── Pre-populate repeatable rows from existing data ───────────────────────
(function prePopulate() {
    // Departure dates
    @if(!empty($tour->departure_dates))
    @foreach($tour->departure_dates as $dd)
    addDepartureDate({
        start: @json($dd['start'] ?? ''),
        end:   @json($dd['end'] ?? ''),
        price: @json($dd['price'] ?? ''),
        maxCapacity: @json($dd['maxCapacity'] ?? ''),
        currentBookings: @json($dd['currentBookings'] ?? 0),
        isAvailable: @json($dd['isAvailable'] ?? true)
    });
    @endforeach
    @endif

    // Itinerary
    @if(!empty($tour->itinerary))
    @foreach($tour->itinerary as $day)
    addItineraryDay({
        title: @json($day['title'] ?? ''),
        description: @json($day['description'] ?? ''),
        image: @json($day['image'] ?? '')
    });
    @endforeach
    @endif

    // Full stops
    @if(!empty($tour->full_stops))
    @foreach($tour->full_stops as $stop)
    addFullStop({
        city: @json($stop['city'] ?? ''),
        country: @json($stop['country'] ?? ''),
        days: @json($stop['days'] ?? ''),
        day_title: @json($stop['day_title'] ?? ''),
        description: @json($stop['description'] ?? ''),
        optional_activity: @json($stop['optional_activity'] ?? ''),
        waypoints: @json($stop['waypoints'] ?? ''),
        travel_times: @json($stop['travel_times'] ?? ''),
        image: @json($stop['image'] ?? ''),
        images: @json($stop['images'] ?? [])
    });
    @endforeach
    @endif

    // Main cities
    @php $ai = $tour->additional_info ?? []; @endphp
    @if(!empty($ai['main_cities']))
    @foreach($ai['main_cities'] as $mc)
    addMainCitiesRow({
        country: @json($mc['country'] ?? ''),
        cities_text: @json(is_array($mc['cities'] ?? null) ? implode(', ', $mc['cities']) : ($mc['cities_text'] ?? ''))
    });
    @endforeach
    @endif

    @if(!empty($ai['countries']))
    @foreach($ai['countries'] as $c)
    addNameImagePair('aiCountriesContainer','ai_countries',{name:@json($c['name']??''),image:@json($c['image']??'')});
    @endforeach
    @endif

    @if(!empty($ai['cities_to_visit']))
    @foreach($ai['cities_to_visit'] as $c)
    addNameImagePair('aiCitiesContainer','ai_cities_to_visit',{name:@json($c['name']??''),image:@json($c['image']??'')});
    @endforeach
    @endif

    // Booking links
    @if(!empty($tour->booking_links))
    @foreach($tour->booking_links as $bl)
    addBookingLinkYear({
        year: @json($bl['year'] ?? date('Y')),
        urls: @json($bl['urls'] ?? [])
    });
    @endforeach
    @endif

    // Optional tours
    @if(!empty($tour->optional_tours))
    @foreach($tour->optional_tours as $ot)
    addOptionalTour({
        day: @json($ot['day'] ?? ''),
        title: @json($ot['title'] ?? ''),
        regularPrice: @json($ot['regularPrice'] ?? ''),
        promoType: @json($ot['promoType'] ?? ''),
        promoValue: @json($ot['promoValue'] ?? ''),
        flipbookUrl: @json($ot['flipbookUrl'] ?? '')
    });
    @endforeach
    @endif

    // Cash freebies
    @if(!empty($tour->cash_freebies))
    @foreach($tour->cash_freebies as $cf)
    addCashFreebie({
        label: @json($cf['label'] ?? ''),
        type:  @json($cf['type'] ?? 'cash'),
        value: @json($cf['value'] ?? '')
    });
    @endforeach
    @endif
})();
</script>
@endpush
