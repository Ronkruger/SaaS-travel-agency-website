{{-- Skeleton: Detail page (bookings/show, users/show, diy/show) --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem">
    <div class="skeleton skeleton-title" style="width:30%"></div>
    <div style="display:flex;gap:.75rem">
        <div class="skeleton skeleton-btn"></div>
        <div class="skeleton skeleton-btn" style="width:6rem"></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
    <div>
        {{-- Main info card --}}
        <div class="card mb-4">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:35%;margin:0"></div>
            </div>
            <div class="card-body">
                @for($i = 0; $i < 6; $i++)
                    <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid #f1f5f9">
                        <div class="skeleton skeleton-text" style="width:25%"></div>
                        <div class="skeleton skeleton-text" style="width:40%"></div>
                    </div>
                @endfor
            </div>
        </div>
        {{-- Second card --}}
        <div class="card">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:28%;margin:0"></div>
            </div>
            <div class="card-body">
                @for($i = 0; $i < 4; $i++)
                    <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid #f1f5f9">
                        <div class="skeleton skeleton-text" style="width:30%"></div>
                        <div class="skeleton skeleton-text" style="width:35%"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header" style="border-bottom:1px solid var(--gray-300)">
                <div class="skeleton skeleton-text-lg" style="width:50%;margin:0"></div>
            </div>
            <div class="card-body">
                <div class="skeleton skeleton-badge" style="margin-bottom:.75rem"></div>
                <div class="skeleton skeleton-text" style="width:80%"></div>
                <div class="skeleton skeleton-text" style="width:65%"></div>
                <div class="skeleton skeleton-text" style="width:70%"></div>
            </div>
        </div>
    </div>
</div>
