@extends('central.platform.layouts.admin')
@section('title', 'Gateway Requests')
@section('page-title', 'Gateway Requests')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-list"></i></div>
        <div>
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Requests</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-num">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#2563eb,#3b82f6)"><i class="fas fa-spinner"></i></div>
        <div>
            <div class="stat-num">{{ $stats['in_progress'] }}</div>
            <div class="stat-label">In Progress</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-num">{{ $stats['approved'] }}</div>
            <div class="stat-label">Approved</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body" style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by gateway or agency..." class="form-control" style="max-width:300px;padding:.5rem .9rem;border:1px solid var(--border);border-radius:8px;font-size:.88rem">
            <select name="status" style="padding:.5rem .9rem;border:1px solid var(--border);border-radius:8px;font-size:.88rem;background:#fff">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search" style="margin-right:4px"></i> Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('platform.gateway-requests.index') }}" class="btn btn-sm btn-outline">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Requests Table --}}
<div class="card">
    <div class="card-header">
        <h3>Gateway Requests</h3>
        <span class="badge badge-info">{{ $requests->total() }} total</span>
    </div>
    @if($requests->count() > 0)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Agency</th>
                    <th>Gateway</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                <tr id="request-{{ $req->id }}">
                    <td>
                        <div style="font-weight:600">{{ $req->tenant->company_name ?? $req->tenant->name }}</div>
                        <div style="font-size:.78rem;color:var(--text-muted)">{{ $req->tenant->email }}</div>
                    </td>
                    <td><span style="font-weight:600;font-size:.9rem">{{ $req->gateway_name }}</span></td>
                    <td style="max-width:220px;font-size:.84rem;color:var(--text-muted)">{{ Str::limit($req->message, 100) ?: '—' }}</td>
                    <td>
                        @php
                            $badgeMap = ['pending' => 'badge-warning', 'in_progress' => 'badge-info', 'approved' => 'badge-success', 'rejected' => 'badge-danger'];
                        @endphp
                        <span class="badge {{ $badgeMap[$req->status] ?? 'badge-muted' }}">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-muted);white-space:nowrap">{{ $req->created_at->format('M d, Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="openUpdateModal({{ $req->id }}, '{{ $req->status }}', `{{ addslashes($req->admin_notes ?? '') }}`)">
                            <i class="fas fa-edit" style="margin-right:3px"></i> Update
                        </button>
                    </td>
                </tr>
                @if($req->admin_notes)
                <tr>
                    <td colspan="6" style="padding:0 1rem .6rem 3.5rem;border-bottom:1px solid var(--border);background:var(--bg)">
                        <div style="font-size:.82rem;color:var(--text-muted)"><i class="fas fa-sticky-note" style="margin-right:4px;color:#d97706"></i> <strong>Admin Notes:</strong> {{ $req->admin_notes }}</div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">
        {{ $requests->links('pagination::simple-bootstrap-5') }}
    </div>
    @else
    <div class="card-body" style="text-align:center;padding:3rem">
        <i class="fas fa-inbox" style="font-size:3rem;color:var(--text-muted);margin-bottom:1rem"></i>
        <p style="color:var(--text-muted);font-size:.95rem">No gateway requests found.</p>
    </div>
    @endif
</div>

{{-- Update Modal --}}
<div id="update-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:500px;box-shadow:0 25px 50px rgba(0,0,0,.15);margin:1rem">
        <div style="padding:1.2rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <h3 style="font-size:1rem;font-weight:700;margin:0"><i class="fas fa-edit" style="margin-right:6px;color:var(--primary)"></i> Update Gateway Request</h3>
            <button onclick="closeUpdateModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted)">&times;</button>
        </div>
        <form id="update-form" method="POST" style="padding:1.5rem">
            @csrf @method('PUT')

            <div style="margin-bottom:16px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Status</label>
                <select name="status" id="modal-status" style="width:100%;padding:.55rem .9rem;border:1px solid var(--border);border-radius:8px;font-size:.88rem;background:#fff">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Admin Notes</label>
                <textarea name="admin_notes" id="modal-notes" rows="3" style="width:100%;padding:.55rem .9rem;border:1px solid var(--border);border-radius:8px;font-size:.85rem;resize:vertical" placeholder="Add notes about this request (visible to the tenant)..."></textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="closeUpdateModal()" class="btn btn-sm btn-outline">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save" style="margin-right:4px"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openUpdateModal(id, status, notes) {
    var modal = document.getElementById('update-modal');
    var form = document.getElementById('update-form');
    form.action = '{{ url("platform/gateway-requests") }}/' + id;
    document.getElementById('modal-status').value = status;
    document.getElementById('modal-notes').value = notes;
    modal.style.display = 'flex';
}
function closeUpdateModal() {
    document.getElementById('update-modal').style.display = 'none';
}
document.getElementById('update-modal').addEventListener('click', function(e) {
    if (e.target === this) closeUpdateModal();
});
</script>
@endpush
