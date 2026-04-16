@extends('layouts.admin')
@section('title', 'Edit User — Admin')

@section('skeleton')
    @include('admin.partials.skeleton-form')
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
    <a href="{{ route('admin.users.index') }}">Users</a> /
    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a> /
    Edit
@endsection

@section('content')
<div class="page-title-row">
    <h2>Edit User</h2>
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul style="margin:0;padding-left:1.25rem">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="admin-detail-grid">

        <!-- Left: Main fields -->
        <div>
            <div class="card mb-4">
                <div class="card-header"><h4>Personal Information</h4></div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        @include('components.phone-input', [
                            'value' => old('phone', $user->phone),
                            'name'  => 'phone',
                            'error' => $errors->first('phone'),
                        ])
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city', $user->city) }}">
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                   value="{{ old('country', $user->country) }}">
                            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address', $user->address) }}">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>Change Password <small class="text-muted">(leave blank to keep current)</small></h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Min. 8 characters" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Repeat new password" autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Role & actions -->
        <div>
            <div class="card mb-4">
                <div class="card-header"><h4>Account</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror">
                            <option value="user"  {{ old('role', $user->role) === 'user'  ? 'selected' : '' }}>Customer</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <p class="text-muted" style="font-size:.8125rem;margin-top:.5rem">
                        <i class="fa-solid fa-calendar-plus"></i>
                        Joined {{ $user->created_at->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
                    <button type="submit" class="btn btn-primary" style="width:100%">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost" style="width:100%;text-align:center">
                        Cancel
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn" style="width:100%;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
                            <i class="fa-solid fa-trash"></i> Delete User
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</form>
@endsection
