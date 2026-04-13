{{-- Skeleton: Form page (create/edit tours, edit users, settings) --}}
<div style="margin-bottom:1.75rem">
    <div class="skeleton skeleton-title" style="width:35%"></div>
    <div class="skeleton skeleton-subtitle" style="width:25%"></div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:2rem;align-items:start">
    <div>
        <div class="card mb-4">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:30%;margin:0"></div>
            </div>
            <div class="card-body">
                @for($i = 0; $i < 4; $i++)
                    <div style="margin-bottom:1.25rem">
                        <div class="skeleton skeleton-text-sm" style="width:20%;margin-bottom:.5rem"></div>
                        <div class="skeleton" style="height:2.75rem;width:100%"></div>
                    </div>
                @endfor
            </div>
        </div>
        <div class="card">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:25%;margin:0"></div>
            </div>
            <div class="card-body">
                @for($i = 0; $i < 3; $i++)
                    <div style="margin-bottom:1.25rem">
                        <div class="skeleton skeleton-text-sm" style="width:25%;margin-bottom:.5rem"></div>
                        <div class="skeleton" style="height:2.75rem;width:100%"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <div>
        <div class="card mb-4">
            <div class="card-body" style="text-align:center">
                <div class="skeleton" style="width:100%;height:160px;border-radius:var(--radius-lg);margin-bottom:1rem"></div>
                <div class="skeleton skeleton-text" style="width:60%;margin:0 auto"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @for($i = 0; $i < 3; $i++)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-300)">
                        <div>
                            <div class="skeleton skeleton-text" style="width:8rem"></div>
                            <div class="skeleton skeleton-text-sm" style="width:10rem"></div>
                        </div>
                        <div class="skeleton" style="width:3.25rem;height:1.75rem;border-radius:50px"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
