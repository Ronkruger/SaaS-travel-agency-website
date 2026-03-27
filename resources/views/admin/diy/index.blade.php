@extends('layouts.admin')

@section('title', 'DIY Tour Requests')

@section('breadcrumb')
    DIY Tours
@endsection

@section('content')
<div class="admin-content">
    <div class="content-header">
        <h1><i class="fas fa-magic"></i> DIY Tour Requests</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" class="admin-filter-row mb-4">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by name or email…" style="max-width:240px">
        <select name="status" class="form-control form-control-sm" style="max-width:180px">
            <option value="">All Statuses</option>
            @foreach(['draft','pending_review','quoted','booked'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.diy.index') }}" class="btn btn-outline btn-sm">Clear</a>
    </form>

    {{-- Table --}}
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Tour Name</th>
                    <th>Status</th>
                    <th>Days</th>
                    <th>Est. Cost (pp)</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                @php $it = $session->latestItinerary; @endphp
                <tr data-href="{{ route('admin.diy.show', $session) }}">
                    <td class="text-muted small">{{ $session->id }}</td>
                    <td>
                        @if($session->user)
                            <strong>{{ $session->user->name }}</strong><br>
                            <small class="text-muted">{{ $session->user->email }}</small>
                        @else
                            <span class="text-muted">Guest</span>
                        @endif
                    </td>
                    <td>{{ $it?->tour_name ?? '—' }}</td>
                    <td>
                        <span class="status-badge status-{{ $session->status }}">
                            {{ ucfirst(str_replace('_',' ',$session->status)) }}
                        </span>
                    </td>
                    <td>{{ $it?->user_preferences['duration_days'] ?? '—' }}</td>
                    <td>
                        @if($it?->pricing_data['total_per_person'] ?? null)
                            ₱{{ number_format($it->pricing_data['total_per_person']) }}
                        @else —
                        @endif
                    </td>
                    <td class="text-muted small">{{ $session->created_at->format('M j, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.diy.show', $session) }}" class="btn btn-sm btn-outline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No DIY tour requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $sessions->links() }}</div>
</div>
@endsection
