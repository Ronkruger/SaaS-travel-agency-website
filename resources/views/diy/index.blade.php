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
                    <button type="button" class="continent-tab" data-continent="Africa &amp; Oceania" onclick="selectContinent('Africa &amp; Oceania')">🌍 Africa &amp; Oceania</button>
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

                {{-- Recommended tourist spots --}}
                <div id="spotsSuggestions" style="display:none; margin-top:1.5rem">
                    <div class="spots-header">
                        <span class="spots-heading">✨ Popular Spots</span>
                        <span class="spots-subheading"> — tap to add to your must-visit list</span>
                    </div>
                    <div class="spots-scroll" id="spotsGrid"></div>
                    <p id="spotsNote" class="spots-note" style="display:none"></p>
                </div>
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
                    <small class="spots-prefill-note" id="spotsPrefillNote" style="display:none; color:var(--diy-primary); margin-top:.35rem; display:none">
                        ✨ Pre-filled from your spot selections — feel free to edit or add more.
                    </small>
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
// ============================================================
// Tourist spots data
// ============================================================
const TOURIST_SPOTS = {
    // EUROPE
    'France':       [
        { name: 'Eiffel Tower',          city: 'Paris',       img: '1511739001486-6bfe10ce785f' },
        { name: 'Louvre Museum',         city: 'Paris',       img: '1499856408634-8c0195f2f4c6' },
        { name: 'Palace of Versailles',  city: 'Versailles',  img: '1590682680695-43b964a3ae17' },
        { name: 'Mont Saint-Michel',     city: 'Normandy',    img: '1540979388789-6cee28a1cdc9' },
    ],
    'Switzerland':  [
        { name: 'Matterhorn',            city: 'Zermatt',     img: '1531366936337-7c912a4589a7' },
        { name: 'Lake Geneva',           city: 'Geneva',      img: '1506905925346-21bda4d32df4' },
        { name: 'Jungfrau',              city: 'Interlaken',  img: '1464822759023-fed622ff2c3b' },
    ],
    'Italy':        [
        { name: 'Colosseum',             city: 'Rome',        img: '1552832230-c0197dd311b5' },
        { name: 'Venice Canals',         city: 'Venice',      img: '1514890547357-a9ee288728b0' },
        { name: 'Amalfi Coast',          city: 'Amalfi',      img: '1520637902986-42b5abdfee89' },
        { name: 'Florence Cathedral',    city: 'Florence',    img: '1562571052-7b13de1a4a4c' },
    ],
    'Germany':      [
        { name: 'Neuschwanstein Castle', city: 'Bavaria',     img: '1467269204594-9661b134dd2b' },
        { name: 'Brandenburg Gate',      city: 'Berlin',      img: '1560969184-10fe8719e047' },
        { name: 'Rhine Valley',          city: 'Rhineland',   img: '1537953773345-d172ccf13cf1' },
    ],
    'Austria':      [
        { name: 'Hallstatt Village',     city: 'Hallstatt',   img: '1527168027773-0cc890c4f212' },
        { name: 'Schönbrunn Palace',     city: 'Vienna',      img: '1570295999919-56ceb5ecca61' },
        { name: 'Salzburg Old Town',     city: 'Salzburg',    img: '1526656556516-cde8fbe31f20' },
    ],
    'Spain':        [
        { name: 'Sagrada Família',       city: 'Barcelona',   img: '1539037116277-4db20889f2d4' },
        { name: 'Alhambra Palace',       city: 'Granada',     img: '1558618666-fcd25c85cd64' },
        { name: 'Park Güell',            city: 'Barcelona',   img: '1511877325718-9d357e1adb50' },
    ],
    'Netherlands':  [
        { name: 'Amsterdam Canals',      city: 'Amsterdam',   img: '1534351590666-13e3e96b5017' },
        { name: 'Keukenhof Gardens',     city: 'Lisse',       img: '1465519400578-c30745f15a97' },
        { name: 'Kinderdijk Windmills',  city: 'Kinderdijk',  img: '1558642452-9d2a7deb7f62' },
    ],
    'Portugal':     [
        { name: 'Belém Tower',           city: 'Lisbon',      img: '1513622470522-26c3c8a854bc' },
        { name: 'Palácio da Pena',       city: 'Sintra',      img: '1573990977791-1a3e5c1d58c5' },
        { name: 'Douro Valley',          city: 'Porto',       img: '1539782034935-c9e484b3ea8e' },
    ],
    'Greece':       [
        { name: 'Acropolis',             city: 'Athens',      img: '1555993539-1732b0258235' },
        { name: 'Santorini',             city: 'Oia',         img: '1570077188670-e3a8d69ac5ff' },
        { name: 'Meteora Monasteries',   city: 'Kalambaka',   img: '1563385862897-00cb4c6f8e34' },
    ],
    'Belgium':      [
        { name: 'Grand Place',           city: 'Brussels',    img: '1558430665-6ddd08021db2' },
        { name: 'Bruges Canals',         city: 'Bruges',      img: '1559181939-5cf09fce63da' },
        { name: 'Atomium',               city: 'Brussels',    img: '1563387641695-4f744e8b050f' },
    ],
    'Czech Republic': [
        { name: 'Charles Bridge',        city: 'Prague',      img: '1541849546-216549ae216d' },
        { name: 'Prague Castle',         city: 'Prague',      img: '1592905884741-54e0fe9a1e0b' },
        { name: 'Old Town Square',       city: 'Prague',      img: '1489562893-7a91f44ff4bd' },
    ],
    'Hungary':      [
        { name: 'Parliament Building',   city: 'Budapest',    img: '1526044975702-e8ac21f0c60e' },
        { name: "Fisherman's Bastion",   city: 'Budapest',    img: '1549049950-48d5887197a0' },
        { name: 'Széchenyi Bath',        city: 'Budapest',    img: '1565073624497-7e91b8fec4a7' },
    ],
    'Croatia':      [
        { name: 'Dubrovnik Old Town',    city: 'Dubrovnik',   img: '1541800025783-5ef9b11fdf7f' },
        { name: 'Plitvice Lakes',        city: 'Plitvice',    img: '1523592121529-f6dde35f079e' },
        { name: 'Hvar Island',           city: 'Hvar',        img: '1537936585454-eb7e664bbda3' },
    ],
    'Poland':       [
        { name: 'Wawel Castle',          city: 'Kraków',      img: '1565006188261-91b89c54fcfa' },
        { name: 'Wieliczka Salt Mine',   city: 'Kraków',      img: '1590418606746-018840f9ffe0' },
        { name: 'Old Town Warsaw',       city: 'Warsaw',      img: '1583422409186-a09de4a98b97' },
    ],
    'Denmark':      [
        { name: 'Nyhavn Harbour',        city: 'Copenhagen',  img: '1513622470522-26c3c8a854bc' },
        { name: 'Tivoli Gardens',        city: 'Copenhagen',  img: '1584813470613-b3d2df72b427' },
        { name: 'The Little Mermaid',    city: 'Copenhagen',  img: '1530521954074-e64f6810b32d' },
    ],
    'Sweden':       [
        { name: 'Gamla Stan',            city: 'Stockholm',   img: '1509356843151-3e7d96241e11' },
        { name: 'Northern Lights',       city: 'Lapland',     img: '1531366936337-7c912a4589a7' },
        { name: 'ABBA Museum',           city: 'Stockholm',   img: '1578926288207-a90a5366b1a4' },
    ],
    'Ireland':      [
        { name: 'Cliffs of Moher',       city: 'County Clare',img: '1564959130747-897fb406b9af' },
        { name: "Giant's Causeway",      city: 'Antrim',      img: '1531761535209-83ce3a6a676c' },
        { name: 'Trinity College',       city: 'Dublin',      img: '1549887534-1541e9326642' },
    ],
    'Slovakia':     [
        { name: 'Bratislava Castle',     city: 'Bratislava',  img: '1582266255765-fa5cf1a1d501' },
        { name: 'High Tatras',           city: 'Poprad',      img: '1464822759023-fed622ff2c3b' },
    ],
    // ASIA
    'Japan':        [
        { name: 'Mount Fuji',            city: 'Fujikawaguchiko', img: '1490806843957-31f4c9a91c65' },
        { name: 'Fushimi Inari Shrine',  city: 'Kyoto',           img: '1528360983277-13d401cdc186' },
        { name: 'Shibuya Crossing',      city: 'Tokyo',           img: '1540959733332-eab0ea2ee9d3' },
        { name: 'Arashiyama Bamboo',     city: 'Kyoto',           img: '1542640244-7e672d6cef4e' },
    ],
    'South Korea':  [
        { name: 'Gyeongbokgung Palace',  city: 'Seoul',       img: '1517154421773-0855e85ec7e6' },
        { name: 'Bukchon Hanok Village', city: 'Seoul',       img: '1578662996442-48f60103fc96' },
        { name: 'Jeju Seongsan',         city: 'Jeju',        img: '1547036967-23d11aacaee0' },
    ],
    'Thailand':     [
        { name: 'Grand Palace',          city: 'Bangkok',     img: '1528181304800-259b08848526' },
        { name: 'Phi Phi Islands',       city: 'Krabi',       img: '1519046904884-53103b34b206' },
        { name: 'Wat Doi Suthep',        city: 'Chiang Mai',  img: '1555400185-b2e063bb5e6e' },
    ],
    'Vietnam':      [
        { name: 'Ha Long Bay',           city: 'Quảng Ninh',  img: '1573843981267-be1999ff37cd' },
        { name: 'Hoi An Old Town',       city: 'Hội An',      img: '1558618687-98e2b67ee3a7' },
        { name: 'Sapa Rice Terraces',    city: 'Sapa',        img: '1516026672322-bc52d61a55d5' },
    ],
    'Indonesia':    [
        { name: 'Bali Rice Terraces',    city: 'Ubud, Bali',  img: '1537996194471-e657df975ab4' },
        { name: 'Borobudur Temple',      city: 'Magelang',    img: '1580393602453-97b7dec7da72' },
        { name: 'Mount Bromo',           city: 'East Java',   img: '1518548419970-58e3b4079ab2' },
    ],
    'Philippines':  [
        { name: 'El Nido, Palawan',      city: 'El Nido',     img: '1559494007-9f172d91b8a7' },
        { name: 'Chocolate Hills',       city: 'Bohol',       img: '1539150808760-2e5df11fd3d5' },
        { name: 'Intramuros',            city: 'Manila',      img: '1565006188261-91b89c54fcfa' },
    ],
    'Singapore':    [
        { name: 'Marina Bay Sands',      city: 'Downtown Core',  img: '1525625293133-3803b08f67f1' },
        { name: 'Gardens by the Bay',    city: 'Marina Bay',     img: '1501386761578-eac5c94b800a' },
        { name: 'Sentosa Island',        city: 'Sentosa',        img: '1569878050761-c8ae14eb1ea0' },
    ],
    'Malaysia':     [
        { name: 'Petronas Twin Towers',  city: 'Kuala Lumpur',img: '1556611832-cf2c2ed7e2be' },
        { name: 'Penang Georgetown',     city: 'Penang',      img: '1533900298834-ad37a4d7bba6' },
        { name: 'Langkawi Island',       city: 'Langkawi',    img: '1493752603190-0b75d86a1b78' },
    ],
    'China':        [
        { name: 'Great Wall',            city: 'Beijing',     img: '1508804185872-173df2f45d5e' },
        { name: 'Forbidden City',        city: 'Beijing',     img: '1537115938432-0b30bc53e8c9' },
        { name: 'Zhangjiajie',           city: 'Hunan',       img: '1501685532562-aa6846b14a7e' },
        { name: 'West Lake',             city: 'Hangzhou',    img: '1543190907-9f7d4d27c8c4' },
    ],
    'India':        [
        { name: 'Taj Mahal',             city: 'Agra',        img: '1564507592333-c60657eea523' },
        { name: 'Jaipur Pink City',      city: 'Jaipur',      img: '1477587458883-47145ed6736c' },
        { name: 'Kerala Backwaters',     city: 'Alappuzha',   img: '1602216056096-3b40cc0c9944' },
        { name: 'Golden Temple',         city: 'Amritsar',    img: '1588416499018-d8c621e7d2c2' },
    ],
    'Nepal':        [
        { name: 'Everest Base Camp',     city: 'Khumbu',      img: '1516912481800-0788d7cd7c8a' },
        { name: 'Phewa Lake',            city: 'Pokhara',     img: '1506905925346-21bda4d32df4' },
        { name: 'Boudhanath Stupa',      city: 'Kathmandu',   img: '1583422409186-a09de4a98b97' },
    ],
    'Sri Lanka':    [
        { name: 'Sigiriya Rock',         city: 'Dambulla',    img: '1588681664899-f142ff2dc9b1' },
        { name: 'Nine Arch Bridge',      city: 'Ella',        img: '1566296302710-52eb2e97adce' },
        { name: 'Temple of the Tooth',   city: 'Kandy',       img: '1583417319070-4a69db38a482' },
    ],
    'Cambodia':     [
        { name: 'Angkor Wat',            city: 'Siem Reap',   img: '1508009603885-50cf7c579365' },
        { name: 'Bayon Temple',          city: 'Siem Reap',   img: '1600456899121-68eda5b33cf7' },
        { name: 'Royal Palace',          city: 'Phnom Penh',  img: '1597838816882-4435b1977fbe' },
    ],
    'Myanmar':      [
        { name: 'Bagan Temples',         city: 'Bagan',       img: '1494548162494-384bba4ab999' },
        { name: 'Inle Lake',             city: 'Nyaungshwe',  img: '1552465011-b4e21bf6e79a' },
        { name: 'Shwedagon Pagoda',      city: 'Yangon',      img: '1531169509526-f8f1dfe08c56' },
    ],
    'Maldives':     [
        { name: 'Overwater Bungalows',   city: 'North Malé Atoll', img: '1540202404-a2f29016b523' },
        { name: 'Coral Reef Snorkelling',city: 'Ari Atoll',        img: '1559494007-9f172d91b8a7' },
        { name: 'Bioluminescent Beach',  city: 'Vaadhoo Island',   img: '1573228486375-d1f5e3d0d2f7' },
    ],
    // MIDDLE EAST
    'UAE':          [
        { name: 'Burj Khalifa',          city: 'Dubai',       img: '1512453979798-5ea266f8880c' },
        { name: 'Desert Safari',         city: 'Dubai',       img: '1451337516015-6b6e9a44a8a3' },
        { name: 'Sheikh Zayed Mosque',   city: 'Abu Dhabi',   img: '1563201515-33d30a83b1ff' },
    ],
    'Turkey':       [
        { name: 'Hagia Sophia',          city: 'Istanbul',    img: '1527838832700-5059252407fa' },
        { name: 'Cappadocia Balloons',   city: 'Göreme',      img: '1565073624565-0c73a7834b3d' },
        { name: 'Pamukkale',             city: 'Denizli',     img: '1578895949274-7756a0a3f77a' },
    ],
    'Jordan':       [
        { name: 'Petra — Rose City',     city: 'Petra',       img: '1548786811-dd6e453ccca7' },
        { name: 'Wadi Rum Desert',       city: 'Aqaba',       img: '1451337516015-6b6e9a44a8a3' },
        { name: 'Dead Sea Float',        city: 'Dead Sea',    img: '1579532582937-16c108930bf6' },
    ],
    'Qatar':        [
        { name: 'Museum of Islamic Art', city: 'Doha',        img: '1553913861-c0fddf2619ee' },
        { name: 'The Pearl',             city: 'Doha',        img: '1525625293133-3803b08f67f1' },
        { name: 'Souq Waqif',            city: 'Doha',        img: '1597212618440-806262de4f0e' },
    ],
    'Israel':       [
        { name: 'Western Wall',          city: 'Jerusalem',   img: '1558452919-08ae4aea8e29' },
        { name: 'Dead Sea',              city: 'Ein Bokek',   img: '1579532582937-16c108930bf6' },
        { name: 'Masada Fortress',       city: 'Masada',      img: '1548786811-dd6e453ccca7' },
    ],
    'Oman':         [
        { name: 'Sultan Qaboos Mosque',  city: 'Muscat',      img: '1563201515-33d30a83b1ff' },
        { name: 'Wahiba Sands',          city: 'Al Sharqiyah',img: '1451337516015-6b6e9a44a8a3' },
        { name: 'Wadi Shab',             city: 'Sur',         img: '1537953773345-d172ccf13cf1' },
    ],
    // AMERICAS
    'United States':[
        { name: 'Grand Canyon',          city: 'Arizona',     img: '1509316785289-025f5b846b35' },
        { name: 'New York City',         city: 'New York',    img: '1496442226666-8d4d0e62e6e9' },
        { name: 'Golden Gate Bridge',    city: 'San Francisco', img: '1501594907352-04cda38ebc29' },
        { name: 'Yellowstone',           city: 'Wyoming',     img: '1464822759023-fed622ff2c3b' },
    ],
    'Canada':       [
        { name: 'Banff National Park',   city: 'Alberta',     img: '1516912481800-0788d7cd7c8a' },
        { name: 'Niagara Falls',         city: 'Ontario',     img: '1564339543225-21bb89e80f4b' },
        { name: 'Old Quebec City',       city: 'Québec',      img: '1583422409186-a09de4a98b97' },
    ],
    'Mexico':       [
        { name: 'Chichen Itza',          city: 'Yucatán',     img: '1568322425220-6b60fc3a8c5f' },
        { name: 'Tulum Ruins',           city: 'Tulum',       img: '1543632968-9e5f4e0c9c6e' },
        { name: 'Cenotes',               city: 'Yucatán',     img: '1554188248-986adbb73be4' },
    ],
    'Brazil':       [
        { name: 'Christ the Redeemer',   city: 'Rio de Janeiro', img: '1544494083-7b7a71ed7a14' },
        { name: 'Iguazu Falls',          city: 'Paraná',      img: '1547013406-5d8c89f2b898' },
        { name: 'Amazon Rainforest',     city: 'Manaus',      img: '1586348943529-beaae6c28db9' },
    ],
    'Argentina':    [
        { name: 'Perito Moreno Glacier', city: 'Patagonia',   img: '1519214605650-76b2f8f2ace2' },
        { name: 'Buenos Aires',          city: 'Buenos Aires',img: '1589909202802-8f4aadce1849' },
        { name: 'Iguazu Falls',          city: 'Misiones',    img: '1547013406-5d8c89f2b898' },
    ],
    'Peru':         [
        { name: 'Machu Picchu',          city: 'Cusco Region',img: '1526392060635-9d6019884377' },
        { name: 'Cusco Sacred Valley',   city: 'Cusco',       img: '1580522154071-c6ca47a859ad' },
        { name: 'Lake Titicaca',         city: 'Puno',        img: '1537953773345-d172ccf13cf1' },
    ],
    'Colombia':     [
        { name: 'Cartagena Old Town',    city: 'Cartagena',   img: '1583202893284-ef8e2499459c' },
        { name: 'Coffee Region',         city: 'Manizales',   img: '1568702846714-dd40b1cc3c44' },
        { name: 'Lost City',             city: 'Santa Marta', img: '1586348943529-beaae6c28db9' },
    ],
    'Chile':        [
        { name: 'Torres del Paine',      city: 'Patagonia',   img: '1510766857978-c3de70c5b8ab' },
        { name: 'Atacama Desert',        city: 'San Pedro',   img: '1451337516015-6b6e9a44a8a3' },
        { name: 'Easter Island',         city: 'Rapa Nui',    img: '1586348943529-beaae6c28db9' },
    ],
    // AFRICA & OCEANIA
    'Morocco':      [
        { name: 'Djemaa el-Fna',         city: 'Marrakech',   img: '1597212618440-806262de4f0e' },
        { name: 'Sahara Dunes',          city: 'Merzouga',    img: '1451337516015-6b6e9a44a8a3' },
        { name: 'Fes Medina',            city: 'Fes',         img: '1553913861-c0fddf2619ee' },
    ],
    'South Africa': [
        { name: 'Table Mountain',        city: 'Cape Town',   img: '1580060839134-75a5edca2e99' },
        { name: 'Kruger Park Safari',    city: 'Limpopo',     img: '1549366021-58eeafc8f75a' },
        { name: 'Cape of Good Hope',     city: 'Cape Point',  img: '1516026672322-bc52d61a55d5' },
    ],
    'Kenya':        [
        { name: 'Maasai Mara Safari',    city: 'Narok',       img: '1549366021-58eeafc8f75a' },
        { name: 'Mount Kenya',           city: 'Central Kenya',img: '1464822759023-fed622ff2c3b' },
        { name: 'Amboseli Park',         city: 'Kajiado',     img: '1521651201144-634f700b36ef' },
    ],
    'Egypt':        [
        { name: 'Pyramids of Giza',      city: 'Giza',        img: '1539768942893-daf53e448371' },
        { name: 'Luxor Temple',          city: 'Luxor',       img: '1553913861-c0fddf2619ee' },
        { name: 'Abu Simbel',            city: 'Aswan',       img: '1572252009286-96463070e51b' },
    ],
    'Australia':    [
        { name: 'Sydney Opera House',    city: 'Sydney',      img: '1506973035872-a4ec16b8e8d9' },
        { name: 'Great Barrier Reef',    city: 'Queensland',  img: '1559494007-9f172d91b8a7' },
        { name: 'Uluru',                 city: 'N. Territory',img: '1523482580672-f109ba8cb9be' },
        { name: 'Great Ocean Road',      city: 'Victoria',    img: '1545158535-c3f7168c28b6' },
    ],
    'New Zealand':  [
        { name: 'Milford Sound',         city: 'Fiordland',   img: '1567215378620-71ab698e7e28' },
        { name: 'Hobbiton',              city: 'Matamata',    img: '1507699622108-4be3abd695ad' },
        { name: 'Tongariro Crossing',    city: 'Ruapehu',     img: '1453872302360-eed3c5f8ff66' },
    ],
};

