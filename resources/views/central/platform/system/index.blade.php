@extends('central.platform.layouts.admin')
@section('title', 'System Health')
@section('page-title', 'System Health')

@section('topbar-actions')
<button class="btn btn-primary btn-sm" id="recheck-btn" onclick="recheckAll()">
    <i class="fas fa-sync-alt" id="recheck-icon"></i> Re-check All
</button>
@endsection

@section('content')
{{-- Summary Stats --}}
<div class="stats-grid" id="summary-stats">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-server"></i></div>
        <div>
            <div class="stat-num" id="stat-total">{{ $summary['total'] }}</div>
            <div class="stat-label">Total Services</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-num" id="stat-connected">{{ $summary['connected'] }}</div>
            <div class="stat-label">Connected</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div>
            <div class="stat-num" id="stat-errors">{{ $summary['errors'] }}</div>
            <div class="stat-label">Errors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#6b7280,#9ca3af)"><i class="fas fa-minus-circle"></i></div>
        <div>
            <div class="stat-num" id="stat-nc">{{ $summary['notConfigured'] }}</div>
            <div class="stat-label">Not Configured</div>
        </div>
    </div>
</div>

<div style="font-size:.78rem;color:var(--text-muted);margin-bottom:1.2rem">
    <i class="fas fa-clock" style="margin-right:4px"></i> Last checked: <span id="last-checked">{{ now()->format('M d, Y h:i:s A') }}</span>
</div>

{{-- Service Cards Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.2rem" id="services-grid">
    @foreach($checks as $key => $svc)
    <div class="card service-card" data-key="{{ $key }}" id="svc-{{ $key }}">
        <div style="padding:1.3rem 1.5rem">
            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;color:#fff;
                    @if($svc['status'] === 'connected') background:linear-gradient(135deg,#059669,#10b981)
                    @elseif($svc['status'] === 'error') background:linear-gradient(135deg,#dc2626,#ef4444)
                    @else background:linear-gradient(135deg,#6b7280,#9ca3af)
                    @endif
                ">
                    <i class="{{ $svc['icon'] }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:700;font-size:.95rem">{{ $svc['name'] }}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px" data-field="message">{{ $svc['message'] }}</div>
                </div>
                <div data-field="badge">
                    @if($svc['status'] === 'connected')
                        <span class="badge badge-success">Connected</span>
                    @elseif($svc['status'] === 'error')
                        <span class="badge badge-danger">Error</span>
                    @else
                        <span class="badge badge-muted">Not Configured</span>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            @if(!empty($svc['details']))
            <div style="border-top:1px solid var(--border);padding-top:12px">
                @foreach($svc['details'] as $label => $value)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;font-size:.82rem">
                    <span style="color:var(--text-muted);font-weight:500">{{ $label }}</span>
                    <span style="font-weight:600;color:var(--text);max-width:60%;text-align:right;word-break:break-all" data-detail="{{ $label }}">{{ $value }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Status Bar --}}
        <div style="height:4px;
            @if($svc['status'] === 'connected') background:linear-gradient(90deg,#059669,#10b981)
            @elseif($svc['status'] === 'error') background:linear-gradient(90deg,#dc2626,#ef4444)
            @else background:linear-gradient(90deg,#9ca3af,#d1d5db)
            @endif
        " data-field="bar"></div>
    </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
.service-card { transition: all .3s ease; overflow: hidden; }
.service-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(10,45,116,.12); }
.service-card.checking { opacity: .6; }
.service-card.checking::after {
    content: '';
    position: absolute; inset: 0;
    background: repeating-linear-gradient(90deg, transparent, transparent 40%, rgba(255,255,255,.4) 50%, transparent 60%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 1s linear infinite; }
</style>
@endpush

@push('scripts')
<script>
function recheckAll() {
    var btn = document.getElementById('recheck-btn');
    var icon = document.getElementById('recheck-icon');
    btn.disabled = true;
    icon.className = 'fas fa-sync-alt spin';

    document.querySelectorAll('.service-card').forEach(function(c) {
        c.classList.add('checking');
        c.style.position = 'relative';
    });

    fetch('{{ route("platform.system.check") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        // Update summary stats
        document.getElementById('stat-total').textContent = data.summary.total;
        document.getElementById('stat-connected').textContent = data.summary.connected;
        document.getElementById('stat-errors').textContent = data.summary.errors;
        document.getElementById('stat-nc').textContent = data.summary.notConfigured;

        // Update each service card
        for (var key in data.checks) {
            var svc = data.checks[key];
            var card = document.getElementById('svc-' + key);
            if (!card) continue;

            // Update icon bg
            var iconDiv = card.querySelector('[style*="width:44px"]');
            if (iconDiv) {
                if (svc.status === 'connected') iconDiv.style.background = 'linear-gradient(135deg,#059669,#10b981)';
                else if (svc.status === 'error') iconDiv.style.background = 'linear-gradient(135deg,#dc2626,#ef4444)';
                else iconDiv.style.background = 'linear-gradient(135deg,#6b7280,#9ca3af)';
            }

            // Update message
            var msgEl = card.querySelector('[data-field="message"]');
            if (msgEl) msgEl.textContent = svc.message;

            // Update badge
            var badgeEl = card.querySelector('[data-field="badge"]');
            if (badgeEl) {
                if (svc.status === 'connected') badgeEl.innerHTML = '<span class="badge badge-success">Connected</span>';
                else if (svc.status === 'error') badgeEl.innerHTML = '<span class="badge badge-danger">Error</span>';
                else badgeEl.innerHTML = '<span class="badge badge-muted">Not Configured</span>';
            }

            // Update status bar
            var barEl = card.querySelector('[data-field="bar"]');
            if (barEl) {
                if (svc.status === 'connected') barEl.style.background = 'linear-gradient(90deg,#059669,#10b981)';
                else if (svc.status === 'error') barEl.style.background = 'linear-gradient(90deg,#dc2626,#ef4444)';
                else barEl.style.background = 'linear-gradient(90deg,#9ca3af,#d1d5db)';
            }

            // Update details
            if (svc.details) {
                for (var label in svc.details) {
                    var detailEl = card.querySelector('[data-detail="' + label + '"]');
                    if (detailEl) detailEl.textContent = svc.details[label];
                }
            }

            card.classList.remove('checking');
        }

        // Update timestamp
        document.getElementById('last-checked').textContent = new Date().toLocaleString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    })
    .catch(function(err) {
        alert('Re-check failed: ' + err.message);
    })
    .finally(function() {
        btn.disabled = false;
        icon.className = 'fas fa-sync-alt';
        document.querySelectorAll('.service-card').forEach(function(c) { c.classList.remove('checking'); });
    });
}
</script>
@endpush
