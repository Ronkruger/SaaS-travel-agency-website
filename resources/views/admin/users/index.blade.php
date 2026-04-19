@extends('layouts.admin')
@section('title', 'Clients')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Clients
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => false, 'filterCount' => 1, 'cols' => 5, 'rows' => 8])
@endsection

@section('content')
<div class="page-title-row">
    <h2>Clients</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="filter-row">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Name or Email..." class="form-control">
            <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="data-table" style="min-width:700px">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>Auth</th>
                    <th>Bookings</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->country ?? '—' }}</td>
                        <td>
                            @if($user->auth0_id)
                                <span style="display:inline-flex;align-items:center;gap:.3rem;background:#ede9fe;color:#5b21b6;padding:.2rem .55rem;border-radius:1rem;font-size:.75rem;font-weight:600;white-space:nowrap">
                                    <i class="fas fa-shield-alt"></i> Auth0
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:.3rem;background:#dbeafe;color:#1e40af;padding:.2rem .55rem;border-radius:1rem;font-size:.75rem;font-weight:600;white-space:nowrap">
                                    <i class="fas fa-envelope"></i> Email
                                </span>
                            @endif
                        </td>
                        <td>{{ $user->bookings_count }}</td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="white-space:nowrap">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-outline">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-outline" style="margin-left:.25rem">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;margin-left:.25rem"
                                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <div style="font-size:2rem;margin-bottom:.5rem;opacity:.4;">
                                <i class="fas fa-users"></i>
                            </div>
                            <strong>No clients yet.</strong>
                            <div class="small mt-1">
                                This page lists customers who sign up to book your tours.
                                Share your booking page to start collecting clients.
                                <br>
                                Looking for staff accounts? See <em>Admin Staff</em> in the sidebar.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
