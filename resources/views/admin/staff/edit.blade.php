@extends('layouts.admin')

@section('title', 'Edit Permissions — ' . $member->name)

@section('breadcrumb')
    <a href="{{ route('admin.staff.index') }}">Staff &amp; Permissions</a> /
    <span>{{ $member->name }}</span>
@endsection

@section('content')
<div class="page-header" style="margin-bottom:1.5rem">
    <h1 style="font-size:1.375rem;font-weight:700;margin:0">Edit Permissions — {{ $member->name }}</h1>
</div>

<form method="POST" action="{{ route('admin.staff.update', $member) }}">
    @csrf
    @method('PUT')

    <div class="card" style="max-width:640px">
        <div class="card-body" style="padding:1.5rem">

            {{-- Role --}}
            <div class="form-group" style="margin-bottom:1.25rem">
                <label class="form-label" style="font-weight:600">Role</label>
                <div style="display:flex;gap:1rem;margin-top:.4rem">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                        <input type="radio" name="role" value="super_admin"
                               {{ old('role', $member->role) === 'super_admin' ? 'checked' : '' }}
                               onchange="togglePermissions(this.value)">
                        <span><strong>Super Admin</strong> — full access to everything</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                        <input type="radio" name="role" value="staff"
                               {{ old('role', $member->role) === 'staff' ? 'checked' : '' }}
                               onchange="togglePermissions(this.value)">
                        <span><strong>Staff</strong> — limited to selected permissions</span>
                    </label>
                </div>
            </div>

            {{-- Permissions --}}
            @php
                $granted = old('permissions', $member->permissions ?? $defaults);
            @endphp
            <div id="perms-section" style="{{ $member->role === 'super_admin' ? 'opacity:.4;pointer-events:none' : '' }}">
                <label class="form-label" style="font-weight:600;display:block;margin-bottom:.75rem">
                    Permissions
                    <span style="font-size:.75rem;font-weight:400;color:var(--gray-500)">(select all that apply)</span>
                </label>

                <div style="display:grid;gap:.6rem">
                    @foreach($permissions as $key => $label)
                    <label style="display:flex;align-items:center;gap:.7rem;padding:.65rem .9rem;border:1px solid var(--gray-200);border-radius:8px;cursor:pointer;transition:background .15s"
                           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                               {{ in_array($key, (array) $granted) ? 'checked' : '' }}
                               style="width:16px;height:16px;accent-color:var(--primary)">
                        <div>
                            <div style="font-weight:500;font-size:.875rem">{{ $label }}</div>
                            <div style="font-size:.72rem;color:var(--gray-400)">{{ $key }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div style="margin-top:.75rem">
                    <button type="button" onclick="toggleAll(true)" class="btn btn-sm btn-secondary" style="margin-right:.4rem">Check all</button>
                    <button type="button" onclick="toggleAll(false)" class="btn btn-sm btn-secondary">Uncheck all</button>
                </div>
            </div>
        </div>

        <div class="card-footer" style="display:flex;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid var(--gray-100)">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
function togglePermissions(role) {
    const section = document.getElementById('perms-section');
    if (role === 'super_admin') {
        section.style.opacity = '.4';
        section.style.pointerEvents = 'none';
    } else {
        section.style.opacity = '1';
        section.style.pointerEvents = '';
    }
}
function toggleAll(state) {
    document.querySelectorAll('#perms-section input[type=checkbox]').forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection
