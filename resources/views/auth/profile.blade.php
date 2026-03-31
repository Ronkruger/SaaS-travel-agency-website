@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="page-header" style="background: var(--gradient-primary);">
    <div class="container">
        <h1>My Profile</h1>
        <p>Manage your account and view bookings</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="profile-grid">
            <!-- Profile Card -->
            <div class="profile-sidebar">
                <div class="profile-avatar-card">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>{{ $user->name }}</h3>
                    <p>{{ $user->email }}</p>
                    <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                </div>

                <nav class="profile-nav">
                    <a href="#personal-info" class="active"><i class="fas fa-user"></i> Personal Info</a>
                    <a href="#change-password"><i class="fas fa-lock"></i> Change Password</a>
                    <a href="{{ route('booking.index') }}"><i class="fas fa-calendar-check"></i> My Bookings</a>
                    <a href="{{ route('wishlist') }}"><i class="fas fa-heart"></i> Wishlist</a>
                </nav>
            </div>

            <!-- Profile Main -->
            <div class="profile-main">
                <!-- Personal Info -->
                <div class="card" id="personal-info">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                    </div>
                    <form action="{{ route('profile.update') }}" method="POST" class="card-body">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                @include('components.phone-input', [
                                    'value' => old('phone', $user->phone),
                                    'name'  => 'phone',
                                ])
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="{{ $user->email }}" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Address <span style="font-weight:400;font-size:12px;color:#6b7280">(Street / Barangay)</span></label>
                            <textarea name="address" class="form-control" rows="2"
                                placeholder="e.g. Balucuc, Sto. Niño St., House No.">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:2">
                                <label>Country</label>
                                <div style="position:relative">
                                    <select id="csc-country" name="country" class="form-control"
                                        data-current="{{ old('country', $user->country) }}">
                                        <option value="">Loading countries…</option>
                                    </select>
                                    <span id="csc-country-spin" style="display:none;position:absolute;right:32px;top:50%;transform:translateY(-50%);color:#6b7280">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group" style="flex:0 0 auto;display:flex;align-items:flex-end">
                                <button type="button" id="detect-loc-btn" class="btn btn-outline"
                                    title="Auto-fill from GPS location" style="white-space:nowrap">
                                    <i class="fas fa-location-arrow"></i> Detect
                                </button>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>State / Province</label>
                                <div style="position:relative">
                                    <select id="csc-state" name="state" class="form-control"
                                        data-current="{{ old('state', $user->state ?? '') }}" disabled>
                                        <option value="">— select country first —</option>
                                    </select>
                                    <span id="csc-state-spin" style="display:none;position:absolute;right:32px;top:50%;transform:translateY(-50%);color:#6b7280">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>City / Municipality</label>
                                <div style="position:relative">
                                    <select id="csc-city" name="city" class="form-control"
                                        data-current="{{ old('city', $user->city) }}" disabled>
                                        <option value="">— select state first —</option>
                                    </select>
                                    <span id="csc-city-spin" style="display:none;position:absolute;right:32px;top:50%;transform:translateY(-50%);color:#6b7280">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card mt-4" id="change-password">
                    <div class="card-header">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                    </div>
                    <form action="{{ route('profile.password') }}" method="POST" class="card-body">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div style="margin-bottom:14px">
                            <button type="button" id="gen-pwd-btn" class="btn btn-outline btn-sm">
                                <i class="fas fa-magic"></i> Generate Strong Password
                            </button>
                            <span id="gen-pwd-preview" style="display:none;margin-left:10px;font-family:monospace;font-size:13px;background:#f1f5f9;padding:4px 10px;border-radius:6px;letter-spacing:.05em;color:#1e3a5f;cursor:pointer" title="Click to copy"></span>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Password</label>
                                <div style="position:relative">
                                    <input type="password" id="pwd-new" name="password" class="form-control" required>
                                    <button type="button" onclick="togglePwd('pwd-new',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <div style="position:relative">
                                    <input type="password" id="pwd-confirm" name="password_confirmation" class="form-control" required>
                                    <button type="button" onclick="togglePwd('pwd-confirm',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>

                <!-- Recent Bookings -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-check"></i> Recent Bookings</h3>
                        <a href="{{ route('booking.index') }}" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($bookings->take(5) as $booking)
                            <div class="booking-row">
                                <div class="booking-row-info">
                                    <strong>{{ $booking->booking_number }}</strong>
                                    <span>{{ $booking->tour->title }}</span>
                                    <span class="text-muted">{{ $booking->tour_date->format('M d, Y') }}</span>
                                </div>
                                <div class="booking-row-amount">
                                    ₱{{ number_format($booking->total_amount, 2) }}
                                </div>
                                <span class="status-badge status-{{ $booking->status }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <a href="{{ route('booking.show', $booking) }}" class="btn btn-sm btn-outline">
                                    View
                                </a>
                            </div>
                        @empty
                            <div class="empty-state p-4">
                                <i class="fas fa-calendar-times fa-2x text-muted"></i>
                                <p class="mt-2">No bookings yet. <a href="{{ route('tours.index') }}">Browse tours</a></p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const API        = 'https://countriesnow.space/api/v0.1';
    const countryEl  = document.getElementById('csc-country');
    const stateEl    = document.getElementById('csc-state');
    const cityEl     = document.getElementById('csc-city');
    const ctrSpin    = document.getElementById('csc-country-spin');
    const staSpin    = document.getElementById('csc-state-spin');
    const citSpin    = document.getElementById('csc-city-spin');

    const INIT_COUNTRY = countryEl.dataset.current || '';
    const INIT_STATE   = stateEl.dataset.current   || '';
    const INIT_CITY    = cityEl.dataset.current    || '';

    /* ── helpers ────────────────────────────────────────────── */
    function populate(el, values, preselect) {
        el.innerHTML = '<option value="">— select —</option>';
        values.forEach(v => {
            const o = document.createElement('option');
            o.value = o.textContent = v;
            if (v === preselect) o.selected = true;
            el.appendChild(o);
        });
        el.disabled = false;
    }

    function resetEl(el, placeholder) {
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled  = true;
    }

    /* ── API calls ──────────────────────────────────────────── */
    async function loadCountries() {
        ctrSpin.style.display = 'inline';
        countryEl.disabled    = true;
        try {
            const r = await fetch(`${API}/countries`);
            const d = await r.json();
            if (!d.error) {
                const names = d.data.map(c => c.country).sort();
                populate(countryEl, names, INIT_COUNTRY);
                if (INIT_COUNTRY) await loadStates(INIT_COUNTRY, true);
            }
        } catch {
            countryEl.innerHTML = '<option value="">Failed to load — type state/city below</option>';
            countryEl.disabled  = false;
        } finally {
            ctrSpin.style.display = 'none';
        }
    }

    async function loadStates(country, isInit = false) {
        staSpin.style.display = 'inline';
        resetEl(stateEl, 'Loading…');
        resetEl(cityEl,  '— select state first —');
        try {
            const r = await fetch(`${API}/countries/states`, {
                method:  'POST',
                headers: {'Content-Type': 'application/json'},
                body:    JSON.stringify({ country }),
            });
            const d = await r.json();
            const states = d.data?.states ?? [];
            if (!d.error && states.length) {
                const names     = states.map(s => s.name).sort();
                const preselect = isInit ? INIT_STATE : '';
                populate(stateEl, names, preselect);
                if (isInit && INIT_STATE) await loadCities(country, INIT_STATE, true);
            } else {
                stateEl.innerHTML = '<option value="">No provinces available</option>';
                stateEl.disabled  = false;
            }
        } catch {
            stateEl.innerHTML = '<option value="">Failed to load</option>';
            stateEl.disabled  = false;
        } finally {
            staSpin.style.display = 'none';
        }
    }

    async function loadCities(country, state, isInit = false) {
        citSpin.style.display = 'inline';
        resetEl(cityEl, 'Loading…');
        try {
            const r = await fetch(`${API}/countries/state/cities`, {
                method:  'POST',
                headers: {'Content-Type': 'application/json'},
                body:    JSON.stringify({ country, state }),
            });
            const d = await r.json();
            if (!d.error && d.data?.length) {
                const preselect = isInit ? INIT_CITY : '';
                populate(cityEl, [...d.data].sort(), preselect);
            } else {
                cityEl.innerHTML = '<option value="">No cities available</option>';
                cityEl.disabled  = false;
            }
        } catch {
            cityEl.innerHTML = '<option value="">Failed to load</option>';
            cityEl.disabled  = false;
        } finally {
            citSpin.style.display = 'none';
        }
    }

    /* ── change events ──────────────────────────────────────── */
    countryEl.addEventListener('change', () => {
        if (countryEl.value) {
            loadStates(countryEl.value);
        } else {
            resetEl(stateEl, '— select country first —');
            resetEl(cityEl,  '— select state first —');
        }
    });

    stateEl.addEventListener('change', () => {
        if (stateEl.value) {
            loadCities(countryEl.value, stateEl.value);
        } else {
            resetEl(cityEl, '— select state first —');
        }
    });

    /* ── GPS detect ─────────────────────────────────────────── */
    document.getElementById('detect-loc-btn').addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        const btn = document.getElementById('detect-loc-btn');
        btn.disabled    = true;
        btn.innerHTML   = '<i class="fas fa-circle-notch fa-spin"></i> Detecting…';

        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const { latitude, longitude } = pos.coords;
                const nr = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&format=json`,
                    { headers: { 'Accept-Language': 'en' } }
                );
                const geo  = await nr.json();
                const addr = geo.address ?? {};

                // Philippines returns addr.province; other countries return addr.state
                const detectedCountry = addr.country ?? '';
                const detectedState   = addr.province ?? addr.county ?? addr.state ?? '';
                const detectedCity    = addr.city ?? addr.town ?? addr.municipality ?? addr.village ?? '';
                const detectedStreet  = [addr.suburb, addr.neighbourhood, addr.village, addr.road]
                    .filter(Boolean).join(', ');

                // Match country option (case-insensitive)
                const cOpt = [...countryEl.options].find(
                    o => o.value.toLowerCase() === detectedCountry.toLowerCase()
                );
                if (cOpt) {
                    countryEl.value = cOpt.value;
                    await loadStates(cOpt.value);
                    const sOpt = [...stateEl.options].find(
                        o => o.value.toLowerCase() === detectedState.toLowerCase()
                    );
                    if (sOpt) {
                        stateEl.value = sOpt.value;
                        await loadCities(cOpt.value, sOpt.value);
                        const ciOpt = [...cityEl.options].find(
                            o => o.value.toLowerCase() === detectedCity.toLowerCase()
                        );
                        if (ciOpt) cityEl.value = ciOpt.value;
                    }
                }

                const addressField = document.querySelector('textarea[name="address"]');
                if (addressField && detectedStreet) addressField.value = detectedStreet;

            } catch {
                alert('Could not detect your location. Please fill in manually.');
            } finally {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-location-arrow"></i> Detect';
            }
        }, () => {
            alert('Location access was denied.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-location-arrow"></i> Detect';
        });
    });

    /* ── strong password generator ─────────────────────────── */
    function togglePwd(id, btn) {
        const el = document.getElementById(id);
        el.type = el.type === 'password' ? 'text' : 'password';
        btn.querySelector('i').className = el.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    document.getElementById('gen-pwd-btn').addEventListener('click', () => {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        let pwd = '';
        const arr = new Uint32Array(16);
        crypto.getRandomValues(arr);
        arr.forEach(v => { pwd += chars[v % chars.length]; });
        // Ensure at least one of each type
        pwd = pwd.slice(0, 12) +
              'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[arr[12] % 26] +
              '0123456789'[arr[13] % 10] +
              '!@#$%^&*'[arr[14] % 8] +
              'abcdefghijklmnopqrstuvwxyz'[arr[15] % 26];

        document.getElementById('pwd-new').type     = 'text';
        document.getElementById('pwd-new').value    = pwd;
        document.getElementById('pwd-confirm').type  = 'text';
        document.getElementById('pwd-confirm').value = pwd;

        const preview = document.getElementById('gen-pwd-preview');
        preview.textContent = pwd + ' 📋';
        preview.style.display = 'inline';
        preview.onclick = () => {
            navigator.clipboard.writeText(pwd).then(() => {
                preview.textContent = '✅ Copied!';
                setTimeout(() => { preview.textContent = pwd + ' 📋'; }, 2000);
            });
        };
    });
})();
</script>
@endpush
