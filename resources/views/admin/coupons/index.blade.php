@extends('layouts.admin')
@section('title', 'Discount Codes')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Discount Codes
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>Discount Codes</h2>
        <p>Create and manage discount codes for subscriptions</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">
        <i class="fas fa-plus"></i> New Discount Code
    </button>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="filter-row">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Coupon code..." class="form-control">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive / Expired</option>
            </select>
            <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Min Spend</th>
                    <th>Usage</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    @php
                        $expired   = $coupon->expires_at && $coupon->expires_at->lt(today());
                        $exhausted = $coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit;
                        $live      = $coupon->is_active && !$expired && !$exhausted;
                    @endphp
                    <tr>
                        <td><code style="font-size:.9rem;letter-spacing:.05em">{{ $coupon->code }}</code></td>
                        <td>
                            @if($coupon->type === 'percent')
                                <span style="font-weight:700;color:#7c3aed">{{ $coupon->value }}% off</span>
                            @else
                                <span style="font-weight:700;color:#0f766e">₱{{ number_format($coupon->value, 2) }} off</span>
                            @endif
                            @if($coupon->description)
                                <br><small class="text-muted">{{ Str::limit($coupon->description, 40) }}</small>
                            @endif
                        </td>
                        <td>
                            @if((float)$coupon->min_spend > 0)
                                ₱{{ number_format($coupon->min_spend, 0) }}+
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            {{ $coupon->used_count }}
                            @if($coupon->usage_limit)
                                / {{ $coupon->usage_limit }}
                                <div style="width:80px;height:4px;background:#e5e7eb;border-radius:2px;margin-top:3px">
                                    <div style="width:{{ min(100, ($coupon->used_count/$coupon->usage_limit)*100) }}%;height:100%;background:{{ $coupon->used_count>=$coupon->usage_limit ? '#ef4444' : '#22c55e' }};border-radius:2px"></div>
                                </div>
                            @else
                                <span class="text-muted text-sm">/ ∞</span>
                            @endif
                        </td>
                        <td style="font-size:.85rem">
                            @if($coupon->expires_at)
                                <span style="{{ $expired ? 'color:#ef4444' : '' }}">
                                    {{ $coupon->expires_at->format('M d, Y') }}
                                    @if($expired) <br><small style="color:#ef4444">Expired</small> @endif
                                </span>
                            @else
                                <span class="text-muted">No expiry</span>
                            @endif
                        </td>
                        <td>
                            @if($live)
                                <span style="background:#dcfce7;color:#166534;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">Active</span>
                            @elseif($exhausted)
                                <span style="background:#fee2e2;color:#991b1b;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">Exhausted</span>
                            @elseif($expired)
                                <span style="background:#f3f4f6;color:#6b7280;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">Expired</span>
                            @else
                                <span style="background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">Disabled</span>
                            @endif
                        </td>
                        <td style="font-size:.82rem">{{ $coupon->createdBy?->name ?? '—' }}</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn btn-xs btn-outline"
                                        onclick="openEditModal({{ $coupon->id }}, '{{ $coupon->code }}', '{{ $coupon->type }}', '{{ $coupon->value }}', '{{ $coupon->min_spend }}', '{{ $coupon->usage_limit ?? '' }}', '{{ $coupon->expires_at?->format('Y-m-d') ?? '' }}', '{{ addslashes($coupon->description ?? '') }}', {{ $coupon->is_active ? 'true' : 'false' }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                                      onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No coupons yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $coupons->links() }}</div>
</div>

{{-- Create Modal --}}
<div class="modal" id="createModal">
    <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-tag"></i> New Coupon</h4>
            <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">×</button>
        </div>
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf
            <div class="modal-body">
                @include('admin.coupons._form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="this.closest('.modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal" id="editModal">
    <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-edit"></i> Edit Coupon — <span id="editModalCode"></span></h4>
            <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">×</button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                @include('admin.coupons._form', ['edit' => true])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="this.closest('.modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, code, type, value, minSpend, usageLimit, expiresAt, description, isActive) {
    document.getElementById('editModalCode').textContent = code;
    document.getElementById('editForm').action = '/admin/coupons/' + id;

    const form = document.getElementById('editForm');
    form.querySelector('[name=type]').value        = type;
    form.querySelector('[name=value]').value       = value;
    form.querySelector('[name=min_spend]').value   = minSpend;
    form.querySelector('[name=usage_limit]').value = usageLimit;
    form.querySelector('[name=expires_at]').value  = expiresAt;
    form.querySelector('[name=description]').value = description;
    form.querySelector('[name=is_active]').checked = isActive;

    document.getElementById('editModal').classList.add('open');
}
</script>
@endpush
