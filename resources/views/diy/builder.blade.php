@extends('layouts.app')

@section('title', $itinerary->tour_name ?? 'Build Your Tour')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/diy.css') }}">
<link href='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css' rel='stylesheet' />
@endpush

@section('content')
{{-- =====================================================================
     Builder Layout: full-width split pane
     ===================================================================== --}}
<div class="diy-builder-wrapper" id="diyBuilder"
     data-session="{{ $session->session_token }}"
     data-save-url="{{ route('diy.save', $session->session_token) }}"
     data-suggestions-url="{{ route('diy.api.suggestions') }}"
     data-optimize-url="{{ route('diy.api.optimize-route') }}"
     data-pricing-url="{{ route('diy.api.calculate-pricing') }}"
     data-validate-url="{{ route('diy.api.validate') }}"
     data-reachable-url="{{ route('diy.api.reachable-cities') }}"
     data-mapbox-token="{{ $mapboxToken }}"
     data-itinerary='@json($itinerary->itinerary_data ?? [])'
     data-pricing='@json($itinerary->pricing_data ?? [])'
     data-prefs='@json($itinerary->user_preferences ?? [])'>

    {{-- Top action bar --}}
    <div class="builder-topbar">
        <a href="{{ route('diy.index') }}" class="btn btn-ghost btn-sm">← Back</a>
        <div class="builder-topbar-title">
            <span id="tourNameDisplay">{{ $itinerary->tour_name ?? 'My Custom Tour' }}</span>
            <button class="btn-edit-inline" onclick="editTourName()"><i class="fas fa-pencil-alt"></i></button>
        </div>
        <div class="builder-topbar-actions">
            <span id="saveStatus" class="save-status"></span>
            <button class="btn btn-outline btn-sm" onclick="manualSave()"><i class="fas fa-save"></i> Save Draft</button>
            <a href="{{ route('diy.request-quote', $session->session_token) }}"
               onclick="return confirmQuote(event)"
               class="btn btn-primary btn-sm"><i class="fas fa-file-invoice"></i> Get Quote</a>
        </div>
    </div>

    {{-- AI Explanation banner (first load) --}}
    @if(session('ai_explanation'))
    <div class="ai-explanation-banner" id="aiExplanation">
        <span class="ai-badge">🤖 AI</span>
        {{ session('ai_explanation') }}
        <button class="btn-close-banner" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    {{-- Quick-start guide strip --}}
    <div class="builder-guide-strip" id="builderGuide">
        <div class="guide-steps">
            <span class="guide-step"><span class="guide-num">1</span> Review your cities &amp; route on the map</span>
            <span class="guide-arrow">→</span>
            <span class="guide-step"><span class="guide-num">2</span> Add or remove cities using the panel on the right</span>
            <span class="guide-arrow">→</span>
            <span class="guide-step"><span class="guide-num">3</span> Check the cost breakdown below the cities list</span>
            <span class="guide-arrow">→</span>
            <span class="guide-step guide-step-cta"><span class="guide-num">4</span> Click <strong>Get My Official Quote</strong> when satisfied!</span>
        </div>
        <button class="guide-close" onclick="document.getElementById('builderGuide').remove()" aria-label="Dismiss guide">✕</button>
    </div>

    {{-- Main split layout --}}
    <div class="builder-main">

        {{-- LEFT: Interactive Map (60%) --}}
        <div class="builder-map-panel" id="builderMapPanel">

            {{-- Map view toggles — at TOP so they're always visible --}}
            <div class="map-view-tabs">
                <span class="map-view-label">View as:</span>
                <button class="map-tab active" data-view="map" onclick="setMapView('map', this)">🗺️ Map</button>
                <button class="map-tab" data-view="timeline" onclick="setMapView('timeline', this)">📋 Day-by-Day</button>
                <button class="map-tab" data-view="calendar" onclick="setMapView('calendar', this)">📅 Calendar</button>
            </div>

            <div id="diyMap" class="diy-map-container"></div>

            {{-- Timeline view (hidden by default) --}}
            <div id="timelineView" class="timeline-view" style="display:none">
                <div id="timelineContent"></div>
            </div>

            {{-- Calendar view (hidden by default) --}}
            <div id="calendarView" class="calendar-view" style="display:none">
                <div id="calendarContent"></div>
            </div>
        </div>

        {{-- RIGHT: Controls (40%) --}}
        <div class="builder-controls-panel">

            <div class="builder-sections-scroll">

            {{-- AI Assistant --}}
            <div class="builder-section ai-assistant-section">
                <div class="section-header">
                    <span class="section-icon">🤖</span>
                    <h3>AI Tour Guide</h3>
                </div>

                {{-- Guide tips chips --}}
                <div class="ai-guide-chips" id="aiGuideChips">
                    <p class="ai-guide-label"><i class="fas fa-lightbulb"></i> Quick prompts to get started:</p>
                    <div class="ai-chips-row">
                        <button class="ai-chip" onclick="useChip(this, 'Suggest the best cities to visit based on my itinerary')">
                            🏙️ Suggest cities
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'How can I reduce the total cost of my tour?')">
                            💰 Reduce cost
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'What activities are recommended for this route?')">
                            🎯 Recommend activities
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'What is the best travel order for my cities?')">
                            🗺️ Optimize route
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'Add a free day for rest and exploration')">
                            😴 Add rest day
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'Is my itinerary good for a family trip?')">
                            👨‍👩‍👧 Family-friendly?
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'Suggest a romantic city to add to my tour')">
                            💑 Romantic add-on
                        </button>
                        <button class="ai-chip" onclick="useChip(this, 'What should I pack for this trip?')">
                            🎒 Packing tips
                        </button>
                    </div>
                </div>

                <div id="aiMessages" class="ai-messages-list">
                    <div class="ai-message ai-message--welcome">
                        👋 <strong>Hi! I'm your AI Tour Guide.</strong><br>
                        I can help you add cities, optimize your route, suggest activities, and answer any questions about your tour. Try the quick prompts above or type anything below!
                    </div>
                </div>
                <div class="ai-input-row">
                    <input type="text" id="aiInput" class="form-control form-control-sm"
                           placeholder='e.g. "Add a day in Venice"' maxlength="200">
                    <button class="btn btn-primary btn-sm" onclick="askAI()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>

            {{-- Itinerary Overview --}}
            <div class="builder-section">
                <div class="section-header">
                    <span class="section-icon">📋</span>
                    <h3>Your Itinerary</h3>
                </div>

                <div class="itinerary-meta">
                    <div class="meta-item">
                        <span class="meta-label">Days</span>
                        <div class="counter-inline">
                            <button onclick="adjustDays(-1)">−</button>
                            <span id="daysDisplay">0</span>
                            <button onclick="adjustDays(1)">+</button>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Est. Cost (per person)</span>
                        <strong id="totalCostDisplay" class="text-primary">₱0</strong>
                    </div>
                </div>

                {{-- City list --}}
                <div class="city-list" id="cityList">
                    {{-- Populated by JS --}}
                </div>

                <button class="btn btn-outline btn-sm btn-full mt-2" onclick="openAddCityModal()">
                    <i class="fas fa-plus"></i> Add City
                </button>

                {{-- AI suggestions --}}
                <div id="citySuggestions" class="city-suggestions" style="display:none">
                    <strong>⭐ Suggested next stops:</strong>
                    <div id="citySuggestionList"></div>
                </div>
            </div>

            {{-- Pricing Breakdown (collapsible) --}}
            <div class="builder-section pricing-section" id="pricingSection">
                <div class="section-header cursor-pointer" onclick="toggleSection('pricingBody')">
                    <span class="section-icon">💰</span>
                    <h3>Cost Breakdown</h3>
                    <i class="fas fa-chevron-down toggle-icon" id="pricingToggle"></i>
                </div>
                <div id="pricingBody">
                    <div id="pricingBreakdown">
                        {{-- Populated by JS --}}
                    </div>
                    <div id="budgetSuggestions"></div>
                </div>
            </div>

            {{-- Validation Results (collapsible) --}}
            <div class="builder-section validation-section">
                <div class="section-header cursor-pointer" onclick="toggleSection('validationBody')">
                    <span class="section-icon">🔍</span>
                    <h3>Tour Quality Check</h3>
                    <span id="qualityScore" class="quality-badge">--/100</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>
                <div id="validationBody" style="display:none">
                    <div id="validationResults"></div>
                </div>
            </div>{{-- .builder-section validation --}}

            </div>{{-- .builder-sections-scroll --}}

            {{-- Sticky "Get My Quote" CTA — always visible at the bottom --}}
            <div class="builder-cta-strip">
                <p class="cta-title">✅ Happy with your itinerary?</p>
                <a href="{{ route('diy.request-quote', $session->session_token) }}"
                   onclick="return confirmQuote(event)"
                   class="btn btn-primary btn-full builder-cta-btn">
                    <i class="fas fa-file-invoice-dollar"></i>&nbsp; Get My Official Quote →
                </a>
                <p class="cta-sub">Our team reviews &amp; confirms within 24 hrs — no payment needed yet.</p>
                <div class="cta-divider">or</div>
                <a href="https://www.facebook.com/discovergrp" target="_blank" rel="noopener noreferrer"
                   class="btn btn-facebook btn-full">
                    <i class="fab fa-facebook"></i>&nbsp; Contact Sales for Guidance
                </a>
                <p class="cta-sub">Chat with our travel experts on Facebook for tips &amp; personalized advice.</p>
            </div>

        </div>{{-- .builder-controls-panel --}}

    </div>{{-- .builder-main --}}

