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

    {{-- Main split layout --}}
    <div class="builder-main">

        {{-- LEFT: Interactive Map (60%) --}}
        <div class="builder-map-panel" id="builderMapPanel">
            <div id="diyMap" class="diy-map-container"></div>

            {{-- Map view toggles --}}
            <div class="map-view-tabs">
                <button class="map-tab active" data-view="map" onclick="setMapView('map', this)">🗺️ Map</button>
                <button class="map-tab" data-view="timeline" onclick="setMapView('timeline', this)">📋 Timeline</button>
                <button class="map-tab" data-view="calendar" onclick="setMapView('calendar', this)">📅 Calendar</button>
            </div>

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

            {{-- AI Assistant --}}
            <div class="builder-section ai-assistant-section">
                <div class="section-header">
                    <span class="section-icon">🤖</span>
                    <h3>AI Assistant</h3>
                </div>
                <div id="aiMessages" class="ai-messages-list">
                    <div class="ai-message">Ask me anything about your tour!</div>
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
@endpush
