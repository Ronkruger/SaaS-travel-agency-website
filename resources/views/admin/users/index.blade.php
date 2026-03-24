@extends('layouts.admin')
@section('title', 'Users')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Users
@endsection

@section('content')
<div class="page-title-row">
    <h2>Users</h2>
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
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
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
                        <td>{{ $user->bookings_count }}</td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-outline">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
