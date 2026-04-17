@extends('layouts.admin')
@section('title', 'Import Subscriptions')

@section('skeleton')
    @include('admin.partials.skeleton-import')
@endsection

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
.import-hero-icon { font-size: 3rem; opacity: .85; }
.import-hero h2 { margin: 0 0 .25rem; font-size: 1.5rem; }
.import-hero p  { margin: 0; opacity: .85; }

/* Upload card */
.upload-card {
    display: block;
    background: #fff;
    border-radius: 12px;
    border: 2px dashed #cbd5e1;
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.upload-card:hover, .upload-card.dragging {
    border-color: #2563eb;
    background: #eff6ff;
}
.upload-card .upload-icon {
    display: block;
    font-size: 3rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}
.upload-card p { margin: .4rem 0; color: #64748b; font-size: .9rem; }
.upload-card .upload-cta { font-weight: 600; color: #2563eb; font-size: 1rem; }
.upload-card .file-input-hidden { position: absolute; width: 0; height: 0; opacity: 0; overflow: hidden; }
.upload-card .file-name-display {
    margin-top: .75rem;
    font-weight: 600;
    color: #2563eb;
    font-size: .95rem;
}

/* Upload actions row */
.upload-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.25rem;
    flex-wrap: wrap;
}
.template-tip { font-size: .875rem; color: #64748b; }
.template-tip a { color: #2563eb; text-decoration: none; font-weight: 500; }
.template-tip a:hover { text-decoration: underline; }

/* Warnings */
.warning-list {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 8px 8px 0;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.warning-list h5 { margin: 0 0 .5rem; color: #92400e; }
.warning-list ul { margin: 0; padding-left: 1.25rem; }
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

/* Status badges */
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

/* Column ref table */
.col-ref-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.col-ref-table thead th {
    background: #f1f5f9;
    padding: .75rem 1rem;
    text-align: left;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}
.col-ref-table td {
    padding: .6rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
.col-ref-table tbody tr:last-child td { border-bottom: none; }
.col-ref-table tbody tr:hover { background: #f8fafc; }
.col-ref-footer {
    padding: .75rem 1rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: .8rem;
    color: #64748b;
}
</style>
@endpush

@section('content')
<div class="page-title-row">
    <div>
        <h2>Import Subscriptions</h2>
        <p>Upload a spreadsheet to bulk-import bookings into the system.</p>
    </div>
</div>

{{-- Success flash --}}
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        @if(session('import_errors'))
            <ul style="margin:.5rem 0 0;padding-left:1.25rem;">
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
        <h2>Spreadsheet → Subscriptions</h2>
        <p>Map your existing spreadsheet columns (Route Name, Travel Date, Client Names, PAX, Status, Payment Terms, Rate, Payments) directly to bookings. Missing tour schedules are auto-created.</p>
    </div>
</div>

{{-- ── Upload Form ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-upload"></i> Upload Spreadsheet</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.import.preview') }}" enctype="multipart/form-data" id="upload-form">
            @csrf
            <label for="csv_file" class="upload-card" id="drop-zone">
                <span class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                <p class="upload-cta">Click to browse or drag &amp; drop your spreadsheet file</p>
                <p>Supported formats: <strong>.xlsx, .xls, .csv</strong> &nbsp;|&nbsp; Max size: <strong>10 MB</strong></p>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="file-input-hidden" required>
                <span id="file-name" class="file-name-display"></span>
            </label>

            @error('csv_file')
                <div class="alert alert-danger mt-2">{{ $message }}</div>
            @enderror

            {{-- Upload progress bar --}}
            <div class="upload-progress-wrap" id="upload-progress">
                <div class="upload-progress-bar">
                    <div class="upload-progress-fill" id="upload-progress-fill"></div>
                </div>
                <div class="upload-progress-text">
                    <span id="upload-progress-label">Uploading file…</span>
                    <span class="pct" id="upload-progress-pct">0%</span>
                </div>
            </div>

            <div class="upload-actions" id="upload-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Preview Import
                </button>
                <span class="template-tip">
                    Don't have a CSV yet?
                    <a href="{{ route('admin.import.template') }}">
                        <i class="fas fa-download"></i> Download blank template
                    </a>
                </span>
            </div>
        </form>
    </div>
</div>

{{-- Column mapping reference --}}
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-columns"></i> Slot Tracker Columns</h4>
        <small style="color:#64748b;">Positional (A–J) · Block-grouped routes handled automatically</small>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="col-ref-table">
            <thead>
                <tr>
                    <th>Col</th>
                    <th>Header</th>
                    <th>Description</th>
                    <th>Example</th>
                    <th>Required?</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><strong>B</strong></td><td><code>Route Name</code></td><td>Tour title (BUS suffix auto-stripped for matching)</td><td>ROUTE K DELUXE</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td><strong>C</strong></td><td><code>Travel Date</code></td><td>Date range — start date is used for the schedule</td><td>FEB 11 - 21, 2026</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td><strong>D</strong></td><td><code>Names of Clients</code></td><td>Primary contact / lead traveler name</td><td>JUAN DELA CRUZ</td><td><span class="text-danger">Yes</span></td></tr>
                <tr><td><strong>E</strong></td><td><code>PAX</code></td><td>Number of guests (defaults to 1 if empty)</td><td>2</td><td>No</td></tr>
                <tr><td><strong>F</strong></td><td><code>Status</code></td><td><strong>Paid</strong> → confirmed + paid/partial. Others → pending.</td><td>Paid</td><td>No</td></tr>
                <tr><td><strong>G</strong></td><td><code>Payment Terms</code></td><td>Full Cash / Downpayment / Instalment / Travel Fund / FOC</td><td>Full Cash</td><td>No</td></tr>
                <tr><td><strong>H</strong></td><td><code>Rate Per Person</code></td><td>Price per pax (₱ sign and commas ignored)</td><td>₱180,000.00</td><td>No (uses tour price)</td></tr>
                <tr><td><strong>I</strong></td><td><code>1st Payment Date</code></td><td>Date of first payment (stored as booking note)</td><td>Apr 1, 2026</td><td>No</td></tr>
                <tr><td><strong>J</strong></td><td><code>2nd Payment / Notes</code></td><td>Free-text notes (CONFIRMED DEPARTURE, REFUND, etc.)</td><td>CONFIRMED DEPARTURE</td><td>No</td></tr>
            </tbody>
        </table>
        <div class="col-ref-footer">
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
            <ul>
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
                            <td>
                                {{ $row['client_name'] }}
                                @if(!empty($row['rebooked_from']))
                                    <br><small style="background:#e0e7ff;color:#3730a3;padding:1px 6px;border-radius:99px;font-size:.68rem;font-weight:600;">Rebooked from {{ $row['rebooked_from'] }}</small>
                                @endif
                                @if(!empty($row['is_foc']))
                                    <br><small style="background:#fce7f3;color:#9d174d;padding:1px 6px;border-radius:99px;font-size:.68rem;font-weight:600;">FOC</small>
                                @endif
                            </td>
                            <td>{{ $row['pax'] }}</td>
                            <td><span class="badge-{{ $row['booking_status'] }}">{{ ucfirst($row['booking_status']) }}</span></td>
                            <td>
                                @php
                                    $ps = $row['payment_status'];
                                    $psColors = match($ps) { 'paid' => ['#d1fae5','#065f46'], 'partial' => ['#fef3c7','#92400e'], default => ['#f1f5f9','#475569'] };
                                @endphp
                                <span style="background:{{ $psColors[0] }};color:{{ $psColors[1] }};padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600;">{{ ucfirst($ps) }}</span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $row['terms'])) }}</td>
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
            <div class="upload-actions" style="margin-top:1.5rem;">
                <form method="POST" action="{{ route('admin.import.confirm') }}" id="confirm-form">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="confirm-btn" style="background:#059669;border-color:#059669;">
                        <i class="fas fa-database"></i>
                        Confirm &amp; Import {{ $importable }} Subscription{{ $importable !== 1 ? 's' : '' }}
                    </button>
                </form>
                <a href="{{ route('admin.import.index') }}" class="btn btn-outline">
                    <i class="fas fa-redo"></i> Start Over
                </a>
            </div>
            {{-- Import confirm progress --}}
            <div class="upload-progress-wrap" id="confirm-progress">
                <div class="upload-progress-bar">
                    <div class="upload-progress-fill processing" id="confirm-progress-fill" style="width:100%"></div>
                </div>
                <div class="upload-progress-text">
                    <span id="confirm-progress-label">Importing subscriptions into database…</span>
                    <span class="pct">Please wait</span>
                </div>
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

// Upload with progress
document.getElementById('upload-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const fileInput = document.getElementById('csv_file');
    if (!fileInput.files.length) return;

    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();

    const progressWrap = document.getElementById('upload-progress');
    const progressFill = document.getElementById('upload-progress-fill');
    const progressPct  = document.getElementById('upload-progress-pct');
    const progressLabel = document.getElementById('upload-progress-label');
    const submitBtn = form.querySelector('button[type="submit"]');
    const actionsRow = document.getElementById('upload-actions');

    progressWrap.classList.add('active');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';

    xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progressFill.style.width = pct + '%';
            progressPct.textContent = pct + '%';
            if (pct >= 100) {
                progressLabel.textContent = 'Processing spreadsheet…';
                progressFill.classList.add('processing');
            } else {
                const sizeMB = (e.loaded / 1024 / 1024).toFixed(1);
                const totalMB = (e.total / 1024 / 1024).toFixed(1);
                progressLabel.textContent = 'Uploading ' + sizeMB + ' MB / ' + totalMB + ' MB';
            }
        }
    });

    xhr.addEventListener('load', function () {
        // Cleanly end NProgress before replacing the page to avoid back-and-forth glitch
        if (typeof NProgress !== 'undefined') { NProgress.done(); NProgress.remove(); }
        // Replace the entire page with the response (preview page or error page)
        document.open();
        document.write(xhr.responseText);
        document.close();
    });

    xhr.addEventListener('error', function () {
        progressWrap.classList.remove('active');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-search"></i> Preview Import';
        alert('Upload failed. Please check your connection and try again.');
    });

    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});

// Confirm import with progress
const confirmForm = document.getElementById('confirm-form');
if (confirmForm) {
    confirmForm.addEventListener('submit', function (e) {
        if (!confirm('Import subscriptions into the database?')) {
            e.preventDefault();
            return;
        }
        const btn = document.getElementById('confirm-btn');
        const progressWrap = document.getElementById('confirm-progress');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing…';
        progressWrap.classList.add('active');
    });
}
</script>
@endpush
