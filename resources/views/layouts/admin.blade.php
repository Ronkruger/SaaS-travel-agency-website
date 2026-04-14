<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — DiscoverGRP Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    {{-- NProgress slim progress bar --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <style>
        #nprogress .bar { background: var(--primary, #0e7490) !important; height: 3px; }
        #nprogress .peg  { box-shadow: 0 0 10px var(--primary, #0e7490), 0 0 5px var(--primary, #0e7490); }
        #nprogress .spinner-icon { border-top-color: var(--primary, #0e7490); border-left-color: var(--primary, #0e7490); }
    </style>
    @if($brandFaviconUrl ?? false)
        <link rel="icon" href="{{ $brandFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    @stack('styles')
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                @if($brandLogoUrl ?? false)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName ?? 'DiscoverGRP' }}" style="max-height:32px;width:auto;filter:brightness(0) invert(1)">
                @else
                    <i class="fas fa-compass"></i>
                    <span>{{ $brandName ?? 'DiscoverGRP' }}</span>
                @endif
            </a>
            <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <div class="nav-section">TOURS</div>
            <a href="{{ route('admin.tours.index') }}" class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt"></i> All Tours
            </a>
            <a href="{{ route('admin.tours.create') }}" class="{{ request()->routeIs('admin.tours.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> Add Tour
            </a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Categories
            </a>
            <div class="nav-section">BOOKINGS</div>
            <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> All Bookings
                @php $pendingBookings = \App\Models\Booking::where('status', 'pending')->count(); @endphp
                @if($pendingBookings > 0)
                    <span class="badge">{{ $pendingBookings }}</span>
                @endif
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                <i class="fas fa-tag"></i> Coupons
            </a>
            <a href="{{ route('admin.slot-tracker.index') }}" class="{{ request()->routeIs('admin.slot-tracker.*') || request()->routeIs('admin.tours.schedules.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> Slot Tracker
            </a>
            <a href="{{ route('admin.calendar.index') }}" class="{{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Availability Calendar
            </a>
            <a href="{{ route('admin.import.index') }}" class="{{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                <i class="fas fa-file-import"></i> Import
            </a>
            <a href="{{ route('admin.deletion-requests.index') }}" class="{{ request()->routeIs('admin.deletion-requests.*') ? 'active' : '' }}">
                <i class="fas fa-hand-paper"></i> Deletion Requests
                @php $pendingDeletions = \App\Models\DeletionRequest::where('status','pending')->count(); @endphp
                @if($pendingDeletions > 0)
                    <span class="badge">{{ $pendingDeletions }}</span>
                @endif
            </a>
            <a href="{{ route('admin.diy.index') }}" class="{{ request()->routeIs('admin.diy.*') ? 'active' : '' }}">
                <i class="fas fa-magic"></i> DIY Tours
                @php $pendingDiy = \App\Models\DIYTourSession::where('status','pending_review')->count(); @endphp
                @if($pendingDiy > 0)
                    <span class="badge">{{ $pendingDiy }}</span>
                @endif
            </a>
            <div class="nav-section">CUSTOMERS</div>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i> Reviews
                @php $pendingReviews = \App\Models\Review::where('is_approved', false)->count(); @endphp
                @if($pendingReviews > 0)
                    <span class="badge">{{ $pendingReviews }}</span>
                @endif
            </a>
            <div class="nav-section">REPORTS</div>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Monthly Report
            </a>
            <a href="{{ route('admin.activity-log.index') }}" class="{{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i> Activity Log
            </a>
            <a href="{{ route('admin.email-log.index') }}" class="{{ request()->routeIs('admin.email-log.*') ? 'active' : '' }}">
                <i class="fas fa-envelope-open-text"></i> Email Log
            </a>
            <div class="nav-section">ACCOUNT</div>
            <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i> Staff &amp; Permissions
            </a>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-palette"></i> Branding
            </a>
            <a href="{{ route('home') }}" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-breadcrumb" style="flex:1">
                @yield('breadcrumb')
            </div>
            <!-- Notification Bell -->
            <div id="notif-wrap" style="position:relative;margin-left:auto">
                <button id="notif-btn" onclick="toggleNotifDropdown()" style="background:none;border:none;cursor:pointer;position:relative;padding:6px 8px;color:var(--gray-600);font-size:1.15rem" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span id="notif-badge" style="display:none;position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;font-size:.6rem;font-weight:700;border-radius:99px;padding:1px 4px;min-width:16px;text-align:center;line-height:1.4"></span>
                </button>
                <div id="notif-dropdown" style="display:none;position:absolute;right:0;top:calc(100% + 6px);width:360px;background:#fff;border:1px solid var(--gray-200);border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.12);z-index:9999;overflow:hidden;flex-direction:column;max-height:460px">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:1px solid var(--gray-100);flex-shrink:0">
                        <strong style="font-size:.875rem">Notifications</strong>
                        <div style="display:flex;gap:.75rem;align-items:center">
                            <button onclick="markAllNotifRead()" style="background:none;border:none;cursor:pointer;font-size:.75rem;color:var(--primary);padding:0">Mark all read</button>
                            <button onclick="clearAllNotifs()" style="background:none;border:none;cursor:pointer;font-size:.75rem;color:#ef4444;padding:0">Clear all</button>
                        </div>
                    </div>
                    <div id="notif-list" style="overflow-y:auto;flex:1;padding:.25rem 0">
                        <div style="padding:1.25rem;text-align:center;color:var(--gray-400);font-size:.8rem">Loading…</div>
                    </div>
                </div>
            </div>
            <div class="header-user">
                <div style="text-align:right">
                    <div style="font-weight:700;font-size:.9375rem">{{ auth()->user()->name }}</div>
                    @if(auth()->user()->is_onboarded)
                    <div style="font-size:.75rem;color:var(--gray-500);margin-top:.1rem">
                        {{ auth()->user()->department_label }} &mdash; {{ auth()->user()->position }}
                    </div>
                    @endif
                </div>
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--gray-300)">
                @else
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning" style="background:#fefce8;border:1px solid #fde047;color:#854d0e">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif
            @if(session('error') || $errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    @if($errors->any())
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- Page skeleton (shown while content loads) --}}
            @hasSection('skeleton')
                <div class="page-skeleton" id="pageSkeleton">
                    @yield('skeleton')
                </div>
            @endif

            <div class="@hasSection('skeleton') page-content @endif" id="pageContent">
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/admin.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
<script>
// NProgress configuration — show on all navigations & form submissions
NProgress.configure({ showSpinner: false, minimum: 0.1, speed: 280 });

