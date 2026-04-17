@extends('central.platform.layouts.admin')
@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')

@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

{{-- Plans list --}}
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Monthly</th>
                    <th>Yearly</th>
                    <th>Tours</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                <tr>
                    <td style="color:var(--text-muted)">{{ $plan->sort_order }}</td>
                    <td>
                        <div style="font-weight:700">{{ $plan->name }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted)">{{ $plan->slug }}</div>
                    </td>
                    <td>${{ number_format($plan->monthly_price) }}</td>
                    <td>${{ number_format($plan->yearly_price) }}</td>
                    <td>{{ $plan->max_tours < 0 ? '∞' : $plan->max_tours }}</td>
                    <td>{{ $plan->max_admin_users < 0 ? '∞' : $plan->max_admin_users }}</td>
                    <td>
                        <span class="badge {{ $plan->is_active ? 'badge-success' : 'badge-muted' }}">
                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <button onclick="editPlan({{ $plan->toJson() }})" class="btn btn-outline btn-sm">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Create / Edit plan form --}}
<div class="card" id="planFormCard">
    <div class="card-header"><h3 id="planFormTitle">Create New Plan</h3></div>
    <div class="card-body">
        <form method="POST" id="planForm" action="{{ route('platform.plans.store') }}">
            @csrf
            @method('POST')
            @if($errors->any())
                <div class="flash flash-error" style="font-size:.85rem">{{ $errors->first() }}</div>
            @endif
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Slug</label>
                <input type="text" name="slug" id="field_slug" value="{{ old('slug') }}" placeholder="professional" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
            </div>
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Name</label>
                <input type="text" name="name" id="field_name" value="{{ old('name') }}" placeholder="Professional" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
            </div>
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Description</label>
                <textarea name="description" id="field_description" rows="2" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem;resize:vertical">{{ old('description') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1rem">
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Monthly ($)</label>
                    <input type="number" name="monthly_price" id="field_monthly_price" step="0.01" value="{{ old('monthly_price', 0) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Yearly ($)</label>
                    <input type="number" name="yearly_price" id="field_yearly_price" step="0.01" value="{{ old('yearly_price', 0) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.7rem;margin-bottom:1rem">
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Max Tours</label>
                    <input type="number" name="max_tours" id="field_max_tours" value="{{ old('max_tours', 10) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                    <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem">-1 = unlimited</div>
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Bookings/mo</label>
                    <input type="number" name="max_bookings_per_month" id="field_max_bookings_per_month" value="{{ old('max_bookings_per_month', 50) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Staff</label>
                    <input type="number" name="max_admin_users" id="field_max_admin_users" value="{{ old('max_admin_users', 2) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                </div>
            </div>
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.5rem">Features</label>
                @php $boolFields = ['has_diy_builder' => 'AI Tour Builder', 'has_custom_domain' => 'Custom Domain', 'has_api_access' => 'API Access', 'has_advanced_reports' => 'Advanced Reports', 'has_email_marketing' => 'Email Marketing', 'has_priority_support' => 'Priority Support']; @endphp
                @foreach($boolFields as $fname => $flabel)
                <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem;font-size:.88rem;cursor:pointer">
                    <input type="checkbox" name="{{ $fname }}" id="field_{{ $fname }}" value="1" style="width:16px;height:16px"> {{ $flabel }}
                </label>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1.2rem">
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Sort Order</label>
                    <input type="number" name="sort_order" id="field_sort_order" value="{{ old('sort_order', 0) }}" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                </div>
                <div>
                    <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:.3rem">Active</label>
                    <select name="is_active" id="field_is_active" style="width:100%;padding:.6rem .9rem;border:2px solid var(--border);border-radius:8px;font-family:inherit;font-size:.88rem">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> <span id="formSubmitText">Create Plan</span></button>
                <button type="button" onclick="resetForm()" class="btn btn-sm" style="background:var(--bg);border:2px solid var(--border)">Reset</button>
            </div>
        </form>
    </div>
</div>

</div>

@push('scripts')
<script>
const storeUrl = '{{ route('platform.plans.store') }}';
function editPlan(plan) {
    document.getElementById('planFormTitle').textContent = 'Edit Plan: ' + plan.name;
    document.getElementById('planForm').action = '/platform/plans/' + plan.id;
    document.getElementById('planForm').querySelector('[name="_method"]').value = 'PUT';
    const f = (id, val) => { const el = document.getElementById('field_' + id); if(el) el.type === 'checkbox' ? el.checked = !!val : el.value = val ?? ''; };
    f('slug', plan.slug); f('name', plan.name); f('description', plan.description);
    f('monthly_price', plan.monthly_price); f('yearly_price', plan.yearly_price);
    f('max_tours', plan.max_tours); f('max_bookings_per_month', plan.max_bookings_per_month);
    f('max_admin_users', plan.max_admin_users); f('sort_order', plan.sort_order);
    f('has_diy_builder', plan.has_diy_builder); f('has_custom_domain', plan.has_custom_domain);
    f('has_api_access', plan.has_api_access); f('has_advanced_reports', plan.has_advanced_reports);
    f('has_email_marketing', plan.has_email_marketing); f('has_priority_support', plan.has_priority_support);
    f('is_active', plan.is_active ? '1' : '0');
    document.getElementById('formSubmitText').textContent = 'Update Plan';
    document.getElementById('field_slug').readOnly = true;
    document.getElementById('planFormCard').scrollIntoView({behavior:'smooth'});
}
function resetForm() {
    document.getElementById('planFormTitle').textContent = 'Create New Plan';
    document.getElementById('planForm').action = storeUrl;
    document.getElementById('planForm').querySelector('[name="_method"]').value = 'POST';
    document.getElementById('planForm').reset();
    document.getElementById('field_slug').readOnly = false;
    document.getElementById('formSubmitText').textContent = 'Create Plan';
}
</script>
@endpush
@endsection
