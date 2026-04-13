{{-- Skeleton: Import page --}}
<div style="margin-bottom:1.75rem">
    <div class="skeleton skeleton-title" style="width:30%"></div>
    <div class="skeleton skeleton-subtitle" style="width:50%"></div>
</div>

{{-- Hero banner skeleton --}}
<div class="skeleton" style="height:90px;border-radius:12px;margin-bottom:2rem"></div>

{{-- Upload card --}}
<div class="card mb-4">
    <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
        <div class="skeleton skeleton-text-lg" style="width:30%;margin:0"></div>
    </div>
    <div class="card-body">
        <div class="skeleton" style="height:180px;border-radius:12px;border:2px dashed #e2e8f0;margin-bottom:1rem"></div>
        <div style="display:flex;gap:1rem;align-items:center">
            <div class="skeleton skeleton-btn" style="width:10rem"></div>
            <div class="skeleton skeleton-text" style="width:15rem;margin:0"></div>
        </div>
    </div>
</div>

{{-- Column ref card --}}
<div class="card">
    <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
        <div class="skeleton skeleton-text-lg" style="width:35%;margin:0"></div>
    </div>
    <div class="card-body p-0">
        <div class="skeleton-table-head">
            @for($i = 0; $i < 5; $i++)
                <div class="skeleton skeleton-th"></div>
            @endfor
        </div>
        @for($r = 0; $r < 9; $r++)
            <div class="skeleton-row">
                @for($i = 0; $i < 5; $i++)
                    <div class="skeleton skeleton-cell"></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
