{{-- Skeleton: Report page --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
    <div>
        <div class="skeleton skeleton-title" style="width:20rem"></div>
        <div class="skeleton skeleton-subtitle" style="width:14rem"></div>
    </div>
    <div style="display:flex;gap:.75rem">
        <div class="skeleton skeleton-btn" style="width:7rem;height:2.2rem"></div>
        <div class="skeleton skeleton-btn" style="width:7rem;height:2.2rem"></div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-4" style="padding:1.25rem 1.5rem">
    <div class="skeleton-filter-row">
        <div class="skeleton skeleton-filter" style="min-width:130px;max-width:160px"></div>
        <div class="skeleton skeleton-filter" style="min-width:100px;max-width:120px"></div>
        <div class="skeleton skeleton-btn" style="flex:0 0 auto;width:5rem"></div>
    </div>
</div>

{{-- Stats --}}
<div class="skeleton-stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr))">
    @for($i = 0; $i < 4; $i++)
        <div class="skeleton-stat-card">
            <div class="skeleton skeleton-stat-icon" style="width:2.5rem;height:2.5rem"></div>
            <div class="skeleton-stat-info">
                <div class="skeleton skeleton-stat" style="width:55%;height:1.5rem;margin-bottom:.35rem"></div>
                <div class="skeleton skeleton-text-sm" style="width:75%"></div>
            </div>
        </div>
    @endfor
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="skeleton-table-head">
            @for($i = 0; $i < 6; $i++)
                <div class="skeleton skeleton-th"></div>
            @endfor
        </div>
        @for($r = 0; $r < 6; $r++)
            <div class="skeleton-row">
                @for($i = 0; $i < 6; $i++)
                    <div class="skeleton skeleton-cell"></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