(function () {
    // Start on navigation (links that go to a new page)
    // Skip if the event was already prevented (AJAX link handlers call e.preventDefault first)
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        var a = e.target.closest('a[href]');
        if (!a) return;
        var href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
        NProgress.start();
    });

    // Start on form submission only for real page-navigation submits
    // (AJAX forms call e.preventDefault(), which sets e.defaultPrevented before bubbling here)
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        NProgress.start();
    });

    // Finish when page fully loads — only if NProgress was actually started
    function safeDone() {
        if (NProgress.status !== null) NProgress.done();
        else NProgress.remove(); // ensure bar is hidden on fresh loads
    }

    window.addEventListener('pageshow', safeDone);

    if (document.readyState === 'complete') {
        safeDone();
    } else {
        window.addEventListener('load', safeDone);
    }
})();
</script>
<script>
// Skeleton → real content reveal
(function() {
    var sk = document.getElementById('pageSkeleton');
    var pc = document.getElementById('pageContent');
    if (sk && pc) {
        // Show real content and hide skeleton once DOM is ready
        function reveal() {
            sk.classList.add('loaded');
            pc.classList.add('loaded');
            setTimeout(function() { sk.style.display = 'none'; }, 300);
        }
        if (document.readyState === 'complete') { reveal(); }
        else { window.addEventListener('load', reveal); }
    }
})();
</script>
@stack('scripts')
<script>
// ── Notification Bell ─────────────────────────────────────────────────────────
(function () {
    const POLL_INTERVAL = 30000; // 30 s
    let dropdownOpen = false;

    function fetchNotifications() {
        fetch('{{ route('admin.notifications.unread') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            const list  = document.getElementById('notif-list');
            if (!badge || !list) return;

            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }

            if (data.notifications.length === 0) {
                list.innerHTML = '<div style="padding:1.25rem;text-align:center;color:var(--gray-400);font-size:.8rem">No new notifications</div>';
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                const ago = timeAgo(n.created_at);
                const href = n.url || '#';
                return `<a href="${href}" onclick="markNotifRead(event, ${n.id}, '${href}')"
                    style="display:block;padding:.65rem 1rem;border-bottom:1px solid var(--gray-100);text-decoration:none;color:inherit;transition:background .15s"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
                    data-id="${n.id}">
                    <div style="font-size:.8rem;font-weight:600;color:var(--gray-800);margin-bottom:.15rem">${escHtml(n.title)}</div>
                    <div style="font-size:.75rem;color:var(--gray-500);margin-bottom:.25rem;line-height:1.4">${escHtml(n.body)}</div>
                    <div style="font-size:.7rem;color:var(--gray-400)">${ago}</div>
                </a>`;
            }).join('');
        })
        .catch(() => {});
    }

    window.toggleNotifDropdown = function () {
        const dd = document.getElementById('notif-dropdown');
        if (!dd) return;
        dropdownOpen = !dropdownOpen;
        dd.style.display = dropdownOpen ? 'flex' : 'none';
        if (dropdownOpen) fetchNotifications();
    };

    window.markNotifRead = function (e, id, href) {
        e.preventDefault();
        fetch(`{{ url('admin/notifications') }}/${id}/read`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        }).finally(() => {
            if (href && href !== '#') window.location.href = href;
            else fetchNotifications();
        });
    };

    window.markAllNotifRead = function () {
        fetch('{{ route('admin.notifications.read-all') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => fetchNotifications());
    };

    window.clearAllNotifs = function () {
        if (!confirm('Clear all notifications?')) return;
        fetch('{{ route('admin.notifications.clear') }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => fetchNotifications());
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('notif-wrap');
        if (wrap && !wrap.contains(e.target) && dropdownOpen) {
            dropdownOpen = false;
            const dd = document.getElementById('notif-dropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)  return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Initial fetch + polling
    fetchNotifications();
    setInterval(fetchNotifications, POLL_INTERVAL);
})();
</script>
</body>
</html>
