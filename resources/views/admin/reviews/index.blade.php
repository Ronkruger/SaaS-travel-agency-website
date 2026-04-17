@extends('layouts.admin')
@section('title', 'Testimonials')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Testimonials
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => false, 'filterCount' => 1, 'cols' => 6, 'rows' => 6])
@endsection

@section('content')
<div class="page-title-row">
    <h2>Testimonials</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="filter-row">
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            </select>
            <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Rating</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>{{ $review->user->name }}</td>
                        <td>{{ Str::limit($review->tour->title, 30) }}</td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow' : 'text-muted' }}"></i>
                            @endfor
                        </td>
                        <td>{{ Str::limit($review->title, 40) }}</td>
                        <td>{{ $review->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($review->is_approved)
                                <span class="status-badge status-confirmed">Approved</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                @if(!$review->is_approved)
                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                      onsubmit="return confirm('Delete this review?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No reviews found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $reviews->links() }}</div>
</div>
@endsection
