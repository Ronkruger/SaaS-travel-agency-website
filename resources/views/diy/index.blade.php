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
// img field = unique seed number → used in Unsplash keyword URL for consistent, accurate results
const TOURIST_SPOTS = {
    // EUROPE
    'France':       [
        { name: 'Eiffel Tower',          city: 'Paris',       img: 1  },
        { name: 'Louvre Museum',         city: 'Paris',       img: 2  },
        { name: 'Palace of Versailles',  city: 'Versailles',  img: 3  },
        { name: 'Mont Saint-Michel',     city: 'Normandy',    img: 4  },
    ],
    'Switzerland':  [
        { name: 'Matterhorn',            city: 'Zermatt',     img: 5  },
        { name: 'Lake Geneva',           city: 'Geneva',      img: 6  },
        { name: 'Jungfrau',              city: 'Interlaken',  img: 7  },
    ],
    'Italy':        [
        { name: 'Colosseum',             city: 'Rome',        img: 8  },
        { name: 'Venice Canals',         city: 'Venice',      img: 9  },
        { name: 'Amalfi Coast',          city: 'Amalfi',      img: 10 },
        { name: 'Florence Cathedral',    city: 'Florence',    img: 11 },
    ],
    'Germany':      [
        { name: 'Neuschwanstein Castle', city: 'Bavaria',     img: 12 },
        { name: 'Brandenburg Gate',      city: 'Berlin',      img: 13 },
        { name: 'Rhine Valley',          city: 'Rhineland',   img: 14 },
    ],
    'Austria':      [
        { name: 'Hallstatt Village',     city: 'Hallstatt',   img: 15 },
        { name: 'Schönbrunn Palace',     city: 'Vienna',      img: 16 },
        { name: 'Salzburg Old Town',     city: 'Salzburg',    img: 17 },
    ],
    'Spain':        [
        { name: 'Sagrada Família',       city: 'Barcelona',   img: 18 },
        { name: 'Alhambra Palace',       city: 'Granada',     img: 19 },
        { name: 'Park Güell',            city: 'Barcelona',   img: 20 },
    ],
    'Netherlands':  [
        { name: 'Amsterdam Canals',      city: 'Amsterdam',   img: 21 },
        { name: 'Keukenhof Gardens',     city: 'Lisse',       img: 22 },
        { name: 'Kinderdijk Windmills',  city: 'Kinderdijk',  img: 23 },
    ],
    'Portugal':     [
        { name: 'Belém Tower',           city: 'Lisbon',      img: 24 },
        { name: 'Palácio da Pena',       city: 'Sintra',      img: 25 },
        { name: 'Douro Valley',          city: 'Porto',       img: 26 },
    ],
    'Greece':       [
        { name: 'Acropolis',             city: 'Athens',      img: 27 },
        { name: 'Santorini',             city: 'Oia',         img: 28 },
        { name: 'Meteora Monasteries',   city: 'Kalambaka',   img: 29 },
    ],
    'Belgium':      [
        { name: 'Grand Place',           city: 'Brussels',    img: 30 },
        { name: 'Bruges Canals',         city: 'Bruges',      img: 31 },
        { name: 'Atomium',               city: 'Brussels',    img: 32 },
    ],
    'Czech Republic': [
        { name: 'Charles Bridge',        city: 'Prague',      img: 33 },
        { name: 'Prague Castle',         city: 'Prague',      img: 34 },
        { name: 'Old Town Square',       city: 'Prague',      img: 35 },
    ],
    'Hungary':      [
        { name: 'Parliament Building',   city: 'Budapest',    img: 36 },
        { name: "Fisherman's Bastion",   city: 'Budapest',    img: 37 },
        { name: 'Széchenyi Bath',        city: 'Budapest',    img: 38 },
    ],
    'Croatia':      [
        { name: 'Dubrovnik Old Town',    city: 'Dubrovnik',   img: 39 },
        { name: 'Plitvice Lakes',        city: 'Plitvice',    img: 40 },
        { name: 'Hvar Island',           city: 'Hvar',        img: 41 },
    ],
    'Poland':       [
        { name: 'Wawel Castle',          city: 'Kraków',      img: 42 },
        { name: 'Wieliczka Salt Mine',   city: 'Kraków',      img: 43 },
        { name: 'Old Town Warsaw',       city: 'Warsaw',      img: 44 },
    ],
    'Denmark':      [
        { name: 'Nyhavn Harbour',        city: 'Copenhagen',  img: 45 },
        { name: 'Tivoli Gardens',        city: 'Copenhagen',  img: 46 },
        { name: 'The Little Mermaid',    city: 'Copenhagen',  img: 47 },
    ],
    'Sweden':       [
        { name: 'Gamla Stan',            city: 'Stockholm',   img: 48 },
        { name: 'Northern Lights',       city: 'Lapland',     img: 49 },
        { name: 'ABBA Museum',           city: 'Stockholm',   img: 50 },
    ],
    'Ireland':      [
        { name: 'Cliffs of Moher',       city: 'County Clare',img: 51 },
        { name: "Giant's Causeway",      city: 'Antrim',      img: 52 },
        { name: 'Trinity College',       city: 'Dublin',      img: 53 },
    ],
    'Slovakia':     [
        { name: 'Bratislava Castle',     city: 'Bratislava',  img: 54 },
        { name: 'High Tatras',           city: 'Poprad',      img: 55 },
    ],
    // ASIA
    'Japan':        [
        { name: 'Mount Fuji',            city: 'Fujikawaguchiko', img: 56 },
        { name: 'Fushimi Inari Shrine',  city: 'Kyoto',           img: 57 },
        { name: 'Shibuya Crossing',      city: 'Tokyo',           img: 58 },
        { name: 'Arashiyama Bamboo',     city: 'Kyoto',           img: 59 },
    ],
    'South Korea':  [
        { name: 'Gyeongbokgung Palace',  city: 'Seoul',       img: 60 },
        { name: 'Bukchon Hanok Village', city: 'Seoul',       img: 61 },
        { name: 'Jeju Seongsan',         city: 'Jeju',        img: 62 },
    ],
    'Thailand':     [
        { name: 'Grand Palace',          city: 'Bangkok',     img: 63 },
        { name: 'Phi Phi Islands',       city: 'Krabi',       img: 64 },
        { name: 'Wat Doi Suthep',        city: 'Chiang Mai',  img: 65 },
    ],
    'Vietnam':      [
        { name: 'Ha Long Bay',           city: 'Quảng Ninh',  img: 66 },
        { name: 'Hoi An Old Town',       city: 'Hoi An',      img: 67 },
        { name: 'Sapa Rice Terraces',    city: 'Sapa',        img: 68 },
    ],
    'Indonesia':    [
        { name: 'Bali Rice Terraces',    city: 'Ubud Bali',   img: 69 },
        { name: 'Borobudur Temple',      city: 'Magelang',    img: 70 },
        { name: 'Mount Bromo',           city: 'East Java',   img: 71 },
    ],
    'Philippines':  [
        { name: 'El Nido Palawan',       city: 'El Nido',     img: 72 },
        { name: 'Chocolate Hills',       city: 'Bohol',       img: 73 },
        { name: 'Intramuros',            city: 'Manila',      img: 74 },
    ],
    'Singapore':    [
        { name: 'Marina Bay Sands',      city: 'Singapore',   img: 75 },
        { name: 'Gardens by the Bay',    city: 'Singapore',   img: 76 },
        { name: 'Sentosa Island',        city: 'Singapore',   img: 77 },
    ],
    'Malaysia':     [
        { name: 'Petronas Twin Towers',  city: 'Kuala Lumpur',img: 78 },
        { name: 'Penang Georgetown',     city: 'Penang',      img: 79 },
        { name: 'Langkawi Island',       city: 'Langkawi',    img: 80 },
    ],
    'China':        [
        { name: 'Great Wall of China',   city: 'Beijing',     img: 81 },
        { name: 'Forbidden City',        city: 'Beijing',     img: 82 },
        { name: 'Zhangjiajie',           city: 'Hunan',       img: 83 },
        { name: 'West Lake',             city: 'Hangzhou',    img: 84 },
    ],
    'India':        [
        { name: 'Taj Mahal',             city: 'Agra',        img: 85 },
        { name: 'Jaipur Pink City',      city: 'Jaipur',      img: 86 },
        { name: 'Kerala Backwaters',     city: 'Alappuzha',   img: 87 },
        { name: 'Golden Temple',         city: 'Amritsar',    img: 88 },
    ],
    'Nepal':        [
        { name: 'Everest Base Camp',     city: 'Khumbu',      img: 89 },
        { name: 'Phewa Lake',            city: 'Pokhara',     img: 90 },
        { name: 'Boudhanath Stupa',      city: 'Kathmandu',   img: 91 },
    ],
    'Sri Lanka':    [
        { name: 'Sigiriya Rock',         city: 'Dambulla',    img: 92 },
        { name: 'Nine Arch Bridge',      city: 'Ella',        img: 93 },
        { name: 'Temple of the Tooth',   city: 'Kandy',       img: 94 },
    ],
    'Cambodia':     [
        { name: 'Angkor Wat',            city: 'Siem Reap',   img: 95 },
        { name: 'Bayon Temple',          city: 'Siem Reap',   img: 96 },
        { name: 'Royal Palace',          city: 'Phnom Penh',  img: 97 },
    ],
    'Myanmar':      [
        { name: 'Bagan Temples',         city: 'Bagan',       img: 98 },
        { name: 'Inle Lake',             city: 'Nyaungshwe',  img: 99 },
        { name: 'Shwedagon Pagoda',      city: 'Yangon',      img: 100},
    ],
    'Maldives':     [
        { name: 'Overwater Bungalows',   city: 'Maldives',    img: 101},
        { name: 'Coral Reef Snorkelling',city: 'Maldives',    img: 102},
        { name: 'Bioluminescent Beach',  city: 'Maldives',    img: 103},
    ],
    // MIDDLE EAST
    'UAE':          [
        { name: 'Burj Khalifa',          city: 'Dubai',       img: 104},
        { name: 'Dubai Desert Safari',   city: 'Dubai',       img: 105},
        { name: 'Sheikh Zayed Mosque',   city: 'Abu Dhabi',   img: 106},
    ],
    'Turkey':       [
        { name: 'Hagia Sophia',          city: 'Istanbul',    img: 107},
        { name: 'Cappadocia Balloons',   city: 'Göreme',      img: 108},
        { name: 'Pamukkale',             city: 'Denizli',     img: 109},
    ],
    'Jordan':       [
        { name: 'Petra Rose City',       city: 'Petra',       img: 110},
        { name: 'Wadi Rum Desert',       city: 'Wadi Rum',    img: 111},
        { name: 'Dead Sea Float',        city: 'Dead Sea',    img: 112},
    ],
    'Qatar':        [
        { name: 'Museum of Islamic Art', city: 'Doha',        img: 113},
        { name: 'The Pearl Qatar',       city: 'Doha',        img: 114},
        { name: 'Souq Waqif',            city: 'Doha',        img: 115},
    ],
    'Israel':       [
        { name: 'Western Wall',          city: 'Jerusalem',   img: 116},
        { name: 'Dead Sea',              city: 'Ein Bokek',   img: 117},
        { name: 'Masada Fortress',       city: 'Masada',      img: 118},
    ],
    'Oman':         [
        { name: 'Sultan Qaboos Mosque',  city: 'Muscat',      img: 119},
        { name: 'Wahiba Sands',          city: 'Oman',        img: 120},
        { name: 'Wadi Shab',             city: 'Oman',        img: 121},
    ],
    // AMERICAS
    'United States':[
        { name: 'Grand Canyon',          city: 'Arizona',     img: 122},
        { name: 'New York City',         city: 'New York',    img: 123},
        { name: 'Golden Gate Bridge',    city: 'San Francisco',img:124},
        { name: 'Yellowstone',           city: 'Wyoming',     img: 125},
    ],
    'Canada':       [
        { name: 'Banff National Park',   city: 'Alberta',     img: 126},
        { name: 'Niagara Falls',         city: 'Ontario',     img: 127},
        { name: 'Old Quebec City',       city: 'Québec',      img: 128},
    ],
    'Mexico':       [
        { name: 'Chichen Itza',          city: 'Yucatán',     img: 129},
        { name: 'Tulum Ruins',           city: 'Tulum',       img: 130},
        { name: 'Cenotes',               city: 'Yucatán',     img: 131},
    ],
    'Brazil':       [
        { name: 'Christ the Redeemer',   city: 'Rio de Janeiro',img:132},
        { name: 'Iguazu Falls',          city: 'Paraná',      img: 133},
        { name: 'Amazon Rainforest',     city: 'Manaus',      img: 134},
    ],
    'Argentina':    [
        { name: 'Perito Moreno Glacier', city: 'Patagonia',   img: 135},
        { name: 'Buenos Aires',          city: 'Buenos Aires',img: 136},
        { name: 'Iguazu Falls Argentina',city: 'Misiones',    img: 137},
    ],
    'Peru':         [
        { name: 'Machu Picchu',          city: 'Cusco',       img: 138},
        { name: 'Cusco Sacred Valley',   city: 'Cusco',       img: 139},
        { name: 'Lake Titicaca',         city: 'Puno',        img: 140},
    ],
    'Colombia':     [
        { name: 'Cartagena Old Town',    city: 'Cartagena',   img: 141},
        { name: 'Coffee Region',         city: 'Manizales',   img: 142},
        { name: 'Lost City',             city: 'Santa Marta', img: 143},
    ],
    'Chile':        [
        { name: 'Torres del Paine',      city: 'Patagonia',   img: 144},
        { name: 'Atacama Desert',        city: 'San Pedro',   img: 145},
        { name: 'Easter Island',         city: 'Rapa Nui',    img: 146},
    ],
    // AFRICA & OCEANIA
    'Morocco':      [
        { name: 'Djemaa el-Fna',         city: 'Marrakech',   img: 147},
        { name: 'Sahara Dunes',          city: 'Merzouga',    img: 148},
        { name: 'Fes Medina',            city: 'Fes',         img: 149},
    ],
    'South Africa': [
        { name: 'Table Mountain',        city: 'Cape Town',   img: 150},
        { name: 'Kruger Park Safari',    city: 'Limpopo',     img: 151},
        { name: 'Cape of Good Hope',     city: 'Cape Town',   img: 152},
    ],
    'Kenya':        [
        { name: 'Maasai Mara Safari',    city: 'Narok',       img: 153},
        { name: 'Mount Kenya',           city: 'Kenya',       img: 154},
        { name: 'Amboseli Park',         city: 'Kajiado',     img: 155},
    ],
    'Egypt':        [
        { name: 'Pyramids of Giza',      city: 'Giza',        img: 156},
        { name: 'Luxor Temple',          city: 'Luxor',       img: 157},
        { name: 'Abu Simbel',            city: 'Aswan',       img: 158},
    ],
    'Australia':    [
        { name: 'Sydney Opera House',    city: 'Sydney',      img: 159},
        { name: 'Great Barrier Reef',    city: 'Queensland',  img: 160},
        { name: 'Uluru',                 city: 'Northern Territory',img:161},
        { name: 'Great Ocean Road',      city: 'Victoria',    img: 162},
    ],
    'New Zealand':  [
        { name: 'Milford Sound',         city: 'Fiordland',   img: 163},
        { name: 'Hobbiton',              city: 'Matamata',    img: 164},
        { name: 'Tongariro Crossing',    city: 'Ruapehu',     img: 165},
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
        const keyword = encodeURIComponent(s.name + ' ' + s.city);
        const imgUrl = `https://source.unsplash.com/400x300/?${keyword}&sig=${s.img}`;
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
