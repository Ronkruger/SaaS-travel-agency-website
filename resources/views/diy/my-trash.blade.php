@extends('layouts.app')

@section('title', 'My DIY Tours — Trash')

@push('styles')
<style>
.my-diy-trash { padding: 60px 0 80px; min-height: 60vh; }
.my-diy-trash h1 { font-size: 1.8rem; font-weight: 700; color: #6c757d; margin-bottom: 0.25rem; }
.my-diy-trash .subtitle { color: #6c757d; margin-bottom: 2rem; }

.trash-card {
    background: #fff;
    border: 1px dashed #dee2e6;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    opacity: .85;
    transition: opacity .15s;
}
.trash-card:hover { opacity: 1; }

.trash-card-icon {
    width: 48px; height: 48px;
    background: #e9ecef;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #adb5bd; font-size: 1.2rem; flex-shrink: 0;
}

.trash-card-body { flex: 1; min-width: 0; }
.trash-card-name { font-weight: 600; font-size: 1rem; color: #6c757d; margin-bottom: .2rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: line-through; }
.trash-card-meta { font-size: .8rem; color: #adb5bd; }
.trash-card-meta span { margin-right: .75rem; }

.trash-card-actions { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }

.btn-restore {
    background: #fff;
    border: 1px solid #28a745;
    color: #28a745;
    border-radius: 8px;
    padding: .35rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, color .15s;
    text-decoration: none;
    display: inline-block;
}
.btn-restore:hover { background: #28a745; color: #fff; }

.btn-perm-delete {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    border-radius: 8px;
    padding: .35rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.btn-perm-delete:hover { background: #dc3545; color: #fff; }

.back-link { font-size: .85rem; color: #0A2D74; text-decoration: none; font-weight: 500; }
.back-link:hover { text-decoration: underline; }

.deleted-date { font-size: .78rem; color: #dc3545; font-weight: 600; }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-state i { font-size: 3.5rem; color: #cdd5e0; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.3rem; color: #444; margin-bottom: .5rem; }
.empty-state p { color: #888; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="my-diy-trash">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
            <div>
                <h1><i class="fas fa-trash-alt me-2"></i>Trash</h1>
                <p class="subtitle">Deleted tours are kept here. Restore to recover or permanently delete.</p>
            </div>
            <a href="{{ route('diy.my-tours') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to My DIY Tours
            </a>
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
            @endphp
            <div class="trash-card">
                <div class="trash-card-icon">
                    <i class="fas fa-route"></i>
                </div>

                <div class="trash-card-body">
                    <div class="trash-card-name">{{ $name }}</div>
                    <div class="trash-card-meta">
                        @if($days)<span><i class="fas fa-calendar-alt"></i> {{ $days }} days</span>@endif
                        @if(count($countries))<span><i class="fas fa-globe-asia"></i> {{ implode(', ', $countries) }}</span>@endif
                        @if($price)<span><i class="fas fa-tag"></i> ₱{{ number_format($price) }}/person est.</span>@endif
                        <span class="deleted-date"><i class="fas fa-trash-alt"></i> Deleted {{ $session->deleted_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="trash-card-actions">
                    {{-- Restore --}}
                    <form action="{{ route('diy.restore', $session->session_token) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-restore">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    </form>

                    {{-- Permanently Delete --}}
                    <form action="{{ route('diy.force-delete', $session->session_token) }}" method="POST"
                          onsubmit="return confirm('Permanently delete this tour? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-perm-delete">
                            <i class="fas fa-times"></i> Delete Forever
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-trash"></i>
                <h3>Trash is empty</h3>
                <p>No deleted DIY tours found.</p>
                <a href="{{ route('diy.my-tours') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to My DIY Tours
                </a>
            </div>
        @endforelse

        <div class="mt-3">{{ $sessions->links() }}</div>
    </div>
</div>
@endsection
