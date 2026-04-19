<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile — {{ $currentTenant->company_name ?? $currentTenant->name ?? '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary:      #0e7490;
            --primary-dark: #0c6278;
            --primary-light:#e0f2f8;
            --success:      #16a34a;
            --danger:       #dc2626;
            --gray-900:     #111827;
            --gray-700:     #374151;
            --gray-500:     #6b7280;
            --gray-300:     #d1d5db;
            --gray-100:     #f3f4f6;
        }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2f8 50%, #f0fdf4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .onboard-wrap {
            width: 100%;
            max-width: 640px;
        }

        /* Top logo */
        .onboard-logo {
            display: flex; align-items: center; gap: .75rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .onboard-logo-icon {
            width: 44px; height: 44px;
            background: var(--primary); border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.25rem;
        }
        .onboard-logo h2 { font-size: 1.375rem; font-weight: 800; color: var(--gray-900); }
        .onboard-logo span { font-size: .8125rem; color: var(--gray-500); font-weight: 500; display: block; }

        /* Progress steps */
        .progress-steps {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .prog-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }
        .prog-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px;
            left: calc(50% + 18px);
            right: calc(-50% + 18px);
            height: 2px;
            background: var(--gray-300);
        }
        .prog-step.done:not(:last-child)::after { background: var(--primary); }
        .prog-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--gray-100);
            border: 2px solid var(--gray-300);
            display: flex; align-items: center; justify-content: center;
            font-size: .875rem; font-weight: 800;
            color: var(--gray-500);
            position: relative; z-index: 1;
            margin-bottom: .5rem;
        }
        .prog-step.done .prog-circle {
            background: var(--primary); border-color: var(--primary); color: #fff;
        }
        .prog-step.active .prog-circle {
            background: #fff; border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 0 4px rgba(14,116,144,.15);
        }
        .prog-label { font-size: .75rem; font-weight: 600; color: var(--gray-500); text-align: center; white-space: nowrap; }
        .prog-step.done .prog-label,
        .prog-step.active .prog-label { color: var(--primary); }

        /* Card */
        .onboard-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.08), 0 1px 3px rgba(0,0,0,.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .onboard-card-head {
            background: linear-gradient(135deg, #0f172a 0%, #0e7490 100%);
            padding: 2rem 2.5rem;
            color: #fff;
        }
        .onboard-card-head h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: .375rem; }
        .onboard-card-head p { color: rgba(255,255,255,.7); font-size: .9375rem; }
        .onboard-card-head .welcome-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.15);
            border-radius: 50px;
            padding: .375rem 1rem;
            font-size: .8125rem; font-weight: 600;
            color: #7dd3fc;
            margin-bottom: 1rem;
            border: 1px solid rgba(255,255,255,.2);
        }

        .onboard-card-body { padding: 2.5rem; }

        /* Form elements */
        .form-group { display: flex; flex-direction: column; gap: .5rem; margin-bottom: 1.5rem; }
        .form-group label {
            font-weight: 700; font-size: .9375rem; color: var(--gray-700);
            display: flex; align-items: center; gap: .5rem;
        }
        .form-group label .label-badge {
            font-size: .75rem; font-weight: 600;
            background: var(--primary-light); color: var(--primary);
            padding: .1rem .5rem; border-radius: 50px;
        }
        .form-group small { color: var(--gray-500); font-size: .8125rem; }
        .form-select {
            width: 100%; padding: .75rem 1rem;
            border: 2px solid var(--gray-300); border-radius: 12px;
            font-size: .9375rem; color: var(--gray-900);
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 1rem center;
            appearance: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,116,144,.12); }
        .form-select.is-invalid { border-color: var(--danger); }
        .form-select:disabled { background-color: var(--gray-100); color: var(--gray-500); cursor: not-allowed; }
        .invalid-feedback { color: var(--danger); font-size: .8125rem; display: block; }

        /* Selected department display */
        .dept-preview {
            display: none;
            align-items: center; gap: .75rem;
            padding: .875rem 1rem;
            background: var(--primary-light);
            border-radius: 10px;
            border: 1px solid rgba(14,116,144,.2);
            margin-top: .5rem;
        }
        .dept-preview.visible { display: flex; }
        .dept-preview i { color: var(--primary); font-size: 1.125rem; }
        .dept-preview span { font-weight: 600; color: var(--primary); font-size: .9375rem; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: .875rem 1.5rem;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            font-size: 1rem; font-weight: 700;
            border: none; cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: .625rem;
            margin-top: .5rem;
            box-shadow: 0 4px 12px rgba(14,116,144,.35);
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(14,116,144,.45); }
        .btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* Skip button */
        .btn-skip {
            width: 100%;
            padding: .875rem 1.5rem;
            border-radius: 12px;
            background: #fff;
            color: var(--gray-700);
            font-size: .9375rem; font-weight: 600;
            border: 2px solid var(--gray-300);
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: .625rem;
        }
        .btn-skip:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
        }

        .alert { display: flex; align-items: flex-start; gap: .625rem; padding: .875rem 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: .875rem; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert ul { margin-top: .4rem; padding-left: 1.25rem; }
        .alert i { margin-top: 2px; flex-shrink: 0; }

        /* Footer note */
        .onboard-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .8125rem;
            color: var(--gray-500);
        }

        @media (max-width: 480px) {
            .onboard-card-head { padding: 1.5rem; }
            .onboard-card-body { padding: 1.5rem; }
            .prog-label { display: none; }
        }
    </style>