</div>{{-- .diy-builder-wrapper --}}

{{-- =====================================================================
     Add City Modal
     ===================================================================== --}}
<div class="modal-overlay" id="addCityModal" style="display:none" onclick="closeModal('addCityModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3>Add a City</h3>
        <div class="form-group">
            <label>City Name</label>
            <input type="text" id="newCityName" class="form-control" placeholder="e.g. Venice" list="cityDatalist">
            <datalist id="cityDatalist">
                @foreach(['Paris','Amsterdam','Brussels','Barcelona','Madrid','Lisbon','Geneva','Zurich','Lucerne','Interlaken','Milan','Venice','Florence','Rome','Naples','Vienna','Salzburg','Prague','Budapest','Berlin','Munich','Dubrovnik','Athens','Santorini','Porto','Seville'] as $c)
                <option value="{{ $c }}">
                @endforeach
            </datalist>
        </div>
        <div class="form-group">
            <label>Insert after city:</label>
            <select id="insertAfterCity" class="form-control">
                <option value="end">At end of itinerary</option>
            </select>
        </div>
        <div class="form-group">
            <label>Duration (days):</label>
            <input type="number" id="newCityDays" class="form-control" value="2" min="1" max="14">
        </div>
        <div class="form-group">
            <label>Hotel tier:</label>
            <select id="newCityTier" class="form-control">
                <option value="3-star">3-star</option>
                <option value="4-star" selected>4-star</option>
                <option value="5-star">5-star</option>
            </select>
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('addCityModal')">Cancel</button>
            <button class="btn btn-primary" onclick="confirmAddCity()">Add City</button>
        </div>
    </div>
