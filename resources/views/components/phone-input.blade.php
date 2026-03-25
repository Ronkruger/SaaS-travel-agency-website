@php
/**
 * Phone input with country code dropdown.
 *
 * Props:
 *   $value    – current full phone value (e.g. "+63 912 345 6789")
 *   $name     – form field name (default: "phone")
 *   $required – whether the field is required (default: false)
 *   $error    – error message string (optional)
 *
 * On submit the hidden input "phone" contains the combined value,
 * e.g. "+63 912 345 6789".
 */

$name     = $name     ?? 'phone';
$required = $required ?? false;
$value    = $value    ?? old($name, '');

// Split stored value into dial code + local number
$dialCode   = '+63'; // default Philippines
$localPart  = $value;

$countries = [
    ['code' => 'PH', 'dial' => '+63',  'flag' => '🇵🇭', 'name' => 'Philippines'],
    ['code' => 'US', 'dial' => '+1',   'flag' => '🇺🇸', 'name' => 'United States'],
    ['code' => 'GB', 'dial' => '+44',  'flag' => '🇬🇧', 'name' => 'United Kingdom'],
    ['code' => 'AU', 'dial' => '+61',  'flag' => '🇦🇺', 'name' => 'Australia'],
    ['code' => 'CA', 'dial' => '+1',   'flag' => '🇨🇦', 'name' => 'Canada'],
    ['code' => 'SG', 'dial' => '+65',  'flag' => '🇸🇬', 'name' => 'Singapore'],
    ['code' => 'MY', 'dial' => '+60',  'flag' => '🇲🇾', 'name' => 'Malaysia'],
    ['code' => 'ID', 'dial' => '+62',  'flag' => '🇮🇩', 'name' => 'Indonesia'],
    ['code' => 'TH', 'dial' => '+66',  'flag' => '🇹🇭', 'name' => 'Thailand'],
    ['code' => 'VN', 'dial' => '+84',  'flag' => '🇻🇳', 'name' => 'Vietnam'],
    ['code' => 'JP', 'dial' => '+81',  'flag' => '🇯🇵', 'name' => 'Japan'],
    ['code' => 'KR', 'dial' => '+82',  'flag' => '🇰🇷', 'name' => 'South Korea'],
    ['code' => 'CN', 'dial' => '+86',  'flag' => '🇨🇳', 'name' => 'China'],
    ['code' => 'IN', 'dial' => '+91',  'flag' => '🇮🇳', 'name' => 'India'],
    ['code' => 'AE', 'dial' => '+971', 'flag' => '🇦🇪', 'name' => 'UAE'],
    ['code' => 'SA', 'dial' => '+966', 'flag' => '🇸🇦', 'name' => 'Saudi Arabia'],
    ['code' => 'QA', 'dial' => '+974', 'flag' => '🇶🇦', 'name' => 'Qatar'],
    ['code' => 'DE', 'dial' => '+49',  'flag' => '🇩🇪', 'name' => 'Germany'],
    ['code' => 'FR', 'dial' => '+33',  'flag' => '🇫🇷', 'name' => 'France'],
    ['code' => 'IT', 'dial' => '+39',  'flag' => '🇮🇹', 'name' => 'Italy'],
    ['code' => 'ES', 'dial' => '+34',  'flag' => '🇪🇸', 'name' => 'Spain'],
    ['code' => 'NZ', 'dial' => '+64',  'flag' => '🇳🇿', 'name' => 'New Zealand'],
    ['code' => 'HK', 'dial' => '+852', 'flag' => '🇭🇰', 'name' => 'Hong Kong'],
    ['code' => 'TW', 'dial' => '+886', 'flag' => '🇹🇼', 'name' => 'Taiwan'],
    ['code' => 'BR', 'dial' => '+55',  'flag' => '🇧🇷', 'name' => 'Brazil'],
    ['code' => 'MX', 'dial' => '+52',  'flag' => '🇲🇽', 'name' => 'Mexico'],
    ['code' => 'ZA', 'dial' => '+27',  'flag' => '🇿🇦', 'name' => 'South Africa'],
];

// Attempt to detect dial code from stored value
foreach ($countries as $c) {
    if (str_starts_with(ltrim($value), ltrim($c['dial']))) {
        $dialCode  = $c['dial'];
        $localPart = trim(substr(ltrim($value), strlen(ltrim($c['dial']))));
        break;
    }
}

$uid = 'phone_' . uniqid();
@endphp

<div class="phone-input-group" style="display:flex;gap:.375rem;width:100%;align-items:stretch">
    {{-- Country code dropdown --}}
    <div style="position:relative;flex-shrink:0;width:auto">
        <select id="{{ $uid }}_dial"
                style="appearance:none;-webkit-appearance:none;padding:.5rem 2rem .5rem .625rem;border:1px solid #d1d5db;border-radius:.5rem;background:#f9fafb;font-size:.9375rem;cursor:pointer;height:100%;width:auto;min-width:5rem"
                onchange="updatePhone_{{ $uid }}()">
            @foreach($countries as $c)
                <option value="{{ $c['dial'] }}" {{ $dialCode === $c['dial'] ? 'selected' : '' }}>
                    {{ $c['flag'] }} {{ $c['dial'] }}
                </option>
            @endforeach
        </select>
        <span style="pointer-events:none;position:absolute;right:.5rem;top:50%;transform:translateY(-50%);font-size:.7rem;color:#6b7280">▼</span>
    </div>

    {{-- Local number input --}}
    <input type="tel"
           id="{{ $uid }}_local"
           class="form-control {{ isset($error) && $error ? 'is-invalid' : '' }}"
           placeholder="912 345 6789"
           value="{{ $localPart }}"
           style="flex:1;min-width:0"
           {{ $required ? 'required' : '' }}
           oninput="updatePhone_{{ $uid }}()">

    {{-- Hidden combined field that gets submitted --}}
    <input type="hidden" id="{{ $uid }}_combined" name="{{ $name }}" value="{{ $value }}">
</div>

@if(isset($error) && $error)
    <span class="invalid-feedback" style="display:block">{{ $error }}</span>
@endif

<script>
function updatePhone_{{ $uid }}() {
    var dial  = document.getElementById('{{ $uid }}_dial').value;
    var local = document.getElementById('{{ $uid }}_local').value.trim();
    document.getElementById('{{ $uid }}_combined').value = local ? (dial + ' ' + local) : '';
}
</script>
