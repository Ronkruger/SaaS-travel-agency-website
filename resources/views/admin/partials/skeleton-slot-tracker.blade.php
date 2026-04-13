{{-- Skeleton: Slot Tracker --}}
<div style="margin-bottom:1.75rem">
    <div class="skeleton skeleton-title" style="width:25%"></div>
</div>

{{-- Stat grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">
    @for($i = 0; $i < 5; $i++)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem 1rem;text-align:center">
            <div class="skeleton" style="height:2rem;width:50%;margin:0 auto .4rem"></div>
            <div class="skeleton skeleton-text-sm" style="width:70%;margin:0 auto"></div>
        </div>
    @endfor
</div>

{{-- Filter tabs --}}
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem">
    @for($i = 0; $i < 4; $i++)
        <div class="skeleton" style="height:2rem;width:5rem;border-radius:.5rem"></div>
    @endfor
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="skeleton-table-head">
            @for($i = 0; $i < 8; $i++)
                <div class="skeleton skeleton-th"></div>
            @endfor
        </div>
        @for($r = 0; $r < 8; $r++)
            <div class="skeleton-row">
                @for($i = 0; $i < 8; $i++)
                    <div class="skeleton skeleton-cell"></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
