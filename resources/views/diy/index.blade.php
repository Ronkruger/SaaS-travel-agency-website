@extends('layouts.app')

@section('title', 'Build Your Custom Tour')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/diy.css') }}">
@endpush

@section('content')
<div class="diy-hero">
    <div class="container">
        <div class="diy-hero-content">
            <div class="diy-hero-badge">✨ AI-Powered</div>
            <h1>Design Your <span class="text-accent">Perfect</span> Custom Tour</h1>
            <p class="diy-hero-sub">Answer a few quick questions and our AI will craft a personalised itinerary&nbsp;— then customise every detail.</p>
        </div>
    </div>
</div>

{{-- Multi-step preference wizard --}}
<div class="container diy-wizard-container">
    <div class="diy-wizard-card">

        {{-- Progress bar --}}
        <div class="wizard-progress" id="wizardProgress">
            <div class="wizard-progress-bar" id="progressBar" style="width:16.6%"></div>
        </div>

        <form action="{{ route('diy.store') }}" method="POST" id="diyWizardForm" novalidate>
            @csrf

            {{-- Step 1: Duration --}}
            <div class="wizard-step active" id="step1">
                <div class="wizard-step-icon">📅</div>
                <h2>How many days do you have?</h2>
                <p class="wizard-step-hint">We recommend at least 7 days for a proper travel experience.</p>
                <div class="duration-grid">
                    @foreach([7, 10, 14] as $d)
                    <label class="duration-option">
                        <input type="radio" name="duration_option" value="{{ $d }}" {{ $d == 10 ? 'checked' : '' }}>
                        <span class="duration-card">
                            <strong>{{ $d }}</strong><small>days</small>
                        </span>
                    </label>
                    @endforeach
                    <label class="duration-option">
                        <input type="radio" name="duration_option" value="custom" id="durationCustomRadio">
                        <span class="duration-card">
                            <strong>Custom</strong><small>I'll enter days</small>
                        </span>
                    </label>
                </div>
                <div id="customDaysInput" class="custom-input-group" style="display:none">
                    <label>Enter number of days (3–60):</label>
                    <input type="number" id="customDaysValue" min="3" max="60" class="form-control w-auto" placeholder="e.g. 21">
                </div>
                <input type="hidden" name="duration_days" id="durationHidden" value="10">
            </div>

            {{-- Step 2: Countries --}}
            <div class="wizard-step" id="step2">
                <div class="wizard-step-icon">🌍</div>
                <h2>Which countries interest you?</h2>
                <p class="wizard-step-hint">First pick a continent, then choose your countries.</p>

                {{-- Continent selector --}}
                <input type="hidden" name="continent" id="selectedContinent" value="">
                <div class="continent-tabs">
                    <button type="button" class="continent-tab" data-continent="Europe"          onclick="selectContinent('Europe')">🇪🇺 Europe</button>
                    <button type="button" class="continent-tab" data-continent="Asia"            onclick="selectContinent('Asia')">🌏 Asia</button>
                    <button type="button" class="continent-tab" data-continent="Middle East"     onclick="selectContinent('Middle East')">🕌 Middle East</button>
                    <button type="button" class="continent-tab" data-continent="Americas"        onclick="selectContinent('Americas')">🌎 Americas</button>
                    <button type="button" class="continent-tab" data-continent="Africa & Oceania" onclick="selectContinent('Africa &amp; Oceania')">🌍 Africa &amp; Oceania</button>
                </div>
                <p id="continentHint" class="continent-hint">Choose a continent above to see the available countries.</p>

                <div class="country-search-wrap" id="countrySearchWrap" style="display:none">
                    <i class="fas fa-search country-search-icon"></i>
                    <input type="text" id="countrySearch" class="country-search-input" placeholder="Search countries…" autocomplete="off">
                    <button type="button" id="countrySearchClear" class="country-search-clear" style="display:none" aria-label="Clear search">✕</button>
                </div>

                <div class="countries-grid" id="countriesGrid">
                    @foreach([
                        {{-- Europe --}}
                        ['France','🇫🇷','Europe'],['Switzerland','🇨🇭','Europe'],['Italy','🇮🇹','Europe'],
                        ['Germany','🇩🇪','Europe'],['Austria','🇦🇹','Europe'],['Spain','🇪🇸','Europe'],
                        ['Netherlands','🇳🇱','Europe'],['Portugal','🇵🇹','Europe'],['Greece','🇬🇷','Europe'],
                        ['Belgium','🇧🇪','Europe'],['Czech Republic','🇨🇿','Europe'],['Hungary','🇭🇺','Europe'],
                        ['Croatia','🇭🇷','Europe'],['Poland','🇵🇱','Europe'],['Denmark','🇩🇰','Europe'],
                        ['Sweden','🇸🇪','Europe'],['Ireland','🇮🇪','Europe'],['Slovakia','🇸🇰','Europe'],
                        {{-- Asia --}}
                        ['Japan','🇯🇵','Asia'],['South Korea','🇰🇷','Asia'],['Thailand','🇹🇭','Asia'],
                        ['Vietnam','🇻🇳','Asia'],['Indonesia','🇮🇩','Asia'],['Philippines','🇵🇭','Asia'],
                        ['Singapore','🇸🇬','Asia'],['Malaysia','🇲🇾','Asia'],['China','🇨🇳','Asia'],
                        ['India','🇮🇳','Asia'],['Nepal','🇳🇵','Asia'],['Sri Lanka','🇱🇰','Asia'],
                        ['Cambodia','🇰🇭','Asia'],['Myanmar','🇲🇲','Asia'],['Maldives','🇲🇻','Asia'],
                        {{-- Middle East --}}
                        ['UAE','🇦🇪','Middle East'],['Turkey','🇹🇷','Middle East'],['Jordan','🇯🇴','Middle East'],
                        ['Qatar','🇶🇦','Middle East'],['Israel','🇮🇱','Middle East'],['Oman','🇴🇲','Middle East'],
                        {{-- Americas --}}
                        ['United States','🇺🇸','Americas'],['Canada','🇨🇦','Americas'],['Mexico','🇲🇽','Americas'],
                        ['Brazil','🇧🇷','Americas'],['Argentina','🇦🇷','Americas'],['Peru','🇵🇪','Americas'],
                        ['Colombia','🇨🇴','Americas'],['Chile','🇨🇱','Americas'],
                        {{-- Africa & Oceania --}}
                        ['Morocco','🇲🇦','Africa & Oceania'],['South Africa','🇿🇦','Africa & Oceania'],['Kenya','🇰🇪','Africa & Oceania'],
                        ['Egypt','🇪🇬','Africa & Oceania'],['Australia','🇦🇺','Africa & Oceania'],['New Zealand','🇳🇿','Africa & Oceania'],
                    ] as [$country, $flag, $continent])
                    <label class="country-option" data-continent="{{ $continent }}" style="display:none">
                        <input type="checkbox" name="countries[]" value="{{ $country }}">
                        <span class="country-card">
                            <span class="country-flag">{{ $flag }}</span>
                            <span class="country-name">{{ $country }}</span>
                        </span>
                    </label>
                    @endforeach
                    <label class="country-option surprise-option">
                        <input type="checkbox" name="countries[]" value="Surprise me" id="surpriseCheck">
                        <span class="country-card surprise">
                            🎲 Surprise me!
                        </span>
                    </label>
                </div>
                <p id="noCountryResults" style="display:none; text-align:center; color:var(--diy-muted); padding:.5rem 0;">No countries match your search.</p>
            </div>

            {{-- Step 3: Travel style --}}
            <div class="wizard-step" id="step3">
                <div class="wizard-step-icon">🎭</div>
                <h2>What's your travel style?</h2>
                <p class="wizard-step-hint">Pick as many as you like.</p>
                <div class="style-grid">
                    @foreach([
                        ['cultural','🏛️','Cultural / Historical','Museums, monuments, heritage'],
                        ['nature','🏔️','Nature / Adventure','Mountains, lakes, hiking'],
                        ['food','🍷','Food & Wine','Local cuisine, wine tours'],
                        ['romantic','💑','Romantic / Leisure','Slow pace, scenic spots'],
                        ['shopping','🛍️','Shopping / Modern','Outlets, city life'],
                        ['balanced','⚖️','Balanced Mix','A bit of everything'],
                    ] as [$val, $icon, $label, $desc])
                    <label class="style-option">
                        <input type="checkbox" name="travel_style[]" value="{{ $val }}">
                        <span class="style-card">
                            <span class="style-icon">{{ $icon }}</span>
                            <strong>{{ $label }}</strong>
                            <small>{{ $desc }}</small>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Step 4: Budget --}}
            <div class="wizard-step" id="step4">
                <div class="wizard-step-icon">💰</div>
                <h2>Budget per person (PHP)?</h2>
                <p class="wizard-step-hint">Covers accommodation, transport, activities, meals, guide &amp; travel insurance — all in.</p>
                <div class="budget-options">
                    @foreach([
                        ['80000-140000', 'Budget',   '₱80,000 – ₱140,000',  '7 days · 3-star hotels, public transport, self-guided'],
                        ['140000-200000','Standard', '₱140,000 – ₱200,000', '10 days · 4-star hotels, first-class trains, shared guide'],
                        ['200000-280000','Premium',  '₱200,000 – ₱280,000', '14 days · 4–5 star, private transport, dedicated guide'],
                        ['280000+',      'Luxury',   '₱280,000+',            '14+ days · 5-star, private transfers, exclusive access'],
                    ] as [$val, $tier, $range, $desc])
                    <label class="budget-option">
                        <input type="radio" name="budget_range" value="{{ $val }}" {{ $val === '140000-200000' ? 'checked' : '' }}>
                        <span class="budget-card">
                            <strong>{{ $tier }}</strong>
                            <span class="budget-range">{{ $range }}</span>
                            <small>{{ $desc }}</small>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Step 5: Must-visit + preferences --}}
            <div class="wizard-step" id="step5">
                <div class="wizard-step-icon">📍</div>
                <h2>Any must-visit places?</h2>
                <p class="wizard-step-hint">Separate multiple places with commas. We'll make sure to include them.</p>
                <div class="form-group">
                    <input type="text" name="must_visit" class="form-control" maxlength="500"
                           placeholder="e.g. Eiffel Tower, Swiss Alps, Venice, Santorini">
                </div>

                <div class="mt-4">
                    <label class="form-label fw-semibold">Travel month (optional)</label>
                    <select name="travel_month" class="form-control">
                        <option value="">Not sure yet</option>
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                        <option value="{{ $m }}" {{ $m === 'June' ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Step 6: Pace + group size --}}
            <div class="wizard-step" id="step6">
                <div class="wizard-step-icon">⚡</div>
                <h2>Travel pace &amp; group size</h2>

                <div class="form-group">
                    <label class="form-label fw-semibold">Travel pace:</label>
                    <div class="pace-options">
                        @foreach([
                            ['relaxed','🐢','Relaxed','2–3 cities, more time per place'],
                            ['moderate','🚶','Moderate','4–5 cities, balanced'],
                            ['fast','🏃','Fast-paced','6+ cities, see more'],
                        ] as [$val, $icon, $label, $desc])
                        <label class="pace-option">
                            <input type="radio" name="pace" value="{{ $val }}" {{ $val === 'moderate' ? 'checked' : '' }}>
                            <span class="pace-card">
                                <span>{{ $icon }}</span>
                                <strong>{{ $label }}</strong>
                                <small>{{ $desc }}</small>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label class="form-label fw-semibold">Group size (number of travelers):</label>
                    <div class="group-counter">
                        <button type="button" class="counter-btn" onclick="changeGroup(-1)">−</button>
                        <span id="groupDisplay">2</span>
                        <input type="hidden" name="group_size" id="groupSize" value="2">
                        <button type="button" class="counter-btn" onclick="changeGroup(1)">+</button>
                    </div>
                    <small class="text-muted">Pricing will be calculated per person</small>
                </div>

                {{-- Submit --}}
                <div class="wizard-submit-area">
                    <button type="submit" class="btn btn-primary btn-lg btn-generate" id="generateBtn">
                        <i class="fas fa-magic"></i>&nbsp; Generate My Itinerary
                        <span class="btn-ai-badge">AI</span>
                    </button>
                    <p class="wizard-submit-note">Takes about 15–30 seconds. Our AI will design your itinerary.</p>
                </div>
            </div>

        </form>

        {{-- Wizard navigation --}}
        <div class="wizard-nav" id="wizardNav">
            <button type="button" class="btn btn-outline btn-sm" id="prevBtn" onclick="wizardStep(-1)" style="display:none">
                ← Back
            </button>
            <div class="wizard-step-dots" id="stepDots"></div>
            <button type="button" class="btn btn-primary btn-sm" id="nextBtn" onclick="wizardStep(1)">
                Next →
            </button>
        </div>

    </div>{{-- .diy-wizard-card --}}

    {{-- Testimonials / social proof --}}
    <div class="diy-social-proof">
        <div class="proof-item"><i class="fas fa-star text-warning"></i> "Built my whole 14-day trip in 20 minutes!" — Maria S.</div>
        <div class="proof-item"><i class="fas fa-star text-warning"></i> "The AI suggested Venice which I almost skipped. Glad I didn't!" — Carlos R.</div>
        <div class="proof-item"><i class="fas fa-star text-warning"></i> "₱185,000 all-in for 2 people. Perfectly within budget." — Jess T.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ============================================================