const selectedSpots = new Set();

function renderSpots() {
    const checked = [...document.querySelectorAll('input[name="countries[]"]:checked:not(#surpriseCheck)')].map(cb => cb.value);
    const sugg = document.getElementById('spotsSuggestions');
    if (!checked.length) { sugg.style.display = 'none'; return; }
    const spots = [];
    checked.forEach(country => { if (TOURIST_SPOTS[country]) spots.push(...TOURIST_SPOTS[country]); });
    if (!spots.length) { sugg.style.display = 'none'; return; }
    sugg.style.display = '';
    document.getElementById('spotsGrid').innerHTML = spots.map(s => {
        const sel = selectedSpots.has(s.name);
        const imgUrl = `https://images.unsplash.com/photo-${s.img}?auto=format&fit=crop&w=400&q=80`;
        return `<div class="spot-card${sel ? ' selected' : ''}" data-name="${s.name.replace(/"/g,'&quot;')}" onclick="toggleSpot(this)">
            <div class="spot-img-wrap">
                <img src="${imgUrl}" alt="${s.name}" loading="lazy" onerror="this.parentNode.style.background='#e8ecef';this.style.display='none'">
            </div>
            <div class="spot-info"><strong>${s.name}</strong><span>${s.city}</span></div>
            <div class="spot-check-badge">✓</div>
        </div>`;
    }).join('');
    updateSpotsNote();
}

