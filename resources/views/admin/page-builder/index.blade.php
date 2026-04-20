@extends('layouts.admin')
@section('title', 'Page Builder')
@section('breadcrumb')
<nav style="font-size:.85rem;color:var(--gray-500)">
    <a href="{{ route('admin.dashboard') }}" style="color:var(--primary)">Dashboard</a>
    <span style="margin:0 6px">/</span> Page Builder
</nav>
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 class="page-title" style="margin:0 0 4px">Page Builder</h1>
        <p style="color:var(--gray-500);font-size:.9rem;margin:0">Drag and drop sections to build your home page.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline" style="gap:6px">
        <i class="fas fa-external-link-alt"></i> Preview Site
    </a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start">
    {{-- Left: Available Sections --}}
    <div class="card" style="position:sticky;top:80px">
        <div class="card-header" style="padding:16px 20px">
            <h3 style="margin:0;font-size:.95rem;font-weight:700"><i class="fas fa-puzzle-piece" style="margin-right:8px;color:var(--primary)"></i>Add Sections</h3>
        </div>
        <div class="card-body" style="padding:12px">
            @foreach($sectionTypes as $type => $meta)
            <form action="{{ route('admin.page-builder.store') }}" method="POST" style="margin-bottom:8px">
                @csrf
                <input type="hidden" name="section_type" value="{{ $type }}">
                <button type="submit" class="section-type-btn" style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:10px;cursor:pointer;text-align:left;transition:all .2s">
                    <div style="width:38px;height:38px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="{{ $meta['icon'] }}" style="color:#fff;font-size:.9rem"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:600;font-size:.85rem;color:var(--gray-800)">{{ $meta['label'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);line-height:1.3">{{ \Illuminate\Support\Str::limit($meta['description'], 50) }}</div>
                    </div>
                    <i class="fas fa-plus" style="color:var(--primary);font-size:.8rem"></i>
                </button>
            </form>
            @endforeach
        </div>
    </div>

    {{-- Right: Active Sections --}}
    <div>
        @if($sections->isEmpty())
        <div class="card" style="text-align:center;padding:80px 40px">
            <div style="width:80px;height:80px;background:var(--gray-100);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <i class="fas fa-layer-group" style="font-size:2rem;color:var(--gray-400)"></i>
            </div>
            <h3 style="margin:0 0 8px;font-size:1.2rem;color:var(--gray-700)">Your Home Page is Empty</h3>
            <p style="color:var(--gray-500);margin:0 0 20px;max-width:400px;margin-left:auto;margin-right:auto">
                Click any section from the left panel to start building your site. Drag sections to reorder them.
            </p>
            <p style="color:var(--gray-400);font-size:.85rem;margin:0">
                <i class="fas fa-info-circle"></i> Your customers will see a "Coming Soon" page until you add sections.
            </p>
        </div>
        @else
        <div id="sections-list">
            @foreach($sections as $section)
            @php $meta = $sectionTypes[$section->section_type] ?? ['label' => $section->section_type, 'icon' => 'fas fa-puzzle-piece']; @endphp
            <div class="card section-card" data-id="{{ $section->id }}" style="margin-bottom:12px;{{ !$section->is_active ? 'opacity:.5;' : '' }}">
                <div style="display:flex;align-items:center;gap:14px;padding:16px 20px">
                    <div class="drag-handle" style="cursor:grab;color:var(--gray-400);font-size:1.1rem;padding:4px" title="Drag to reorder">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <div style="width:40px;height:40px;background:{{ $section->is_active ? 'var(--primary)' : 'var(--gray-300)' }};border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="{{ $meta['icon'] }}" style="color:#fff;font-size:.9rem"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;font-size:.9rem;color:var(--gray-800)">{{ $section->title ?: $meta['label'] }}</div>
                        <div style="font-size:.8rem;color:var(--gray-500)">{{ $meta['label'] }}{{ !$section->is_active ? ' · Disabled' : '' }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <form action="{{ route('admin.page-builder.toggle', $section) }}" method="POST" style="margin:0">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-ghost" title="{{ $section->is_active ? 'Disable' : 'Enable' }}" style="padding:6px 10px">
                                <i class="fas {{ $section->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.page-builder.edit', $section) }}" class="btn btn-sm btn-outline" style="padding:6px 12px">
                            <i class="fas fa-pen" style="margin-right:4px"></i> Edit
                        </a>
                        <form action="{{ route('admin.page-builder.destroy', $section) }}" method="POST" style="margin:0" onsubmit="return confirm('Remove this section?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-ghost" style="padding:6px 10px;color:var(--danger)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.section-type-btn:hover { background: var(--primary-light, #eef2ff) !important; border-color: var(--primary) !important; }
.section-card { transition: box-shadow .2s, transform .1s; }
.section-card.sortable-ghost { opacity: .4; }
.section-card.sortable-chosen { box-shadow: 0 8px 25px rgba(0,0,0,.15); transform: scale(1.02); }
.drag-handle:hover { color: var(--gray-600); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var list = document.getElementById('sections-list');
    if (!list) return;

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 200,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function() {
            var ids = [];
            list.querySelectorAll('.section-card').forEach(function(el) {
                ids.push(parseInt(el.dataset.id));
            });
            fetch('{{ route("admin.page-builder.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: ids })
            });
        }
    });
});
</script>
@endpush