</head>
<body>
<div class="onboard-wrap">

    {{-- Logo --}}
    <div class="onboard-logo">
        <div class="onboard-logo-icon"><i class="fas fa-globe-asia"></i></div>
        <div>
            <h2>{{ $currentTenant->company_name ?? $currentTenant->name ?? '' }}</h2>
            <span>Employee Portal</span>
        </div>
    </div>

    {{-- Progress Steps --}}
    <div class="progress-steps">
        <div class="prog-step done">
            <div class="prog-circle"><i class="fas fa-check"></i></div>
            <div class="prog-label">Account Created</div>
        </div>
        <div class="prog-step active">
            <div class="prog-circle">2</div>
            <div class="prog-label">Complete Profile</div>
        </div>
        <div class="prog-step">
            <div class="prog-circle">3</div>
            <div class="prog-label">Access Panel</div>
        </div>
    </div>

    {{-- Card --}}
    <div class="onboard-card">
        <div class="onboard-card-head">
            <div class="welcome-badge">
                <i class="fas fa-user-check"></i>
                Account ready — one more step!
            </div>
            <h3>Select Your Department &amp; Position <span style="font-size:.9rem;font-weight:400;color:#666;">(Optional)</span></h3>
            <p>Help us set up your account correctly by telling us where you work. You can skip this and add it later.</p>
        </div>

        <div class="onboard-card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.onboarding.save') }}" id="onboardForm">
                @csrf

                {{-- Department --}}
                <div class="form-group">
                    <label for="department">
                        <i class="fas fa-building" style="color:var(--primary)"></i>
                        Department
                        <span class="label-badge" style="background:#6b7280;">Optional</span>
                    </label>
                    <select
                        id="department" name="department"
                        class="form-select{{ $errors->has('department') ? ' is-invalid' : '' }}"
                    >
                        <option value="">— Select your department —</option>
                        @foreach($departments as $key => $dept)
                            <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>
                                {{ $dept['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="dept-preview" id="deptPreview">
                        <i class="fas fa-check-circle"></i>
                        <span id="deptPreviewText"></span>
                    </div>
                    @error('department')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Position --}}
                <div class="form-group">
                    <label for="position">
                        <i class="fas fa-id-badge" style="color:var(--primary)"></i>
                        Position / Role
                        <span class="label-badge" style="background:#6b7280;">Optional</span>
                    </label>
                    <select
                        id="position" name="position"
                        class="form-select{{ $errors->has('position') ? ' is-invalid' : '' }}"
                        disabled
                    >
                        <option value="">— Select your department first —</option>
                    </select>
                    <small id="positionHint" style="display:none">
                        <i class="fas fa-info-circle"></i>
                        These are the available roles for your selected department.
                    </small>
                    @error('position')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-check-circle"></i>
                    Complete Setup &amp; Enter Dashboard
                </button>
                
                <button type="submit" class="btn-skip" id="skipBtn" style="margin-top:0.75rem;">
                    <i class="fas fa-forward"></i>
                    Skip for now — Set up later
                </button>
            </form>

            <div class="onboard-note">
                <i class="fas fa-info-circle" style="margin-right:.3rem"></i>
                You can update your department and position anytime from your profile settings.
            </div>
        </div>
    </div>

</div>

{{-- Department → Position map (embedded server-side) --}}
<script>
const DEPARTMENTS = @json($departments);
const oldDept     = @json(old('department'));
const oldPos      = @json(old('position'));

const deptSelect   = document.getElementById('department');
const posSelect    = document.getElementById('position');
const deptPreview  = document.getElementById('deptPreview');
const deptPreviewT = document.getElementById('deptPreviewText');
const posHint      = document.getElementById('positionHint');
const submitBtn    = document.getElementById('submitBtn');

function populatePositions(deptKey) {
    posSelect.innerHTML = '';

    if (!deptKey || !DEPARTMENTS[deptKey]) {
        posSelect.innerHTML = '<option value="">— Select your department first —</option>';
        posSelect.disabled  = true;
        deptPreview.classList.remove('visible');
        posHint.style.display = 'none';
        return;
    }

    const dept = DEPARTMENTS[deptKey];
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = '— Choose your position —';
    posSelect.appendChild(placeholder);

    dept.positions.forEach(pos => {
        const opt = document.createElement('option');
        opt.value = pos;
        opt.textContent = pos;
        if (pos === oldPos) opt.selected = true;
        posSelect.appendChild(opt);
    });

    posSelect.disabled = false;
    deptPreviewT.textContent = dept.label;
    deptPreview.classList.add('visible');
    posHint.style.display = 'flex';
}

deptSelect.addEventListener('change', () => populatePositions(deptSelect.value));

// Re-hydrate previous selection (validation fail)
if (oldDept) {
    populatePositions(oldDept);
}
</script>
</body>
</html>
