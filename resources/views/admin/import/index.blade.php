@extends('layouts.admin')
@section('title', 'Import Bookings from CSV')

@push('styles')
<style>
.import-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    border-radius: 12px;
    padding: 2rem;
    color: #fff;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.import-hero-icon {
    font-size: 3rem;
    opacity: .85;
}
.import-hero h2 { margin: 0 0 .25rem; font-size: 1.5rem; }
.import-hero p  { margin: 0; opacity: .85; }

/* Upload card */
.upload-card {
    background: #fff;
    border-radius: 12px;
    border: 2px dashed #cbd5e1;
    padding: 2.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.upload-card:hover, .upload-card.dragging {
    border-color: #2563eb;
    background: #eff6ff;
}
.upload-card i { font-size: 2.5rem; color: #94a3b8; margin-bottom: .75rem; }
.upload-card p { margin: .25rem 0; color: #64748b; }
.upload-card .upload-cta { font-weight: 600; color: #2563eb; }

/* Template download */
.template-tip {
    margin-top: 1rem;
    font-size: .875rem;
    color: #64748b;
}

/* Warnings */
.warning-list {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 8px 8px 0;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.warning-list h5 { margin: 0 0 .5rem; color: #92400e; }
.warning-list li { color: #78350f; font-size: .875rem; margin-bottom: .2rem; }

/* Preview table */
.preview-section { margin-top: 2rem; }
.preview-section h4 { font-size: 1.1rem; margin-bottom: 1rem; }
.preview-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #e2e8f0; }
.preview-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
.preview-table th {
    background: #1e3a8a;
    color: #fff;
    padding: .65rem .75rem;
    text-align: left;
    white-space: nowrap;
}
.preview-table td {
    padding: .6rem .75rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.preview-table tr:last-child td { border-bottom: none; }
.preview-table tr.skipped td { background: #fef2f2; color: #9ca3af; text-decoration: line-through; }
.preview-table tr.has-warn td { background: #fffbeb; }

/* Status badges (reuse existing) */
.badge-pending   { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 99px; font-size: .75rem; font-weight: 600; }
.badge-confirmed { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 99px; font-size: .75rem; font-weight: 600; }
.badge-cancelled { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 99px; font-size: .75rem; font-weight: 600; }

/* Summary bar */
.import-summary {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.import-summary-item { text-align: center; }
.import-summary-item .num { font-size: 1.5rem; font-weight: 700; color: #1e3a8a; }
.import-summary-item .lbl { font-size: .75rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-file-import"></i> Import Bookings</h1>
        <p>Upload a CSV file to bulk-import bookings from your spreadsheet into the system.</p>
    </div>
</div>

{{-- Success flash --}}
@if(session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        @if(session('import_errors'))
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

<div class="import-hero">
    <div class="import-hero-icon"><i class="fas fa-table"></i></div>
    <div>
        <h2>Spreadsheet → Bookings</h2>
        <p>Map your existing spreadsheet columns (Route Name, Travel Date, Client Names, PAX, Status, Payment Terms, Rate, Payments) directly to bookings. Missing tour schedules are auto-created.</p>
    </div>
</div>

{{-- ── Upload Form ─────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-upload"></i> Upload CSV File</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.import.preview') }}" enctype="multipart/form-data" id="upload-form">
            @csrf
            <label for="csv_file" class="upload-card" id="drop-zone">
                <i class="fas fa-cloud-upload-alt d-block"></i>
                <p class="upload-cta">Click to browse or drag & drop your CSV file</p>
                <p>Supported format: <strong>.csv</strong> &nbsp;|&nbsp; Max size: <strong>5 MB</strong></p>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" class="d-none" required>
                <p id="file-name" class="mt-2 text-primary fw-semibold"></p>
            </label>

            @error('csv_file')
                <div class="alert alert-danger mt-2">{{ $message }}</div>
            @enderror

            <div class="d-flex align-items-center gap-3 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Preview Import
                </button>
                <div class="template-tip">
                    Don't have a CSV yet?
                    <a href="{{ route('admin.import.template') }}">
                        <i class="fas fa-download"></i> Download blank template
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Column mapping reference --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fas fa-columns"></i> Slot Tracker CSV Columns</h4>
        <small class="text-muted">Columns are positional (A–J). The importer handles block-grouped routes and repeated header rows automatically.</small>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Col</th>
                    <th>Header</th>
                    <th>Description</th>
                    <th>Example</th>
                    <th>Required?</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>B</td><td><code>Route Name</code></td><td>Tour title (BUS suffix auto-stripped for matching)</td><td>ROUTE K DELUXE</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td>C</td><td><code>Travel Date</code></td><td>Date range — start date is used for the schedule</td><td>FEB 11 - 21, 2026</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td>D</td><td><code>Names of Clients</code></td><td>Primary contact / lead traveler name</td><td>JUAN DELA CRUZ</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td>E</td><td><code>PAX</code></td><td>Number of guests (defaults to 1 if empty)</td><td>2</td><td>No</td></tr>
                <tr><td>F</td><td><code>Status</code></td><td><strong>Paid</strong> → confirmed booking + paid/partial payment status. Others → pending.</td><td>Paid</td><td>No</td></tr>
                <tr><td>G</td><td><code>Payment Terms</code></td><td>Full Cash / Downpayment / Instalment / Travel Fund</td><td>Full Cash</td><td>No</td></tr>
                <tr><td>H</td><td><code>Package Rate Per Person</code></td><td>Price per pax (₱ sign and commas ignored)</td><td>₱180,000.00</td><td>No (uses tour price)</td></tr>
                <tr><td>I</td><td><code>1st Payment Date</code></td><td>Date of first payment (stored as booking note)</td><td>Apr 1, 2026</td><td>No</td></tr>
                <tr><td>J</td><td><code>2nd Payment / Notes</code></td><td>Free-text notes (CONFIRMED DEPARTURE, REFUND, etc.)</td><td>CONFIRMED DEPARTURE</td><td>No</td></tr>
            </tbody>
        </table>
        <div class="px-3 py-2 bg-light border-top" style="font-size:.82rem;color:#64748b;">
            <i class="fas fa-info-circle text-primary"></i>
            <strong>Format supported:</strong> The DiscoverGRP "DG SLOTS TRACKER" spreadsheet format — block-grouped routes with repeated section headers and mixed metadata/client rows are all handled automatically.
            Rows with blank column D (Names of Clients) are treated as route headers or metadata and skipped.
        </div>
    </div>
</div>

{{-- ── Preview Section ──────────────────────────────────────────────────── --}}
@isset($preview)
    @if($warnings)
        <div class="warning-list">
            <h5><i class="fas fa-exclamation-triangle"></i> Warnings ({{ count($warnings) }})</h5>
            <ul class="mb-0 ps-3">
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="preview-section">
        <div class="import-summary">
            <div class="import-summary-item">
                <div class="num">{{ count($preview) }}</div>
                <div class="lbl">Total Rows</div>
            </div>
            <div class="import-summary-item">
                <div class="num text-success">{{ $importable }}</div>
                <div class="lbl">Will Import</div>
            </div>
            <div class="import-summary-item">
                <div class="num text-danger">{{ count($preview) - $importable }}</div>
                <div class="lbl">Will Skip</div>
            </div>
            <div class="import-summary-item">
                <div class="num">{{ collect($preview)->where('skipped', false)->where('booking_status', 'confirmed')->count() }}</div>
                <div class="lbl">Confirmed</div>
            </div>
            <div class="import-summary-item">
                <div class="num">{{ collect($preview)->where('skipped', false)->where('booking_status', 'pending')->count() }}</div>
                <div class="lbl">Pending</div>
            </div>
        </div>

        <h4><i class="fas fa-table"></i> Preview ({{ count($preview) }} rows)</h4>

        <div class="preview-table-wrap">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tour / Route</th>
                        <th>Travel Date</th>
                        <th>Client Name</th>
                        <th>PAX</th>
                        <th>Booking</th>
                        <th>Payment</th>
                        <th>Terms</th>
                        <th>Rate/Person</th>
                        <th>Total</th>
                        <th>1st Pmt Date</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $row)
                        <tr class="{{ $row['skipped'] ? 'skipped' : (count($row['warnings']) ? 'has-warn' : '') }}">
                            <td>{{ $row['row'] }}</td>
                            <td>
                                @if($row['skipped'] && !$row['tour_id'])
                                    <span class="text-danger"><i class="fas fa-exclamation-circle"></i> {{ $row['tour_name'] }}</span>
                                @else
                                    {{ $row['tour_name'] }}
                                @endif
                            </td>
                            <td>
                                @if(!$row['travel_date'])
                                    <span class="text-danger">Invalid</span>
                                @else
                                    {{ \Carbon\Carbon::parse($row['travel_date'])->format('M d, Y') }}
                                    <br><small class="text-muted" style="font-size:.72rem;">{{ $row['travel_date_raw'] }}</small>
                                @endif
                            </td>
                            <td>{{ $row['client_name'] }}</td>
                            <td>{{ $row['pax'] }}</td>
                            <td><span class="badge-{{ $row['booking_status'] }}">{{ ucfirst($row['booking_status']) }}</span></td>
                            <td>
                                @php
                                    $ps = $row['payment_status'];
                                    $psColor = match($ps) { 'paid' => 'success', 'partial' => 'warning', default => 'secondary' };
                                @endphp
                                <span class="badge bg-{{ $psColor }} text-{{ $ps === 'partial' ? 'dark' : 'white' }}" style="font-size:.7rem;">{{ ucfirst($ps) }}</span>
                            </td>
                            <td>{{ ucfirst($row['terms']) }}</td>
                            <td>{{ $row['rate'] > 0 ? '₱' . number_format($row['rate'], 0) : '—' }}</td>
                            <td>{{ $row['total_amount'] > 0 ? '₱' . number_format($row['total_amount'], 0) : '—' }}</td>
                            <td>{{ $row['pay1_date'] ?: '—' }}</td>
                            <td style="max-width:160px;white-space:normal;">{{ Str::limit($row['notes'] ?? '', 50) }}</td>
                            <td>
                                @if($row['skipped'])
                                    <i class="fas fa-times-circle text-danger" title="{{ implode(' | ', $row['warnings']) }}"></i>
                                @elseif(count($row['warnings']))
                                    <i class="fas fa-exclamation-triangle text-warning" title="{{ implode(' | ', $row['warnings']) }}"></i>
                                @else
                                    <i class="fas fa-check-circle text-success"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($importable > 0)
            <div class="mt-4 d-flex gap-3 align-items-center">
                <form method="POST" action="{{ route('admin.import.confirm') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg"
                            onclick="return confirm('Import {{ $importable }} booking(s) into the database?')">
                        <i class="fas fa-database"></i>
                        Confirm &amp; Import {{ $importable }} Booking{{ $importable !== 1 ? 's' : '' }}
                    </button>
                </form>
                <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i> Start Over
                </a>
            </div>
        @else
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                No importable rows found. Please fix the warnings above and re-upload.
            </div>
        @endif
    </div>
@endisset

@endsection

@push('scripts')
<script>
// File input label update
document.getElementById('csv_file').addEventListener('change', function () {
    const name = this.files[0]?.name ?? '';
    document.getElementById('file-name').textContent = name ? '📄 ' + name : '';
});

// Drag & drop
const zone = document.getElementById('drop-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragging'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragging'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragging');
    const dt = e.dataTransfer;
    if (dt.files.length) {
        const input = document.getElementById('csv_file');
        input.files = dt.files;
        document.getElementById('file-name').textContent = '📄 ' + dt.files[0].name;
    }
});
</script>
@endpush
