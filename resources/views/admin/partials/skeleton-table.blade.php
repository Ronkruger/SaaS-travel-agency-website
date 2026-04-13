{{-- Skeleton: Page title + filter + table --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem">
    <div>
        <div class="skeleton skeleton-title"></div>
        <div class="skeleton skeleton-subtitle"></div>
    </div>
    @if($showAction ?? false)
        <div class="skeleton skeleton-btn"></div>
    @endif
</div>

{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="skeleton-filter-row">
            @for($i = 0; $i < ($filterCount ?? 3); $i++)
                <div class="skeleton skeleton-filter"></div>
            @endfor
            <div class="skeleton skeleton-btn" style="flex:0 0 auto;width:6rem"></div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="skeleton-table-head">
            @for($i = 0; $i < ($cols ?? 7); $i++)
                <div class="skeleton skeleton-th"></div>
            @endfor
        </div>
        @for($r = 0; $r < ($rows ?? 8); $r++)
            <div class="skeleton-row">
                @for($i = 0; $i < ($cols ?? 7); $i++)
                    <div class="skeleton skeleton-cell"></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