</div>

{{-- =====================================================================
     Edit City Modal
     ===================================================================== --}}
<div class="modal-overlay" id="editCityModal" style="display:none" onclick="closeModal('editCityModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3>Edit City: <span id="editCityNameDisplay"></span></h3>
        <input type="hidden" id="editCityIndex">
        <div class="form-group">
            <label>Duration (days):</label>
            <input type="number" id="editCityDays" class="form-control" min="1" max="21">
        </div>
        <div class="form-group">
            <label>Hotel tier:</label>
            <select id="editCityTier" class="form-control">
                <option value="3-star">3-star</option>
                <option value="4-star">4-star</option>
                <option value="5-star">5-star</option>
            </select>
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('editCityModal')">Cancel</button>
            <button class="btn btn-danger" onclick="removeCity(document.getElementById('editCityIndex').value)">Remove City</button>
            <button class="btn btn-primary" onclick="saveEditCity()">Save</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js'></script>
<script src="{{ asset('js/diy-builder.js') }}"></script>
<script>
window.useChip = function (btn, text) {
    document.getElementById('aiInput').value = text;
    // visually mark selected chip
    document.querySelectorAll('.ai-chip').forEach(function (c) { c.classList.remove('ai-chip--active'); });
    btn.classList.add('ai-chip--active');
    window.askAI();
};
</script>
@endpush
