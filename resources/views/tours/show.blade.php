@extends('layouts.app')
@section('title', $tour->title)

@push('styles')
<link href='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css' rel='stylesheet' />
<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
<link href='https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&display=swap' rel='stylesheet'>
<style>
/* ── Tour Route Map ─────────────────────────────────────── */
/* ── Tour Media Embeds ──────────────────────────────────── */
.tour-media-embeds{display:grid;gap:24px}
.tour-media-block h4{font-size:1rem;font-weight:700;margin:0 0 12px;color:#1e293b}
.tour-video-wrap{position:relative;padding-bottom:56.25%;height:0;border-radius:12px;overflow:hidden;background:#000}
.tour-video-wrap iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:none}
.tour-fb-wrap{display:flex;justify-content:center;overflow:hidden;border-radius:12px}
.tour-fb-wrap iframe{max-width:100%;width:500px;min-height:450px;border:none}
@media(max-width:640px){.tour-fb-wrap iframe{width:100%;min-height:380px}}
#tourStopsMapWrap{margin:0 -2rem 2rem -2rem;border-radius:0;overflow:hidden;background:#0f2d5c}
#tab-destinations{overflow:visible}
.tour-tabs{overflow:visible}
.tour-stops-map-sticky{position:sticky;top:90px;z-index:10}
#tourStopsMapWrap .map-heading{padding:1.05rem 1.25rem .75rem;display:flex;align-items:center;justify-content:space-between;gap:.9rem;flex-wrap:wrap}
.map-heading-text{font-size:1.2rem;font-weight:800;color:#fff;letter-spacing:.02em}
.map-visual-toggle{display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.12);padding:.3rem;border-radius:999px}
.map-visual-btn{border:none;border-radius:999px;padding:.4rem .8rem;font-size:.78rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;transition:all .2s ease;color:rgba(255,255,255,.9);background:transparent}
.map-visual-btn.is-active{background:#fff;color:#0f2d5c;box-shadow:0 2px 6px rgba(0,0,0,.15)}
.map-visual-btn.is-hidden{display:none}
.map-visual-hint{font-size:.72rem;color:rgba(255,255,255,.78);font-weight:600;letter-spacing:.03em;white-space:nowrap}
.map-visual-hint.is-hidden{display:none}
#tourStopsMap{height:460px;width:100%;display:block;}
.tour-map-canvas-wrap{position:relative}
.tour-map-canvas-wrap #tourStopsMap{transition:opacity .35s ease}
.tour-map-canvas-wrap .mapboxgl-control-container{transition:opacity .25s ease}
.tour-map-canvas-wrap.map-photo-mode #tourStopsMap{opacity:0}
.tour-map-canvas-wrap.map-photo-mode .mapboxgl-control-container{opacity:0;pointer-events:none}
.tour-map-photo-overlay{position:absolute;inset:0;opacity:0;pointer-events:none;transition:opacity .35s ease;z-index:4;overflow:hidden;box-shadow:inset 0 0 90px 18px rgba(0,0,0,.55)}
.tour-map-photo-overlay img{width:100%;height:100%;object-fit:cover;filter:saturate(1.1) contrast(1.05) brightness(.92) drop-shadow(0 4px 24px rgba(0,0,0,.55));transform:scale(1);transition:transform .9s ease}
/* Vignette: soft black shadow ring around image edges */
.tour-map-photo-overlay::before{content:'';position:absolute;inset:0;z-index:6;background:radial-gradient(ellipse at center,transparent 42%,rgba(0,0,0,.62) 100%);pointer-events:none}
.tour-map-photo-overlay::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,45,92,.62) 0%,rgba(15,45,92,.44) 38%,rgba(15,45,92,.5) 100%)}
.tour-map-photo-overlay.is-visible{opacity:.74}
.tour-map-canvas-wrap.map-photo-mode .tour-map-photo-overlay.is-visible{opacity:1}
.tour-map-canvas-wrap.map-photo-mode .tour-map-photo-overlay::after{background:linear-gradient(180deg,rgba(15,45,92,.42) 0%,rgba(15,45,92,.12) 55%,rgba(15,45,92,.22) 100%)}
.tour-map-photo-overlay.is-animating img{transform:scale(1.06)}
.tour-map-photo-caption{position:absolute;left:1rem;top:1rem;z-index:7;background:rgba(15,45,92,.86);color:#fff;padding:.55rem .95rem;border-radius:8px;font-size:1rem;font-weight:800;font-family:'Poppins','Segoe UI',sans-serif;letter-spacing:.04em;text-transform:uppercase;max-width:calc(100% - 2rem);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;box-shadow:0 8px 20px rgba(0,0,0,.25)}
.tour-map-photo-caption{position:absolute;left:1rem;top:1rem;z-index:7;background:rgba(15,45,92,.86);color:#fff;padding:.55rem .95rem;border-radius:8px;font-size:1rem;font-weight:800;font-family:'Poppins','Segoe UI',sans-serif;letter-spacing:.04em;text-transform:uppercase;max-width:calc(100% - 5.5rem);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;box-shadow:0 8px 20px rgba(0,0,0,.25)}
/* ── Overlay slideshow nav ──────────────────────────────── */
.overlay-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:7;background:rgba(0,0,0,.46);color:#fff;border:none;border-radius:50%;width:38px;height:38px;font-size:1.6rem;line-height:1;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s,transform .2s;opacity:0;pointer-events:none;padding:0;font-family:inherit}
.overlay-nav:hover{background:rgba(0,0,0,.7);transform:translateY(-50%) scale(1.12)}
.overlay-nav-prev{left:.75rem}
.overlay-nav-next{right:.75rem}
.tour-map-photo-overlay.is-visible .overlay-nav:not(.is-hidden){opacity:1;pointer-events:auto}
.overlay-dots{position:absolute;bottom:.7rem;left:50%;transform:translateX(-50%);z-index:7;display:flex;gap:.35rem;align-items:center}
.overlay-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.48);cursor:pointer;transition:all .2s;border:none;padding:0}
.overlay-dot.is-active{background:#fff;transform:scale(1.3)}
.overlay-img-counter{position:absolute;top:1rem;right:1rem;z-index:7;background:rgba(0,0,0,.5);color:#fff;font-size:.72rem;font-weight:700;padding:.25rem .55rem;border-radius:20px;letter-spacing:.04em}
.overlay-img-counter.is-hidden{display:none}

/* ── Itinerary heading ──────────────────────────────────── */
.itin-section-title{font-size:1rem;font-weight:700;color:#374151;letter-spacing:.05em;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem}
.itin-section-title span{font-weight:400;font-size:.875rem;color:#9ca3af}

/* ── City group header ──────────────────────────────────── */
.itin-city-header{background:linear-gradient(135deg,#0f2d5c 0%,#1e4fa3 60%,#2563eb 100%);border-radius:10px 10px 0 0;padding:.875rem 1.25rem;display:flex;align-items:center;gap:.75rem;margin-top:1.75rem}
.itin-city-header:first-child{margin-top:0}
.itin-city-name{font-size:1rem;font-weight:800;color:#fff;letter-spacing:.12em;text-transform:uppercase;flex:1}
.itin-city-country{font-size:.8rem;color:rgba(255,255,255,.75);font-weight:500}
.itin-nights-badge{background:rgba(255,255,255,.18);color:#fff;font-size:.75rem;font-weight:700;padding:.2rem .65rem;border-radius:20px;white-space:nowrap;border:1px solid rgba(255,255,255,.3)}

/* ── Day card ───────────────────────────────────────────── */
.itin-day-card{display:flex;gap:1rem;padding:1.25rem 1.25rem 1.25rem 1rem;background:#fff;border:1px solid #e5e7eb;border-top:none;transition:background .15s}
.itin-day-card:last-child{border-radius:0 0 10px 10px}
.itin-day-card:hover{background:#f9fafb}
.itin-day-num-wrap{flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:.25rem;padding-top:.1rem}
.itin-day-num{width:36px;height:36px;border-radius:50%;background:#1e4fa3;color:#fff;font-size:.8rem;font-weight:800;display:flex;align-items:center;justify-content:center;letter-spacing:.04em}
.itin-day-body{flex:1;min-width:0}
.itin-day-title{font-size:.9375rem;font-weight:700;color:#111827;margin:0 0 .5rem;line-height:1.4;text-transform:uppercase;letter-spacing:.04em}
.itin-day-desc{font-size:.9rem;color:#4b5563;line-height:1.7;margin:0 0 .75rem;white-space:pre-line}
.itin-optional{background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;padding:.5rem .875rem;font-size:.875rem;color:#92400e;display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.75rem}
.itin-optional i{color:#d97706;flex-shrink:0;margin-top:.15rem}
.itin-travel-times{display:flex;flex-direction:column;gap:.35rem}
.itin-travel-time-line{font-size:.8125rem;color:#6b7280;display:flex;align-items:center;gap:.4rem}
.itin-travel-time-line i{color:#9ca3af;font-size:.75rem}

/* ── Route tab map heading ──────────────────────────────── */
.route-map-heading{font-size:1.3rem;font-weight:800;color:#111827;margin-bottom:1.25rem}/* ── Active day card ────────────────────────────────────────── */
.itin-day-card--active{background:#eff6ff!important;border-left:3px solid #2563eb!important}
.itin-day-card--active .itin-day-num{background:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.2)}
/* ── Waypoints strip ──────────────────────────────────────── */
.itin-day-waypoints{display:flex;flex-wrap:wrap;align-items:center;gap:.3rem;margin:.5rem 0 .6rem}
.itin-day-waypoint{background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:20px;padding:.15rem .65rem;font-size:.78rem;font-weight:700}
.itin-day-waypoints .wp-arrow{color:#d97706;font-size:.65rem;flex-shrink:0}
.wp-dist-badge{font-size:.7rem;color:#b45309;font-weight:600;white-space:nowrap}
@media(max-width:640px){
    .tour-stops-map-sticky{position:static}
    .map-heading-text{font-size:1rem}
    .map-visual-btn{font-size:.7rem;padding:.35rem .65rem}
    .map-visual-hint{font-size:.66rem}
    .tour-map-photo-caption{font-size:.8rem;top:.7rem;left:.7rem;max-width:calc(100% - 1.4rem)}
    #tourStopsMap{height:280px}
    .itin-city-name{font-size:.875rem}
}
@media(max-width:480px){
    #tourStopsMapWrap{margin:0 -1.25rem 2rem -1.25rem}
    .itin-city-header{padding:.75rem 1rem}
    .itin-day-card{padding:.875rem}
    .tour-map-photo-caption{padding:.4rem .65rem;font-size:.75rem}
}
@media(max-width:375px){
    #tourStopsMap{height:250px}
    .map-heading{flex-direction:column;align-items:flex-start;gap:.4rem}
    .map-heading-text{font-size:.875rem;line-height:1.3}
    .itin-day-num{width:28px;height:28px;font-size:.68rem}
    .itin-day-card{gap:.5rem}
    .itin-nights-badge{font-size:.68rem}
}
</style>
@endpush

@section('content')
<!-- Tour Hero -->
<div class="tour-hero">
    <div class="tour-hero-img" style="background-image: url('{{ cdn_url($tour->main_image, asset('images/placeholder-tour.jpg')) }}')">
        <div class="tour-hero-overlay"></div>
    </div>
    <div class="container">
        <div class="tour-hero-content">
            <div class="breadcrumb-nav">
                <a href="{{ route('home') }}">Home</a> /
                <a href="{{ route('tours.index') }}">Tours</a> /
                <a href="{{ route('tours.index', ['continent' => $tour->continent]) }}">{{ $tour->continent }}</a> /
                <span>{{ $tour->title }}</span>
            </div>
            <h1>{{ $tour->title }}</h1>
            <div class="tour-hero-meta">
                <span><i class="fas fa-map-marker-alt"></i> {{ $tour->line }} &mdash; {{ $tour->continent }}</span>
                <span><i class="fas fa-clock"></i> {{ $tour->duration_days }} Days</span>
                @if($tour->guaranteed_departure)
                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Guaranteed Departure</span>
                @endif
                @if($tour->average_rating > 0)
                    <span><i class="fas fa-star text-yellow"></i> {{ number_format($tour->average_rating, 1) }} ({{ $tour->total_reviews }} reviews)</span>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="tour-detail-layout">

            <!-- Main Content -->
            <div class="tour-detail-main">

                <!-- Image Gallery -->
                @php $galleryImages = $tour->gallery_images ?? []; @endphp
                @if(count($galleryImages) > 0)
                    <div class="tour-gallery">
                        <div class="gallery-main" id="galleryMain">
                            <img src="{{ cdn_url($galleryImages[0]) }}"
                                 alt="{{ $tour->title }}" id="mainGalleryImg">
                        </div>
                        <div class="gallery-thumbs" id="galleryThumbs">
                            @foreach($galleryImages as $i => $imgPath)
                                <img src="{{ cdn_url($imgPath) }}"
                                     alt="{{ $tour->title }}"
                                     class="gallery-thumb {{ $i === 0 ? 'active' : '' }}"
                                     onclick="changeGalleryImage(this)"
                                     loading="lazy">
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Tab Navigation -->
                <div class="tour-tabs">
                    <nav class="tab-nav">
                        <button class="tab-btn active" data-tab="overview">Overview</button>
                        @if($tour->full_stops && count($tour->full_stops) > 0)
                            <button class="tab-btn" data-tab="destinations"><i class="fas fa-route"></i> Destinations</button>
                        @endif
                        @if($tour->itinerary && count($tour->itinerary) > 0)
                            <button class="tab-btn" data-tab="itinerary">Itinerary</button>
                        @endif
                        @if(($tour->optional_tours && count($tour->optional_tours) > 0) || ($tour->cash_freebies && count($tour->cash_freebies) > 0))
                            <button class="tab-btn" data-tab="extras">Extras</button>
                        @endif
                        <button class="tab-btn" data-tab="reviews">
                            Reviews ({{ $tour->total_reviews }})
                        </button>
                    </nav>

                    <!-- Overview Tab -->
                    <div class="tab-content active" id="tab-overview">
                        <h3>About This Tour</h3>
                        <div class="tour-description">
                            {!! nl2br(e($tour->summary)) !!}
                        </div>

                        @if($tour->highlights && count($tour->highlights) > 0)
                            <h4 class="mt-4"><i class="fas fa-check-circle text-green"></i> Tour Highlights</h4>
                            <ul class="highlights-list">
                                @foreach($tour->highlights as $highlight)
                                    <li><i class="fas fa-star text-yellow"></i> {{ $highlight }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @php
                            $tourVideoEmbed = video_embed_url($tour->video_url ?? '');
                            $tourFbEmbed    = $tour->facebook_post_url ?? '';
                        @endphp
                        @if($tourVideoEmbed || $tourFbEmbed)
                        <div class="tour-media-embeds mt-4">
                            @if($tourVideoEmbed)
                            <div class="tour-media-block">
                                <h4><i class="fas fa-film"></i> Tour Video</h4>
                                <div class="tour-video-wrap">
                                    <iframe src="{{ $tourVideoEmbed }}" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            </div>
                            @endif
                            @if($tourFbEmbed)
                            <div class="tour-media-block">
                                <h4><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook Post</h4>
                                <div class="tour-fb-wrap">
                                    {!! $tourFbEmbed !!}
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="tour-info-grid mt-4">
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <strong>Tour Line</strong>
                                <span>{{ $tour->line }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-globe"></i>
                                <strong>Continent</strong>
                                <span>{{ $tour->continent }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <strong>Duration</strong>
                                <span>{{ $tour->duration_days }} Days</span>
                            </div>
                            @if($tour->travel_window)
                                <div class="info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong>Travel Window</strong>
                                    <span>
                                        {{ \Carbon\Carbon::parse($tour->travel_window['start'])->format('M Y') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($tour->travel_window['end'])->format('M Y') }}
                                    </span>
                                </div>
                            @endif
                            @if($tour->additional_info)
                                @if(!empty($tour->additional_info['startingPoint']))
                                    <div class="info-item">
                                        <i class="fas fa-plane-departure"></i>
                                        <strong>Starts At</strong>
                                        <span>{{ $tour->additional_info['startingPoint'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($tour->additional_info['endingPoint']))
                                    <div class="info-item">
                                        <i class="fas fa-plane-arrival"></i>
                                        <strong>Ends At</strong>
                                        <span>{{ $tour->additional_info['endingPoint'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($tour->additional_info['countriesVisited']) && count($tour->additional_info['countriesVisited']) > 0)
                                    <div class="info-item">
                                        <i class="fas fa-flag"></i>
                                        <strong>Countries</strong>
                                        <span>{{ implode(', ', $tour->additional_info['countriesVisited']) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Destinations / Full Stops Tab -->
                    @if($tour->full_stops && count($tour->full_stops) > 0)
                    <div class="tab-content" id="tab-destinations">
                        {{-- Mapbox route map --}}
                        <div id="tourStopsMapWrap" class="tour-stops-map-sticky">
                            <div class="map-heading">
                                <div class="map-heading-text">Route Map &amp; Day-by-Day Itinerary</div>
                                <div class="map-visual-toggle" aria-label="Map visual mode switcher">
                                    <button type="button" class="map-visual-btn is-active" id="mapVisualMapBtn" onclick="setMapVisualMode('map')">Map</button>
                                    <button type="button" class="map-visual-btn is-hidden" id="mapVisualPhotoBtn" onclick="setMapVisualMode('photo')">Place Image</button>
                                </div>
                                <span id="mapVisualHint" class="map-visual-hint">Select a day to enable Place Image</span>
                            </div>
                            <div class="tour-map-canvas-wrap">
                                <div id="tourStopsMap"
                                     data-mapbox-token="{{ config('ai.mapbox_token') }}"
                                     data-stops="{{ json_encode($tour->full_stops) }}"></div>
                                <div id="tourMapPhotoOverlay" class="tour-map-photo-overlay" aria-hidden="true">
                                    <img id="tourMapPhotoOverlayImg" src="" alt="Place photo overlay">
                                    <button id="tourMapPrevBtn" class="overlay-nav overlay-nav-prev is-hidden" onclick="overlayPrev()" aria-label="Previous image">&#8249;</button>
                                    <button id="tourMapNextBtn" class="overlay-nav overlay-nav-next is-hidden" onclick="overlayNext()" aria-label="Next image">&#8250;</button>
                                    <div id="tourMapPhotoDots" class="overlay-dots"></div>
                                    <span id="tourMapImgCounter" class="overlay-img-counter is-hidden"></span>
                                    <div id="tourMapPhotoOverlayTitle" class="tour-map-photo-caption"></div>
                                </div>
                            </div>
                        </div>

                        <div class="itin-section-title">
                            Itinerary Days <span>(in travel order)</span>
                        </div>

                        @php
                            /* Group consecutive same-city stops */
                            $itinGroups = [];
                            $mapDefaultImage = '';
                            if (!empty($tour->gallery_images[0])) {
                                $mapDefaultImage = cdn_url($tour->gallery_images[0]);
                            } elseif (!empty($tour->main_image)) {
                                $mapDefaultImage = cdn_url($tour->main_image);
                            }

                            foreach ($tour->full_stops as $stop) {
                                $city = $stop['city'] ?? '';
                                if (empty($itinGroups) || end($itinGroups)['city'] !== $city) {
                                    $itinGroups[] = ['city' => $city, 'country' => $stop['country'] ?? '', 'stops' => []];
                                }
                                $itinGroups[count($itinGroups)-1]['stops'][] = $stop;
                            }
                            $globalDay = 0;
                        @endphp

                        @foreach($itinGroups as $group)
                            @php $groupNights = array_sum(array_column($group['stops'], 'days')); @endphp
                            @php
                                $groupImages = [];
                                foreach ($group['stops'] as $groupStop) {
                                    if (!empty($groupStop['images']) && is_array($groupStop['images'])) {
                                        foreach ($groupStop['images'] as $gImg) { if ($gImg) $groupImages[] = $gImg; }
                                    } elseif (!empty($groupStop['image'])) {
                                        $groupImages[] = $groupStop['image'];
                                    }
                                }
                                if (empty($groupImages) && $mapDefaultImage) { $groupImages = [$mapDefaultImage]; }
                            @endphp
                            <div class="itin-city-header"
                                 data-city="{{ strtolower($group['city']) }}"
                                 data-images="{{ json_encode($groupImages) }}"
                                 data-place-title="{{ $group['city'] }}"
                                 onclick="flyToCityHeader(this)"
                                 style="cursor:pointer">
                                <span class="itin-city-name">{{ $group['city'] }}</span>
                                @if($group['country'])
                                    <span class="itin-city-country">{{ $group['country'] }}</span>
                                @endif
                                @if($groupNights > 0)
                                    <span class="itin-nights-badge">{{ $groupNights }} {{ Str::plural('night', $groupNights) }}</span>
                                @endif
                            </div>

                            @foreach($group['stops'] as $stop)
                                @php $globalDay++; @endphp
                                @php
                                    $dayImages = [];
                                    if (!empty($stop['images']) && is_array($stop['images'])) {
                                        $dayImages = array_values(array_filter($stop['images']));
                                    } elseif (!empty($stop['image'])) {
                                        $dayImages = [$stop['image']];
                                    }
                                    if (empty($dayImages)) {
                                        $dayImages = !empty($groupImages) ? $groupImages : ($mapDefaultImage ? [$mapDefaultImage] : []);
                                    }
                                @endphp
                                <div class="itin-day-card"
                                     data-city="{{ strtolower($stop['city'] ?? '') }}"
                                     data-waypoints="{{ $stop['waypoints'] ?? '' }}"
                                     data-images="{{ json_encode($dayImages) }}"
                                     data-place-title="{{ $stop['day_title'] ?? ($stop['city'] ?? '') }}"
                                     onclick="flyToCity(this)"
                                     style="cursor:pointer">
                                    <div class="itin-day-num-wrap">
                                        <div class="itin-day-num">{{ str_pad($globalDay, 2, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                    <div class="itin-day-body">
                                        @if(!empty($stop['day_title']))
                                            <h4 class="itin-day-title">{{ $stop['day_title'] }}</h4>
                                        @endif
                                        @if(!empty($stop['waypoints']))
                                            @php $wps = array_filter(array_map('trim', explode(',', $stop['waypoints']))); @endphp
                                            @if(count($wps) > 1)
                                                <div class="itin-day-waypoints">
                                                    @foreach(array_values($wps) as $wi => $wp)
                                                        @if($wi > 0)<i class="fas fa-chevron-right wp-arrow"></i>@endif
                                                        <span class="itin-day-waypoint">{{ $wp }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                        @if(!empty($stop['description']))
                                            <p class="itin-day-desc">{{ $stop['description'] }}</p>
                                        @endif
                                        @if(!empty($stop['optional_activity']))
                                            <div class="itin-optional">
                                                <i class="fas fa-star"></i>
                                                <span>{{ $stop['optional_activity'] }}</span>
                                            </div>
                                        @endif
                                        @if(!empty($stop['travel_times']))
                                            <div class="itin-travel-times">
                                                @foreach(explode("\n", $stop['travel_times']) as $tline)
                                                    @if(trim($tline))
                                                        <span class="itin-travel-time-line">
                                                            <i class="fas fa-route"></i> {{ trim($tline) }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                    @endif

                    <!-- Itinerary Tab -->
                    @if($tour->itinerary && count($tour->itinerary) > 0)
                    <div class="tab-content" id="tab-itinerary">
                        <div class="itinerary-list">
                            @foreach($tour->itinerary as $i => $day)
                                <div class="itinerary-day">
                                    <div class="itinerary-day-num">Day {{ $day['day'] ?? ($i + 1) }}</div>
                                    <div class="itinerary-day-content">
                                        <h4>{{ $day['title'] }}</h4>
                                        <p>{{ $day['description'] }}</p>
                                        @if(!empty($day['accommodation']))
                                            <span class="itinerary-chip">
                                                <i class="fas fa-bed"></i> {{ $day['accommodation'] }}
                                            </span>
                                        @endif
                                        @if(!empty($day['meals']) && is_array($day['meals']))
                                            <span class="itinerary-chip">
                                                <i class="fas fa-utensils"></i> {{ implode(', ', $day['meals']) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Extras Tab -->
                    @if(($tour->optional_tours && count($tour->optional_tours) > 0) || ($tour->cash_freebies && count($tour->cash_freebies) > 0))
                    <div class="tab-content" id="tab-extras">
                        @if($tour->cash_freebies && count($tour->cash_freebies) > 0)
                            <h4 class="text-green"><i class="fas fa-gift"></i> Freebies & Inclusions</h4>
                            <ul class="includes-list includes-list--yes mb-4">
                                @foreach($tour->cash_freebies as $freebie)
                                    <li><i class="fas fa-check"></i> {{ $freebie['label'] }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if($tour->optional_tours && count($tour->optional_tours) > 0)
                            <h4 class="mt-3"><i class="fas fa-plus-circle"></i> Optional Add-ons</h4>
                            <div class="optional-tours-list">
                                @foreach($tour->optional_tours as $opt)
                                    <div class="optional-tour-item">
                                        <strong>{{ $opt['name'] ?? $opt['title'] ?? 'Add-on' }}</strong>
                                        @if(!empty($opt['price']))
                                            <span class="optional-tour-price">+₱{{ number_format($opt['price'], 2) }}</span>
                                        @endif
                                        @if(!empty($opt['description']))
                                            <p class="text-muted small">{{ $opt['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Reviews Tab -->
                    <div class="tab-content" id="tab-reviews">
                        @if($tour->reviews->count() > 0)
                            <div class="reviews-summary">
                                <div class="rating-big">
                                    <span class="rating-num">{{ number_format($tour->average_rating, 1) }}</span>
                                    <div class="rating-stars-big">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $tour->average_rating ? 'text-yellow' : 'text-gray' }}"></i>
                                        @endfor
                                    </div>
                                    <span>{{ $tour->total_reviews }} reviews</span>
                                </div>
                            </div>

                            <div class="reviews-list">
                                @foreach($tour->reviews as $review)
                                    <div class="review-item">
                                        <div class="review-item-header">
                                            <div class="reviewer-avatar-sm">
                                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $review->user->name }}</strong>
                                                <div class="review-stars-sm">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow' : 'text-gray' }}"></i>
                                                    @endfor
                                                </div>
                                                <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                        <h5 class="review-title">{{ $review->title }}</h5>
                                        <p>{{ $review->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-star fa-2x text-muted"></i>
                                <p>No reviews yet. Be the first to review this tour!</p>
                            </div>
                        @endif

                        <!-- Write Review -->
                        @auth
                            <div class="write-review mt-4">
                                <h4>Write a Review</h4>
                                <form action="{{ route('reviews.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                                    <div class="form-group">
                                        <label>Your Rating</label>
                                        <div class="star-rating-input" id="starRating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star" data-value="{{ $i }}"></i>
                                            @endfor
                                            <input type="hidden" name="rating" id="ratingInput" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Summarize your experience" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Your Review</label>
                                        <textarea name="body" class="form-control" rows="4"
                                            placeholder="Tell others about your experience (min 20 characters)..." required minlength="20"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit Review
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="mt-3"><a href="{{ route('login') }}">Login</a> to write a review.</p>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <aside class="tour-booking-sidebar">
                <div class="booking-widget">
                    <div class="booking-widget-header">
                        <div class="booking-price">
                            @if($tour->promo_price_per_person)
                                <span class="price-original">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
                                <span class="price-current large">₱{{ number_format($tour->promo_price_per_person, 2) }}</span>
                            @else
                                <span class="price-current large">₱{{ number_format($tour->regular_price_per_person, 2) }}</span>
                            @endif
                            <small>per person</small>
                        </div>
                        @if($tour->discount_percent > 0)
                            <span class="discount-badge">{{ $tour->discount_percent }}% OFF</span>
                        @endif
                    </div>

                    @if($tour->allows_downpayment && $tour->fixed_downpayment_amount)
                        <div class="downpayment-note">
                            <i class="fas fa-info-circle"></i>
                            Reserve with ₱{{ number_format($tour->fixed_downpayment_amount, 2) }} downpayment
                            @if($tour->balance_due_days_before_travel)
                                &mdash; balance due {{ $tour->balance_due_days_before_travel }} days before travel
                            @endif
                        </div>
                    @endif

                    <!-- Departure Dates -->
                    @if($tour->schedules->isNotEmpty())
                        {{-- Live data from tour_schedules (slot tracker / imports) --}}
                        <div class="departure-dates-list" id="departureDatesList" data-tour-slug="{{ $tour->slug }}">
                            <h5><i class="fas fa-calendar"></i> Available Departures <span class="live-dot" id="depLiveDot" title="Updates automatically"></span></h5>
                            @foreach($tour->schedules as $sched)
                                @php
                                    $remaining = $sched->available_seats - $sched->booked_seats;
                                    $isFull    = $remaining <= 0 || $sched->status === 'sold_out';
                                    $bookUrl   = auth()->check()
                                        ? route('booking.create', ['tour_id' => $tour->id, 'schedule_id' => $sched->id, 'departure_date' => $sched->departure_date->format('Y-m-d')])
                                        : route('login');
                                @endphp
                                @if($isFull)
                                    <div class="departure-date-row departure-date-row--full" data-start="{{ $sched->departure_date->format('Y-m-d') }}">
                                @else
                                    <a href="{{ $bookUrl }}" class="departure-date-row departure-date-row--available" data-start="{{ $sched->departure_date->format('Y-m-d') }}" title="Click to book this date">
                                @endif
                                    <span class="dep-date-label">
                                        {{ $sched->departure_date->format('M d') }}
                                        &ndash;
                                        {{ ($sched->return_date ?? $sched->departure_date)->format('M d, Y') }}
                                    </span>
                                    @if($isFull)
                                        <span class="seats-badge seats-full">FULL</span>
                                    @elseif($remaining <= 5)
                                        <span class="seats-badge seats-low">{{ $remaining }} slot{{ $remaining == 1 ? '' : 's' }} left</span>
                                    @else
                                        <span class="seats-badge seats-open">{{ $remaining }} slots open</span>
                                    @endif
                                @if($isFull)
                                    </div>
                                @else
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                    @php $dates = $tour->departure_dates ?? []; @endphp
                    @if(count($dates) > 0)
                        <div class="departure-dates-list" id="departureDatesList" data-tour-slug="{{ $tour->slug }}">
                            <h5><i class="fas fa-calendar"></i> Available Departures <span class="live-dot" id="depLiveDot" title="Updates automatically"></span></h5>
                            @foreach($dates as $date)
                                @php
                                    $maxCap    = isset($date['maxCapacity']) && $date['maxCapacity'] !== '' ? (int) $date['maxCapacity'] : null;
                                    $booked    = (int) ($date['currentBookings'] ?? 0);
                                    $remaining = $maxCap !== null ? $maxCap - $booked : null;
                                    $isFull    = ($date['isAvailable'] ?? true) === false
                                                 || ($remaining !== null && $remaining <= 0);
                                    $bookUrl   = auth()->check()
                                        ? route('booking.create', array_filter(['tour_id' => $tour->id, 'departure_date' => $date['start']]))
                                        : route('login');
                                @endphp
                                @if($isFull)
                                    <div class="departure-date-row departure-date-row--full" data-start="{{ $date['start'] }}">
                                @else
                                    <a href="{{ $bookUrl }}" class="departure-date-row departure-date-row--available" data-start="{{ $date['start'] }}" title="Click to book this date">
                                @endif
                                    <span class="dep-date-label">
                                        {{ \Carbon\Carbon::parse($date['start'])->format('M d') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($date['end'])->format('M d, Y') }}
                                    </span>
                                    @if($isFull)
                                        <span class="seats-badge seats-full">FULL</span>
                                    @elseif($remaining !== null && $remaining <= 5)
                                        <span class="seats-badge seats-low">{{ $remaining }} slot{{ $remaining == 1 ? '' : 's' }} left</span>
                                    @elseif($remaining !== null)
                                        <span class="seats-badge seats-open">{{ $remaining }} slots open</span>
                                    @else
                                        <span class="seats-badge seats-open">Available</span>
                                    @endif
                                @if($isFull)
                                    </div>
                                @else
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @endif

                    <!-- Book Now Button (internal) -->
                    @php
                        $allDeparturesFull = $tour->schedules->isNotEmpty() && $tour->schedules->every(fn($s) => ($s->available_seats - $s->booked_seats) <= 0 || $s->status === 'sold_out');
                    @endphp
                    @if($allDeparturesFull)
                        <button class="btn btn-block btn-lg mt-3" disabled
                                style="background:#dc2626;color:#fff;font-weight:700;cursor:not-allowed;opacity:1;border:none">
                            <i class="fas fa-ban"></i> Fully Booked
                        </button>
                    @elseif(auth()->check())
                        <a href="{{ route('booking.create', ['tour_id' => $tour->id]) }}"
                           class="btn btn-primary btn-block btn-lg mt-3">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg mt-3">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    @endif

                    <!-- External Booking / Flipbook Links -->
                    @php
                        $bookingLinks = $tour->booking_links ?? [];
                        // Group back by year for display, support both formats
                        $linksByYear = [];
                        foreach ($bookingLinks as $link) {
                            $year = $link['year'] ?? null;
                            $urls = isset($link['urls']) && is_array($link['urls'])
                                ? $link['urls']
                                : (isset($link['url']) ? [$link['url']] : []);
                            if ($urls) {
                                $linksByYear[$year ?? 'Other'][] = $urls;
                                // flatten
                                $linksByYear[$year ?? 'Other'] = array_merge(...$linksByYear[$year ?? 'Other']);
                            }
                        }
                    @endphp
                    @if(count($linksByYear) > 0)
                        <div class="online-booking-links mt-3">
                            <p style="font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;margin-bottom:.5rem">
                                Tour Brochure / Flipbook
                            </p>
                            @foreach($linksByYear as $year => $urls)
                                @foreach($urls as $idx => $url)
                                    @if(!empty($url))
                                        @php $suffix = count($urls) > 1 ? ' ' . chr(65 + $idx) : ''; @endphp
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                           class="btn btn-outline btn-block mb-2" style="font-size:.875rem">
                                            <i class="fas fa-book-open"></i>
                                            View {{ $year }} Flipbook{{ $suffix }}
                                        </a>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    @endif

                    <!-- Wishlist Toggle -->
                    @auth
                    <button type="button"
                            class="btn btn-outline btn-block mt-2 wishlist-toggle-btn {{ $isWishlisted ? 'wishlist-active' : '' }}"
                            data-tour="{{ $tour->id }}"
                            data-url="{{ route('tours.wishlist.toggle', $tour) }}">
                        <i class="fas fa-heart"></i>
                        <span>{{ $isWishlisted ? 'In Wishlist' : 'Add to Wishlist' }}</span>
                    </button>
                    @endauth

                    <div class="booking-widget-footer">
                        <i class="fas fa-shield-alt"></i> Secure booking. Free cancellation 48h before.
                    </div>
                </div>
            </aside>
        </div>

        <!-- Related Tours -->
        @if($relatedTours->count() > 0)
            <div class="related-tours mt-5">
                <h3>You Might Also Like</h3>
                <div class="tours-grid tours-grid--4">
                    @foreach($relatedTours as $relatedTour)
                        @include('partials.tour-card', ['tour' => $relatedTour])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js'></script>
<script>
// ── Tour Route Map — lazy init (tab is hidden on load) ───────────────────
let _tourMapInstance = null;
let _tourMapInited = false;
const _tourCityMarkers = {}; // keyed by lowercase city name
let _dayRouteMarkers = [];
let _mapVisualMode = 'map';
let _overlayState = { images: [], captions: [], title: '', currentIndex: 0 };
let _hasSelectedItineraryDay = false;
let _overlayAutoTimer = null;

function initTourStopsMap() {
    if (_tourMapInited) { if (_tourMapInstance) _tourMapInstance.resize(); return; }
    const mapEl = document.getElementById('tourStopsMap');
    if (!mapEl) return;
    const token = mapEl.dataset.mapboxToken;
    if (!token || typeof mapboxgl === 'undefined') return;

    const stops = JSON.parse(mapEl.dataset.stops || '[]');
    if (!stops.length) return;
    _tourMapInited = true;

    // Known city coords fallback (same as admin form)
    const CITY_COORDS = {
        'manila':{lat:14.5995,lng:120.9842},'paris':{lat:48.8566,lng:2.3522},
        'zurich':{lat:47.3769,lng:8.5417},'milan':{lat:45.4642,lng:9.19},
        'florence':{lat:43.7696,lng:11.2558},'rome':{lat:41.9028,lng:12.4964},
        'london':{lat:51.5074,lng:-0.1278},'barcelona':{lat:41.3851,lng:2.1734},
        'madrid':{lat:40.4168,lng:-3.7038},'amsterdam':{lat:52.3676,lng:4.9041},
        'berlin':{lat:52.52,lng:13.405},'prague':{lat:50.0755,lng:14.4378},
        'vienna':{lat:48.2082,lng:16.3738},'budapest':{lat:47.4979,lng:19.0402},
        'athens':{lat:37.9838,lng:23.7275},'istanbul':{lat:41.0082,lng:28.9784},
        'dubai':{lat:25.2048,lng:55.2708},'tokyo':{lat:35.6762,lng:139.6503},
        'osaka':{lat:34.6937,lng:135.5023},'kyoto':{lat:35.0116,lng:135.7681},
        'singapore':{lat:1.3521,lng:103.8198},'bangkok':{lat:13.7563,lng:100.5018},
        'new york':{lat:40.7128,lng:-74.006},'los angeles':{lat:34.0522,lng:-118.2437},
        'sydney':{lat:-33.8688,lng:151.2093},'melbourne':{lat:-37.8136,lng:144.9631},
        'toronto':{lat:43.6532,lng:-79.3832},'vancouver':{lat:49.2827,lng:-123.1207},
        'lisbon':{lat:38.7169,lng:-9.1399},'brussels':{lat:50.8503,lng:4.3517},
        'geneva':{lat:46.2044,lng:6.1432},'nice':{lat:43.7102,lng:7.262},
        'venice':{lat:45.4408,lng:12.3155},'naples':{lat:40.8518,lng:14.2681},
        'munich':{lat:48.1351,lng:11.582},'frankfurt':{lat:50.1109,lng:8.6821},
        'copenhagen':{lat:55.6761,lng:12.5683},'stockholm':{lat:59.3293,lng:18.0686},
        'warsaw':{lat:52.2297,lng:21.0122},'krakow':{lat:50.0647,lng:19.945},
        'cebu':{lat:10.3157,lng:123.8854},'boracay':{lat:11.9674,lng:121.9248},
        'cairo':{lat:30.0444,lng:31.2357},'nairobi':{lat:-1.2921,lng:36.8219},
        'buenos aires':{lat:-34.6037,lng:-58.3816},'rio de janeiro':{lat:-22.9068,lng:-43.1729},
    };

    // Deduplicate coords by city for map markers (use first occurrence index per city)
    const uniqueCities = [];
    const seenCities = new Set();
    stops.forEach(s => {
        const key = (s.city || '').toLowerCase().trim();
        if (!seenCities.has(key)) { seenCities.add(key); uniqueCities.push(s); }
    });

    // Resolve coordinates — geocode unknowns via Mapbox
    function resolveCoords(stop) {
        const key = (stop.city || '').toLowerCase().trim();
        if (CITY_COORDS[key]) return Promise.resolve(CITY_COORDS[key]);
        // Mapbox geocoding fallback
        const q = encodeURIComponent((stop.city || '') + (stop.country ? ', ' + stop.country : ''));
        return fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${q}.json?access_token=${token}&limit=1`)
            .then(r => r.json())
            .then(data => {
                const f = data.features && data.features[0];
                if (!f) return null;
                return { lng: f.center[0], lat: f.center[1] };
            })
            .catch(() => null);
    }

    mapboxgl.accessToken = token;
    const map = new mapboxgl.Map({
        container: 'tourStopsMap',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [20, 15],
        zoom: 1.5
    });
    _tourMapInstance = map;
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

    map.on('load', async function() {
        const markerData = [];
        // Build unique city marker list (deduplicated)
        for (let i = 0; i < uniqueCities.length; i++) {
            const c = await resolveCoords(uniqueCities[i]);
            if (!c) continue;
            markerData.push({ city: uniqueCities[i].city, lng: c.lng, lat: c.lat, idx: i });
        }

        // Place numbered markers and store by city key
        markerData.forEach(m => {
            const el = document.createElement('div');
            el.style.cssText = 'width:30px;height:30px;border-radius:50%;background:#1e4fa3;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4);cursor:pointer;transition:transform .2s';
            el.textContent = m.idx + 1;
            const popup = new mapboxgl.Popup({ offset: 16, closeButton: false }).setHTML(`<strong style="font-size:.9rem">${m.city}</strong>`);
            const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                .setLngLat([m.lng, m.lat])
                .setPopup(popup)
                .addTo(map);
            _tourCityMarkers[m.city.toLowerCase().trim()] = { marker, lng: m.lng, lat: m.lat, el };
        });

        // Draw route line
        const coords = markerData.map(m => [m.lng, m.lat]);
        if (coords.length > 1) {
            map.addSource('tour-route', {
                type: 'geojson',
                data: { type: 'Feature', geometry: { type: 'LineString', coordinates: coords } }
            });
            map.addLayer({
                id: 'tour-route',
                type: 'line',
                source: 'tour-route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#2563eb', 'line-width': 3, 'line-dasharray': [2, 1.5] }
            });
        }

        // Fit map to all markers
        if (coords.length === 1) {
            map.flyTo({ center: coords[0], zoom: 6 });
        } else if (coords.length > 1) {
            const bounds = coords.reduce((b, c) => b.extend(c), new mapboxgl.LngLatBounds(coords[0], coords[0]));
            map.fitBounds(bounds, { padding: 60, maxZoom: 8, duration: 0 });
        }
    });
}

function haversineKm(lng1, lat1, lng2, lat2) {
    const R = 6371, toRad = x => x * Math.PI / 180;
    const dLat = toRad(lat2-lat1), dLng = toRad(lng2-lng1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLng/2)**2;
    return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

function clearDayRoute() {
    if (_tourMapInstance) {
        try {
            ['day-route-labels','day-route','day-route-glow'].forEach(id => { if (_tourMapInstance.getLayer(id)) _tourMapInstance.removeLayer(id); });
            ['day-route-labels','day-route'].forEach(s => { if (_tourMapInstance.getSource(s)) _tourMapInstance.removeSource(s); });
        } catch(e) {}
    }
    _dayRouteMarkers.forEach(m => m.remove());
    _dayRouteMarkers = [];
}

function stopOverlayAutoplay() {
    if (_overlayAutoTimer) {
        clearInterval(_overlayAutoTimer);
        _overlayAutoTimer = null;
    }
}

function renderOverlayDots(total, active) {
    const dotsWrap = document.getElementById('tourMapPhotoDots');
    if (!dotsWrap) return;
    if (total <= 1) {
        dotsWrap.innerHTML = '';
        return;
    }
    dotsWrap.innerHTML = '';
    for (let i = 0; i < total; i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'overlay-dot' + (i === active ? ' is-active' : '');
        b.setAttribute('aria-label', `Image ${i + 1}`);
        b.onclick = () => overlayGoTo(i, true);
        dotsWrap.appendChild(b);
    }
}

function renderOverlayImage(restartAnimation = true) {
    const overlay = document.getElementById('tourMapPhotoOverlay');
    const img = document.getElementById('tourMapPhotoOverlayImg');
    const caption = document.getElementById('tourMapPhotoOverlayTitle');
    const prevBtn = document.getElementById('tourMapPrevBtn');
    const nextBtn = document.getElementById('tourMapNextBtn');
    const counter = document.getElementById('tourMapImgCounter');
    if (!overlay || !img || !caption) return;

    const total = _overlayState.images.length;
    const hasImage = total > 0;

    if (_mapVisualMode !== 'photo' || !hasImage) {
        overlay.classList.remove('is-visible', 'is-animating');
        img.src = '';
        caption.textContent = '';
        if (counter) { counter.textContent = ''; counter.classList.add('is-hidden'); }
        if (prevBtn) prevBtn.classList.add('is-hidden');
        if (nextBtn) nextBtn.classList.add('is-hidden');
        renderOverlayDots(0, 0);
        stopOverlayAutoplay();
        syncMapVisualPresentation();
        return;
    }

    _overlayState.currentIndex = Math.max(0, Math.min(_overlayState.currentIndex, total - 1));
    const current = _overlayState.images[_overlayState.currentIndex];
    const currentCaption = _overlayState.captions[_overlayState.currentIndex] || _overlayState.title || 'Selected place';

    if (restartAnimation) {
        overlay.classList.remove('is-animating');
        void overlay.offsetWidth;
    }

    img.src = current;
    caption.textContent = currentCaption;
    overlay.classList.add('is-visible', 'is-animating');

    const multi = total > 1;
    if (prevBtn) prevBtn.classList.toggle('is-hidden', !multi);
    if (nextBtn) nextBtn.classList.toggle('is-hidden', !multi);
    renderOverlayDots(total, _overlayState.currentIndex);

    if (counter) {
        counter.classList.toggle('is-hidden', !multi);
        counter.textContent = multi ? `${_overlayState.currentIndex + 1} / ${total}` : '';
    }

    stopOverlayAutoplay();
    if (multi && _mapVisualMode === 'photo') {
        _overlayAutoTimer = setInterval(() => overlayNext(false), 3800);
    }

    syncMapVisualPresentation();
}

function buildOverlayCaptions(images, captions, fallbackTitle) {
    const cleanImages = Array.isArray(images) ? images : [];
    const cleanCaptions = Array.isArray(captions)
        ? captions.map(x => (x || '').trim()).filter(Boolean)
        : [];

    if (!cleanImages.length) return [];
    if (!cleanCaptions.length) return cleanImages.map(() => fallbackTitle || 'Selected place');

    return cleanImages.map((_, idx) => cleanCaptions[Math.min(idx, cleanCaptions.length - 1)] || fallbackTitle || 'Selected place');
}

function setMapPhotoOverlay(images, title, captions) {
    const list = Array.isArray(images)
        ? images.map(x => (x || '').trim()).filter(Boolean)
        : ((images || '').trim() ? [(images || '').trim()] : []);

    const prevFirst = _overlayState.images[0] || '';
    const nextFirst = list[0] || '';

    _overlayState.images = list;
    _overlayState.captions = buildOverlayCaptions(list, captions, (title || 'Selected place').trim());
    _overlayState.title = (title || 'Selected place').trim();
    _overlayState.currentIndex = (prevFirst && prevFirst === nextFirst) ? _overlayState.currentIndex : 0;

    renderOverlayImage(true);
}

function overlayGoTo(index, stopAuto = false) {
    if (!_overlayState.images.length) return;
    _overlayState.currentIndex = ((index % _overlayState.images.length) + _overlayState.images.length) % _overlayState.images.length;
    if (stopAuto) stopOverlayAutoplay();
    renderOverlayImage(true);
}

function overlayNext(stopAuto = true) {
    overlayGoTo(_overlayState.currentIndex + 1, stopAuto);
}

function overlayPrev() {
    overlayGoTo(_overlayState.currentIndex - 1, true);
}

function setMapVisualMode(mode) {
    _mapVisualMode = mode === 'photo' ? 'photo' : 'map';

    if (!_hasSelectedItineraryDay && _mapVisualMode === 'photo') {
        _mapVisualMode = 'map';
    }

    const mapBtn = document.getElementById('mapVisualMapBtn');
    const photoBtn = document.getElementById('mapVisualPhotoBtn');
    if (mapBtn) mapBtn.classList.toggle('is-active', _mapVisualMode === 'map');
    if (photoBtn) photoBtn.classList.toggle('is-active', _mapVisualMode === 'photo');

    renderOverlayImage(false);
}

function syncMapVisualPresentation() {
    const mapCanvasWrap = document.querySelector('.tour-map-canvas-wrap');
    const photoBtn = document.getElementById('mapVisualPhotoBtn');
    const mapBtn = document.getElementById('mapVisualMapBtn');
    const hint = document.getElementById('mapVisualHint');

    if (photoBtn) {
        photoBtn.classList.toggle('is-hidden', !_hasSelectedItineraryDay);
    }
    if (hint) {
        hint.classList.toggle('is-hidden', _hasSelectedItineraryDay);
    }

    if (!_hasSelectedItineraryDay) {
        _mapVisualMode = 'map';
        if (mapBtn) mapBtn.classList.add('is-active');
        if (photoBtn) photoBtn.classList.remove('is-active');
    }

    if (!mapCanvasWrap) return;

    const shouldShowPhoto = _mapVisualMode === 'photo' && _overlayState.images && _overlayState.images.length > 0;
    mapCanvasWrap.classList.toggle('map-photo-mode', shouldShowPhoto);
}

// Geocode a place name with a bbox constraint ±4° around the base city
async function geocodeWaypoint(name, token, baseLng, baseLat) {
    const key = name.toLowerCase().trim();
    if (_tourCityMarkers[key]) {
        const { lng, lat } = _tourCityMarkers[key];
        return { lng, lat };
    }
    try {
        const q = encodeURIComponent(name);
        let url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${q}.json?access_token=${token}&limit=1&types=place,locality,poi,address,neighborhood,region`;
        if (baseLng != null && baseLat != null) {
            const pad = 4;
            url += `&bbox=${baseLng-pad},${baseLat-pad},${baseLng+pad},${baseLat+pad}`;
        }
        const d = await fetch(url).then(r => r.json());
        const f = d.features && d.features[0];
        return f ? { lng: f.center[0], lat: f.center[1] } : null;
    } catch { return null; }
}

async function showDayRoute(waypoints, baseLng, baseLat, card) {
    const map = _tourMapInstance;
    const token = document.getElementById('tourStopsMap').dataset.mapboxToken;
    clearDayRoute();
    const coords = [];
    const resolved = [];
    for (const wp of waypoints) {
        const c = await geocodeWaypoint(wp, token, baseLng, baseLat);
        if (!c) continue;
        coords.push([c.lng, c.lat]);
        resolved.push({ name: wp, lng: c.lng, lat: c.lat });
    }
    if (coords.length < 1) return;

    // Calculate km distances between consecutive waypoints
    const distances = resolved.slice(1).map((wp, i) => haversineKm(resolved[i].lng, resolved[i].lat, wp.lng, wp.lat));

    // Update the clicked card's waypoints strip with distance badges
    if (card) {
        const strip = card.querySelector('.itin-day-waypoints');
        if (strip) {
            let html = '';
            resolved.forEach((wp, i) => {
                if (i > 0) html += `<span class="wp-dist-badge">~${distances[i-1]}km</span><i class="fas fa-chevron-right wp-arrow"></i>`;
                html += `<span class="itin-day-waypoint">${wp.name}</span>`;
            });
            strip.innerHTML = html;
        }
    }

    // Draw amber day-route line
    if (coords.length > 1) {
        map.addSource('day-route', { type:'geojson', data:{ type:'Feature', geometry:{ type:'LineString', coordinates:coords } } });
        map.addLayer({ id:'day-route-glow', type:'line', source:'day-route', layout:{'line-join':'round','line-cap':'round'}, paint:{'line-color':'#f59e0b','line-width':8,'line-opacity':0.25} });
        map.addLayer({ id:'day-route', type:'line', source:'day-route', layout:{'line-join':'round','line-cap':'round'}, paint:{'line-color':'#f59e0b','line-width':3} });
        // Distance labels at each segment midpoint
        const labelFeatures = resolved.slice(1).map((wp, i) => ({
            type: 'Feature',
            geometry: { type: 'Point', coordinates: [(resolved[i].lng+wp.lng)/2, (resolved[i].lat+wp.lat)/2] },
            properties: { label: `~${distances[i]} km` }
        }));
        map.addSource('day-route-labels', { type:'geojson', data:{ type:'FeatureCollection', features: labelFeatures } });
        map.addLayer({ id:'day-route-labels', type:'symbol', source:'day-route-labels',
            layout:{ 'text-field':['get','label'], 'text-size':11, 'text-anchor':'center', 'text-offset':[0,-1] },
            paint:{ 'text-color':'#92400e', 'text-halo-color':'#fffbeb', 'text-halo-width':1.5 }
        });
    }

    // Place lettered markers (A, B, C…)
    resolved.forEach((wp, idx) => {
        const el = document.createElement('div');
        el.style.cssText = 'width:24px;height:24px;border-radius:50%;background:#d97706;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);cursor:pointer';
        el.textContent = String.fromCharCode(65 + idx);
        const distLabel = idx > 0 ? `<br><small style="color:#b45309">~${distances[idx-1]} km from prev</small>` : '';
        const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat([wp.lng, wp.lat])
            .setPopup(new mapboxgl.Popup({ offset: 14, closeButton: false }).setHTML(`<strong style="font-size:.85rem">${wp.name}</strong>${distLabel}`))
            .addTo(map);
        _dayRouteMarkers.push(marker);
    });

    // Fit to all day waypoints
    const bounds = coords.reduce((b,c) => b.extend(c), new mapboxgl.LngLatBounds(coords[0],coords[0]));
    map.fitBounds(bounds, { padding: 80, maxZoom: 10, duration: 900 });
}

function flyToCity(card) {
    const city = (card.dataset.city || '').toLowerCase().trim();
    const waypointsRaw = (card.dataset.waypoints || '').trim();
    let imageList = [];
    try {
        imageList = JSON.parse(card.dataset.images || '[]');
    } catch (e) {
        imageList = [];
    }
    const placeTitle = (card.dataset.placeTitle || card.dataset.city || '').trim();
    const waypointTitles = waypointsRaw
        ? waypointsRaw.split(',').map(w => w.trim()).filter(Boolean)
        : [];

    _hasSelectedItineraryDay = true;
    syncMapVisualPresentation();

    setMapPhotoOverlay(imageList, placeTitle, waypointTitles);

    // Highlight active card
    document.querySelectorAll('.itin-day-card').forEach(c => c.classList.remove('itin-day-card--active'));
    card.classList.add('itin-day-card--active');

    // Scroll map into view
    const mapWrap = document.getElementById('tourStopsMapWrap');
    if (mapWrap) mapWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    if (!_tourMapInstance) return;

    // If the day has waypoints — show within-day route
    if (waypointsRaw) {
        const waypoints = waypointTitles;
        if (waypoints.length > 1) {
            // Pass base city coords so geocoding uses a regional bbox
            const base = _tourCityMarkers[city];
            showDayRoute(waypoints, base ? base.lng : null, base ? base.lat : null, card);
            return;
        }
    }

    // No waypoints: clear any previous day route and fly to the city marker
    clearDayRoute();
    const m = _tourCityMarkers[city];
    if (!m) return;

    Object.values(_tourCityMarkers).forEach(({ marker }) => marker.getPopup()?.isOpen() && marker.togglePopup());
    _tourMapInstance.flyTo({ center: [m.lng, m.lat], zoom: 7, duration: 900, essential: true });
    setTimeout(() => {
        if (!m.marker.getPopup().isOpen()) m.marker.togglePopup();
        m.el.style.transform = 'scale(1.35)';
        setTimeout(() => { m.el.style.transform = 'scale(1)'; }, 400);
    }, 950);
}

function flyToCityHeader(header) {
    const city = (header.dataset.city || '').toLowerCase().trim();
    let imageList = [];
    try {
        imageList = JSON.parse(header.dataset.images || '[]');
    } catch (e) {
        imageList = [];
    }
    const title = (header.dataset.placeTitle || city || '').trim();

    _hasSelectedItineraryDay = true;
    syncMapVisualPresentation();

    setMapPhotoOverlay(imageList, title, []);

    if (!_tourMapInstance) return;

    clearDayRoute();
    const markerData = _tourCityMarkers[city];
    if (!markerData) return;

    _tourMapInstance.flyTo({ center: [markerData.lng, markerData.lat], zoom: 6.6, duration: 900, essential: true });
    setTimeout(() => {
        if (!markerData.marker.getPopup().isOpen()) markerData.marker.togglePopup();
    }, 900);
}
</script>
<script>
const basePrice = {{ $tour->effective_price ?? 0 }};
const childRate = 0.75;

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        // Initialize/resize the route map when Destinations tab is opened
        if (btn.dataset.tab === 'destinations') {
            setTimeout(initTourStopsMap, 50);
        }
    });
});

// Gallery
function changeGalleryImage(thumb) {
    document.getElementById('mainGalleryImg').src = thumb.src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

// Star Rating Input
document.querySelectorAll('#starRating .fa-star').forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.value);
        document.getElementById('ratingInput').value = val;
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < val);
        });
    });
    star.addEventListener('mouseover', function() {
        const val = parseInt(this.dataset.value);
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < val);
        });
    });
    star.addEventListener('mouseout', function() {
        const current = parseInt(document.getElementById('ratingInput').value || 0);
        document.querySelectorAll('#starRating .fa-star').forEach((s, i) => {
            s.classList.toggle('text-yellow', i < current);
        });
    });
});

// Wishlist toggle
document.querySelectorAll('.wishlist-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            const span = this.querySelector('span');
            if (data.wishlisted) {
                this.classList.add('wishlist-active');
                span.textContent = 'In Wishlist';
            } else {
                this.classList.remove('wishlist-active');
                span.textContent = 'Add to Wishlist';
            }
        });
    });
});
</script>

{{-- ── Live departure-slot polling ─────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    const list = document.getElementById('departureDatesList');
    if (!list) return;

    const slug = list.dataset.tourSlug;
    if (!slug) return;

    const POLL_URL   = '/tours/' + encodeURIComponent(slug) + '/departures.json';
    const INTERVAL   = 30000; // 30 s
    const dot        = document.getElementById('depLiveDot');

    function flash(el) {
        el.classList.remove('stat-flash');
        void el.offsetWidth;
        el.classList.add('stat-flash');
    }

    async function refresh() {
        try {
            const res = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const dates = await res.json();

            let changed = false;
            dates.forEach(d => {
                const row = list.querySelector(`.departure-date-row[data-start="${CSS.escape(d.start)}"]`);
                if (!row) return;

                const badge = row.querySelector('.seats-badge');
                if (!badge) return;

                if (badge.textContent.trim() !== d.badgeText || !badge.classList.contains(d.badgeClass)) {
                    badge.textContent = d.badgeText;
                    badge.className   = 'seats-badge ' + d.badgeClass;
                    row.classList.toggle('departure-date-row--full', d.isFull);
                    flash(badge);
                    changed = true;
                }
            });

            if (changed && dot) flash(dot);
        } catch (_) { /* network hiccup — ignore */ }
    }

    setInterval(refresh, INTERVAL);
})();
</script>
@endpush
