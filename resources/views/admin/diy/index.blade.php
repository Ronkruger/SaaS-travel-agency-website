@extends('layouts.admin')

@section('title', 'DIY Tour Requests')

@section('breadcrumb')
    DIY Tours
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => false, 'filterCount' => 2, 'cols' => 7, 'rows' => 6])
@endsection

@section('content')
<div class="admin-content">

    <div class="page-title-row">
        <h1 class="page-title"><i class="fas fa-magic" style="color:var(--primary);margin-right:.5rem"></i> DIY Tour Requests</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-row">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name or email…" style="min-width:220px">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" style="min-width:160px">
                <option value="">All Statuses</option>
                @foreach(['draft','pending_review','quoted','booked'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.diy.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Tour Name</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Days</th>
                        <th>Est. Cost (pp)</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    @php
                        $it = $session->latestItinerary;
                        $adminSt = $session->admin_status ?? 'pending';
                        $sessionStatusClass = match($session->status) {
                            'booked'         => 'status-confirmed',
                            'quoted'         => 'status-completed',
                            'pending_review' => 'status-pending',
                            default          => '',
                        };
                    @endphp
                    <tr>
                        <td class="text-muted text-sm">{{ $session->id }}</td>
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
                            @if($sessionStatusClass)
                                <span class="status-badge {{ $sessionStatusClass }}">
                                    {{ ucfirst(str_replace('_',' ',$session->status)) }}
                                </span>
                            @else
                                <span class="status-badge" style="background:var(--gray-100);color:var(--gray-500)">
                                    {{ ucfirst($session->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($adminSt === 'approved')
                                <span class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> Approved</span>
                            @elseif($adminSt === 'rejected')
                                <span class="status-badge status-cancelled"><i class="fas fa-times-circle"></i> Rejected</span>
                            @else
                                <span class="status-badge status-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                            @endif
                        </td>
                        <td>{{ $it?->user_preferences['duration_days'] ?? '—' }}</td>
                        <td>
                            @if($it?->pricing_data['total_per_person'] ?? null)
                                ₱{{ number_format($it->pricing_data['total_per_person']) }}
                            @else —
                            @endif
                        </td>
                        <td class="text-muted text-sm">{{ $session->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.diy.show', $session) }}" class="btn btn-xs btn-outline">View</a>
                                @if($adminSt === 'pending')
                                <form action="{{ route('admin.diy.approve', $session) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button class="btn btn-xs btn-success">Approve</button>
                                </form>
                                <form action="{{ route('admin.diy.reject', $session) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button class="btn btn-xs btn-danger" onclick="return confirm('Reject this DIY tour request?')">Reject</button>
                                </form>
                                @endif
                                <form action="{{ route('admin.diy.destroy', $session) }}" method="POST" style="display:inline"
                                      onsubmit="return confirm('Permanently delete this DIY tour session? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:var(--gray-500);padding:2.5rem 1rem">
                            <i class="fas fa-inbox" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.75rem"></i>
                            No DIY tour requests found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $sessions->links() }}</div>
</div>
@endsection
