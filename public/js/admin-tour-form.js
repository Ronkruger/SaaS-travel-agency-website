/**
 * admin-tour-form.js
 * Dynamic repeatable-row builders for the Tour admin create/edit form.
 */

// ─── Departure Dates ─────────────────────────────────────────────────────────
let departureDateIdx = 0;
function addDepartureDate(data) {
    data = data || {};
    const i = departureDateIdx++;
    const maxCap   = data.maxCapacity  !== undefined ? data.maxCapacity  : '';
    const booked   = data.currentBookings !== undefined ? data.currentBookings : 0;
    const avail    = data.isAvailable !== false;
    const remaining = (maxCap !== '' && maxCap !== null) ? (parseInt(maxCap) - parseInt(booked)) : null;
    const isFull   = !avail || (remaining !== null && remaining <= 0);
    const html = `<div class="repeatable-row" id="dd_${i}">
        <button type="button" class="remove-row" onclick="removeRow('dd_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-3">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="departure_dates[${i}][start]" class="form-control" value="${data.start||''}">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="departure_dates[${i}][end]" class="form-control" value="${data.end||''}">
            </div>
            <div class="form-group">
                <label>Price Override (₱) <small style="color:#6b7280;font-weight:400">— blank = use tour default</small></label>
                <input type="number" name="departure_dates[${i}][price]" class="form-control" value="${data.price||''}" step="0.01" min="0" placeholder="Leave blank for default">
            </div>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label>Total Slots <small style="color:#6b7280;font-weight:400">— total seats for this departure</small></label>
                <input type="number" name="departure_dates[${i}][maxCapacity]" class="form-control"
                       id="dd_max_${i}" value="${maxCap}" min="0" placeholder="e.g. 30"
                       oninput="updateDepStatus(${i})">
            </div>
            <div class="form-group">
                <label>Slots Booked <small style="color:#6b7280;font-weight:400">— filled automatically on booking</small></label>
                <input type="number" name="departure_dates[${i}][currentBookings]" class="form-control"
                       id="dd_booked_${i}" value="${booked}" min="0"
                       oninput="updateDepStatus(${i})">
            </div>
            <div class="form-group">
                <label>Status</label>
                <div id="dd_status_${i}" class="dep-status-pill ${isFull ? 'dep-status-full' : 'dep-status-open'}">
                    ${isFull ? '⛔ FULL' : '✅ Available'}
                </div>
                <label style="cursor:pointer;display:flex;align-items:center;gap:.5rem;margin-top:.5rem;font-size:.82rem;color:#6b7280">
                    <input type="checkbox" name="departure_dates[${i}][isAvailable]" id="dd_avail_${i}" value="1" ${avail ? 'checked' : ''}
                           onchange="updateDepStatus(${i})">
                    Force mark available
                </label>
            </div>
        </div>
    </div>`;
    document.getElementById('departureDatesContainer').insertAdjacentHTML('beforeend', html);
}

function updateDepStatus(i) {
    const maxEl    = document.getElementById('dd_max_' + i);
    const bookedEl = document.getElementById('dd_booked_' + i);
    const availEl  = document.getElementById('dd_avail_' + i);
    const pill     = document.getElementById('dd_status_' + i);
    if (!pill) return;
    const max    = maxEl && maxEl.value !== '' ? parseInt(maxEl.value) : null;
    const booked = bookedEl ? parseInt(bookedEl.value || 0) : 0;
    const forceAvail = availEl ? availEl.checked : true;
    const remaining  = max !== null ? max - booked : null;
    const isFull     = !forceAvail || (remaining !== null && remaining <= 0);
    pill.className   = 'dep-status-pill ' + (isFull ? 'dep-status-full' : (remaining !== null && remaining <= 5 ? 'dep-status-low' : 'dep-status-open'));
    if (isFull) {
        pill.textContent = '⛔ FULL';
    } else if (remaining !== null && remaining <= 5) {
        pill.textContent = '⚠️ ' + remaining + ' slot' + (remaining === 1 ? '' : 's') + ' left';
    } else if (remaining !== null) {
        pill.textContent = '✅ ' + remaining + ' slots open';
    } else {
        pill.textContent = '✅ Available';
    }
}

// ─── Itinerary Days ───────────────────────────────────────────────────────────
let itineraryIdx = 0;
function addItineraryDay(data) {
    data = data || {};
    const i = itineraryIdx++;
    const dayNum = i + 1;
    const html = `<div class="repeatable-row" id="it_${i}">
        <button type="button" class="remove-row" onclick="removeRow('it_${i}')"><i class="fas fa-times"></i></button>
        <div style="font-weight:600;margin-bottom:.5rem;color:#2563eb">Day ${dayNum}</div>
        <div class="form-row-2">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="itinerary[${i}][title]" class="form-control" value="${esc(data.title||'')}">
            </div>
            <div class="form-group">
                <label>Day Image URL</label>
                <input type="text" name="itinerary[${i}][image]" class="form-control" value="${esc(data.image||'')}" placeholder="https://...">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="itinerary[${i}][description]" class="form-control" rows="3">${esc(data.description||'')}</textarea>
        </div>
    </div>`;
    document.getElementById('itineraryContainer').insertAdjacentHTML('beforeend', html);
}

// ─── Full Stops (Day-by-Day Itinerary with Mapbox Map) ───────────────────────

// Inject group bracket + autocomplete styles once
(function injectStopStyles() {
    if (document.getElementById('stop-group-styles')) return;
    const s = document.createElement('style');
    s.id = 'stop-group-styles';
    s.textContent = `
        .city-group-header{display:flex;align-items:center;justify-content:space-between;
            background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);color:#fff;
            padding:.55rem 1rem;border-radius:8px 8px 0 0;margin-top:1.25rem;
            border-bottom:2px solid rgba(255,255,255,.15)}
        .city-group-header:first-child{margin-top:0}
        .city-group-name{font-weight:700;font-size:.9rem;letter-spacing:.08em}
        .city-group-nights{font-size:.78rem;background:rgba(255,255,255,.2);
            padding:.18rem .6rem;border-radius:20px;white-space:nowrap}
        .city-group-header + .stop-day-row{border-top-left-radius:0!important;border-top-right-radius:0!important}
        .stop-city-input.autofilled{background:#f0fdf4}
        .stop-city-wrap{position:relative}
        .stop-city-suggestions{position:absolute;top:100%;left:0;right:0;background:#fff;
            border:1px solid #cbd5e1;border-top:none;border-radius:0 0 8px 8px;
            box-shadow:0 6px 20px rgba(0,0,0,.13);z-index:9999;max-height:220px;overflow-y:auto}
        .stop-city-suggestion{padding:.5rem .85rem;cursor:pointer;font-size:.875rem;
            border-bottom:1px solid #f1f5f9;line-height:1.35}
        .stop-city-suggestion:last-child{border-bottom:none}
        .stop-city-suggestion:hover,.stop-city-suggestion.active{background:#eff6ff;color:#1d4ed8}
        .stop-city-suggestion small{display:block;color:#94a3b8;font-size:.75rem}
    `;
    document.head.appendChild(s);
})();

// ── Mapbox Place Autocomplete (cities, streets, provinces, POIs, etc.) ────────

let _acDebounceTimer = null;

function getMapboxToken() {
    return document.getElementById('adminStopsMap')?.dataset.mapboxToken || '';
}

// Attach live autocomplete dropdown to a city input after it's added to the DOM
function attachCityAutocomplete(input) {
    const wrap = document.createElement('div');
    wrap.className = 'stop-city-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const dropdown = document.createElement('div');
    dropdown.className = 'stop-city-suggestions';
    dropdown.style.display = 'none';
    wrap.appendChild(dropdown);

    let activeIdx = -1;

    function closeSuggestions() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        activeIdx = -1;
    }

    function selectSuggestion(feature) {
        // Use the full place_name but show just the first part (before the first comma) as input value
        input.value = feature.text || feature.place_name.split(',')[0];
        const row = input.closest('.stop-day-row');
        if (row) {
            const countryInput = row.querySelector('input[name*="[country]"]');
            if (countryInput) {
                const countryCtx = (feature.context || []).find(c => c.id.startsWith('country.'));
                if (countryCtx) countryInput.value = countryCtx.text;
            }
            if (feature.center) {
                input.dataset.lng = feature.center[0];
                input.dataset.lat = feature.center[1];
            }
        }
        input.classList.add('autofilled');
        closeSuggestions();
        refreshStopsMap();
        rerenderGroupBrackets();
    }

    function renderSuggestions(features) {
        dropdown.innerHTML = '';
        activeIdx = -1;
        if (!features.length) { dropdown.style.display = 'none'; return; }
        features.forEach(f => {
            const li = document.createElement('div');
            li.className = 'stop-city-suggestion';
            // Sub-label: region + country from context
            const sub = (f.context || [])
                .filter(c => c.id.startsWith('region.') || c.id.startsWith('country.'))
                .map(c => c.text).join(', ');
            li.innerHTML = `${f.text}<small>${sub || f.place_name}</small>`;
            li.addEventListener('mousedown', e => { e.preventDefault(); selectSuggestion(f); });
            dropdown.appendChild(li);
        });
        dropdown.style.display = 'block';
    }

    async function fetchSuggestions(query) {
        const token = getMapboxToken();
        if (!token || query.length < 2) { closeSuggestions(); return; }
        try {
            // All place types: city, street, address, neighborhood, district, region, POI
            const res = await fetch(
                `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json` +
                `?access_token=${token}&autocomplete=true&limit=6` +
                `&types=place,locality,neighborhood,district,region,address,poi`
            );
            const json = await res.json();
            renderSuggestions(json.features || []);
        } catch (e) { closeSuggestions(); }
    }

    input.addEventListener('input', () => {
        clearTimeout(_acDebounceTimer);
        const q = input.value.trim();
        if (!q) { closeSuggestions(); return; }
        _acDebounceTimer = setTimeout(() => fetchSuggestions(q), 280);
        refreshStopsMap();
        rerenderGroupBrackets();
    });

    input.addEventListener('keydown', e => {
        const items = dropdown.querySelectorAll('.stop-city-suggestion');
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
            items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, 0);
            items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            items[activeIdx].dispatchEvent(new MouseEvent('mousedown'));
        } else if (e.key === 'Escape') {
            closeSuggestions();
        }
    });

    input.addEventListener('blur', () => setTimeout(closeSuggestions, 200));
}

// Fallback blur handler — geocodes if country still empty after typing without using the dropdown
function autofillCountry(cityInput) {
    const row = cityInput.closest('.stop-day-row');
    if (!row) return;
    const countryInput = row.querySelector('input[name*="[country]"]');
    if (!countryInput || countryInput.value.trim()) return;
    const token = getMapboxToken();
    const q = (cityInput.value || '').trim();
    if (!token || !q) return;
    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json` +
          `?access_token=${token}&limit=1` +
          `&types=place,locality,neighborhood,district,region,address,poi`)
        .then(r => r.json())
        .then(json => {
            const f = json.features?.[0];
            if (!f) return;
            const countryCtx = (f.context || []).find(c => c.id.startsWith('country.'));
            if (countryCtx) { countryInput.value = countryCtx.text; rerenderGroupBrackets(); }
            if (f.center) {
                cityInput.dataset.lng = f.center[0];
                cityInput.dataset.lat = f.center[1];
                refreshStopsMap();
            }
        }).catch(() => {});
}

// Rerender city-group bracket headers
function rerenderGroupBrackets() {
    document.querySelectorAll('.city-group-header').forEach(h => h.remove());
    const rows = Array.from(document.querySelectorAll('#fullStopsContainer .stop-day-row'));
    if (!rows.length) return;

    const groups = [];
    let cur = null;
    rows.forEach(row => {
        const city = (row.querySelector('.stop-city-input')?.value || '').trim();
        const key = city.toLowerCase();
        if (cur && key === cur.key) {
            cur.rows.push(row);
        } else {
            if (cur) groups.push(cur);
            cur = { key, city, rows: [row] };
        }
    });
    if (cur) groups.push(cur);

    groups.forEach(group => {
        if (!group.city) return;
        const totalNights = group.rows.reduce((sum, r) => {
            return sum + (parseInt(r.querySelector('input[name*="[days]"]')?.value) || 0);
        }, 0);
        const hdr = document.createElement('div');
        hdr.className = 'city-group-header';
        hdr.innerHTML =
            `<span class="city-group-name">${esc(group.city.toUpperCase())}</span>` +
            (totalNights > 0
                ? `<span class="city-group-nights">${totalNights} night${totalNights !== 1 ? 's' : ''} total</span>`
                : '');
        group.rows[0].parentNode.insertBefore(hdr, group.rows[0]);
    });
}

// Known city coordinates for map rendering
const STOP_CITY_COORDS = {
    'manila':{'lat':14.5995,'lng':120.9842},'paris':{'lat':48.8566,'lng':2.3522},
    'zurich':{'lat':47.3769,'lng':8.5417},'milan':{'lat':45.4642,'lng':9.1900},
    'florence':{'lat':43.7696,'lng':11.2558},'rome':{'lat':41.9028,'lng':12.4964},
    'london':{'lat':51.5074,'lng':-0.1278},'barcelona':{'lat':41.3851,'lng':2.1734},
    'madrid':{'lat':40.4168,'lng':-3.7038},'amsterdam':{'lat':52.3676,'lng':4.9041},
    'berlin':{'lat':52.5200,'lng':13.4050},'prague':{'lat':50.0755,'lng':14.4378},
    'vienna':{'lat':48.2082,'lng':16.3738},'budapest':{'lat':47.4979,'lng':19.0402},
    'athens':{'lat':37.9838,'lng':23.7275},'istanbul':{'lat':41.0082,'lng':28.9784},
    'dubai':{'lat':25.2048,'lng':55.2708},'tokyo':{'lat':35.6762,'lng':139.6503},
    'osaka':{'lat':34.6937,'lng':135.5023},'kyoto':{'lat':35.0116,'lng':135.7681},
    'singapore':{'lat':1.3521,'lng':103.8198},'bangkok':{'lat':13.7563,'lng':100.5018},
    'new york':{'lat':40.7128,'lng':-74.0060},'los angeles':{'lat':34.0522,'lng':-118.2437},
    'sydney':{'lat':-33.8688,'lng':151.2093},'melbourne':{'lat':-37.8136,'lng':144.9631},
    'toronto':{'lat':43.6532,'lng':-79.3832},'vancouver':{'lat':49.2827,'lng':-123.1207},
    'mexico city':{'lat':19.4326,'lng':-99.1332},'cairo':{'lat':30.0444,'lng':31.2357},
    'nairobi':{'lat':-1.2921,'lng':36.8219},'johannesburg':{'lat':-26.2041,'lng':28.0473},
    'buenos aires':{'lat':-34.6037,'lng':-58.3816},'rio de janeiro':{'lat':-22.9068,'lng':-43.1729},
    'lisbon':{'lat':38.7169,'lng':-9.1399},'brussels':{'lat':50.8503,'lng':4.3517},
    'geneva':{'lat':46.2044,'lng':6.1432},'nice':{'lat':43.7102,'lng':7.2620},
    'venice':{'lat':45.4408,'lng':12.3155},'naples':{'lat':40.8518,'lng':14.2681},
    'munich':{'lat':48.1351,'lng':11.5820},'frankfurt':{'lat':50.1109,'lng':8.6821},
    'copenhagen':{'lat':55.6761,'lng':12.5683},'stockholm':{'lat':59.3293,'lng':18.0686},
    'oslo':{'lat':59.9139,'lng':10.7522},'helsinki':{'lat':60.1699,'lng':24.9384},
    'warsaw':{'lat':52.2297,'lng':21.0122},'krakow':{'lat':50.0647,'lng':19.9450},
    'cebu':{'lat':10.3157,'lng':123.8854},'boracay':{'lat':11.9674,'lng':121.9248},
};

function haversineKm(lng1, lat1, lng2, lat2) {
    const R = 6371, toRad = x => x * Math.PI / 180;
    const dLat = toRad(lat2-lat1), dLng = toRad(lng2-lng1);
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLng/2)**2;
    return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

let stopsMapInstance = null;
let stopsMapMarkers = [];
let stopsMapLine = null;
let stopsWaypointMarkers = []; // amber lettered markers for within-day routes

function initStopsMap() {
    const mapEl = document.getElementById('adminStopsMap');
    if (!mapEl || stopsMapInstance) return;
    const token = mapEl.dataset.mapboxToken;
    if (!token || typeof mapboxgl === 'undefined') return;
    mapboxgl.accessToken = token;
    stopsMapInstance = new mapboxgl.Map({
        container: 'adminStopsMap',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [20, 20],
        zoom: 1.5
    });
    stopsMapInstance.addControl(new mapboxgl.NavigationControl(), 'top-right');
    stopsMapInstance.on('load', refreshStopsMap);
}

async function refreshStopsMap() {
    if (!stopsMapInstance || !stopsMapInstance.loaded()) return;

    const map = stopsMapInstance;
    const token = document.getElementById('adminStopsMap')?.dataset.mapboxToken;

    // Clear old markers
    stopsMapMarkers.forEach(m => m.remove());
    stopsMapMarkers = [];
    stopsWaypointMarkers.forEach(m => m.remove());
    stopsWaypointMarkers = [];

    // Remove existing route layers/sources
    ['stops-route', 'stops-waypoints-glow', 'stops-waypoints'].forEach(id => {
        if (map.getLayer(id)) map.removeLayer(id);
        if (map.getSource(id)) map.removeSource(id);
    });
    // Remove any per-day waypoint layers from previous run
    map.getStyle().layers.forEach(l => {
        if (l.id.startsWith('day-wp-')) { map.removeLayer(l.id); }
    });
    [...(map.getStyle().sources ? Object.keys(map.getStyle().sources) : [])].forEach(s => {
        if (s.startsWith('day-wp-')) map.removeSource(s);
    });

    // Geocode helper — uses bbox ±4° around base city to stay in the right region
    async function geocode(name, baseLng, baseLat) {
        const key = name.toLowerCase().trim();
        if (STOP_CITY_COORDS[key]) return STOP_CITY_COORDS[key];
        if (!token) return null;
        try {
            const q = encodeURIComponent(name);
            let url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${q}.json?access_token=${token}&limit=1&types=place,locality,poi,region,address,neighborhood`;
            if (baseLng != null && baseLat != null) {
                const pad = 4;
                url += `&bbox=${baseLng-pad},${baseLat-pad},${baseLng+pad},${baseLat+pad}`;
            }
            const d = await fetch(url).then(r => r.json());
            const f = d.features && d.features[0];
            return f ? { lng: f.center[0], lat: f.center[1] } : null;
        } catch { return null; }
    }

    // Collect day rows in DOM order
    const dayRows = document.querySelectorAll('.stop-day-row');
    const mainCoords = [];      // blue main-city route
    const allWpByDay = [];      // amber waypoint coords per day (for sub-route lines)

    let dayIdx = 0;
    for (const row of dayRows) {
        const cityInput = row.querySelector('.stop-city-input');
        const wpInput = row.querySelector('input[name*="[waypoints]"]');
        const wpRaw = wpInput ? wpInput.value.trim() : '';

        // ----- Main city marker -----
        let lng = parseFloat(cityInput?.dataset.lng);
        let lat = parseFloat(cityInput?.dataset.lat);
        if (isNaN(lng) || isNaN(lat)) {
            const c = STOP_CITY_COORDS[(cityInput?.value || '').toLowerCase().trim()];
            if (c) { lng = c.lng; lat = c.lat; }
        }
        if (!isNaN(lng) && !isNaN(lat)) {
            mainCoords.push([lng, lat]);
            const el = document.createElement('div');
            el.style.cssText = `width:28px;height:28px;border-radius:50%;background:#2563eb;color:#fff;
                display:flex;align-items:center;justify-content:center;font-size:12px;
                font-weight:700;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4);cursor:pointer;z-index:2`;
            el.textContent = ++dayIdx;
            const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup({ offset: 16 }).setText(cityInput.value.trim()))
                .addTo(map);
            stopsMapMarkers.push(marker);
        }

        // ----- Waypoints -----
        if (wpRaw) {
            const parts = wpRaw.split(',').map(w => w.trim()).filter(Boolean);
            if (parts.length > 1) {
                // Use the base city's resolved coords as bbox anchor (±4°)
                const bboxLng = !isNaN(lng) ? lng : null;
                const bboxLat = !isNaN(lat) ? lat : null;
                const wpCoords = [];
                const wpResolved = [];
                for (let wi = 0; wi < parts.length; wi++) {
                    const c = await geocode(parts[wi], bboxLng, bboxLat);
                    if (!c) continue;
                    wpCoords.push([c.lng, c.lat]);
                    wpResolved.push({ name: parts[wi], ...c });
                }
                if (wpCoords.length > 1) {
                    const dists = wpResolved.slice(1).map((wp,i) => haversineKm(wpResolved[i].lng,wpResolved[i].lat,wp.lng,wp.lat));
                    const lblFeatures = wpResolved.slice(1).map((wp,i) => ({
                        type:'Feature',
                        geometry:{type:'Point',coordinates:[(wpResolved[i].lng+wp.lng)/2,(wpResolved[i].lat+wp.lat)/2]},
                        properties:{label:`~${dists[i]} km`}
                    }));
                    // Update marker popups to include distance
                    wpResolved.forEach((wp,wi) => {
                        if (wi > 0) wp.distLabel = `~${dists[wi-1]} km from prev`;
                    });
                    allWpByDay.push({ sourceId:`day-wp-src-${dayIdx}`, layerGlow:`day-wp-glow-${dayIdx}`, layerId:`day-wp-${dayIdx}`, coords:wpCoords, lblSourceId:`day-wp-lbl-src-${dayIdx}`, lblLayerId:`day-wp-lbl-${dayIdx}`, lblFeatures, wpResolved });
                    // Amber lettered markers with distance in popup
                    wpResolved.forEach((wp, wi) => {
                        const el = document.createElement('div');
                        el.style.cssText = `width:22px;height:22px;border-radius:50%;background:#d97706;color:#fff;
                            display:flex;align-items:center;justify-content:center;font-size:10px;
                            font-weight:800;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35);cursor:pointer;z-index:3`;
                        el.textContent = String.fromCharCode(65 + wi);
                        const distHtml = wp.distLabel ? `<br><small style="color:#b45309">${wp.distLabel}</small>` : '';
                        const m = new mapboxgl.Marker({ element: el, anchor: 'center' })
                            .setLngLat([wp.lng, wp.lat])
                            .setPopup(new mapboxgl.Popup({ offset: 12, closeButton: false }).setHTML(`<strong style="font-size:.8rem">${wp.name}</strong>${distHtml}`))
                            .addTo(map);
                        stopsWaypointMarkers.push(m);
                    });
                }
            }
        }
    }

    // Draw main blue route
    const routeGeoJSON = { type: 'Feature', geometry: { type: 'LineString', coordinates: mainCoords } };
    if (mainCoords.length > 1) {
        map.addSource('stops-route', { type: 'geojson', data: routeGeoJSON });
        map.addLayer({ id: 'stops-route', type: 'line', source: 'stops-route',
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#2563eb', 'line-width': 3, 'line-dasharray': [2, 1.5] } });
    }

    // Draw amber waypoint sub-routes + distance labels
    for (const wp of allWpByDay) {
        map.addSource(wp.sourceId, { type: 'geojson', data: { type: 'Feature', geometry: { type: 'LineString', coordinates: wp.coords } } });
        map.addLayer({ id: wp.layerGlow, type: 'line', source: wp.sourceId,
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#f59e0b', 'line-width': 7, 'line-opacity': 0.22 } });
        map.addLayer({ id: wp.layerId, type: 'line', source: wp.sourceId,
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#d97706', 'line-width': 2.5 } });
        if (wp.lblFeatures && wp.lblFeatures.length) {
            map.addSource(wp.lblSourceId, { type:'geojson', data:{ type:'FeatureCollection', features: wp.lblFeatures } });
            map.addLayer({ id: wp.lblLayerId, type:'symbol', source: wp.lblSourceId,
                layout:{ 'text-field':['get','label'], 'text-size':11, 'text-anchor':'center', 'text-offset':[0,-1] },
                paint:{ 'text-color':'#92400e', 'text-halo-color':'#fffbeb', 'text-halo-width':1.5 }
            });
        }
    }

    // Fit map to bounds
    const allCoords = [...mainCoords, ...allWpByDay.flatMap(w => w.coords)];
    if (allCoords.length === 1) {
        map.flyTo({ center: allCoords[0], zoom: 6 });
    } else if (allCoords.length > 1) {
        const bounds = allCoords.reduce((b, c) => b.extend(c), new mapboxgl.LngLatBounds(allCoords[0], allCoords[0]));
        map.fitBounds(bounds, { padding: 60, maxZoom: 8 });
    }
}

let fullStopIdx = 0;
function addFullStop(data) {
    data = data || {};
    const i = fullStopIdx++;
    const dayNum = (i + 1).toString().padStart(2, '0');
    // Build existing images HTML (support both legacy `image` string and new `images` array)
    const existingImgs = (data.images && data.images.length)
        ? data.images
        : (data.image ? [data.image] : []);
    const existingImgsHtml = existingImgs.filter(Boolean).map((imgUrl, j) => {
        const slotId = `simg_${i}_${j}`;
        return `<div class="stop-img-item" id="${slotId}" draggable="true" title="Drag to reorder" style="position:relative;display:inline-block;vertical-align:top;cursor:grab">
            <img src="${esc(imgUrl)}" style="height:64px;width:64px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;display:block">
            <span style="position:absolute;left:4px;top:4px;background:rgba(22,163,74,.92);color:#fff;border-radius:999px;padding:2px 6px;font-size:10px;font-weight:700;letter-spacing:.02em;pointer-events:none">Saved</span>
            <input type="hidden" name="full_stops[${i}][images][]" value="${esc(imgUrl)}">
            <div style="display:flex;gap:.2rem;justify-content:center;margin-top:.25rem">
                <button type="button" onclick="moveStopImageItem('${slotId}', -1)" aria-label="Move image left" title="Move left" style="min-width:24px;height:24px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;font-size:.8rem;line-height:1;padding:0">&#8592;</button>
                <button type="button" onclick="moveStopImageItem('${slotId}', 1)" aria-label="Move image right" title="Move right" style="min-width:24px;height:24px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;font-size:.8rem;line-height:1;padding:0">&#8594;</button>
            </div>
            <button type="button" onclick="document.getElementById('${slotId}').remove()" style="position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:#ef4444;color:#fff;border:none;font-size:.7rem;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;padding:0">&times;</button>
        </div>`;
    }).join('');

    const html = `<div class="repeatable-row stop-day-row" id="fs_${i}" style="border:1px solid #e2e8f0;border-radius:10px;padding:1rem;margin-bottom:1rem;background:#fff;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
            <span style="font-weight:700;font-size:1rem;color:#2563eb;letter-spacing:.05em;">DAY ${dayNum}</span>
            <button type="button" class="remove-row" onclick="removeRow('fs_${i}');refreshStopsMap();rerenderGroupBrackets()"><i class="fas fa-times"></i></button>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label>City / Location</label>
                <input type="text" name="full_stops[${i}][city]" class="form-control stop-city-input"
                       value="${esc(data.city||'')}"
                       oninput="refreshStopsMap();rerenderGroupBrackets()"
                       onblur="autofillCountry(this)"
                       placeholder="e.g. Paris">
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="full_stops[${i}][country]" class="form-control" value="${esc(data.country||'')}">
            </div>
            <div class="form-group">
                <label>Nights</label>
                <input type="number" name="full_stops[${i}][days]" class="form-control" value="${data.days||''}" min="0" placeholder="0"
                       oninput="rerenderGroupBrackets()">
            </div>
        </div>
        <input type="hidden" name="full_stops[${i}][_idx]" value="${i}">
        <div class="form-group">
            <label>Day Title</label>
            <input type="text" name="full_stops[${i}][day_title]" class="form-control"
                   value="${esc(data.day_title||'')}" placeholder="e.g. DEPARTURE MANILA - PARIS">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="full_stops[${i}][description]" class="form-control" rows="4" placeholder="Day activities and highlights...">${esc(data.description||'')}</textarea>
        </div>
        <div class="form-group">
            <label>Optional Activity <small class="text-muted">(leave blank if none)</small></label>
            <input type="text" name="full_stops[${i}][optional_activity]" class="form-control"
                   value="${esc(data.optional_activity||'')}" placeholder="e.g. Optional: Disneyland Paris Tour">
        </div>
        <div class="form-group">
            <label>Day Route / Waypoints <small class="text-muted">comma-separated locations visited this day — e.g. Zurich, Mt. Titlis, Lucerne, Zurich</small></label>
            <input type="text" name="full_stops[${i}][waypoints]" class="form-control stop-waypoints-input"
                   value="${esc(data.waypoints||'')}" placeholder="e.g. Zurich, Mt. Titlis, Lucerne, Zurich"
                   onchange="refreshStopsMap()">
        </div>
        <div class="form-group">
            <label>Approximate Travel Times <small class="text-muted">(one per line &mdash; e.g. Paris - Zurich: 6hrs 56mins)</small></label>
            <textarea name="full_stops[${i}][travel_times]" class="form-control" rows="2" placeholder="Paris - Zurich: 6hrs 56mins">${esc(data.travel_times||'')}</textarea>
        </div>
        <div class="form-group">
            <label>Place Images <small class="text-muted">(slideshow on map overlay when this day is clicked &mdash; up to 5)</small></label>
            <div id="stop_imgs_${i}" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem;align-items:flex-start">${existingImgsHtml}</div>
            <label style="cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .8rem;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;font-size:.8125rem;font-weight:500;color:#475569;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'">
                <i class="fas fa-plus" style="font-size:.75rem"></i> Add Image
                <input type="file" accept="image/*" style="display:none" onchange="addStopImageFile(${i},this)">
            </label>
        </div>
    </div>`;
    document.getElementById('fullStopsContainer').insertAdjacentHTML('beforeend', html);
    const imgWrap = document.getElementById(`stop_imgs_${i}`);
    if (imgWrap) enableStopImageReorder(imgWrap);
    ensureStopImageSubmitGuard();

    // Attach live autocomplete to the newly inserted city input
    const newInput = document.querySelector(`#fs_${i} .stop-city-input`);
    if (newInput) attachCityAutocomplete(newInput);
    refreshStopsMap();
    rerenderGroupBrackets();
}

function enableStopImageReorder(container) {
    if (!container || container.dataset.dragBound === '1') return;
    container.dataset.dragBound = '1';

    let dragging = null;

    container.addEventListener('dragstart', (e) => {
        const item = e.target.closest('.stop-img-item');
        if (!item) return;
        dragging = item;
        item.style.opacity = '.55';
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', item.id || 'drag'); } catch (_) {}
        }
    });

    container.addEventListener('dragend', () => {
        if (!dragging) return;
        dragging.style.opacity = '1';
        dragging = null;
    });

    container.addEventListener('dragover', (e) => {
        if (!dragging) return;
        e.preventDefault();
        const over = e.target.closest('.stop-img-item');
        if (!over || over === dragging) return;

        const rect = over.getBoundingClientRect();
        const insertAfter = e.clientX > rect.left + rect.width / 2;
        container.insertBefore(dragging, insertAfter ? over.nextSibling : over);
    });
}

function moveStopImageItem(itemId, direction) {
    const item = document.getElementById(itemId);
    if (!item || !item.parentElement) return;

    const container = item.parentElement;
    if (direction < 0) {
        const prev = item.previousElementSibling;
        if (prev) {
            container.insertBefore(item, prev);
            const moveBtn = item.querySelector('button[aria-label="Move image left"], button[aria-label="Move image right"]');
            if (moveBtn) moveBtn.focus();
        }
        return;
    }

    const next = item.nextElementSibling;
    if (next) {
        container.insertBefore(next, item);
        const moveBtn = item.querySelector('button[aria-label="Move image left"], button[aria-label="Move image right"]');
        if (moveBtn) moveBtn.focus();
    }
}

function createStopImagePickerInput(stopIdx) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.style.display = 'none';
    input.onchange = function () { addStopImageFile(stopIdx, this); };
    return input;
}

const STOP_IMAGE_DAY_BUDGET_BYTES = 12 * 1024 * 1024;
const STOP_IMAGE_TOTAL_BUDGET_BYTES = 16 * 1024 * 1024;

function formatBytes(bytes) {
    if (!bytes) return '0 MB';
    return `${(bytes / (1024 * 1024)).toFixed(bytes >= 10 * 1024 * 1024 ? 0 : 1)} MB`;
}

function getQueuedStopImageBytes(container) {
    if (!container) return 0;
    return Array.from(container.querySelectorAll('.stop-img-item input[type="file"]'))
        .reduce((sum, input) => {
            const file = input.files && input.files[0];
            return sum + (file ? file.size : 0);
        }, 0);
}

function getTotalQueuedStopImageBytes() {
    return Array.from(document.querySelectorAll('.stop-img-item input[type="file"][name^="full_stop_images["]'))
        .reduce((sum, input) => {
            const file = input.files && input.files[0];
            return sum + (file ? file.size : 0);
        }, 0);
}

function ensureStopImageSubmitGuard() {
    const form = document.getElementById('tourForm');
    if (!form || form.dataset.stopImageBudgetBound === '1') return;
    form.dataset.stopImageBudgetBound = '1';

    form.addEventListener('submit', function (event) {
        const totalBytes = getTotalQueuedStopImageBytes();
        if (totalBytes > STOP_IMAGE_TOTAL_BUDGET_BYTES) {
            event.preventDefault();
            alert(`Queued place images are too large for one submission (${formatBytes(totalBytes)}). Please remove some images or use smaller files. Limit: ${formatBytes(STOP_IMAGE_TOTAL_BUDGET_BYTES)} total queued place images.`);
            return;
        }

        const oversizeDay = Array.from(document.querySelectorAll('[id^="stop_imgs_"]')).find(container => getQueuedStopImageBytes(container) > STOP_IMAGE_DAY_BUDGET_BYTES);
        if (oversizeDay) {
            event.preventDefault();
            alert(`One itinerary day has too many queued place images (${formatBytes(getQueuedStopImageBytes(oversizeDay))}). Limit: ${formatBytes(STOP_IMAGE_DAY_BUDGET_BYTES)} per day.`);
        }
    });
}

async function addStopImageFile(stopIdx, input) {
    const file = input.files && input.files[0];
    if (!file) return;

    const container = document.getElementById(`stop_imgs_${stopIdx}`);
    if (!container) return;

    // Limit to 5 images per day
    if (container.querySelectorAll('.stop-img-item').length >= 5) {
        alert('Maximum 5 images per day.');
        input.value = '';
        return;
    }

    // Keep the real selected input in the form so uploads survive submit.
    // If optimization can be applied, replace the selected file on that same input.
    let previewFile = file;
    let wasOptimized = false;
    try {
        const optimized = await optimizeImageFile(file);
        if (optimized && optimized !== file) {
            applyFilesToInput(input, [optimized]);
            previewFile = optimized;
            wasOptimized = true;
        }
    } catch (e) {
        previewFile = file;
    }

    // Only enforce budget limits when we know the optimized (smaller) size.
    // If optimization returned the original file, skip the in-page guard and
    // rely on server-side validation to catch genuinely oversized uploads.
    if (wasOptimized) {
        const projectedDayBytes = getQueuedStopImageBytes(container) + previewFile.size;
        if (projectedDayBytes > STOP_IMAGE_DAY_BUDGET_BYTES) {
            alert(`This image would bring this day's queue to ${formatBytes(projectedDayBytes)}, exceeding the ${formatBytes(STOP_IMAGE_DAY_BUDGET_BYTES)} per-day limit. Please remove an existing image first.`);
            input.value = '';
            return;
        }

        const projectedTotalBytes = getTotalQueuedStopImageBytes() + previewFile.size;
        if (projectedTotalBytes > STOP_IMAGE_TOTAL_BUDGET_BYTES) {
            alert(`This image would bring the total queued images to ${formatBytes(projectedTotalBytes)}, exceeding the ${formatBytes(STOP_IMAGE_TOTAL_BUDGET_BYTES)} total limit. Please remove images from other days first.`);
            input.value = '';
            return;
        }
    }

    // Turn the used picker into a submitted form field.
    input.name = `full_stop_images[${stopIdx}][]`;
    input.removeAttribute('onchange');
    input.onchange = null;
    input.style.display = 'none';

    // Replace the picker in the add-image label with a fresh empty picker.
    const pickerLabel = input.closest('label');
    if (pickerLabel) {
        input.remove();
        pickerLabel.appendChild(createStopImagePickerInput(stopIdx));
    }

    const slotId = `simg_${stopIdx}_new_${Date.now()}`;
    const slot = document.createElement('div');
    slot.className = 'stop-img-item';
    slot.id = slotId;
    slot.draggable = true;
    slot.title = 'Drag to reorder';
    slot.style.cssText = 'position:relative;display:inline-block;vertical-align:top;cursor:grab';

    const preview = document.createElement('img');
    preview.src = URL.createObjectURL(previewFile);
    preview.style.cssText = 'height:64px;width:64px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;display:block';

    const queuedBadge = document.createElement('span');
    queuedBadge.textContent = 'Queued';
    queuedBadge.style.cssText = 'position:absolute;left:4px;top:4px;background:rgba(37,99,235,.92);color:#fff;border-radius:999px;padding:2px 6px;font-size:10px;font-weight:700;letter-spacing:.02em;pointer-events:none';

    const controls = document.createElement('div');
    controls.style.cssText = 'display:flex;gap:.2rem;justify-content:center;margin-top:.25rem';

    const moveLeftBtn = document.createElement('button');
    moveLeftBtn.type = 'button';
    moveLeftBtn.innerHTML = '&#8592;';
    moveLeftBtn.setAttribute('aria-label', 'Move image left');
    moveLeftBtn.title = 'Move left';
    moveLeftBtn.style.cssText = 'min-width:24px;height:24px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;font-size:.8rem;line-height:1;padding:0';
    moveLeftBtn.onclick = () => moveStopImageItem(slotId, -1);

    const moveRightBtn = document.createElement('button');
    moveRightBtn.type = 'button';
    moveRightBtn.innerHTML = '&#8594;';
    moveRightBtn.setAttribute('aria-label', 'Move image right');
    moveRightBtn.title = 'Move right';
    moveRightBtn.style.cssText = 'min-width:24px;height:24px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#334155;font-size:.8rem;line-height:1;padding:0';
    moveRightBtn.onclick = () => moveStopImageItem(slotId, 1);

    controls.appendChild(moveLeftBtn);
    controls.appendChild(moveRightBtn);

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.innerHTML = '&times;';
    removeBtn.style.cssText = 'position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:#ef4444;color:#fff;border:none;font-size:.7rem;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;padding:0';
    removeBtn.onclick = () => {
        const fileField = slot.querySelector('input[type="file"]');
        if (fileField) fileField.remove();
        slot.remove();
    };

    slot.appendChild(preview);
    slot.appendChild(queuedBadge);
    slot.appendChild(controls);
    slot.appendChild(input);
    slot.appendChild(removeBtn);
    container.appendChild(slot);
    enableStopImageReorder(container);
    ensureStopImageSubmitGuard();
}

// ─── Main Cities by Country ───────────────────────────────────────────────────
let mainCitiesIdx = 0;
function addMainCitiesRow(data) {
    data = data || {};
    const i = mainCitiesIdx++;
    const html = `<div class="repeatable-row" id="mc_${i}">
        <button type="button" class="remove-row" onclick="removeRow('mc_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-2">
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="ai_main_cities[${i}][country]" class="form-control" value="${esc(data.country||'')}">
            </div>
            <div class="form-group">
                <label>Cities <small>(comma-separated)</small></label>
                <input type="text" name="ai_main_cities[${i}][cities_text]" class="form-control" value="${esc(data.cities_text||'')}" placeholder="Tokyo, Kyoto, Osaka">
            </div>
        </div>
    </div>`;
    document.getElementById('mainCitiesContainer').insertAdjacentHTML('beforeend', html);
}

// ─── Name + Image Pairs (countries / cities) ─────────────────────────────────
const nameImageCounters = {};
function addNameImagePair(containerId, fieldName, data) {
    data = data || {};
    if (nameImageCounters[containerId] === undefined) nameImageCounters[containerId] = 0;
    const i = nameImageCounters[containerId]++;
    const html = `<div class="repeatable-row" id="${containerId}_${i}">
        <button type="button" class="remove-row" onclick="removeRow('${containerId}_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-2">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="${fieldName}[${i}][name]" class="form-control" value="${esc(data.name||'')}">
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="${fieldName}[${i}][image]" class="form-control" value="${esc(data.image||'')}" placeholder="https://...">
            </div>
        </div>
    </div>`;
    document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
}

// ─── Flipbook / Presentation Links by Year ──────────────────────────────────
let bookingLinkIdx = 0;
function addBookingLinkYear(data) {
    data = data || {};
    const i = bookingLinkIdx++;
    const urls = data.urls || [''];
    const urlInputs = urls.map((u, j) =>
        `<div style="display:flex;gap:.5rem;margin-bottom:.3rem">
            <input type="url" name="booking_links[${i}][urls][]" class="form-control" value="${esc(u)}" placeholder="https://...">
            <button type="button" class="btn btn-outline btn-sm" onclick="addBookingUrl(this)"><i class="fas fa-plus"></i></button>
        </div>`
    ).join('');
    const html = `<div class="repeatable-row" id="bl_${i}">
        <button type="button" class="remove-row" onclick="removeRow('bl_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-group">
            <label>Year</label>
            <input type="number" name="booking_links[${i}][year]" class="form-control" value="${data.year||new Date().getFullYear()}" min="2000" max="2100" style="max-width:120px">
        </div>
        <div class="form-group">
            <label>Flipbook / Presentation URLs</label>
            <div class="bl-urls-${i}">${urlInputs}</div>
        </div>
    </div>`;
    document.getElementById('bookingLinksContainer').insertAdjacentHTML('beforeend', html);
}
function addBookingUrl(btn) {
    const urlsDiv = btn.closest('.repeatable-row').querySelector('[class^="bl-urls-"]');
    const tmpl = btn.closest('div').cloneNode(true);
    tmpl.querySelector('input').value = '';
    urlsDiv.appendChild(tmpl);
}

// ─── Optional Tours ───────────────────────────────────────────────────────────
let optionalTourIdx = 0;
function addOptionalTour(data) {
    data = data || {};
    const i = optionalTourIdx++;
    const promoTypes = ['percentage','fixed'];
    const html = `<div class="repeatable-row" id="ot_${i}">
        <button type="button" class="remove-row" onclick="removeRow('ot_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-2">
            <div class="form-group">
                <label>Day #</label>
                <input type="number" name="optional_tours[${i}][day]" class="form-control" value="${data.day||''}" min="1">
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="optional_tours[${i}][title]" class="form-control" value="${esc(data.title||'')}">
            </div>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label>Regular Price ($)</label>
                <input type="number" name="optional_tours[${i}][regularPrice]" class="form-control" value="${data.regularPrice||''}" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Promo Type</label>
                <select name="optional_tours[${i}][promoType]" class="form-control">
                    <option value="">None</option>
                    ${promoTypes.map(t => `<option value="${t}" ${(data.promoType||'')===t?'selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
                </select>
            </div>
            <div class="form-group">
                <label>Promo Value</label>
                <input type="number" name="optional_tours[${i}][promoValue]" class="form-control" value="${data.promoValue||''}" step="0.01" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Flipbook/Detail URL</label>
            <input type="text" name="optional_tours[${i}][flipbookUrl]" class="form-control" value="${esc(data.flipbookUrl||'')}" placeholder="https://...">
        </div>
    </div>`;
    document.getElementById('optionalToursContainer').insertAdjacentHTML('beforeend', html);
}

// ─── Cash Freebies ────────────────────────────────────────────────────────────
let cashFreebieIdx = 0;
function addCashFreebie(data) {
    data = data || {};
    const i = cashFreebieIdx++;
    const types = ['cash','item','discount','voucher','other'];
    const html = `<div class="repeatable-row" id="cf_${i}">
        <button type="button" class="remove-row" onclick="removeRow('cf_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-3">
            <div class="form-group">
                <label>Label</label>
                <input type="text" name="cash_freebies[${i}][label]" class="form-control" value="${esc(data.label||'')}" placeholder="e.g. $50 dining credit">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="cash_freebies[${i}][type]" class="form-control">
                    ${types.map(t => `<option value="${t}" ${(data.type||'')===t?'selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
                </select>
            </div>
            <div class="form-group">
                <label>Value ($)</label>
                <input type="number" name="cash_freebies[${i}][value]" class="form-control" value="${data.value||''}" step="0.01" min="0">
            </div>
        </div>
    </div>`;
    document.getElementById('cashFreebiesContainer').insertAdjacentHTML('beforeend', html);
}

// ─── Utilities ────────────────────────────────────────────────────────────────
function removeRow(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}
function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;');
}

// ─── Image Cropper for Uploads ─────────────────────────────────────────────
(function initUploadCropper() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupUploadCropper);
    } else {
        setupUploadCropper();
    }

    function setupUploadCropper() {
        if (typeof Cropper === 'undefined') return;

        injectUploadCropperStyles();

        const inputs = Array.from(document.querySelectorAll([
            'input[type="file"][name="main_image"]',
            'input[type="file"][name="gallery_image_files[]"]',
            'input[type="file"][name="related_image_files[]"]',
            'input[type="file"][name^="full_stop_images["]'
        ].join(',')));

        inputs.forEach(input => {
            if (input.dataset.cropperReady === '1') return;
            input.dataset.cropperReady = '1';

            input.addEventListener('change', async function () {
                if (input.dataset.cropperApplying === '1') {
                    input.dataset.cropperApplying = '0';
                    return;
                }

                const files = Array.from(input.files || []);
                if (!files.length) return;

                const processed = [];

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type || !file.type.startsWith('image/')) {
                        processed.push(file);
                        continue;
                    }

                    // Ask crop action one file at a time so users can position each image precisely.
                    const cropped = await openCropDialog(file, i + 1, files.length);
                    const candidate = cropped || file;
                    const optimized = await optimizeImageFile(candidate);
                    processed.push(optimized || candidate);
                }

                applyFilesToInput(input, processed);
            });
        });
    }

    function injectUploadCropperStyles() {
        if (document.getElementById('upload-cropper-styles')) return;
        const style = document.createElement('style');
        style.id = 'upload-cropper-styles';
        style.textContent = `
            .upload-cropper-overlay{position:fixed;inset:0;background:rgba(15,23,42,.72);z-index:1200;display:flex;align-items:center;justify-content:center;padding:1rem}
            .upload-cropper-modal{width:min(980px,100%);max-height:92vh;background:#fff;border-radius:12px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 52px rgba(0,0,0,.35)}
            .upload-cropper-header{display:flex;align-items:center;justify-content:space-between;padding:.8rem 1rem;border-bottom:1px solid #e2e8f0;gap:1rem}
            .upload-cropper-title{font-weight:700;color:#0f172a;font-size:.95rem}
            .upload-cropper-note{font-size:.8rem;color:#64748b}
            .upload-cropper-body{padding:1rem;background:#f8fafc;overflow:auto}
            .upload-cropper-image-wrap{height:min(62vh,560px);background:#fff;border:1px solid #dbe4ef;border-radius:10px;overflow:hidden}
            .upload-cropper-image{display:block;max-width:100%}
            .upload-cropper-toolbar{padding:.8rem 1rem;border-top:1px solid #e2e8f0;display:flex;gap:.5rem;justify-content:space-between;align-items:center;flex-wrap:wrap}
            .upload-cropper-tools{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
            .upload-cropper-select{height:34px;min-width:136px;border:1px solid #cbd5e1;border-radius:6px;padding:0 .5rem;background:#fff}
            @media(max-width:640px){.upload-cropper-body{padding:.7rem}.upload-cropper-header,.upload-cropper-toolbar{padding:.7rem}}
        `;
        document.head.appendChild(style);
    }

    async function openCropDialog(file, fileIndex, totalFiles) {
        const modal = ensureCropperModal();
        modal.index.textContent = totalFiles > 1 ? `Image ${fileIndex} of ${totalFiles}` : 'Image upload';
        modal.name.textContent = file.name;
        modal.fileType = file.type;

        const objectUrl = URL.createObjectURL(file);
        modal.img.src = objectUrl;
        modal.overlay.style.display = 'flex';

        if (modal.cropper) {
            modal.cropper.destroy();
            modal.cropper = null;
        }

        await waitForImageLoad(modal.img);

        modal.cropper = new Cropper(modal.img, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            background: false,
            movable: true,
            zoomable: true,
            rotatable: true,
            scalable: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            aspectRatio: NaN
        });

        modal.aspect.value = 'free';

        try {
            const result = await new Promise(resolve => {
                modal.resolve = resolve;
            });
            return result;
        } finally {
            if (modal.cropper) {
                modal.cropper.destroy();
                modal.cropper = null;
            }
            modal.overlay.style.display = 'none';
            URL.revokeObjectURL(objectUrl);
        }
    }

    function ensureCropperModal() {
        if (window.__uploadCropperModal) return window.__uploadCropperModal;

        const overlay = document.createElement('div');
        overlay.className = 'upload-cropper-overlay';
        overlay.style.display = 'none';
        overlay.innerHTML = `
            <div class="upload-cropper-modal" role="dialog" aria-modal="true" aria-label="Crop image upload">
                <div class="upload-cropper-header">
                    <div>
                        <div class="upload-cropper-title">Adjust image position and crop area</div>
                        <div class="upload-cropper-note"><span data-crop-index></span> · <span data-crop-name></span></div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" data-crop-keep>Keep Original</button>
                </div>
                <div class="upload-cropper-body">
                    <div class="upload-cropper-image-wrap">
                        <img class="upload-cropper-image" alt="Crop preview" data-crop-image>
                    </div>
                </div>
                <div class="upload-cropper-toolbar">
                    <div class="upload-cropper-tools">
                        <button type="button" class="btn btn-outline btn-sm" data-crop-zoom-in>Zoom +</button>
                        <button type="button" class="btn btn-outline btn-sm" data-crop-zoom-out>Zoom -</button>
                        <button type="button" class="btn btn-outline btn-sm" data-crop-rotate>Rotate</button>
                        <button type="button" class="btn btn-outline btn-sm" data-crop-reset>Reset</button>
                        <select class="upload-cropper-select" data-crop-aspect>
                            <option value="free">Free ratio</option>
                            <option value="1">1:1 Square</option>
                            <option value="1.3333333333">4:3</option>
                            <option value="1.7777777778">16:9</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-crop-apply>Apply Crop</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const modal = {
            overlay,
            img: overlay.querySelector('[data-crop-image]'),
            index: overlay.querySelector('[data-crop-index]'),
            name: overlay.querySelector('[data-crop-name]'),
            aspect: overlay.querySelector('[data-crop-aspect]'),
            cropper: null,
            fileType: '',
            resolve: null
        };

        const keepBtn = overlay.querySelector('[data-crop-keep]');
        const applyBtn = overlay.querySelector('[data-crop-apply]');
        const zoomInBtn = overlay.querySelector('[data-crop-zoom-in]');
        const zoomOutBtn = overlay.querySelector('[data-crop-zoom-out]');
        const rotateBtn = overlay.querySelector('[data-crop-rotate]');
        const resetBtn = overlay.querySelector('[data-crop-reset]');

        keepBtn.addEventListener('click', function () {
            if (modal.resolve) {
                const resolve = modal.resolve;
                modal.resolve = null;
                resolve(null);
            }
        });

        applyBtn.addEventListener('click', function () {
            if (!modal.cropper || !modal.resolve) return;

            const canvas = modal.cropper.getCroppedCanvas({
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            if (!canvas) {
                const resolve = modal.resolve;
                modal.resolve = null;
                resolve(null);
                return;
            }

            const srcType = modal.fileType === 'image/png' ? 'image/png' : 'image/jpeg';
            canvas.toBlob(function (blob) {
                if (!blob) {
                    const resolve = modal.resolve;
                    modal.resolve = null;
                    resolve(null);
                    return;
                }

                const extension = blob.type === 'image/png' ? 'png' : 'jpg';
                const safeBase = (modal.name.textContent || 'image').replace(/\.[^.]+$/, '');
                const outFile = new File([blob], safeBase + '-cropped.' + extension, {
                    type: blob.type,
                    lastModified: Date.now()
                });

                const resolve = modal.resolve;
                modal.resolve = null;
                resolve(outFile);
            }, srcType, 0.92);
        });

        zoomInBtn.addEventListener('click', function () {
            if (modal.cropper) modal.cropper.zoom(0.1);
        });

        zoomOutBtn.addEventListener('click', function () {
            if (modal.cropper) modal.cropper.zoom(-0.1);
        });

        rotateBtn.addEventListener('click', function () {
            if (modal.cropper) modal.cropper.rotate(90);
        });

        resetBtn.addEventListener('click', function () {
            if (modal.cropper) modal.cropper.reset();
        });

        modal.aspect.addEventListener('change', function () {
            if (!modal.cropper) return;
            if (modal.aspect.value === 'free') {
                modal.cropper.setAspectRatio(NaN);
                return;
            }
            modal.cropper.setAspectRatio(parseFloat(modal.aspect.value));
        });

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay && modal.resolve) {
                const resolve = modal.resolve;
                modal.resolve = null;
                resolve(null);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.overlay.style.display !== 'none' && modal.resolve) {
                const resolve = modal.resolve;
                modal.resolve = null;
                resolve(null);
            }
        });

        window.__uploadCropperModal = modal;
        return modal;
    }

    function applyFilesToInput(input, files) {
        const dt = new DataTransfer();
        files.forEach(file => dt.items.add(file));
        input.dataset.cropperApplying = '1';
        input.files = dt.files;
    }

    function waitForImageLoad(img) {
        if (img.complete && img.naturalWidth > 0) return Promise.resolve();
        return new Promise(resolve => {
            img.onload = function () { resolve(); };
            img.onerror = function () { resolve(); };
        });
    }

    async function optimizeImageFile(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) return file;

        const image = await loadImageFromFile(file);
        if (!image) return file;

        const maxLongEdge = 2200;
        const targetBytes = 1.8 * 1024 * 1024;

        const srcW = image.naturalWidth || image.width;
        const srcH = image.naturalHeight || image.height;
        if (!srcW || !srcH) return file;

        const scale = Math.min(1, maxLongEdge / Math.max(srcW, srcH));
        const outW = Math.max(1, Math.round(srcW * scale));
        const outH = Math.max(1, Math.round(srcH * scale));

        const canvas = document.createElement('canvas');
        canvas.width = outW;
        canvas.height = outH;

        const ctx = canvas.getContext('2d', { alpha: true });
        if (!ctx) return file;
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(image, 0, 0, outW, outH);

        const safeBase = file.name.replace(/\.[^.]+$/, '') || 'image';
        const hasAlpha = detectAlphaChannel(ctx, outW, outH);

        // PNGs with alpha are kept as PNG to avoid visual artifacts in transparent areas.
        if (file.type === 'image/png' && hasAlpha) {
            const pngBlob = await canvasToBlob(canvas, 'image/png');
            if (!pngBlob) return file;
            const pngOut = new File([pngBlob], safeBase + '-optimized.png', {
                type: 'image/png',
                lastModified: Date.now()
            });
            return pngOut.size <= file.size ? pngOut : file;
        }

        const qualitySteps = [0.9, 0.84, 0.78, 0.72, 0.66, 0.6];
        let bestBlob = null;

        for (const q of qualitySteps) {
            const blob = await canvasToBlob(canvas, 'image/jpeg', q);
            if (!blob) continue;
            bestBlob = blob;
            if (blob.size <= targetBytes) break;
        }

        if (!bestBlob) return file;

        const optimized = new File([bestBlob], safeBase + '-optimized.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now()
        });

        return optimized.size <= file.size ? optimized : file;
    }

    function loadImageFromFile(file) {
        return new Promise(resolve => {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);
            img.onload = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(null);
            };
            img.src = objectUrl;
        });
    }

    function canvasToBlob(canvas, mimeType, quality) {
        return new Promise(resolve => {
            canvas.toBlob(function (blob) {
                resolve(blob || null);
            }, mimeType, quality);
        });
    }

    function detectAlphaChannel(ctx, width, height) {
        try {
            const sampleStep = Math.max(1, Math.floor((width * height) / 50000));
            const imageData = ctx.getImageData(0, 0, width, height).data;
            for (let i = 3; i < imageData.length; i += 4 * sampleStep) {
                if (imageData[i] < 255) return true;
            }
            return false;
        } catch (e) {
            return false;
        }
    }

    // Expose compression helpers so code outside this IIFE (e.g. addStopImageFile) can use them.
    window.optimizeImageFile = optimizeImageFile;
    window.applyFilesToInput = applyFilesToInput;
})();

// ─── AI Smart Paste ───────────────────────────────────────────────────────────
function toggleSmartPaste() {
    const body     = document.getElementById('aiSmartPasteBody');
    const chevron  = document.getElementById('aiPasteChevron');
    const isOpen   = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    chevron.innerHTML  = isOpen
        ? 'Open <i class="fas fa-chevron-down"></i>'
        : 'Close <i class="fas fa-chevron-up"></i>';
}

function clearSmartPaste() {
    document.getElementById('aiPasteInput').value = '';
    const p = document.getElementById('aiParsePreview');
    if (p) { p.style.display = 'none'; p.innerHTML = ''; }
}

function runSmartPaste() {
    const text = (document.getElementById('aiPasteInput').value || '').trim();
    if (!text) return;
    const btn = document.getElementById('btnParsePaste');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Parsing…'; }
    setTimeout(function () {
        try {
            var data = parseTourText(text);
            applyParsedTourData(data);
            showSmartPastePreview(data);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic"></i> Parse &amp; Auto-fill'; }
        }
    }, 80);
}

// Month name → 0-based index
var _MONTHS = {
    jan:0,january:0,feb:1,february:1,mar:2,march:2,apr:3,april:3,
    may:4,jun:5,june:5,jul:6,july:6,aug:7,august:7,
    sep:8,september:8,oct:9,october:9,nov:10,november:10,dec:11,december:11
};

function _fmtDate(d) {
    return d.getFullYear() + '-' +
           String(d.getMonth() + 1).padStart(2, '0') + '-' +
           String(d.getDate()).padStart(2, '0');
}

// Try to parse a single date-range line, e.g.:
//   "May 13 – 27, 2026 (Php 170,000)"     → same-month range
//   "May 25 – June 8, 2026 (Php 170,000)" → cross-month range
function _parseDateLine(line, data) {
    // extract price (currency-agnostic, just grab the number)
    var price = null;
    var pm = line.match(/[\d,]+(?:\.\d{1,2})?/g);
    // find the largest number in parentheses (most likely the price)
    var parenContent = line.match(/\(([^)]+)\)/);
    if (parenContent) {
        var nums = parenContent[1].match(/[\d,]+(?:\.\d{1,2})?/g);
        if (nums && nums.length) price = parseFloat(nums[nums.length - 1].replace(/,/g, ''));
    }

    // Same-month: "May 13 – 27, 2026"
    var p1 = line.match(/(\w+)\s+(\d{1,2})\s*[–\-]\s*(\d{1,2})\s*,\s*(\d{4})/i);
    if (p1) {
        var mo = _MONTHS[p1[1].toLowerCase()];
        if (mo !== undefined) {
            var yr = parseInt(p1[4]);
            data.departure_dates.push({
                start: _fmtDate(new Date(yr, mo, parseInt(p1[2]))),
                end:   _fmtDate(new Date(yr, mo, parseInt(p1[3]))),
                price: price, maxCapacity: '', currentBookings: 0, isAvailable: true
            });
            return true;
        }
    }
    // Cross-month: "May 25 – June 8, 2026"
    var p2 = line.match(/(\w+)\s+(\d{1,2})\s*[–\-]\s*(\w+)\s+(\d{1,2})\s*,\s*(\d{4})/i);
    if (p2) {
        var m1 = _MONTHS[p2[1].toLowerCase()], m2 = _MONTHS[p2[3].toLowerCase()];
        if (m1 !== undefined && m2 !== undefined) {
            var yr2 = parseInt(p2[5]);
            data.departure_dates.push({
                start: _fmtDate(new Date(yr2, m1, parseInt(p2[2]))),
                end:   _fmtDate(new Date(yr2, m2, parseInt(p2[4]))),
                price: price, maxCapacity: '', currentBookings: 0, isAvailable: true
            });
            return true;
        }
    }
    return false;
}

function parseTourText(text) {
    var lines = text.split('\n');
    var data = {
        title: null, duration_days: null, price: null,
        booking_links: [], departure_dates: [],
        optional_tours: [], cash_freebies: [],
        highlights: [], countries_visited: [],
        optional_link: null, downpayment: null
    };
    var mode = null;
    var firstLine = true;

    for (var li = 0; li < lines.length; li++) {
        var raw  = lines[li];
        var line = raw.trim();
        if (!line) continue;

        // ── Section header triggers ──
        if (/^optional\s+tours?\s*:?\s*$/i.test(line))                          { mode = 'optional';    continue; }
        if (/^excursions?\s*:?\s*$/i.test(line))                                 { mode = 'optional';    continue; }
        if (/^cash\s*(freebies?|allowances?|inclusions?)?\s*:?\s*$/i.test(line)) { mode = 'freebies';    continue; }
        if (/^freebies?\s*:?\s*$/i.test(line))                                   { mode = 'freebies';    continue; }
        if (/^full\s*cash\s+payment\s+freebies?\s*:?\s*$/i.test(line))          { mode = 'freebies';    continue; }
        if (/^(highlights?|inclusions?|what.?s\s+included)\s*:?\s*$/i.test(line)){ mode = 'highlights';  continue; }

        // ── Optional Tours with inline URL: "Optional Tours: https://..." ──
        var optUrlM = line.match(/^optional\s+tours?\s*:\s*(https?:\/\/\S+)/i);
        if (optUrlM) { data.optional_link = optUrlM[1]; mode = 'optional'; continue; }

        // ── Countries to visit: "Country to Visit: X | Y | Z" ──
        var countryM = line.match(/^countr(?:y|ies)\s+to\s+visit\s*:\s*(.+)/i);
        if (countryM) {
            data.countries_visited = countryM[1].split(/\s*[|,]\s*/).map(function(c){ return c.trim(); }).filter(Boolean);
            mode = null; continue;
        }

        // ── Title + Duration (very first content line) ──
        if (firstLine) {
            firstLine = false;
            var dayMatch = line.match(/\((\d+)\s*days?\)/i);
            if (dayMatch) {
                data.duration_days = parseInt(dayMatch[1]);
                data.title = line.replace(/\s*\(\d+\s*days?\)/i, '').trim().replace(/[-–\s]+$/, '').trim();
            } else {
                data.title = line;
            }
            // Strip any inline URL from the title; save it as a booking link
            var urlInTitle = data.title.match(/(https?:\/\/\S+)/i);
            if (urlInTitle) {
                var _u = urlInTitle[1];
                var _yrM = _u.match(/(20\d{2})/);
                var _yr = _yrM ? _yrM[1] : String(new Date().getFullYear());
                data.booking_links.push({ year: _yr, urls: [_u] });
                data.title = data.title.replace(/\s*-?\s*https?:\/\/\S+/i, '').trim().replace(/[-–\s]+$/, '').trim();
            }
            mode = null;
            continue;
        }

        // ── Booking links: "Links for 2026: url1, and url2" ──
        var linksM = line.match(/links?\s+for\s+(\d{4})\s*:\s*(.+)/i);
        if (linksM) {
            var year    = linksM[1];
            var urlPart = linksM[2];
            var urls = urlPart.split(/[\s,]+(?:and\s+)?/i)
                .map(function(u){ return u.trim().replace(/[,.]$/, ''); })
                .filter(function(u){ return /^https?:\/\//i.test(u); });
            if (urls.length) data.booking_links.push({ year: year, urls: urls });
            mode = null;
            continue;
        }

        // ── Travel dates trigger: "Travel Date: …" ──
        if (/^travel\s+dates?\s*:/i.test(line)) {
            mode = 'dates';
            var rest = line.replace(/^travel\s+dates?\s*:\s*/i, '').trim();
            if (rest) _parseDateLine(rest, data);
            continue;
        }

        // ── In "dates" mode ──
        if (mode === 'dates') {
            var isIndented = /^\s/.test(raw);
            var isSectionBreak = !isIndented &&
                /^(optional|cash|freebies?|highlights?|inclusions?|links?\s+for|day\s*\d+\s*:)/i.test(line);
            if (isSectionBreak) {
                mode = null;
                // fall through to process as a different section
            } else {
                var parsed = _parseDateLine(line, data);
                // if unparseable and not indented, exit date mode
                if (!parsed && !isIndented) mode = null;
                else continue;
            }
        }

        // ── Optional tours ──
        if (mode === 'optional') {
            if (/^(cash\s*(freebies?|allowances?)?|freebies?|highlights?|inclusions?|links?\s+for|travel\s+date)/i.test(line)) {
                mode = null; // fall through
            } else {
                // "Day 4: Disneyland Paris Tour (Php 1,500)"
                var otM = line.match(/^day\s*(\d+)\s*[:\-–]\s*(.+?)(?:\s*[\(]\s*(?:php|₱|\$|usd)?\s*([\d,]+(?:\.\d+)?)\s*\))?$/i);
                if (otM) {
                    data.optional_tours.push({
                        day: parseInt(otM[1]),
                        title: otM[2].trim(),
                        regularPrice: otM[3] ? parseFloat(otM[3].replace(/,/g,'')) : null
                    });
                    continue;
                }
                if (!/^day\s*\d+/i.test(line)) mode = null;
                else continue;
            }
        }

        // ── Cash freebies ──
        if (mode === 'freebies') {
            if (/^(optional\s*tours?|excursions?|highlights?|inclusions?|links?\s+for|travel\s+date)/i.test(line)) {
                mode = null;
            } else {
                data.cash_freebies.push(line.replace(/^[-•*]\s*/, '').trim());
                continue;
            }
        }

        // ── Highlights ──
        if (mode === 'highlights') {
            if (/^(optional\s*tours?|cash\s*(freebies?)?|links?\s+for|travel\s+date)/i.test(line)) {
                mode = null;
            } else {
                data.highlights.push(line.replace(/^[-•*]\s*/, '').trim());
                continue;
            }
        }

        // ── Down payment (inline, any position) ──
        var dpM = line.match(/down\s*payment\s*:?\s*(?:php|₱|\$|usd)?\s*([\d,]+(?:\.\d+)?)/i);
        if (dpM) { data.downpayment = parseFloat(dpM[1].replace(/,/g,'')); }

        // ── Inline "Day N: …" outside optional mode ──
        if (mode === null) {
            var inlineOt = line.match(/^day\s*(\d+)\s*[:\-–]\s*(.+?)(?:\s*[\(]\s*(?:php|₱|\$|usd)?\s*([\d,]+(?:\.\d+)?)\s*\))?$/i);
            if (inlineOt) {
                data.optional_tours.push({
                    day: parseInt(inlineOt[1]),
                    title: inlineOt[2].trim(),
                    regularPrice: inlineOt[3] ? parseFloat(inlineOt[3].replace(/,/g,'')) : null
                });
            }
        }
    }
    return data;
}

function applyParsedTourData(data) {
    if (data.title) {
        var t = document.querySelector('[name="title"]');
        if (t) { t.value = data.title; t.dispatchEvent(new Event('input')); }
    }
    if (data.duration_days) {
        var d = document.querySelector('[name="duration_days"]');
        if (d) d.value = data.duration_days;
    }
    // Price: use explicit data.price or fall back to the first departure date's price
    var resolvedPrice = data.price || (data.departure_dates.length && data.departure_dates[0].price) || null;
    if (resolvedPrice) {
        var pr = document.querySelector('[name="regular_price_per_person"]');
        if (pr) pr.value = resolvedPrice;
    }
    data.booking_links.forEach(function(bl)  { addBookingLinkYear(bl); });
    data.departure_dates.forEach(function(dd) { addDepartureDate(dd); });
    data.optional_tours.forEach(function(ot)  { addOptionalTour(ot); });
    data.cash_freebies.forEach(function(fr)   { addCashFreebie({ label: fr }); });
    if (data.highlights.length) {
        var h = document.querySelector('[name="highlights"]');
        if (h) h.value = data.highlights.join('\n');
    }
    if (data.countries_visited.length) {
        var c = document.querySelector('[name="ai_countries_visited"]');
        if (c) c.value = data.countries_visited.join('\n');
    }
    if (data.downpayment) {
        var dp = document.getElementById('allowsDownpayment');
        if (dp) { dp.checked = true; dp.dispatchEvent(new Event('change')); }
        var amt = document.querySelector('[name="fixed_downpayment_amount"]');
        if (amt) amt.value = data.downpayment;
    }
}

function showSmartPastePreview(data) {
    var preview = document.getElementById('aiParsePreview');
    if (!preview) return;
    var rows = [];
    var _resolvedPrice = data.price || (data.departure_dates.length && data.departure_dates[0].price) || null;
    if (data.title)               rows.push(['Title',           esc(data.title)]);
    if (data.duration_days)       rows.push(['Duration',        data.duration_days + ' days']);
    if (_resolvedPrice)           rows.push(['Price',           '₱' + Number(_resolvedPrice).toLocaleString()]);
    if (data.countries_visited.length) rows.push(['Countries',   esc(data.countries_visited.join(', '))]);
    if (data.optional_link)       rows.push(['Optional Tours Link', '<a href="'+esc(data.optional_link)+'" target="_blank">'+esc(data.optional_link)+'</a>']);
    if (data.booking_links.length)  rows.push(['Flipbook Links', data.booking_links.map(function(b){ return b.year+': '+b.urls.length+' URL(s)'; }).join('; ')]);
    if (data.departure_dates.length) rows.push(['Departure Dates', data.departure_dates.length + ' date(s) added to Travel Dates tab']);
    if (data.optional_tours.length) rows.push(['Optional Tours', esc(data.optional_tours.map(function(o){ return 'Day '+o.day+': '+o.title; }).join(', '))]);
    if (data.cash_freebies.length)  rows.push(['Cash Freebies', esc(data.cash_freebies.join(', '))]);
    if (data.highlights.length)     rows.push(['Highlights',    data.highlights.length + ' item(s) filled']);
    if (data.downpayment)           rows.push(['Down Payment',  data.downpayment.toLocaleString()]);

    preview.style.display = 'block';
    if (!rows.length) {
        preview.innerHTML = '<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:1rem;color:#92400e"><i class="fas fa-exclamation-triangle"></i> Could not detect any recognisable data. Please check the format and try again.</div>';
        return;
    }
    var items = rows.map(function(r){
        return '<div class="ai-preview-item"><span class="ai-preview-label">'+r[0]+'</span><span class="ai-preview-val">'+r[1]+'</span></div>';
    }).join('');
    preview.innerHTML =
        '<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:1rem">' +
        '<div style="font-weight:700;color:#166534;margin-bottom:.625rem"><i class="fas fa-check-circle"></i> ' +
        rows.length + ' field group(s) filled — scroll down to review &amp; edit details</div>' +
        items + '</div>';
}
