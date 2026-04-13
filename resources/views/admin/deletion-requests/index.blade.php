@extends('layouts.admin')
@section('title', 'Deletion Requests')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Deletion Requests
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => false, 'filterCount' => 1, 'cols' => 7, 'rows' => 6])
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>Deletion Requests</h2>
        <p>{{ auth('admin')->user()->isSuperAdmin() ? 'Review and manage deletion requests from staff' : 'Your submitted deletion requests' }}</p>
    </div>
    @if($pendingCount > 0)
        <span class="status-badge status-pending" style="font-size:.9rem;padding:.4rem 1rem">
            {{ $pendingCount }} Pending
        </span>
    @endif
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.deletion-requests.index') }}" method="GET" class="filter-row">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.deletion-requests.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Requested By</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    @if(auth('admin')->user()->isSuperAdmin())
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $dr)
                    <tr>
                        <td>{{ $dr->id }}</td>
                        <td>
                            <strong>{{ $dr->requester->name ?? '—' }}</strong>
                            <br><small class="text-muted">{{ $dr->requester->department_label ?? '' }} — {{ $dr->requester->position ?? '' }}</small>
                        </td>
                        <td><span class="badge badge-primary">{{ ucfirst($dr->type) }}</span></td>
                        <td><code>{{ $dr->target_label }}</code></td>
                        <td style="max-width:220px;white-space:normal">{{ Str::limit($dr->reason, 80) }}</td>
                        <td>
                            @php
                                $statusClass = match($dr->status) {
                                    'approved' => 'status-confirmed',
                                    'rejected' => 'status-cancelled',
                                    default    => 'status-pending',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ ucfirst($dr->status) }}</span>
                            @if($dr->reviewed_at)
                                <br><small class="text-muted">{{ $dr->reviewed_at->format('M d, Y') }}</small>
                            @endif
                            @if($dr->review_note)
                                <br><small class="text-muted" title="{{ $dr->review_note }}">
                                    <i class="fas fa-comment"></i> {{ Str::limit($dr->review_note, 40) }}
                                </small>
                            @endif
                        </td>
                        <td>{{ $dr->created_at->format('M d, Y') }}<br><small class="text-muted">{{ $dr->created_at->diffForHumans() }}</small></td>
                        @if(auth('admin')->user()->isSuperAdmin())
                            <td>
                                @if($dr->status === 'pending')
                                    <div class="action-btns" style="flex-direction:column;gap:.4rem">
                                        <form method="POST" action="{{ route('admin.deletion-requests.approve', $dr) }}"
                                              onsubmit="return confirm('Approve and permanently delete {{ $dr->target_label }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success" style="width:100%">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-xs btn-danger" style="width:100%"
                                                onclick="openRejectModal({{ $dr->id }}, '{{ $dr->target_label }}')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth('admin')->user()->isSuperAdmin() ? 8 : 7 }}" class="text-center text-muted" style="padding:2rem">
                            No deletion requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="card-footer">{{ $requests->links() }}</div>
    @endif
</div>

{{-- Reject modal (super admin only) --}}
@if(auth('admin')->user()->isSuperAdmin())
<div class="modal" id="rejectModal">
    <div class="modal-backdrop" onclick="closeRejectModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-times-circle text-danger"></i> Reject Deletion Request</h4>
            <button class="modal-close" onclick="closeRejectModal()">×</button>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="modal-body">
                <p>Rejecting deletion request for <strong id="reject-label"></strong>.</p>
                <div class="form-group">
                    <label>Reason for rejection <span class="text-danger">*</span></label>
                    <textarea name="review_note" class="form-control" rows="3"
                              placeholder="Explain why you're rejecting this request…"
                              required maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Reject Request
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@if(auth('admin')->user()->isSuperAdmin())
@push('scripts')
<script>
function openRejectModal(id, label) {
    document.getElementById('reject-label').textContent = label;
    document.getElementById('rejectForm').action = '{{ url("admin/deletion-requests") }}/' + id + '/reject';
    document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('open');
}
</script>
@endpush
@endif
