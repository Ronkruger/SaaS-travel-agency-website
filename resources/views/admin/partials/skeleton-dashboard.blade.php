{{-- Skeleton: Dashboard --}}
<div style="margin-bottom:1.75rem">
    <div class="skeleton skeleton-title" style="width:35%"></div>
    <div class="skeleton skeleton-subtitle" style="width:25%"></div>
</div>

{{-- Stats grid --}}
<div class="skeleton-stats-grid">
    @for($i = 0; $i < 6; $i++)
        <div class="skeleton-stat-card">
            <div class="skeleton skeleton-stat-icon"></div>
            <div class="skeleton-stat-info">
                <div class="skeleton skeleton-stat" style="width:60%;height:1.75rem;margin-bottom:.4rem"></div>
                <div class="skeleton skeleton-text-sm" style="width:80%"></div>
            </div>
        </div>
    @endfor
</div>

{{-- Dashboard grid: table + sidebar --}}
<div class="dashboard-grid mt-4">
    <div class="card">
        <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
            <div class="skeleton skeleton-text-lg" style="width:40%;margin:0"></div>
            <div class="skeleton skeleton-btn" style="width:5rem;height:2rem"></div>
        </div>
        <div class="card-body p-0">
            <div class="skeleton-table-head">
                @for($i = 0; $i < 7; $i++)
                    <div class="skeleton skeleton-th"></div>
                @endfor
            </div>
            @for($r = 0; $r < 5; $r++)
                <div class="skeleton-row">
                    @for($i = 0; $i < 7; $i++)
                        <div class="skeleton skeleton-cell"></div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
    <div>
        <div class="card mb-4">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:50%;margin:0"></div>
            </div>
            <div class="card-body">
                @for($i = 0; $i < 4; $i++)
                    <div style="display:flex;gap:.875rem;align-items:center;padding:.75rem 0;border-bottom:1px solid var(--gray-300)">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1">
                            <div class="skeleton skeleton-text" style="width:70%"></div>
                            <div class="skeleton skeleton-text-sm" style="width:45%"></div>
                        </div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
