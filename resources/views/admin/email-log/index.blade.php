@extends('layouts.admin')
@section('title', 'Email Log')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Email Log
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-table', ['showAction' => false, 'filterCount' => 4, 'cols' => 6, 'rows' => 12])
@endsection

@section('content')
<div class="page-title-row">
    <div>
        <h2>Email Log</h2>
        <p>All outgoing emails sent to clients</p>
    </div>
    <div style="font-size:.85rem;color:#6b7280">
        <i class="fas fa-circle" style="color:#22c55e;font-size:.6rem"></i>
        Auto-captured — no setup required
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.email-log.index') }}" method="GET" class="filter-row">
            <input type="text" name="email" value="{{ request('email') }}"
                placeholder="Client email..." class="form-control">
            <input type="text" name="booking" value="{{ request('booking') }}"
                placeholder="Booking # ..." class="form-control">
            <select name="type" class="form-control">
                <option value="">All Email Types</option>
                @foreach([
                    'BookingConfirmationMail'  => 'Booking Confirmation',
                    'BookingReservationMail'   => 'Booking Reservation',
                    'BookingRejectionMail'     => 'Booking Rejection / Cancellation',
                    'PaymentFollowupMail'      => 'Payment Follow-up Reminder',
                    'DIYTourQuoteMail'         => 'DIY Tour Quote',
                    'OtpMail'                  => 'OTP / Password Reset',
                ] as $val => $label)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.email-log.index') }}" class="btn btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Booking</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space:nowrap;font-size:.82rem;color:#6b7280">
                            {{ $log->created_at->format('M d, Y') }}<br>
                            <span style="font-size:.75rem">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <strong style="font-size:.875rem">{{ $log->to_email }}</strong>
                            @if($log->to_name)
                                <br><small class="text-muted">{{ $log->to_name }}</small>
                            @endif
                        </td>
                        <td style="max-width:240px;font-size:.85rem">{{ Str::limit($log->subject, 60) }}</td>
                        <td>
                            @php
                                $typeColors = [
                                    'BookingConfirmationMail' => ['bg'=>'#dcfce7','color'=>'#166534'],
                                    'BookingReservationMail'  => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                    'BookingRejectionMail'    => ['bg'=>'#fee2e2','color'=>'#991b1b'],
                                    'PaymentFollowupMail'     => ['bg'=>'#fef3c7','color'=>'#92400e'],
                                    'DIYTourQuoteMail'        => ['bg'=>'#ede9fe','color'=>'#5b21b6'],
                                    'OtpMail'                 => ['bg'=>'#f1f5f9','color'=>'#475569'],
                                ];
                                $typeLabels = [
                                    'BookingConfirmationMail' => 'Confirmed',
                                    'BookingReservationMail'  => 'Reservation',
                                    'BookingRejectionMail'    => 'Rejection',
                                    'PaymentFollowupMail'     => 'Pay Reminder',
                                    'DIYTourQuoteMail'        => 'DIY Quote',
                                    'OtpMail'                 => 'OTP',
                                ];
                                $c = $typeColors[$log->mail_class] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
                                $lbl = $typeLabels[$log->mail_class] ?? ($log->mail_class ?? '—');
                            @endphp
                            <span style="background:{{ $c['bg'] }};color:{{ $c['color'] }};padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">
                                {{ $lbl }}
                            </span>
                        </td>
                        <td>
                            @if($log->booking_number)
                                @if($log->booking_id)
                                    <a href="{{ route('admin.bookings.show', $log->booking_id) }}" style="font-size:.82rem">
                                        <code>{{ $log->booking_number }}</code>
                                    </a>
                                @else
                                    <code style="font-size:.82rem">{{ $log->booking_number }}</code>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span style="background:#dcfce7;color:#166534;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600">
                                    <i class="fas fa-check"></i> Sent
                                </span>
                            @else
                                <span style="background:#fee2e2;color:#991b1b;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:600"
                                      title="{{ $log->error_message }}">
                                    <i class="fas fa-times"></i> Failed
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No emails logged yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
