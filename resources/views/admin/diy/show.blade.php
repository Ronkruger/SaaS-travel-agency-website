@extends('layouts.admin')

@section('title', 'DIY Session #' . $diySession->id)

@section('skeleton')
    @include('admin.partials.skeleton-detail')
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.diy.index') }}">DIY Tours</a> / Session #{{ $diySession->id }}
@endsection

@section('content')
<div class="admin-content">

    {{-- Page Header --}}
    <div class="page-title-row">
        <div>
            <h1 class="page-title">{{ $itinerary?->tour_name ?? 'Untitled DIY Tour' }}</h1>
            <p class="text-muted text-sm" style="margin-top:.25rem">
                Session #{{ $diySession->id }}
                &nbsp;·&nbsp;Token: <code style="font-size:.8rem;background:var(--gray-100);padding:.1rem .4rem;border-radius:.25rem">{{ substr($diySession->session_token, 0, 16) }}…</code>
            </p>
        </div>
        <div class="action-btns" style="flex-wrap:wrap;gap:.5rem">
            {{-- Approve / Reject --}}
            @if($diySession->admin_status === 'pending')
                <form action="{{ route('admin.diy.approve', $diySession) }}" method="POST" style="display:inline">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                </form>
                <form action="{{ route('admin.diy.reject', $diySession) }}" method="POST" style="display:inline">
                    @csrf
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Reject this DIY tour request?')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
            @elseif($diySession->admin_status === 'approved')
                <span class="status-badge status-confirmed" style="font-size:.9rem;padding:.35rem .875rem">
                    <i class="fas fa-check-circle"></i> Approved
                </span>
                <form action="{{ route('admin.diy.reject', $diySession) }}" method="POST" style="display:inline">
                    @csrf
                    <button class="btn btn-ghost btn-sm" onclick="return confirm('Reject this DIY tour request?')">Reject</button>
                </form>
            @else
                <span class="status-badge status-cancelled" style="font-size:.9rem;padding:.35rem .875rem">
                    <i class="fas fa-times-circle"></i> Rejected
                </span>
                <form action="{{ route('admin.diy.approve', $diySession) }}" method="POST" style="display:inline">
                    @csrf
                    <button class="btn btn-success btn-sm">Re-Approve</button>
                </form>
            @endif

            {{-- Status dropdown --}}
            <form action="{{ route('admin.diy.status', $diySession) }}" method="POST" style="display:inline">
                @csrf @method('PATCH')
                <select name="status" class="form-control" style="display:inline;width:auto;padding:.4rem .875rem;font-size:.875rem" onchange="this.form.submit()">
                    @foreach(['draft','pending_review','quoted','booked'] as $s)
                    <option value="{{ $s }}" {{ $diySession->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="admin-detail-grid" id="diy-layout">

        {{-- LEFT: Itinerary + Quotes + Collaborators --}}
        <div>

            {{-- Generate Quote --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-file-invoice" style="color:var(--primary);margin-right:.5rem"></i> Generate Official Quote</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.diy.quote', $diySession) }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label>Price per person (PHP)</label>
                                <input type="number" name="price_override" class="form-control" min="1000" step="100"
                                    placeholder="{{ number_format($pricing['total_per_person'] ?? 0) }} (calculated)">
                                <small>Leave blank to use the AI-calculated price.</small>
                            </div>
                            <div class="form-group">
                                <label>Quote valid for (days)</label>
                                <input type="number" name="valid_days" class="form-control" value="7" min="1" max="90">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Terms &amp; Conditions <span class="text-muted">(optional)</span></label>
                            <textarea name="terms" class="form-control" rows="3" maxlength="2000" placeholder="e.g. 50% deposit required on acceptance…"></textarea>
                        </div>
                        <button class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i> Generate Quote
                        </button>
                    </form>
                </div>
            </div>

            {{-- Previous Quotes --}}
            @if($itinerary?->quotes?->count())
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-history" style="color:var(--primary);margin-right:.5rem"></i> Previous Quotes</h4>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itinerary->quotes->sortByDesc('created_at') as $q)
                            <tr>
                                <td><strong>₱{{ number_format($q->quoted_price_php) }}</strong></td>
                                <td>{{ $q->valid_until?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <span class="status-badge {{ $q->isExpired() ? 'status-cancelled' : 'status-confirmed' }}">
                                        {{ $q->isExpired() ? 'Expired' : ucfirst($q->status) }}
                                    </span>
                                </td>
                                <td class="text-muted text-sm">{{ $q->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Day-by-day Itinerary --}}
            @php $dayByDay = $itinerary?->itinerary_data['day_by_day'] ?? []; @endphp
            @if(!empty($dayByDay))
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-route" style="color:var(--primary);margin-right:.5rem"></i> Itinerary &mdash; {{ count($dayByDay) }} Days</h4>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:60px">Day</th>
                                <th>City</th>
                                <th>Activities</th>
                                <th>Overnight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayByDay as $day)
                            <tr>
                                <td>
                                    <span class="status-badge status-completed" style="justify-content:center;width:2rem;padding:.25rem 0">
                                        {{ $day['day'] }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $day['city'] }}</strong>
                                    <span class="text-muted text-sm">, {{ $day['country'] }}</span>
                                </td>
                                <td style="line-height:1.8">
                                    @foreach($day['activities'] ?? [] as $act)
                                        @if($act['included'])
                                            <span class="badge badge-success" style="margin:.1rem .2rem .1rem 0">{{ $act['name'] }}</span>
                                        @else
                                            <span class="badge" style="background:var(--gray-200);color:var(--gray-600);margin:.1rem .2rem .1rem 0">{{ $act['name'] }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="text-muted text-sm">{{ $day['overnight'] ?? $day['city'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Collaborators --}}
            @if($diySession->collaborators->count())
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users" style="color:var(--primary);margin-right:.5rem"></i> Collaborators</h4>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Permission</th>
                                <th>Invited</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diySession->collaborators as $col)
                            <tr>
                                <td>
                                    <strong>{{ $col->user->name }}</strong>
                                    <span class="text-muted text-sm"> — {{ $col->user->email }}</span>
                                </td>
                                <td>{{ ucfirst($col->permission_level) }}</td>
                                <td class="text-muted text-sm">{{ $col->invited_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT: Customer Info + Preferences + Pricing --}}
        <div>

            {{-- Customer Info --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-user" style="color:var(--primary);margin-right:.5rem"></i> Customer Info</h4>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                    @if($diySession->user)
                        <div>
                            <div class="text-muted text-sm" style="margin-bottom:.2rem">Name</div>
                            <strong>{{ $diySession->user->name }}</strong>
                        </div>
                        <div>
                            <div class="text-muted text-sm" style="margin-bottom:.2rem">Email</div>
                            <a href="mailto:{{ $diySession->user->email }}" style="color:var(--primary)">{{ $diySession->user->email }}</a>
                        </div>
                        <div>
                            <div class="text-muted text-sm" style="margin-bottom:.2rem">Phone</div>
                            {{ $diySession->user->phone ?? '—' }}
                        </div>
                    @else
                        <p class="text-muted">Guest user (not logged in)</p>
                    @endif
                    <div style="border-top:1px solid var(--gray-300);padding-top:.875rem;display:flex;justify-content:space-between">
                        <div>
                            <div class="text-muted text-sm">Created</div>
                            <span>{{ $diySession->created_at->format('M j, Y') }}</span>
                        </div>
                        <div style="text-align:right">
                            <div class="text-muted text-sm">Expires</div>
                            <span>{{ $diySession->expires_at?->format('M j, Y') ?? 'Never' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User Preferences --}}
            @php $prefs = $itinerary?->user_preferences ?? []; @endphp
            @if(!empty($prefs))
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-sliders-h" style="color:var(--primary);margin-right:.5rem"></i> Preferences</h4>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
                    @php
                        $prefRows = [
                            ['Duration',     $prefs['duration_days'] ? $prefs['duration_days'].' days' : '—'],
                            ['Countries',    implode(', ', (array)($prefs['countries'] ?? [])) ?: '—'],
                            ['Travel Style', implode(', ', (array)($prefs['travel_style'] ?? [])) ?: '—'],
                            ['Budget',       '₱'.str_replace('-', ' – ₱', $prefs['budget_range'] ?? '—')],
                            ['Must-Visit',   implode(', ', (array)($prefs['must_visit'] ?? [])) ?: '—'],
                            ['Pace',         ucfirst($prefs['pace'] ?? '—')],
                            ['Group Size',   $prefs['group_size'] ?? '—'],
                            ['Travel Month', $prefs['travel_month'] ?? '—'],
                        ];
                    @endphp
                    @foreach($prefRows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:baseline;padding:.375rem 0;border-bottom:1px solid var(--gray-100)">
                        <span class="text-muted text-sm" style="white-space:nowrap">{{ $label }}</span>
                        <span style="font-weight:600;text-align:right;font-size:.9rem">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Pricing Breakdown --}}
            @php $pricing = $itinerary?->pricing_data ?? []; @endphp
            @if(!empty($pricing))
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-calculator" style="color:var(--primary);margin-right:.5rem"></i> Pricing Breakdown</h4>
                </div>
                <div class="card-body" style="padding:0">
                    <table class="data-table" style="font-size:.875rem">
                        <tbody>
                            @php
                                $lineItems = [
                                    ['Accommodation',   $pricing['accommodation']  ?? 0],
                                    ['Transportation',  $pricing['transportation'] ?? 0],
                                    ['Activities',      $pricing['activities']     ?? 0],
                                    ['Meals',           $pricing['meals']          ?? 0],
                                    ['Guide Services',  $pricing['guide_services'] ?? 0],
                                    ['Visa & Insurance',$pricing['visa_insurance'] ?? 0],
                                ];
                            @endphp
                            @foreach($lineItems as [$label, $amount])
                            <tr>
                                <td style="color:var(--gray-600)">{{ $label }}</td>
                                <td style="text-align:right;font-weight:600">₱{{ number_format($amount) }}</td>
                            </tr>
                            @endforeach
                            <tr style="background:var(--gray-100)">
                                <td style="color:var(--gray-600)">Service Fee ({{ $pricing['markup_percent'] ?? 15 }}%)</td>
                                <td style="text-align:right;font-weight:600">₱{{ number_format($pricing['markup'] ?? 0) }}</td>
                            </tr>
                            <tr style="background:var(--primary-light)">
                                <td style="font-weight:700;color:var(--primary-dark)">Per Person</td>
                                <td style="text-align:right;font-weight:800;color:var(--primary);font-size:1rem">₱{{ number_format($pricing['total_per_person'] ?? 0) }}</td>
                            </tr>
                            <tr style="background:var(--primary-light)">
                                <td style="font-weight:600;color:var(--primary-dark)">Total ({{ $pricing['group_size'] ?? 1 }} pax)</td>
                                <td style="text-align:right;font-weight:700;color:var(--primary)">₱{{ number_format($pricing['total_group'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>

@endsection
