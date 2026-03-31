@extends('layouts.app')
@section('title', 'Destinations')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────── --}}
<div class="dest-header">
    <div class="dest-header-overlay"></div>
    <div class="container dest-header-content">
        <span class="dest-header-label"><i class="fas fa-compass"></i> Where to?</span>
        <h1 class="dest-header-title">Explore Our Destinations</h1>
        <p class="dest-header-sub">Browse the world by continent, discover countries, and find the cities on your bucket list.</p>
    </div>
</div>

@if(empty($destinations))
    <div class="container" style="padding:60px 0;text-align:center;color:#64748b;">
        <i class="fas fa-globe" style="font-size:3rem;margin-bottom:16px;opacity:.4;"></i>
        <p style="font-size:1.1rem;">No destinations available yet. Check back soon!</p>
        <a href="{{ route('tours.index') }}" class="btn btn-primary" style="margin-top:16px;">Browse All Tours</a>
    </div>
@else

{{-- ── Continent Tabs ───────────────────────────────────────── --}}
<div class="dest-tabs-wrap">
    <div class="container">
        <div class="dest-continent-tabs" role="tablist">
            @foreach($destinations as $continent => $countries)
                @php
                    $meta      = $continentMeta[$continent] ?? $continentMeta['Other'];
                    $slug      = \Illuminate\Support\Str::slug($continent);
                    $tourTotal = collect($countries)->sum('tour_count');
                @endphp
                <button class="dest-continent-tab {{ $loop->first ? 'active' : '' }}"
                        data-target="continent-{{ $slug }}"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <span class="dest-tab-icon" style="background:{{ $meta['gradient'] }}">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </span>
                    <span class="dest-tab-name">{{ $continent }}</span>
                    <span class="dest-tab-count">{{ $tourTotal }} {{ $tourTotal === 1 ? 'tour' : 'tours' }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Countries Grid ───────────────────────────────────────── --}}
<div class="dest-body">
    <div class="container">
        @foreach($destinations as $continent => $countries)
            @php
                $slug = \Illuminate\Support\Str::slug($continent);
                $meta = $continentMeta[$continent] ?? $continentMeta['Other'];
            @endphp
            <div class="dest-section {{ $loop->first ? '' : 'd-none' }}"
                 id="continent-{{ $slug }}"
                 role="tabpanel">

                <div class="dest-section-header">
                    <h2><i class="fas {{ $meta['icon'] }}" style="margin-right:10px;"></i>{{ $continent }}</h2>
                    <span class="dest-section-subtitle">
                        {{ count($countries) }} {{ count($countries) === 1 ? 'country' : 'countries' }} &middot;
                        {{ collect($countries)->sum('city_count') }} cities
                    </span>
                </div>

                <div class="dest-countries-grid">
                    @foreach($countries as $country => $data)
                        <div class="dest-country-card" tabindex="0" role="button" aria-expanded="false">

                            {{-- Thumbnail --}}
                            <div class="dest-country-thumb">
                                @if($data['image'])
                                    <div class="dest-country-bg"
                                         style="background-image:url('{{ $data['image'] }}')"></div>
                                @else
                                    <div class="dest-country-bg"
                                         style="{{ $meta['gradient'] }};background-size:cover;"></div>
                                @endif
                                <div class="dest-country-overlay"></div>

                                <div class="dest-country-info">
                                    <h3 class="dest-country-name">{{ $country }}</h3>
                                    <div class="dest-country-meta">
                                        <span><i class="fas fa-city"></i>
                                            {{ $data['city_count'] }} {{ $data['city_count'] === 1 ? 'city' : 'cities' }}
                                        </span>
                                        <span><i class="fas fa-route"></i>
                                            {{ $data['tour_count'] }} {{ $data['tour_count'] === 1 ? 'tour' : 'tours' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="dest-expand-btn" aria-hidden="true">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>

                            {{-- Expandable cities panel --}}
                            <div class="dest-cities-panel">
                                <div class="dest-cities-inner">
                                    <p class="dest-cities-label">
                                        <i class="fas fa-map-marked-alt"></i> Cities in {{ $country }}
                                    </p>
                                    <div class="dest-city-tags">
                                        @foreach($data['cities'] as $city)
                                            <a href="{{ route('tours.index', ['search' => $city]) }}"
                                               class="dest-city-tag">
                                                <i class="fas fa-map-pin"></i> {{ $city }}
                                            </a>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('tours.index', ['search' => $country]) }}"
                                       class="dest-see-tours-btn">
                                        <i class="fas fa-suitcase-rolling"></i>
                                        View all {{ $country }} tours
                                    </a>
                                </div>
                            </div>

                        </div>{{-- end .dest-country-card --}}
                    @endforeach
                </div>{{-- end .dest-countries-grid --}}

            </div>{{-- end .dest-section --}}
        @endforeach
    </div>
</div>

@endif {{-- end if destinations --}}

@push('scripts')
<script>
(function () {
    // ── Continent tab switching ────────────────────────────────
    const tabs     = document.querySelectorAll('.dest-continent-tab');
    const sections = document.querySelectorAll('.dest-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
            sections.forEach(s => s.classList.add('d-none'));

            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');

            const target = document.getElementById(tab.dataset.target);
            if (target) target.classList.remove('d-none');
        });
    });

    // ── Country card expand / collapse ────────────────────────
    document.querySelectorAll('.dest-country-card').forEach(card => {
        function toggle(e) {
            // Don't collapse when clicking a city tag or the see-tours button
            if (e.target.closest('.dest-city-tag') || e.target.closest('.dest-see-tours-btn')) return;
            const expanded = card.getAttribute('aria-expanded') === 'true';
            card.setAttribute('aria-expanded', String(!expanded));
            card.classList.toggle('expanded', !expanded);
        }
        card.addEventListener('click', toggle);
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(e); }
        });
    });
})();
</script>
@endpush

@endsection