function toggleSpot(card) {
    const name = card.dataset.name;
    if (selectedSpots.has(name)) { selectedSpots.delete(name); card.classList.remove('selected'); }
    else { selectedSpots.add(name); card.classList.add('selected'); }
    syncSpotsToMustVisit();
    updateSpotsNote();
}

function updateSpotsNote() {
    const n = document.getElementById('spotsNote');
    if (selectedSpots.size > 0) {
        n.style.display = '';
        n.innerHTML = `<strong>${selectedSpots.size}</strong> spot${selectedSpots.size > 1 ? 's' : ''} selected — we'll add ${selectedSpots.size > 1 ? 'them' : 'it'} to your itinerary.`;
    } else {
        n.style.display = 'none';
    }
}

function syncSpotsToMustVisit() {
    const input = document.querySelector('input[name="must_visit"]');
    const note = document.getElementById('spotsPrefillNote');
    if (!input) return;
    input.value = [...selectedSpots].join(', ');
    if (note) note.style.display = selectedSpots.size > 0 ? '' : 'none';
}

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
    renderSpots();
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
        renderSpots();
    });
});
document.getElementById('surpriseCheck').addEventListener('change', function () {
    if (this.checked) {
        document.querySelectorAll('input[name="countries[]"]:not(#surpriseCheck)').forEach(cb => cb.checked = false);
        document.getElementById('spotsSuggestions').style.display = 'none';
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
