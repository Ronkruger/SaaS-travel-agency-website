@extends('layouts.admin')

@section('title', 'DIY Session #' . $diySession->id)

@section('breadcrumb')
    <a href="{{ route('admin.diy.index') }}">DIY Tours</a> / Session #{{ $diySession->id }}
@endsection

@section('content')
<div class="admin-content">

    {{-- Header --}}
    <div class="content-header">
        <div>
            <h1>{{ $itinerary?->tour_name ?? 'Untitled DIY Tour' }}</h1>
            <small class="text-muted">Session #{{ $diySession->id }} · Token: {{ substr($diySession->session_token, 0, 16) }}…</small>
        </div>
        <div class="header-actions">
            {{-- Approve / Reject --}}
            @if($diySession->admin_status === 'pending')
            <form action="{{ route('admin.diy.approve', $diySession) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success btn-sm">
                    <i class="fas fa-check"></i> Approve
                </button>
            </form>
            <form action="{{ route('admin.diy.reject', $diySession) }}" method="POST" class="d-inline ms-1">
                @csrf
                <button class="btn btn-danger btn-sm" onclick="return confirm('Reject this DIY tour request?')">
                    <i class="fas fa-times"></i> Reject
                </button>
            </form>
            @elseif($diySession->admin_status === 'approved')
            <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle"></i> Approved</span>
            <form action="{{ route('admin.diy.reject', $diySession) }}" method="POST" class="d-inline ms-1">
                @csrf
                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject this DIY tour request?')">Reject</button>
            </form>
            @else
            <span class="badge bg-danger px-3 py-2"><i class="fas fa-times-circle"></i> Rejected</span>
            <form action="{{ route('admin.diy.approve', $diySession) }}" method="POST" class="d-inline ms-1">
                @csrf
                <button class="btn btn-outline-success btn-sm">Re-Approve</button>
            </form>
            @endif

            {{-- Status update --}}
            <form action="{{ route('admin.diy.status', $diySession) }}" method="POST" class="d-inline ms-2">
                @csrf @method('PATCH')
                <select name="status" class="form-control form-control-sm d-inline w-auto" onchange="this.form.submit()">
                    @foreach(['draft','pending_review','quoted','booked'] as $s)
                    <option value="{{ $s }}" {{ $diySession->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="admin-detail-grid">

        {{-- LEFT: Session & User info --}}
        <div class="detail-left">
            <div class="card mb-3">
                <div class="card-header">Customer Info</div>
                <div class="card-body">
                    @if($diySession->user)
                    <p><strong>Name:</strong> {{ $diySession->user->name }}</p>
                    <p><strong>Email:</strong> {{ $diySession->user->email }}</p>
                    <p><strong>Phone:</strong> {{ $diySession->user->phone ?? '—' }}</p>
                    @else
                    <p class="text-muted">Guest user (not logged in)</p>
                    @endif
                    <p><strong>Created:</strong> {{ $diySession->created_at->format('F j, Y H:i') }}</p>
                    <p><strong>Expires:</strong> {{ $diySession->expires_at?->format('F j, Y') ?? 'Never' }}</p>
                </div>
            </div>

            {{-- Preferences --}}
            @php $prefs = $itinerary?->user_preferences ?? []; @endphp
            @if(!empty($prefs))
            <div class="card mb-3">
                <div class="card-header">User Preferences</div>
                <div class="card-body">
                    <p><strong>Duration:</strong> {{ $prefs['duration_days'] ?? '—' }} days</p>
                    <p><strong>Countries:</strong> {{ implode(', ', (array)($prefs['countries'] ?? [])) }}</p>
                    <p><strong>Travel Style:</strong> {{ implode(', ', (array)($prefs['travel_style'] ?? [])) }}</p>
                    <p><strong>Budget:</strong> ₱{{ str_replace('-', ' – ₱', $prefs['budget_range'] ?? '—') }}</p>
                    <p><strong>Must-Visit:</strong> {{ implode(', ', (array)($prefs['must_visit'] ?? [])) ?: '—' }}</p>
                    <p><strong>Pace:</strong> {{ ucfirst($prefs['pace'] ?? '—') }}</p>
                    <p><strong>Group Size:</strong> {{ $prefs['group_size'] ?? '—' }}</p>
                    <p><strong>Month:</strong> {{ $prefs['travel_month'] ?? '—' }}</p>
                </div>
            </div>
            @endif

            {{-- Pricing --}}
            @php $pricing = $itinerary?->pricing_data ?? []; @endphp
            @if(!empty($pricing))
            <div class="card mb-3">
                <div class="card-header">Pricing Breakdown</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Accommodation</td><td>₱{{ number_format($pricing['accommodation'] ?? 0) }}</td></tr>
                        <tr><td>Transportation</td><td>₱{{ number_format($pricing['transportation'] ?? 0) }}</td></tr>
                        <tr><td>Activities</td><td>₱{{ number_format($pricing['activities'] ?? 0) }}</td></tr>
                        <tr><td>Meals</td><td>₱{{ number_format($pricing['meals'] ?? 0) }}</td></tr>
                        <tr><td>Guide Services</td><td>₱{{ number_format($pricing['guide_services'] ?? 0) }}</td></tr>
                        <tr><td>Visa & Insurance</td><td>₱{{ number_format($pricing['visa_insurance'] ?? 0) }}</td></tr>
                        <tr class="fw-bold"><td>Service Fee ({{ $pricing['markup_percent'] ?? 15 }}%)</td><td>₱{{ number_format($pricing['markup'] ?? 0) }}</td></tr>
                        <tr class="fw-bold table-primary"><td>Total per Person</td><td>₱{{ number_format($pricing['total_per_person'] ?? 0) }}</td></tr>
                        <tr class="fw-bold"><td>Total ({{ $pricing['group_size'] ?? 1 }} pax)</td><td>₱{{ number_format($pricing['total_group'] ?? 0) }}</td></tr>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Itinerary + Generate Quote --}}
        <div class="detail-right">

            {{-- Generate Quote form --}}
            <div class="card mb-3">
                <div class="card-header">Generate Official Quote</div>
                <div class="card-body">
                    <form action="{{ route('admin.diy.quote', $diySession) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Price per person (PHP) — leave blank to use calculated price</label>
                            <input type="number" name="price_override" class="form-control" min="1000" step="100"
                                   placeholder="{{ number_format($pricing['total_per_person'] ?? 0) }}">
                        </div>
                        <div class="form-group">
                            <label>Quote valid for (days):</label>
                            <input type="number" name="valid_days" class="form-control" value="7" min="1" max="90">
                        </div>
                        <div class="form-group">
                            <label>Terms & Conditions (optional):</label>
                            <textarea name="terms" class="form-control" rows="3" maxlength="2000" placeholder="e.g. 50% deposit required on acceptance…"></textarea>
                        </div>
                        <button class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i> Generate Quote
                        </button>
                    </form>
                </div>
            </div>

            {{-- Existing quotes --}}
            @if($itinerary?->quotes?->count())
            <div class="card mb-3">
                <div class="card-header">Previous Quotes</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Amount</th><th>Valid Until</th><th>Status</th><th>Generated</th></tr></thead>
                        <tbody>
                            @foreach($itinerary->quotes->sortByDesc('created_at') as $q)
                            <tr>
                                <td>₱{{ number_format($q->quoted_price_php) }}</td>
                                <td>{{ $q->valid_until?->format('M j, Y') ?? '—' }}</td>
                                <td><span class="status-badge status-{{ $q->status }}">{{ ucfirst($q->status) }}</span></td>
                                <td class="text-muted small">{{ $q->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Day-by-day summary --}}
            @php $dayByDay = $itinerary?->itinerary_data['day_by_day'] ?? []; @endphp
            @if(!empty($dayByDay))
            <div class="card mb-3">
                <div class="card-header">Itinerary ({{ count($dayByDay) }} days)</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Day</th><th>City</th><th>Activities</th><th>Overnight</th></tr></thead>
                        <tbody>
                            @foreach($dayByDay as $day)
                            <tr>
                                <td>{{ $day['day'] }}</td>
                                <td><strong>{{ $day['city'] }}</strong>, {{ $day['country'] }}</td>
                                <td>
                                    @foreach($day['activities'] ?? [] as $act)
                                    <span class="badge {{ $act['included'] ? 'bg-success' : 'bg-secondary' }} me-1">
                                        {{ $act['name'] }}
                                    </span>
                                    @endforeach
                                </td>
                                <td class="text-muted">{{ $day['overnight'] ?? $day['city'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Collaborators --}}
            @if($diySession->collaborators->count())
            <div class="card mb-3">
                <div class="card-header">Collaborators</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>User</th><th>Permission</th><th>Invited</th></tr></thead>
                        <tbody>
                            @foreach($diySession->collaborators as $col)
                            <tr>
                                <td>{{ $col->user->name }} <small class="text-muted">({{ $col->user->email }})</small></td>
                                <td>{{ ucfirst($col->permission_level) }}</td>
                                <td class="text-muted small">{{ $col->invited_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
