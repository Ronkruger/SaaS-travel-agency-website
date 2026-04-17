@extends('layouts.admin')
@section('title', 'Plans')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Plans
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => true, 'filterCount' => 3, 'cols' => 9, 'rows' => 8])
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>All Plans</h2>
        <p>Manage your service plans</p>
    </div>
    <a href="{{ route('admin.tours.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Plan
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.tours.index') }}" method="GET" class="filter-row">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search plans..." class="form-control">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="trashed"  {{ request('status') === 'trashed'  ? 'selected' : '' }}>Trashed</option>
            </select>
            <select name="continent" class="form-control">
                <option value="">All Regions</option>
                @foreach(['Africa','Antarctica','Asia','Europe','North America','Oceania','South America'] as $c)
                    <option value="{{ $c }}" {{ request('continent') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('admin.tours.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:60px">Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Subscriptions</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tours as $tour)
                    <tr {{ $tour->trashed() ? 'class=row-trashed' : '' }}>
                        <td>
                            @if($tour->main_image)
                                <img src="{{ cdn_url($tour->main_image) }}"
                                     alt="{{ $tour->title }}" class="table-thumb">
                            @else
                                <div class="table-thumb" style="background:var(--gray-100);display:flex;align-items:center;justify-content:center">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $tour->title }}</strong><br>
                            @if($tour->line || $tour->continent)
                                <small class="text-muted">{{ implode(' · ', array_filter([$tour->line, $tour->continent])) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($tour->line)
                                <span class="badge badge-primary">{{ $tour->line }}</span><br>
                            @endif
                            <small class="text-muted">{{ $tour->continent ?? '—' }}</small>
                        </td>
                        <td>
                            @if($tour->regular_price_per_person)
                                ₱{{ number_format($tour->regular_price_per_person, 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                            @if($tour->promo_price_per_person)
                                <br><small class="text-green">Sale: ₱{{ number_format($tour->promo_price_per_person, 2) }}</small>
                            @endif
                        </td>
                        <td>{{ $tour->duration_days }}d</td>
                        <td>{{ $tour->total_bookings ?? 0 }}</td>
                        <td>
                            @if($tour->average_rating > 0)
                                <i class="fas fa-star text-yellow"></i>
                                {{ number_format($tour->average_rating, 1) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($tour->trashed())
                                <span class="status-badge status-cancelled">Trashed</span>
                            @elseif($tour->is_active)
                                <span class="status-badge status-confirmed">Active</span>
                            @else
                                <span class="status-badge status-pending">Inactive</span>
                            @endif
                            @if($tour->is_featured)
                                <span class="badge-featured">★ Featured</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                @if(!$tour->trashed())
                                    <a href="{{ route('tours.show', $tour->slug) }}" target="_blank"
                                       class="btn btn-xs btn-ghost" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tours.edit', $tour) }}"
                                       class="btn btn-xs btn-outline" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.tours.destroy', $tour) }}" method="POST"
                                          onsubmit="return confirm('Move plan to trash?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Trash">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.tours.restore', $tour->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline text-green" title="Restore">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.tours.force-delete', $tour->id) }}" method="POST"
                                          onsubmit="return confirm('Permanently delete this plan? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete Forever">
                                            <i class="fas fa-times-circle"></i> Delete Forever
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No plans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $tours->links() }}
    </div>
</div>
@endsection
