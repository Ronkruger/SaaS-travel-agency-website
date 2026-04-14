@extends('layouts.admin')

@section('title', 'Admin Staff & Permissions')

@section('breadcrumb')
    <span>Staff & Permissions</span>
@endsection

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
    <h1 style="font-size:1.375rem;font-weight:700;margin:0">Admin Staff &amp; Permissions</h1>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:1rem">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="data-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department / Position</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    @if(auth('admin')->user()->isSuperAdmin())
                    <th style="width:80px">Edit</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $member)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.6rem">
                            @if($member->avatar)
                                <img src="{{ $member->avatar }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                            @else
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600">{{ $member->name }}</div>
                                <div style="font-size:.75rem;color:var(--gray-500)">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.85rem">{{ $member->department_label }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500)">{{ $member->position }}</div>
                    </td>
                    <td>
                        @if($member->role === 'super_admin')
                            <span class="badge" style="background:#7c3aed;color:#fff">Super Admin</span>
                        @else
                            <span class="badge" style="background:var(--gray-200);color:var(--gray-700)">Staff</span>
                        @endif
                    </td>
                    <td>
                        @if($member->role === 'super_admin')
                            <span style="font-size:.75rem;color:var(--gray-400);font-style:italic">All permissions</span>
                        @else
                            @php
                                $granted = $member->permissions ?? \App\Models\AdminUser::DEFAULT_STAFF_PERMISSIONS;
                            @endphp
                            <div style="display:flex;flex-wrap:wrap;gap:.3rem">
                                @foreach($permissions as $key => $label)
                                    @if(in_array($key, $granted))
                                        <span class="badge" style="background:#dcfce7;color:#166534;font-size:.68rem">{{ $label }}</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.68rem;text-decoration:line-through;opacity:.55">{{ $label }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                    @if(auth('admin')->user()->isSuperAdmin())
                    <td>
                        <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
