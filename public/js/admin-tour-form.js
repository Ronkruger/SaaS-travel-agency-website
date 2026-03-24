/**
 * admin-tour-form.js
 * Dynamic repeatable-row builders for the Tour admin create/edit form.
 */

// ─── Departure Dates ─────────────────────────────────────────────────────────
let departureDateIdx = 0;
function addDepartureDate(data) {
    data = data || {};
    const i = departureDateIdx++;
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
                <label>Price Override ($)</label>
                <input type="number" name="departure_dates[${i}][price]" class="form-control" value="${data.price||''}" step="0.01" min="0" placeholder="Blank = default price">
            </div>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label>Max Capacity</label>
                <input type="number" name="departure_dates[${i}][maxCapacity]" class="form-control" value="${data.maxCapacity||''}" min="0">
            </div>
            <div class="form-group">
                <label>Current Bookings</label>
                <input type="number" name="departure_dates[${i}][currentBookings]" class="form-control" value="${data.currentBookings||0}" min="0">
            </div>
            <div class="form-group d-flex align-items-center" style="padding-top:1.75rem">
                <label style="cursor:pointer;display:flex;align-items:center;gap:.5rem">
                    <input type="checkbox" name="departure_dates[${i}][isAvailable]" value="1" ${data.isAvailable !== false ? 'checked' : ''}>
                    Available
                </label>
            </div>
        </div>
    </div>`;
    document.getElementById('departureDatesContainer').insertAdjacentHTML('beforeend', html);
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

// ─── Full Stops ───────────────────────────────────────────────────────────────
let fullStopIdx = 0;
function addFullStop(data) {
    data = data || {};
    const i = fullStopIdx++;
    const html = `<div class="repeatable-row" id="fs_${i}">
        <button type="button" class="remove-row" onclick="removeRow('fs_${i}')"><i class="fas fa-times"></i></button>
        <div class="form-row-3">
            <div class="form-group">
                <label>City</label>
                <input type="text" name="full_stops[${i}][city]" class="form-control" value="${esc(data.city||'')}">
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="full_stops[${i}][country]" class="form-control" value="${esc(data.country||'')}">
            </div>
            <div class="form-group">
                <label>Nights / Days</label>
                <input type="number" name="full_stops[${i}][days]" class="form-control" value="${data.days||''}" min="0">
            </div>
        </div>
    </div>`;
    document.getElementById('fullStopsContainer').insertAdjacentHTML('beforeend', html);
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
        title: null, duration_days: null,
        booking_links: [], departure_dates: [],
        optional_tours: [], cash_freebies: [],
        highlights: [], countries_visited: [],
        downpayment: null
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
        if (/^(highlights?|inclusions?|what.?s\s+included)\s*:?\s*$/i.test(line)){ mode = 'highlights';  continue; }

        // ── Title + Duration (very first content line) ──
        if (firstLine) {
            firstLine = false;
            var durM = line.match(/\((\d+)\s*n?ights?\s*(?:\/\s*\d+\s*days?)?\)|(\d+)\s*days?/i);
            var dayMatch = line.match(/\((\d+)\s*days?\)/i);
            if (dayMatch) {
                data.duration_days = parseInt(dayMatch[1]);
                data.title = line.replace(/\s*\(\d+\s*days?\)/i, '').trim().replace(/[-–\s]+$/, '').trim();
            } else {
                data.title = line;
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
    if (data.title)               rows.push(['Title',           esc(data.title)]);
    if (data.duration_days)       rows.push(['Duration',        data.duration_days + ' days']);
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
