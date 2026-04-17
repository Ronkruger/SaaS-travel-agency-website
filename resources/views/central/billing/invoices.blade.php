@extends('central.layouts.app')
@section('title', 'Invoices')

@push('styles')
<style>
.page { max-width: 900px; margin: 0 auto; padding: 3rem 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 1.8rem; font-weight: 800; color: #0A2D74; }
.card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: .9rem; }
th { padding: .75rem 1.2rem; text-align: left; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; border-bottom: 2px solid #e5e7eb; background: #f9fafb; }
td { padding: .85rem 1.2rem; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
.badge { display: inline-block; padding: .2rem .6rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.btn-dl { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .8rem; border: 2px solid #0A2D74; border-radius: 8px; color: #0A2D74; font-size: .8rem; font-weight: 600; transition: all .2s; }
.btn-dl:hover { background: #0A2D74; color: #fff; }
.empty { text-align: center; color: #6b7280; padding: 3rem; }
</style>
@endpush

@section('content')
<div class="page">
    <div class="page-header">
        <h1>Billing History</h1>
        <a href="{{ route('central.billing.index') }}" style="color:#6b7280;font-size:.9rem">← Back to Billing</a>
    </div>

    <div class="card">
        @if(count($invoices) > 0)
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $invoice->number ?? 'INV-' . strtoupper(substr($invoice->id, -8)) }}</div>
                        <div style="font-size:.78rem;color:#6b7280">{{ $invoice->id }}</div>
                    </td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('M d, Y') }}</td>
                    <td style="font-weight:700">${{ number_format($invoice->amount_paid / 100, 2) }}</td>
                    <td>
                        <span class="badge {{ $invoice->paid ? 'badge-success' : 'badge-warning' }}">
                            {{ $invoice->paid ? 'Paid' : 'Pending' }}
                        </span>
                    </td>
                    <td style="font-size:.85rem;color:#6b7280">
                        @if($invoice->period_start && $invoice->period_end)
                            {{ \Carbon\Carbon::createFromTimestamp($invoice->period_start)->format('M d') }}
                            – {{ \Carbon\Carbon::createFromTimestamp($invoice->period_end)->format('M d, Y') }}
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($invoice->hosted_invoice_url)
                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="btn-dl">
                            <i class="fas fa-download"></i> PDF
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty">
            <i class="fas fa-file-invoice" style="font-size:2rem;margin-bottom:.8rem;display:block"></i>
            No invoices yet. Your billing history will appear here once you subscribe to a paid plan.
        </div>
        @endif
    </div>
</div>
@endsection
