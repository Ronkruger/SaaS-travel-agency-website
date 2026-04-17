@extends('layouts.app')

@section('title', 'My Custom Plans')

@push('styles')
<style>
.my-diy-tours { padding: 60px 0 80px; min-height: 60vh; }
.my-diy-tours h1 { font-size: 1.8rem; font-weight: 700; color: #0A2D74; margin-bottom: 0.25rem; }
.my-diy-tours .subtitle { color: #6c757d; margin-bottom: 2rem; }

.diy-tour-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: box-shadow .18s, border-color .18s;
}
.diy-tour-card:hover { box-shadow: 0 4px 18px rgba(10,45,116,.08); border-color: #c9d6e8; }

.diy-tour-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg,#0A2D74,#28A2DC);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.2rem; flex-shrink: 0;
}

.diy-tour-body { flex: 1; min-width: 0; }
.diy-tour-name { font-weight: 600; font-size: 1rem; color: #1a1a2e; margin-bottom: .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.diy-tour-meta { font-size: .8rem; color: #888; }
.diy-tour-meta span { margin-right: .75rem; }

.diy-tour-badges { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

/* status badge */
.badge-status { padding: .28rem .7rem; border-radius: 20px; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.badge-draft        { background:#f0f3f8; color:#5a6a82; }
.badge-pending_review { background:#fff3cd; color:#856404; }
.badge-quoted       { background:#d1ecf1; color:#0c5460; }
.badge-booked       { background:#d4edda; color:#155724; }

/* approval badge */
.badge-approval { padding: .28rem .7rem; border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.badge-pending  { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
.badge-approved { background: #d4edda; color: #155724; }
.badge-rejected { background: #f8d7da; color: #721c24; }

.diy-tour-actions { display: flex; align-items: center; gap: .4rem; }
.diy-tour-actions .btn { font-size: .8rem; padding: .35rem .9rem; border-radius: 8px; }
.btn-trash {
    background: none;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: .35rem .6rem;
    color: #adb5bd;
    cursor: pointer;
    transition: color .15s, border-color .15s, background .15s;
    font-size: .85rem;
    line-height: 1;
}
.btn-trash:hover { color: #dc3545; border-color: #dc3545; background: #fff5f5; }

.trash-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;
    padding: .6rem 1rem; margin-bottom: 1.5rem; font-size: .85rem; color: #6c757d;
}
.trash-bar a { color: #dc3545; font-weight: 600; text-decoration: none; }
.trash-bar a:hover { text-decoration: underline; }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-state i { font-size: 3.5rem; color: #cdd5e0; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.3rem; color: #444; margin-bottom: .5rem; }
.empty-state p { color: #888; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="my-diy-tours">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
            <div>
                <h1><i class="fas fa-magic me-2" style="color:#28A2DC"></i>My DIY Tours</h1>
                <p class="subtitle">All your custom tour requests and their approval status.</p>
            </div>
            <a href="{{ route('diy.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Build New Tour
            </a>
        </div>

        {{-- Trash shortcut bar --}}
        <div class="trash-bar">
            <span><i class="fas fa-trash-alt me-1"></i> Deleted tours are moved to trash and can be recovered.</span>
            <a href="{{ route('diy.trash') }}"><i class="fas fa-trash"></i> View Trash</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @forelse($sessions as $session)
            @php
                $it        = $session->latestItinerary;
                $name      = $it?->tour_name ?? 'Untitled Tour';
                $days      = $it?->user_preferences['duration_days'] ?? null;
                $countries = $it?->user_preferences['countries'] ?? [];
                $price     = $it?->pricing_data['total_per_person'] ?? null;
                $adminSt   = $session->admin_status ?? 'pending';
                $status    = $session->status;
            @endphp
            <div class="diy-tour-card">
                <div class="diy-tour-icon">
                    <i class="fas fa-route"></i>
                </div>

                <div class="diy-tour-body">
                    <div class="diy-tour-name">{{ $name }}</div>
                    <div class="diy-tour-meta">
                        @if($days)<span><i class="fas fa-calendar-alt"></i> {{ $days }} days</span>@endif
                        @if(count($countries))<span><i class="fas fa-globe-asia"></i> {{ implode(', ', $countries) }}</span>@endif
                        @if($price)<span><i class="fas fa-tag"></i> ₱{{ number_format($price) }}/person est.</span>@endif
                        <span><i class="fas fa-clock"></i> {{ $session->created_at->format('M j, Y') }}</span>
                    </div>
                </div>

                <div class="diy-tour-badges">
                    {{-- Workflow status --}}
                    <span class="badge-status badge-{{ $status }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>

                    {{-- Admin approval status --}}
                    <span class="badge-approval badge-{{ $adminSt }}">
                        @if($adminSt === 'approved')
                            <i class="fas fa-check-circle"></i> Approved
                        @elseif($adminSt === 'rejected')
                            <i class="fas fa-times-circle"></i> Rejected
                        @else
                            <i class="fas fa-hourglass-half"></i> Pending Approval
                        @endif
                    </span>
                </div>

                <div class="diy-tour-actions">
                    @if($status === 'quoted')
                        <a href="{{ route('diy.quote', $session->session_token) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-invoice"></i> View Quote
                        </a>
                    @else
                        <a href="{{ route('diy.builder', $session->session_token) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-pencil-alt"></i> View
                        </a>
                    @endif

                    {{-- Move to Trash --}}
                    <form action="{{ route('diy.delete', $session->session_token) }}" method="POST"
                          onsubmit="return confirm('Move this tour to trash? You can recover it later.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-trash" title="Move to trash">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No DIY tours yet</h3>
                <p>Build your first custom tour itinerary with AI assistance.</p>
                <a href="{{ route('diy.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-magic"></i> Start Building
                </a>
            </div>
        @endforelse

        <div class="mt-3">{{ $sessions->links() }}</div>
    </div>
</div>
@endsection


@push('styles')
<style>
.my-diy-tours { padding: 60px 0 80px; min-height: 60vh; }
.my-diy-tours h1 { font-size: 1.8rem; font-weight: 700; color: #0A2D74; margin-bottom: 0.25rem; }
.my-diy-tours .subtitle { color: #6c757d; margin-bottom: 2rem; }

.diy-tour-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: box-shadow .18s, border-color .18s;
}
.diy-tour-card:hover { box-shadow: 0 4px 18px rgba(10,45,116,.08); border-color: #c9d6e8; }

.diy-tour-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg,#0A2D74,#28A2DC);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.2rem; flex-shrink: 0;
}

.diy-tour-body { flex: 1; min-width: 0; }
.diy-tour-name { font-weight: 600; font-size: 1rem; color: #1a1a2e; margin-bottom: .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.diy-tour-meta { font-size: .8rem; color: #888; }
.diy-tour-meta span { margin-right: .75rem; }

.diy-tour-badges { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

/* status badge */
.badge-status { padding: .28rem .7rem; border-radius: 20px; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.badge-draft        { background:#f0f3f8; color:#5a6a82; }
.badge-pending_review { background:#fff3cd; color:#856404; }
.badge-quoted       { background:#d1ecf1; color:#0c5460; }
.badge-booked       { background:#d4edda; color:#155724; }

/* approval badge */
.badge-approval { padding: .28rem .7rem; border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.badge-pending  { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
.badge-approved { background: #d4edda; color: #155724; }
.badge-rejected { background: #f8d7da; color: #721c24; }

.diy-tour-actions .btn { font-size: .8rem; padding: .35rem .9rem; border-radius: 8px; }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-state i { font-size: 3.5rem; color: #cdd5e0; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.3rem; color: #444; margin-bottom: .5rem; }
.empty-state p { color: #888; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="my-diy-tours">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
            <div>
                <h1><i class="fas fa-magic me-2" style="color:#28A2DC"></i>My DIY Tours</h1>
                <p class="subtitle">All your custom tour requests and their approval status.</p>
            </div>
            <a href="{{ route('diy.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Build New Tour
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @forelse($sessions as $session)
            @php
                $it      = $session->latestItinerary;
                $name    = $it?->tour_name ?? 'Untitled Tour';
                $days    = $it?->user_preferences['duration_days'] ?? null;
                $countries = $it?->user_preferences['countries'] ?? [];
                $price   = $it?->pricing_data['total_per_person'] ?? null;
                $adminSt = $session->admin_status ?? 'pending';
                $status  = $session->status;
            @endphp
            <div class="diy-tour-card">
                <div class="diy-tour-icon">
                    <i class="fas fa-route"></i>
                </div>

                <div class="diy-tour-body">
                    <div class="diy-tour-name">{{ $name }}</div>
                    <div class="diy-tour-meta">
                        @if($days)<span><i class="fas fa-calendar-alt"></i> {{ $days }} days</span>@endif
                        @if(count($countries))<span><i class="fas fa-globe-asia"></i> {{ implode(', ', $countries) }}</span>@endif
                        @if($price)<span><i class="fas fa-tag"></i> ₱{{ number_format($price) }}/person est.</span>@endif
                        <span><i class="fas fa-clock"></i> {{ $session->created_at->format('M j, Y') }}</span>
                    </div>
                </div>

                <div class="diy-tour-badges">
                    {{-- Workflow status --}}
                    <span class="badge-status badge-{{ $status }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>

                    {{-- Admin approval status --}}
                    <span class="badge-approval badge-{{ $adminSt }}">
                        @if($adminSt === 'approved')
                            <i class="fas fa-check-circle"></i> Approved
                        @elseif($adminSt === 'rejected')
                            <i class="fas fa-times-circle"></i> Rejected
                        @else
                            <i class="fas fa-hourglass-half"></i> Pending Approval
                        @endif
                    </span>
                </div>

                <div class="diy-tour-actions">
                    @if($status === 'quoted')
                        <a href="{{ route('diy.quote', $session->session_token) }}" class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i> View Quote
                        </a>
                    @else
                        <a href="{{ route('diy.builder', $session->session_token) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-pencil-alt"></i> View
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No DIY tours yet</h3>
                <p>Build your first custom tour itinerary with AI assistance.</p>
                <a href="{{ route('diy.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-magic"></i> Start Building
                </a>
            </div>
        @endforelse

        <div class="mt-3">{{ $sessions->links() }}</div>
    </div>
</div>
@endsection
