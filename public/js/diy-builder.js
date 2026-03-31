/**
 * DIY Tour Builder — Interactive Map + Itinerary Editor
 *
 * Reads data attributes from #diyBuilder to bootstrap state.
 * Communicates with the server via the CSRF-authenticated AJAX routes.
 */
(function () {
    'use strict';

    // =========================================================================
    // State
    // =========================================================================
    const el = document.getElementById('diyBuilder');
    if (!el) return;

    let itinerary   = JSON.parse(el.dataset.itinerary  || '{}');
    let pricing     = JSON.parse(el.dataset.pricing    || '{}');
    let prefs       = JSON.parse(el.dataset.prefs      || '{}');
    const TOKEN     = el.dataset.session;
    const SAVE_URL  = el.dataset.saveUrl;
    const SUGG_URL  = el.dataset.suggestionsUrl;
    const OPT_URL      = el.dataset.optimizeUrl;
    const PRICE_URL    = el.dataset.pricingUrl;
    const VAL_URL      = el.dataset.validateUrl;
    const REACH_URL    = el.dataset.reachableUrl;
    const MAPBOX_TK = el.dataset.mapboxToken;
    const CSRF      = document.querySelector('meta[name="csrf-token"]').content;

    let map = null;
    let markers = [];
    let routeLayerAdded = false;
    let autoSaveTimer = null;
    let isDirty = false;

    // Well-known coordinates for quick city lookups
    const CITY_COORDS = {
        'Paris': [2.3522, 48.8566], 'Amsterdam': [4.9041, 52.3676],
        'Brussels': [4.3517, 50.8503], 'Barcelona': [2.1734, 41.3851],
        'Madrid': [-3.7038, 40.4168], 'Lisbon': [-9.1393, 38.7223],
        'Geneva': [6.1432, 46.2044], 'Zurich': [8.5417, 47.3769],
        'Lucerne': [8.3093, 47.0502], 'Interlaken': [7.8632, 46.6863],
        'Bern': [7.4474, 46.9480], 'Milan': [9.1859, 45.4654],
        'Venice': [12.3155, 45.4408], 'Florence': [11.2558, 43.7696],
        'Rome': [12.4964, 41.9028], 'Naples': [14.2681, 40.8522],
        'Vienna': [16.3738, 48.2082], 'Salzburg': [13.0550, 47.8095],
        'Prague': [14.4378, 50.0755], 'Budapest': [19.0402, 47.4979],
        'Berlin': [13.4050, 52.5200], 'Munich': [11.5820, 48.1351],
        'Dubrovnik': [18.0944, 42.6507], 'Athens': [23.7275, 37.9838],
        'Santorini': [25.4615, 36.3932], 'Porto': [-8.6291, 41.1579],
        'Seville': [-5.9845, 37.3891], 'Copenhagen': [12.5683, 55.6761],
        'Stockholm': [18.0686, 59.3293], 'Nice': [7.2620, 43.7102],
        'Annecy': [6.1292, 45.8992], 'Hallstatt': [13.6493, 47.5620],
    };

    // =========================================================================
    // Init
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        initMap();
        renderCityList();
        renderPricing();
        renderValidation();
        startAutoSave();
    });

    // =========================================================================
    // Mapbox Map
    // =========================================================================
    function initMap() {
        if (!MAPBOX_TK) {
            document.getElementById('diyMap').innerHTML =
                '<div class="map-unavailable"><i class="fas fa-map"></i><p>Map unavailable — Mapbox token not configured.</p></div>';
            return;
        }

        mapboxgl.accessToken = MAPBOX_TK;

        const center = getMapCenter();
        map = new mapboxgl.Map({
            container: 'diyMap',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: center,
            zoom: 4,
        });

        map.addControl(new mapboxgl.NavigationControl(), 'top-right');
        map.addControl(new mapboxgl.FullscreenControl(), 'top-right');

        map.on('load', function () {
            renderMapMarkers();
            renderMapRoute();
        });

        // Click map to show "Add city here" suggestion
        map.on('click', function (e) {
            const nearest = findNearestKnownCity(e.lngLat.lng, e.lngLat.lat);
            if (nearest) {
                showMapClickSuggestion(nearest, e.lngLat);
            }
        });
    }

    function getMapCenter() {
        const cities = getCitiesFromItinerary();
        if (!cities.length) return [10, 48];
        const lngs = cities.map(c => c.lng).filter(Boolean);
        const lats = cities.map(c => c.lat).filter(Boolean);
        if (!lngs.length) return [10, 48];
        return [
            lngs.reduce((a, b) => a + b) / lngs.length,
            lats.reduce((a, b) => a + b) / lats.length,
        ];
    }

    function getCitiesFromItinerary() {
        const days = (itinerary.day_by_day || []);
        const seen = {};
        const cities = [];
        days.forEach(function (d) {
            if (!seen[d.city]) {
                seen[d.city] = true;
                const coords = CITY_COORDS[d.city];
                cities.push({
                    name:    d.city,
                    country: d.country,
                    day:     d.day,
                    lng:     coords ? coords[0] : null,
                    lat:     coords ? coords[1] : null,
                });
            }
        });
        return cities;
    }

    function renderMapMarkers() {
        // Remove existing markers
        markers.forEach(function (m) { m.remove(); });
        markers = [];

        const cities = getCitiesFromItinerary();
        cities.forEach(function (city, idx) {
            if (!city.lat) return;

            const markerEl = document.createElement('div');
            markerEl.className = 'diy-map-marker';
            markerEl.innerHTML =
                '<span class="marker-num">' + (idx + 1) + '</span>' +
                '<span class="marker-name">' + city.name + '</span>';

            const popup = new mapboxgl.Popup({ offset: 25 }).setHTML(
                '<div class="map-popup">' +
                '<h4>' + city.name + ', ' + city.country + '</h4>' +
                '<p>Day ' + city.day + '</p>' +
                '<button onclick="openEditCityFromMap(\'' + city.name + '\')" class="btn btn-sm btn-outline">Edit</button>' +
                '</div>'
            );

            const marker = new mapboxgl.Marker({ element: markerEl, draggable: false })
                .setLngLat([city.lng, city.lat])
                .setPopup(popup)
                .addTo(map);

            markers.push(marker);
        });
    }

    function renderMapRoute() {
        const cities = getCitiesFromItinerary().filter(c => c.lat);
        if (cities.length < 2) return;

        const coordinates = cities.map(c => [c.lng, c.lat]);
        const geojson = {
            type: 'Feature',
            geometry: { type: 'LineString', coordinates: coordinates },
        };

        if (map.getSource('diy-route')) {
            map.getSource('diy-route').setData(geojson);
            return;
        }

        map.addSource('diy-route', { type: 'geojson', data: geojson });

        // Background (thicker, lighter) line
        map.addLayer({
            id: 'diy-route-bg',
            type: 'line',
            source: 'diy-route',
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#a0c4f8', 'line-width': 8, 'line-opacity': 0.4 },
        });

        // Foreground (animated dashes)
        map.addLayer({
            id: 'diy-route-line',
            type: 'line',
            source: 'diy-route',
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: {
                'line-color': '#3498db',
                'line-width': 3,
                'line-opacity': 0.9,
                'line-dasharray': [2, 4],
            },
        });

        routeLayerAdded = true;

        // Fit map to route bounds
        const bounds = coordinates.reduce(
            function (b, c) { return b.extend(c); },
            new mapboxgl.LngLatBounds(coordinates[0], coordinates[0])
        );
        map.fitBounds(bounds, { padding: 60 });
    }

    function refreshMap() {
        if (!map) return;
        renderMapMarkers();
        if (routeLayerAdded) {
            const cities = getCitiesFromItinerary().filter(c => c.lat);
            if (cities.length >= 2) {
                const coords = cities.map(c => [c.lng, c.lat]);
                map.getSource('diy-route').setData({
                    type: 'Feature',
                    geometry: { type: 'LineString', coordinates: coords },
                });
            }
        } else if (map.loaded()) {
            renderMapRoute();
        }
    }

    function findNearestKnownCity(lng, lat) {
        let best = null, bestDist = Infinity;
        Object.entries(CITY_COORDS).forEach(function ([name, coords]) {
            const d = Math.hypot(coords[0] - lng, coords[1] - lat);
            if (d < bestDist && d < 3) { // within ~3 degrees (~330km)
                bestDist = d;
                best = name;
            }
        });
        return best;
    }

    function showMapClickSuggestion(cityName, lngLat) {
        const existing = getCitiesFromItinerary().map(c => c.name);
        if (existing.includes(cityName)) return; // already in itinerary

        new mapboxgl.Popup()
            .setLngLat(lngLat)
            .setHTML(
                '<div class="map-popup">' +
                '<strong>Add ' + cityName + '?</strong>' +
                '<div class="mt-2">' +
                '<button onclick="quickAddCity(\'' + cityName + '\')" class="btn btn-sm btn-primary">+ Add to Tour</button>' +
                '</div></div>'
            )
            .addTo(map);
    }

    window.openEditCityFromMap = function (cityName) {
        const days   = itinerary.day_by_day || [];
        const idx    = getCitiesFromItinerary().findIndex(c => c.name === cityName);
        if (idx < 0) return;
        const city   = getCitiesFromItinerary()[idx];
        openEditCityModal(idx);
    };

    window.quickAddCity = function (cityName) {
        document.getElementById('newCityName').value = cityName;
        openAddCityModal();
        map.closePopup && map.closePopup();
    };

    // =========================================================================
    // Map view toggle
    // =========================================================================
    window.setMapView = function (view, btn) {
        document.querySelectorAll('.map-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        const mapEl      = document.getElementById('diyMap');
        const timelineEl = document.getElementById('timelineView');
        const calendarEl = document.getElementById('calendarView');

        mapEl.style.display      = view === 'map'      ? ''      : 'none';
        timelineEl.style.display = view === 'timeline' ? 'block' : 'none';
        calendarEl.style.display = view === 'calendar' ? 'block' : 'none';

        if (view === 'timeline') renderTimelineView();
        if (view === 'calendar') renderCalendarView();
    };

    function renderTimelineView() {
        const days = itinerary.day_by_day || [];
        if (!days.length) {
            document.getElementById('timelineContent').innerHTML =
                '<div style="text-align:center;padding:2rem;color:#7f8c8d;">' +
                '<div style="font-size:2.5rem;margin-bottom:.5rem;">📋</div>' +
                '<p style="font-weight:600;">No itinerary yet.</p>' +
                '<p style="font-size:.88rem;">Add cities on the right panel and your day-by-day schedule will appear here.</p>' +
                '</div>';
            return;
        }
        let html = '<div class="timeline">';
        let lastCity = '';
        days.forEach(function (day) {
            if (day.city !== lastCity) {
                lastCity = day.city;
                html += '<div class="timeline-city-header"><i class="fas fa-map-marker-alt"></i> <strong>' + day.city + '</strong>, ' + day.country + '</div>';
            }
            html += '<div class="timeline-day"><span class="timeline-day-num">Day ' + day.day + '</span>';
            const acts = day.activities || [];
            if (acts.length) {
                acts.forEach(function (act) {
                    html += '<span class="timeline-activity ' + (act.included ? 'included' : 'optional') + '">' + act.time + ' · ' + act.name + '</span>';
                });
            } else {
                html += '<span class="timeline-activity" style="color:#aaa;font-style:italic;">Free day — explore ' + day.city + '</span>';
            }
            html += '</div>';
        });
        html += '</div>';
        document.getElementById('timelineContent').innerHTML = html;
    }

    function renderCalendarView() {
        const days = itinerary.day_by_day || [];
        if (!days.length) {
            document.getElementById('calendarContent').innerHTML =
                '<div style="text-align:center;padding:2rem;color:#7f8c8d;">' +
                '<div style="font-size:2.5rem;margin-bottom:.5rem;">📅</div>' +
                '<p style="font-weight:600;">No itinerary yet.</p>' +
                '<p style="font-size:.88rem;">Add cities on the right panel and your calendar will appear here.</p>' +
                '</div>';
            return;
        }
        let html = '<div class="calendar-grid">';
        days.forEach(function (day) {
            html += '<div class="calendar-day-cell">';
            html += '<div class="cal-day-num">Day ' + day.day + '</div>';
            html += '<div class="cal-city">' + (day.city || '') + '</div>';
            const acts = day.activities || [];
            if (acts.length) {
                acts.slice(0, 2).forEach(function (act) {
                    html += '<div class="cal-activity">' + act.name + '</div>';
                });
            } else {
                html += '<div class="cal-activity" style="color:#aaa;font-style:italic;">Free day</div>';
            }
            html += '</div>';
        });
        html += '</div>';
        document.getElementById('calendarContent').innerHTML = html;
    }

    // =========================================================================
    // City List
    // =========================================================================
    function renderCityList() {
        const container = document.getElementById('cityList');
        const cities    = getCitiesFromItinerary();

        // Update metadata
        const totalDays = (itinerary.total_days) || (itinerary.day_by_day || []).length;
        document.getElementById('daysDisplay').textContent = totalDays;

        if (!cities.length) {
            container.innerHTML = '<p class="text-muted text-center py-3">No cities yet. Add some above!</p>';
            return;
        }

        // Populate "insert after" dropdown in add-city modal
        const insertSel = document.getElementById('insertAfterCity');
        if (insertSel) {
            insertSel.innerHTML = '<option value="end">At end of itinerary</option>';
            cities.forEach(function (c, i) {
                insertSel.innerHTML += '<option value="' + i + '">After ' + c.name + '</option>';
            });
        }

        let html = '';
        cities.forEach(function (city, idx) {
            const stayDays = countCityDays(city.name);
            html += '<div class="city-item" id="cityItem-' + idx + '">';
            html += '<span class="city-num">' + (idx + 1) + '</span>';
            html += '<div class="city-info"><strong>' + city.name + '</strong><small>' + city.country + ' · ' + stayDays + ' days</small></div>';
            html += '<div class="city-actions">';
            html += '<button class="btn-icon" onclick="openEditCityModal(' + idx + ')" title="Edit"><i class="fas fa-pen"></i></button>';
            html += '<button class="btn-icon btn-danger-icon" onclick="removeCity(' + idx + ')" title="Remove"><i class="fas fa-times"></i></button>';
            html += '</div></div>';
        });

        // Route optimisation suggestion button if > 2 cities
        if (cities.length >= 3) {
            html += '<button class="btn btn-outline btn-sm btn-full mt-2" onclick="checkRouteOptimisation()">' +
                '<i class="fas fa-route"></i> Optimise Route Order</button>';
        }

        container.innerHTML = html;

        // Suggested nearby cities
        if (cities.length) {
            fetchReachableCities(cities[cities.length - 1].name);
        }
    }

    function countCityDays(cityName) {
        return (itinerary.day_by_day || []).filter(d => d.city === cityName).length;
    }

    // =========================================================================
    // City CRUD
    // =========================================================================
    window.openAddCityModal = function () {
        window.openModal('addCityModal');
    };

    window.confirmAddCity = function () {
        const name    = document.getElementById('newCityName').value.trim();
        const days    = parseInt(document.getElementById('newCityDays').value) || 2;
        const tier    = document.getElementById('newCityTier').value;
        const after   = document.getElementById('insertAfterCity').value;

        if (!name) { alert('Please enter a city name.'); return; }

        // Find country from coords lookup or default to inferred
        const country = guessCountry(name);

        // Build new days array entries
        const currentDays = itinerary.day_by_day || [];
        const lastDay     = currentDays.length ? currentDays[currentDays.length - 1].day : 0;
        const startDay    = lastDay + 1;

        const newDays = [];
        for (let i = 0; i < days; i++) {
            newDays.push({
                day:          startDay + i,
                city:         name,
                country:      country,
                accommodation: tier + ' hotel in ' + name,
                activities:   [],
                meals_included: ['breakfast'],
                free_time:    '10:00-12:00, 16:00-18:00',
                overnight:    name,
            });
        }

        if (after === 'end') {
            itinerary.day_by_day = (itinerary.day_by_day || []).concat(newDays);
        } else {
            // Insert after specified city index
            const cities    = getCitiesFromItinerary();
            const afterCity = cities[parseInt(after)];
            if (afterCity) {
                const insertAt = (itinerary.day_by_day || []).findLastIndex(d => d.city === afterCity.name) + 1;
                itinerary.day_by_day.splice(insertAt, 0, ...newDays);
                // Renumber days
                renumberDays();
            } else {
                itinerary.day_by_day = (itinerary.day_by_day || []).concat(newDays);
            }
        }

        itinerary.total_days = (itinerary.day_by_day || []).length;
        itinerary.cities_count = getCitiesFromItinerary().length;

        window.closeModal('addCityModal');
        document.getElementById('newCityName').value = '';
        onItineraryChanged('added ' + name + ' to the itinerary');
    };

    window.removeCity = function (idx) {
        const cities   = getCitiesFromItinerary();
        const cityName = cities[idx]?.name;
        if (!cityName) return;
        if (!confirm('Remove ' + cityName + ' from your itinerary?')) return;

        itinerary.day_by_day = (itinerary.day_by_day || []).filter(d => d.city !== cityName);
        renumberDays();
        itinerary.total_days = (itinerary.day_by_day || []).length;
        itinerary.cities_count = getCitiesFromItinerary().length;

        window.closeModal('editCityModal');
        onItineraryChanged('removed ' + cityName + ' from the itinerary');
    };

    window.openEditCityModal = function (idx) {
        const cities = getCitiesFromItinerary();
        const city   = cities[idx];
        if (!city) return;

        document.getElementById('editCityIndex').value    = idx;
        document.getElementById('editCityNameDisplay').textContent = city.name;
        document.getElementById('editCityDays').value     = countCityDays(city.name);
        window.openModal('editCityModal');
    };

    window.saveEditCity = function () {
        const idx      = parseInt(document.getElementById('editCityIndex').value);
        const newDays  = parseInt(document.getElementById('editCityDays').value);
        const newTier  = document.getElementById('editCityTier').value;
        const cities   = getCitiesFromItinerary();
        const city     = cities[idx];
        if (!city) return;

        const currentDays = countCityDays(city.name);
        const diff = newDays - currentDays;

        if (diff > 0) {
            // Add extra days
            const insertAt = (itinerary.day_by_day || []).findLastIndex(d => d.city === city.name) + 1;
            const lastDay  = itinerary.day_by_day[insertAt - 1]?.day || 0;
            for (let i = 0; i < diff; i++) {
                itinerary.day_by_day.splice(insertAt + i, 0, {
                    day: lastDay + i + 1,
                    city: city.name,
                    country: city.country,
                    accommodation: newTier + ' hotel in ' + city.name,
                    activities: [],
                    meals_included: ['breakfast'],
                    free_time: '10:00-12:00, 16:00-18:00',
                    overnight: city.name,
                });
            }
        } else if (diff < 0) {
            // Remove excess days (from the end of this city's stay)
            let toRemove = Math.abs(diff);
            for (let i = itinerary.day_by_day.length - 1; i >= 0 && toRemove > 0; i--) {
                if (itinerary.day_by_day[i].city === city.name) {
                    itinerary.day_by_day.splice(i, 1);
                    toRemove--;
                }
            }
        }

        renumberDays();
        itinerary.total_days = (itinerary.day_by_day || []).length;

        window.closeModal('editCityModal');
        onItineraryChanged('updated ' + city.name + ' stay');
    };

    function renumberDays() {
        (itinerary.day_by_day || []).forEach(function (d, i) { d.day = i + 1; });
    }

    function guessCountry(cityName) {
        const map = {
            'Paris': 'France', 'Lyon': 'France', 'Nice': 'France', 'Marseille': 'France',
            'Barcelona': 'Spain', 'Madrid': 'Spain', 'Seville': 'Spain', 'Granada': 'Spain',
            'Lisbon': 'Portugal', 'Porto': 'Portugal',
            'Milan': 'Italy', 'Venice': 'Italy', 'Florence': 'Italy', 'Rome': 'Italy', 'Naples': 'Italy',
            'Lucerne': 'Switzerland', 'Zurich': 'Switzerland', 'Geneva': 'Switzerland', 'Interlaken': 'Switzerland', 'Bern': 'Switzerland',
            'Berlin': 'Germany', 'Munich': 'Germany', 'Hamburg': 'Germany', 'Frankfurt': 'Germany', 'Cologne': 'Germany',
            'Vienna': 'Austria', 'Salzburg': 'Austria', 'Innsbruck': 'Austria',
            'Amsterdam': 'Netherlands',
            'Brussels': 'Belgium',
            'Prague': 'Czech Republic', 'Krakow': 'Poland',
            'Budapest': 'Hungary', 'Dubrovnik': 'Croatia',
            'Athens': 'Greece', 'Santorini': 'Greece',
            'Copenhagen': 'Denmark', 'Stockholm': 'Sweden', 'Oslo': 'Norway',
        };
        return map[cityName] || 'Europe';
    }

    // =========================================================================
    // Duration adjustment
    // =========================================================================
    window.adjustDays = function (delta) {
        const current  = (itinerary.day_by_day || []).length;
        const target   = Math.max(3, Math.min(60, current + delta));
        const diff     = target - current;

        if (diff === 0) return;

        if (diff > 0) {
            // Extend last city stay
            const lastDay = itinerary.day_by_day[current - 1];
            for (let i = 0; i < diff; i++) {
                itinerary.day_by_day.push(Object.assign({}, lastDay, { day: current + i + 1, activities: [] }));
            }
        } else {
            // Remove days from the end
            itinerary.day_by_day.splice(target);
        }

        itinerary.total_days = target;
        onItineraryChanged(delta > 0 ? 'extended the trip by ' + diff + ' days' : 'shortened the trip by ' + Math.abs(diff) + ' days');
    };

    // =========================================================================
    // Pricing
    // =========================================================================
    function renderPricing() {
        if (!pricing || !pricing.total_per_person) return;

        document.getElementById('totalCostDisplay').textContent = '₱' + fmt(pricing.total_per_person);

        const items = [
            { label: 'Accommodation', key: 'accommodation', icon: '🏨' },
            { label: 'Transportation', key: 'transportation', icon: '🚆' },
            { label: 'Activities & Entries', key: 'activities', icon: '🎟️' },
            { label: 'Meals', key: 'meals', icon: '🍽️' },
            { label: 'Guide Services', key: 'guide_services', icon: '🧑‍✈️' },
            { label: 'Visa & Insurance', key: 'visa_insurance', icon: '🛂' },
        ];

        const paxCount = pricing.group_size || 1;
        let html = '';
        items.forEach(function (item) {
            const val = Math.round((pricing[item.key] || 0) / paxCount);
            html += '<div class="pricing-row">' +
                '<span>' + item.icon + ' ' + item.label + '</span>' +
                '<span>₱' + fmt(val) + '</span></div>';
        });
        html += '<div class="pricing-divider"></div>';
        html += '<div class="pricing-row"><span>Service Fee (' + (pricing.markup_percent || 15) + '%)</span><span>₱' + fmt(Math.round((pricing.markup || 0) / paxCount)) + '</span></div>';
        html += '<div class="pricing-row pricing-total"><strong>Total per person</strong><strong>₱' + fmt(pricing.total_per_person) + '</strong></div>';
        if (paxCount > 1) {
            html += '<div class="pricing-row text-muted"><span>For ' + paxCount + ' people</span><span>₱' + fmt(pricing.total_group) + '</span></div>';
        }

        document.getElementById('pricingBreakdown').innerHTML = html;
    }

    function refreshPricing() {
        fetch(PRICE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                itinerary_data: itinerary,
                group_size:     prefs.group_size || 2,
                travel_month:   prefs.travel_month || 'June',
                budget_range:   prefs.budget_range || '150000-200000',
            }),
        })
        .then(r => r.json())
        .then(function (data) {
            pricing = data.pricing;
            renderPricing();
            renderBudgetSuggestions(data.suggestions || []);
        })
        .catch(function () { /* silent fail */ });
    }

    function renderBudgetSuggestions(suggestions) {
        const container = document.getElementById('budgetSuggestions');
        if (!suggestions.length) { container.innerHTML = ''; return; }
        let html = '<div class="budget-tips"><strong>💡 Budget Tips:</strong>';
        suggestions.forEach(function (s) {
            html += '<div class="budget-tip">• ' + s.action;
            if (s.savings) html += ' → Save ₱' + fmt(s.savings);
            if (s.cost)    html += ' → +₱' + fmt(s.cost);
            html += '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // =========================================================================
    // Validation
    // =========================================================================
    function renderValidation() {
        const itinData  = itinerary;
        const scoreEl   = document.getElementById('qualityScore');
        const resultsEl = document.getElementById('validationResults');

        fetch(VAL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ itinerary_data: itinData, preferences: prefs }),
        })
        .then(r => r.json())
        .then(function (val) {
            const score = val.overall_score || 0;
            scoreEl.textContent = score + '/100';
            scoreEl.className   = 'quality-badge ' + (score >= 80 ? 'score-good' : score >= 60 ? 'score-warn' : 'score-bad');

            let html = '';
            if (val.issues && val.issues.length) {
                html += '<div class="val-group val-issues"><strong>❌ Must Fix:</strong>';
                val.issues.forEach(i => { html += '<div class="val-item">' + i.message + '<small>' + (i.suggestion || '') + '</small></div>'; });
                html += '</div>';
            }
            if (val.warnings && val.warnings.length) {
                html += '<div class="val-group val-warnings"><strong>⚠️ Warnings:</strong>';
                val.warnings.forEach(w => { html += '<div class="val-item">' + w.message + '<small>' + (w.suggestion || '') + '</small></div>'; });
                html += '</div>';
            }
            if (val.good_points && val.good_points.length) {
                html += '<div class="val-group val-good"><strong>✅ Good:</strong>';
                val.good_points.forEach(g => { html += '<div class="val-item good">' + g + '</div>'; });
                html += '</div>';
            }
            if (val.recommendation) {
                html += '<div class="val-recommendation">' + val.recommendation + '</div>';
            }
            resultsEl.innerHTML = html || '<p class="text-muted">Validation complete.</p>';
        })
        .catch(function () {
            if (scoreEl) scoreEl.textContent = '--/100';
        });
    }

    // =========================================================================
    // Route Optimisation
    // =========================================================================
    window.checkRouteOptimisation = function () {
        const cities   = getCitiesFromItinerary().map(c => c.name);
        const mustVisit = (prefs.must_visit || []);

        fetch(OPT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ cities, must_visit: mustVisit }),
        })
        .then(r => r.json())
        .then(function (result) {
            if (result.suggestion) {
                addAiMessage('🗺️ Route suggestion: ' + result.reason +
                    ' Recommended order: ' + result.suggestion.join(' → ') +
                    '. <button class="btn btn-sm btn-primary" onclick="applyRouteOrder(' +
                    JSON.stringify(result.suggestion) + ')">Apply</button>');
            } else {
                addAiMessage('✅ ' + (result.reason || 'Your current route is already optimal!'));
            }
        })
        .catch(function () { addAiMessage('Route optimisation is temporarily unavailable.'); });
    };

    window.applyRouteOrder = function (orderedCities) {
        const daysByCity = {};
        (itinerary.day_by_day || []).forEach(function (d) {
            if (!daysByCity[d.city]) daysByCity[d.city] = [];
            daysByCity[d.city].push(d);
        });

        const newDays = [];
        orderedCities.forEach(function (city) {
            (daysByCity[city] || []).forEach(function (d) { newDays.push(d); });
        });

        itinerary.day_by_day = newDays;
        renumberDays();
        onItineraryChanged('reordered cities to ' + orderedCities.join(', '));
    };

    // =========================================================================
    // Reachable cities suggestion
    // =========================================================================
    function fetchReachableCities(fromCity) {
        fetch(REACH_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ from: fromCity, max_hours: 4 }),
        })
        .then(r => r.json())
        .then(function (data) {
            const reachable = (data.cities || []).filter(c => !getCitiesFromItinerary().map(x => x.name).includes(c)).slice(0, 5);
            if (!reachable.length) return;

            const suggEl   = document.getElementById('citySuggestions');
            const listEl   = document.getElementById('citySuggestionList');
            let html = '';
            reachable.forEach(function (c) {
                html += '<button class="btn btn-outline btn-sm suggestion-chip" onclick="quickAddCity(\'' + c + '\')">' +
                    '+ ' + c + '</button>';
            });
            listEl.innerHTML = html;
            suggEl.style.display = '';
        })
        .catch(function () { /* silent */ });
    }

    // =========================================================================
    // AI Assistant
    // =========================================================================
    function parseAssistantFallback(actionText) {
        const text = (actionText || '').toLowerCase().trim();

        const addCityMatch = text.match(/(?:add\s+(?:city\s+)?)\b([a-z\s-]{2,40})$/i);
        if (addCityMatch && addCityMatch[1]) {
            const city = addCityMatch[1].trim().replace(/\b\w/g, c => c.toUpperCase());
            return [{
                message: 'I can add ' + city + ' to your itinerary now.',
                auto_apply_data: { add_city: city }
            }];
        }

        const removeCityMatch = text.match(/(?:remove\s+(?:city\s+)?)\b([a-z\s-]{2,40})$/i);
        if (removeCityMatch && removeCityMatch[1]) {
            const city = removeCityMatch[1].trim().replace(/\b\w/g, c => c.toUpperCase());
            return [{
                message: 'I can remove ' + city + ' from your itinerary.',
                auto_apply_data: { remove_city: city }
            }];
        }

        return [];
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(payload),
        });

        let data = null;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('invalid_json_response');
        }

        if (!response.ok) {
            const msg = (data && (data.message || data.error)) || 'request_failed';
            throw new Error(msg);
        }

        return data;
    }

    window.askAI = function () {
        const input = document.getElementById('aiInput');
        const text  = input.value.trim();
        if (!text) return;

        addAiMessage('<strong>You:</strong> ' + esc(text));
        input.value = '';

        postJson(SUGG_URL, { itinerary, action: text })
        .then(function (data) {
            const suggestions = data.suggestions || [];
            if (!suggestions.length) {
                addAiMessage('🤖 I couldn\'t find specific suggestions for that. Try editing the cities on the left panel.');
                return;
            }
            suggestions.forEach(function (s) {
                let msg = '🤖 ' + esc(s.message || '');
                if (s.auto_apply_data) {
                    msg += ' <button class="btn btn-xs btn-primary" onclick="applyAiSuggestion(' + JSON.stringify(s.auto_apply_data).replace(/"/g, '&quot;') + ')">Apply</button>';
                }
                addAiMessage(msg);
            });
        })
        .catch(function () {
            const fallback = parseAssistantFallback(text);
            if (fallback.length) {
                fallback.forEach(function (s) {
                    let msg = '🤖 ' + esc(s.message || '');
                    if (s.auto_apply_data) {
                        msg += ' <button class="btn btn-xs btn-primary" onclick="applyAiSuggestion(' + JSON.stringify(s.auto_apply_data).replace(/"/g, '&quot;') + ')">Apply</button>';
                    }
                    addAiMessage(msg);
                });
                return;
            }

            addAiMessage('🤖 I couldn\'t reach live AI suggestions right now. You can still edit cities manually on the left panel.');
        });
    };

    document.getElementById('aiInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') window.askAI();
    });

    function addAiMessage(html) {
        const container = document.getElementById('aiMessages');
        const div       = document.createElement('div');
        div.className   = 'ai-message';
        div.innerHTML   = html;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    window.applyAiSuggestion = function (data) {
        if (data.add_city) window.quickAddCity(data.add_city);
        if (data.remove_city) {
            const idx = getCitiesFromItinerary().findIndex(c => c.name === data.remove_city);
            if (idx >= 0) window.removeCity(idx);
        }
        addAiMessage('✅ Suggestion applied!');
    };

    // =========================================================================
    // Tour Name
    // =========================================================================
    window.editTourName = function () {
        const current = document.getElementById('tourNameDisplay').textContent;
        const name    = prompt('Tour name:', current);
        if (name && name.trim()) {
            document.getElementById('tourNameDisplay').textContent = name.trim();
            markDirty();
        }
    };

    // =========================================================================
    // Save
    // =========================================================================
    function startAutoSave() {
        autoSaveTimer = setInterval(function () {
            if (isDirty) manualSave();
        }, 30000);
    }

    function markDirty() {
        isDirty = true;
        document.getElementById('saveStatus').textContent = '● Unsaved changes';
        document.getElementById('saveStatus').className   = 'save-status unsaved';
    }

    window.manualSave = function () {
        const tourName = document.getElementById('tourNameDisplay').textContent;
        setSaveStatus('saving');

        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ itinerary_data: itinerary, tour_name: tourName }),
        })
        .then(r => r.json())
        .then(function (data) {
            if (data.success) {
                isDirty = false;
                pricing = data.pricing || pricing;
                setSaveStatus('saved');
                renderPricing();
            } else {
                setSaveStatus('error');
            }
        })
        .catch(function () { setSaveStatus('error'); });
    };

    function setSaveStatus(state) {
        const el    = document.getElementById('saveStatus');
        el.className = 'save-status ' + state;
        el.textContent = state === 'saving' ? '⏳ Saving…' :
                         state === 'saved'  ? '✅ Saved'   :
                         state === 'error'  ? '⚠️ Save failed' : '';
        if (state === 'saved') {
            setTimeout(function () { el.textContent = ''; }, 3000);
        }
    }

    // =========================================================================
    // On itinerary change (called after every edit)
    // =========================================================================
    function onItineraryChanged(action) {
        markDirty();
        renderCityList();
        renderPricing();
        refreshMap();
        renderValidation();
        // Fetch AI suggestions for the new state
        fetchAiSuggestions(action);
        // Throttle pricing refresh (avoid too many calls)
        clearTimeout(onItineraryChanged._priceTimer);
        onItineraryChanged._priceTimer = setTimeout(refreshPricing, 1000);
    }

    function fetchAiSuggestions(action) {
        fetch(SUGG_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ itinerary, action }),
        })
        .then(r => r.json())
        .then(function (data) {
            (data.suggestions || []).slice(0, 2).forEach(function (s) {
                addAiMessage('🤖 ' + esc(s.message || ''));
            });
        })
        .catch(function () { /* silent */ });
    }

    // =========================================================================
    // Modals
    // =========================================================================
    window.openModal = function (id) {
        document.getElementById(id).style.display = 'flex';
    };
    window.closeModal = function (id) {
        document.getElementById(id).style.display = 'none';
    };

    // =========================================================================
    // UI helpers
    // =========================================================================
    window.toggleSection = function (bodyId) {
        const body = document.getElementById(bodyId);
        body.style.display = body.style.display === 'none' ? '' : 'none';
    };

    window.confirmQuote = function (e) {
        if (!confirm('Submit your itinerary for a formal quote? You can still edit it afterwards.')) {
            e.preventDefault();
            return false;
        }
        return true;
    };

    function fmt(n) {
        return Number(n || 0).toLocaleString('en-PH');
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // =========================================================================
    // Expose for Blade inline use
    // =========================================================================
    window._diyGetItinerary = function () { return itinerary; };

})();