// Wizard state
// ============================================================
const TOTAL_STEPS = 6;
let currentStep = 1;
let currentContinent = null;

function showStep(n) {
    document.querySelectorAll('.wizard-step').forEach((s) => s.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    document.getElementById('progressBar').style.width = ((n / TOTAL_STEPS) * 100) + '%';
    document.getElementById('prevBtn').style.display = n > 1 ? '' : 'none';
    const isLast = n === TOTAL_STEPS;
    document.getElementById('nextBtn').style.display = isLast ? 'none' : '';
    updateDots();
}

function wizardStep(dir) {
    if (!validateCurrentStep()) return;
    currentStep = Math.max(1, Math.min(TOTAL_STEPS, currentStep + dir));
    showStep(currentStep);
    window.scrollTo({ top: document.querySelector('.diy-wizard-container').offsetTop - 20, behavior: 'smooth' });
}

function validateCurrentStep() {
    if (currentStep === 1) {
        const picked = document.querySelector('input[name="duration_option"]:checked');
        if (!picked) { alert('Please select a duration.'); return false; }
        if (picked.value === 'custom') {
            const cv = parseInt(document.getElementById('customDaysValue').value);
            if (isNaN(cv) || cv < 3 || cv > 60) { alert('Please enter a valid number of days (3–60).'); return false; }
            document.getElementById('durationHidden').value = cv;
        } else {
            document.getElementById('durationHidden').value = picked.value;
        }
    }
    if (currentStep === 2) {
        const surpriseChecked = document.getElementById('surpriseCheck').checked;
        if (!surpriseChecked && !currentContinent) {
            alert('Please select a continent first.'); return false;
        }
        const checked = document.querySelectorAll('input[name="countries[]"]:checked');
        if (!checked.length) { alert('Please select at least one country (or choose "Surprise me!").'); return false; }
    }
    if (currentStep === 3) {
        const checked = document.querySelectorAll('input[name="travel_style[]"]:checked');
        if (!checked.length) { alert('Please select at least one travel style.'); return false; }
    }
    return true;
}

function updateDots() {
    const container = document.getElementById('stepDots');
    container.className = 'wizard-step-dots';
    let html = '';
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        html += `<span class="step-dot ${i === currentStep ? 'active' : i < currentStep ? 'done' : ''}"></span>`;
    }
    container.innerHTML = html;
}

