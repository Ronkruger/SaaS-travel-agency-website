@extends('central.platform.layouts.admin')
@section('title', 'Edit Tenant')
@section('page-title', 'Edit: ' . ($tenant->company_name ?? $tenant->name))

@section('content')
<div style="max-width:680px">
    <div class="card">
        <div class="card-header">
            <h3>Edit Tenant</h3>
            <a href="{{ route('platform.tenants.show', $tenant) }}" class="btn btn-sm" style="background:var(--bg);border:2px solid var(--border)">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="flash flash-error">{{ $errors->first() }}</div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    @php
                    $fields = [
                        ['name', 'Owner Name', 'text', $tenant->name],
                        ['email', 'Email', 'email', $tenant->email],
                        ['company_name', 'Company Name', 'text', $tenant->company_name],
                        ['company_phone', 'Phone', 'text', $tenant->company_phone],
                    ];
                    @endphp
                    @foreach($fields as [$name, $label, $type, $val])
                    <div style="margin-bottom:1rem">
                        <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $val) }}"
                            style="width:100%;padding:.65rem 1rem;border:2px solid {{ $errors->has($name) ? '#dc2626' : 'var(--border)' }};border-radius:8px;font-family:inherit;font-size:.9rem">
                        @error($name)<div style="color:#dc2626;font-size:.78rem;margin-top:.2rem">{{ $message }}</div>@enderror
                    </div>
                    @endforeach
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">Company Address</label>
                    <textarea name="company_address" rows="2" style="width:100%;padding:.65rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem;resize:vertical">{{ old('company_address', $tenant->company_address) }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">Plan</label>
                        <select name="plan" style="width:100%;padding:.65rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem">
                            @foreach(['trial','starter','professional','enterprise'] as $p)
                            <option value="{{ $p }}" {{ old('plan', $tenant->plan) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">Status</label>
                        <select name="is_active" style="width:100%;padding:.65rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem">
                            <option value="1" {{ $tenant->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$tenant->is_active ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                    <div>
                        <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">Trial Ends At</label>
                        <input type="datetime-local" name="trial_ends_at"
                            value="{{ old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d\TH:i')) }}"
                            style="width:100%;padding:.65rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:.88rem;margin-bottom:.35rem">Subscription Ends At</label>
                        <input type="datetime-local" name="subscription_ends_at"
                            value="{{ old('subscription_ends_at', $tenant->subscription_ends_at?->format('Y-m-d\TH:i')) }}"
                            style="width:100%;padding:.65rem 1rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