// Sync radio → hidden field on change
document.querySelectorAll('input[name="duration_option"]').forEach(r => {
    r.addEventListener('change', function () {
        if (this.value === 'custom') {
            document.getElementById('customDaysInput').style.display = 'block';
        } else {
            document.getElementById('customDaysInput').style.display = 'none';
            document.getElementById('durationHidden').value = this.value;
        }
    });
});
// Set initial hidden value from pre-checked radio
(function () {
    const init = document.querySelector('input[name="duration_option"]:checked');
    if (init && init.value !== 'custom') document.getElementById('durationHidden').value = init.value;
})();

// Country search filter
const countrySearchInput = document.getElementById('countrySearch');
const countrySearchClear = document.getElementById('countrySearchClear');
const noCountryResults   = document.getElementById('noCountryResults');

// Continent selector
function selectContinent(name) {
    currentContinent = name;
    document.getElementById('selectedContinent').value = name;
    // Update pill active state
    document.querySelectorAll('.continent-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.continent === name);
    });
    // Hide hint, show search
    document.getElementById('continentHint').style.display = 'none';
    document.getElementById('countrySearchWrap').style.display = '';
    // Uncheck countries not in this continent
    document.querySelectorAll('input[name="countries[]"]:not(#surpriseCheck)').forEach(cb => {
        const label = cb.closest('.country-option');
        if (label && label.dataset.continent !== name) cb.checked = false;
    });
    // Show only countries in this continent
    document.querySelectorAll('#countriesGrid .country-option:not(.surprise-option)').forEach(label => {
        label.style.display = label.dataset.continent === name ? '' : 'none';
    });
    // Reset search input
    countrySearchInput.value = '';
    countrySearchClear.style.display = 'none';
    noCountryResults.style.display = 'none';
}

countrySearchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    countrySearchClear.style.display = q ? '' : 'none';
    let visibleCount = 0;
    document.querySelectorAll('#countriesGrid .country-option:not(.surprise-option)').forEach(label => {
        const inContinent = !currentContinent || label.dataset.continent === currentContinent;
        if (!inContinent) { label.style.display = 'none'; return; }
        const name  = label.querySelector('.country-name').textContent.toLowerCase();
        const match = !q || name.includes(q);
        label.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });
    noCountryResults.style.display = (visibleCount === 0 && q) ? 'block' : 'none';
});

countrySearchClear.addEventListener('click', function () {
    countrySearchInput.value = '';
    this.style.display = 'none';
    document.querySelectorAll('#countriesGrid .country-option:not(.surprise-option)').forEach(l => {
        l.style.display = (!currentContinent || l.dataset.continent === currentContinent) ? '' : 'none';
    });
    noCountryResults.style.display = 'none';
    countrySearchInput.focus();
});

// Uncheck "Surprise me" when specific countries chosen
document.querySelectorAll('input[name="countries[]"]:not(#surpriseCheck)').forEach(cb => {
    cb.addEventListener('change', () => {
        if (cb.checked) document.getElementById('surpriseCheck').checked = false;
    });
});
document.getElementById('surpriseCheck').addEventListener('change', function () {
    if (this.checked) {
        document.querySelectorAll('input[name="countries[]"]:not(#surpriseCheck)').forEach(cb => cb.checked = false);
    }
});

// Group size counter
function changeGroup(delta) {
    const inp = document.getElementById('groupSize');
    const disp = document.getElementById('groupDisplay');
    let val = Math.max(1, Math.min(50, parseInt(inp.value) + delta));
    inp.value = val;
    disp.textContent = val;
}

// Form submit: show loading state
document.getElementById('diyWizardForm').addEventListener('submit', function (e) {
    if (!validateCurrentStep()) { e.preventDefault(); return; }
    // Final validation: at least one country
    const countriesChecked = document.querySelectorAll('input[name="countries[]"]:checked');
    if (!countriesChecked.length) {
        e.preventDefault(); alert('Please go back to Step 2 and select at least one country.'); return;
    }
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp; Generating your itinerary… (15–30 sec)';
});

// Init
updateDots();

// durationHidden already has name="duration_days" and is kept in sync above.
</script>
@endpush
