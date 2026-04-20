@extends('layouts.admin')
@section('title', 'Payment Setup')

@section('breadcrumb')
    <span>Settings</span> / <span>Payment</span>
@endsection

@section('skeleton')
    @include('admin.partials.skeleton-form')
@endsection

@section('content')
<div class="page-title">
    <h2>Payment Setup</h2>
    <p>Configure your Xendit payment gateway to accept online payments from your customers.</p>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:.9rem">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
</div>
@endif

@if(session('gateway_success'))
<div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:.9rem">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('gateway_success') }}
</div>
@endif

{{-- Xendit Setup Guide --}}
<div class="card mb-4" id="setup-guide">
    <div class="card-header" style="cursor:pointer" onclick="toggleGuide()">
        <h3><i class="fas fa-book-open" style="color:var(--primary);margin-right:6px"></i> Xendit Setup Guide</h3>
        <button type="button" class="btn btn-sm btn-outline" id="guide-toggle-btn">
            <i class="fas fa-chevron-down" id="guide-chevron"></i>
            <span id="guide-toggle-text">Show Guide</span>
        </button>
    </div>
    <div class="card-body" id="guide-content" style="display:none">
        <p style="color:var(--gray-600);font-size:.88rem;margin-bottom:20px">
            Follow these steps to connect your Xendit payment gateway and start accepting online payments from your customers.
        </p>

        <div style="display:flex;flex-direction:column;gap:16px">
            {{-- Step 1 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">1</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Create a Xendit Account</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Go to <a href="https://dashboard.xendit.co/register" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600">dashboard.xendit.co/register</a> and sign up for a free account. Complete the verification process with your business documents.</p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">2</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Complete Business Verification</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Submit your business details and documents in the Xendit dashboard. Verification typically takes 1-2 business days. You can use <strong>Test Mode</strong> while waiting.</p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">3</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Get Your Secret API Key</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Navigate to <a href="https://dashboard.xendit.co/settings/developers#api-keys" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600">Settings → Developers → API Keys</a>. Copy your <strong>Secret API Key</strong> (starts with <code>xnd_production_</code> or <code>xnd_development_</code>).</p>
                </div>
            </div>

            {{-- Step 4 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">4</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Set Up Webhooks</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Go to <a href="https://dashboard.xendit.co/settings/developers#webhooks" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600">Settings → Developers → Webhooks</a>. Add the webhook URL shown below on this page. Copy the <strong>Verification Token</strong> from the same page.</p>
                </div>
            </div>

            {{-- Step 5 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">5</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Enter Your Keys Below</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Paste your <strong>Secret API Key</strong> and <strong>Webhook Verification Token</strong> in the fields below and click <em>Save Payment Settings</em>.</p>
                </div>
            </div>

            {{-- Step 6 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">6</div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Choose Payment Methods</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Select the payment methods you want to accept (Credit Card, GCash, GrabPay, etc.). Make sure these are also <strong>activated in your Xendit dashboard</strong> under Payment Methods.</p>
                </div>
            </div>

            {{-- Step 7 --}}
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div style="width:32px;height:32px;background:#10b981;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0"><i class="fas fa-check" style="font-size:.75rem"></i></div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:4px">Test & Go Live</div>
                    <p style="font-size:.84rem;color:var(--gray-600);margin:0">Use a test key first to verify payments work, then switch to your live production key when ready. The connection status indicator below will confirm your setup.</p>
                </div>
            </div>
        </div>

        <div style="margin-top:20px;padding:14px 18px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;font-size:.84rem;color:#0c4a6e">
            <i class="fas fa-info-circle" style="margin-right:6px"></i>
            <strong>Need help?</strong> Visit the <a href="https://docs.xendit.co/" target="_blank" rel="noopener" style="color:#0c4a6e;font-weight:600;text-decoration:underline">Xendit Documentation</a> or contact our support team for assistance.
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.payment.update') }}" method="POST">
    @csrf @method('PUT')

    {{-- Xendit API Keys --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-key"></i> Xendit API Keys</h3>
        </div>
        <div class="card-body">
            <div style="background:#fef3c7;border:1px solid #fcd34d;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.85rem;color:#92400e">
                <i class="fas fa-exclamation-triangle" style="margin-right:6px"></i>
                <strong>Important:</strong> Keep your API keys secure. Never share them publicly. You can find your keys in the
                <a href="https://dashboard.xendit.co/settings/developers#api-keys" target="_blank" rel="noopener" style="color:#92400e;text-decoration:underline;font-weight:600">Xendit Dashboard → Settings → API Keys</a>.
            </div>

            <div class="form-group" style="margin-bottom:18px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Secret API Key</label>
                <div style="position:relative">
                    <input type="password" name="xendit_secret_key" id="xendit_secret_key"
                           value="{{ old('xendit_secret_key', $xenditSecretKey) }}"
                           class="form-control" placeholder="xnd_production_..."
                           style="padding-right:44px;font-family:monospace;font-size:.85rem"
                           autocomplete="off">
                    <button type="button" onclick="toggleKeyVisibility('xendit_secret_key', this)"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400);padding:6px"
                            title="Show/hide key">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small style="color:var(--gray-500);margin-top:4px;display:block">Starts with <code>xnd_production_</code> or <code>xnd_development_</code></small>
                @error('xendit_secret_key')<small style="color:var(--danger)">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Webhook Verification Token</label>
                <div style="position:relative">
                    <input type="password" name="xendit_webhook_token" id="xendit_webhook_token"
                           value="{{ old('xendit_webhook_token', $xenditWebhookToken) }}"
                           class="form-control" placeholder="Enter your webhook verification token"
                           style="padding-right:44px;font-family:monospace;font-size:.85rem"
                           autocomplete="off">
                    <button type="button" onclick="toggleKeyVisibility('xendit_webhook_token', this)"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400);padding:6px"
                            title="Show/hide token">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small style="color:var(--gray-500);margin-top:4px;display:block">Found in Xendit Dashboard → Settings → Webhooks → Verification Token</small>
                @error('xendit_webhook_token')<small style="color:var(--danger)">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    {{-- Webhook URL --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-link"></i> Webhook URL</h3>
        </div>
        <div class="card-body">
            <p style="color:var(--gray-600);font-size:.85rem;margin:0 0 12px">
                Copy this URL and paste it in your <a href="https://dashboard.xendit.co/settings/developers#webhooks" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600">Xendit Dashboard → Webhooks</a> settings.
            </p>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="text" readonly value="{{ route('xendit.webhook') }}" id="webhook-url"
                       class="form-control" style="font-family:monospace;font-size:.82rem;background:var(--gray-50);flex:1">
                <button type="button" onclick="copyWebhookUrl()" class="btn btn-outline" style="white-space:nowrap;padding:8px 16px">
                    <i class="fas fa-copy" style="margin-right:4px"></i> Copy
                </button>
            </div>
            <small id="copy-feedback" style="color:var(--success);display:none;margin-top:6px"><i class="fas fa-check"></i> Copied!</small>
        </div>
    </div>

    {{-- Accepted Payment Methods --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-credit-card"></i> Accepted Payment Methods</h3>
        </div>
        <div class="card-body">
            <p style="color:var(--gray-600);font-size:.85rem;margin:0 0 16px">
                Select which payment methods your customers can use at checkout. If none are selected, all methods will be available.
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
                @foreach([
                    'CREDIT_CARD' => ['label' => 'Credit / Debit Card', 'icon' => 'fas fa-credit-card'],
                    'GCASH'       => ['label' => 'GCash', 'icon' => 'fas fa-mobile-alt'],
                    'GRABPAY'     => ['label' => 'GrabPay', 'icon' => 'fas fa-wallet'],
                    'PAYMAYA'     => ['label' => 'PayMaya / Maya', 'icon' => 'fas fa-wallet'],
                    'BPI'         => ['label' => 'BPI Online', 'icon' => 'fas fa-university'],
                    'BDO'         => ['label' => 'BDO Online', 'icon' => 'fas fa-university'],
                ] as $method => $meta)
                <label style="display:flex;align-items:center;gap:10px;padding:12px 16px;border:1px solid var(--gray-200);border-radius:10px;cursor:pointer;transition:all .2s;{{ in_array($method, $paymentMethods) ? 'background:var(--primary-light,#eff6ff);border-color:var(--primary)' : 'background:#fff' }}">
                    <input type="checkbox" name="payment_methods[]" value="{{ $method }}"
                           {{ in_array($method, $paymentMethods) ? 'checked' : '' }}
                           style="width:18px;height:18px;accent-color:var(--primary)"
                           onchange="this.closest('label').style.background=this.checked?'var(--primary-light,#eff6ff)':'#fff';this.closest('label').style.borderColor=this.checked?'var(--primary)':'var(--gray-200)'">
                    <i class="{{ $meta['icon'] }}" style="color:var(--primary);font-size:1rem;width:20px;text-align:center"></i>
                    <span style="font-size:.85rem;font-weight:500">{{ $meta['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Connection Status --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-plug"></i> Connection Status</h3>
        </div>
        <div class="card-body">
            @if(!empty($xenditSecretKey))
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:10px">
                <div style="width:12px;height:12px;background:#10b981;border-radius:50%;flex-shrink:0"></div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;color:#065f46">Connected</div>
                    <div style="font-size:.8rem;color:#047857">API key is configured. Your payment gateway is ready to accept payments.</div>
                </div>
            </div>
            @else
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;background:#fef3c7;border:1px solid #fcd34d;border-radius:10px">
                <div style="width:12px;height:12px;background:#f59e0b;border-radius:50%;flex-shrink:0"></div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;color:#92400e">Not Connected</div>
                    <div style="font-size:.8rem;color:#a16207">Enter your Xendit API key above to enable online payments.</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:12px">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save" style="margin-right:6px"></i>Save Payment Settings</button>
    </div>
</form>

{{-- Request Another Payment Gateway --}}
<div class="card mb-4" style="margin-top:32px">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:6px"></i> Request Another Payment Gateway</h3>
    </div>
    <div class="card-body">
        <p style="color:var(--gray-600);font-size:.85rem;margin:0 0 16px">
            Need a different payment gateway? Submit a request and our platform team will review and help set it up for your agency.
        </p>

        <form action="{{ route('admin.settings.payment.request-gateway') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Payment Gateway</label>
                <select name="gateway_name" required class="form-control" style="font-size:.88rem">
                    <option value="">— Select a gateway —</option>
                    <option value="Stripe">Stripe</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Razorpay">Razorpay</option>
                    <option value="Paystack">Paystack</option>
                    <option value="Square">Square</option>
                    <option value="Mollie">Mollie</option>
                    <option value="Flutterwave">Flutterwave</option>
                    <option value="Other">Other (specify below)</option>
                </select>
                @error('gateway_name')<small style="color:var(--danger)">{{ $message }}</small>@enderror
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label style="display:block;font-weight:600;font-size:.85rem;margin-bottom:6px">Message / Details <small style="color:var(--gray-400)">(optional)</small></label>
                <textarea name="message" rows="3" class="form-control" placeholder="Tell us why you need this gateway, any specific features, or your expected transaction volume..." style="font-size:.85rem;resize:vertical">{{ old('message') }}</textarea>
                @error('message')<small style="color:var(--danger)">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane" style="margin-right:6px"></i> Submit Request
            </button>
        </form>
    </div>
</div>

{{-- Previous Gateway Requests --}}
@if(isset($gatewayRequests) && $gatewayRequests->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-history" style="color:var(--primary);margin-right:6px"></i> Your Gateway Requests</h3>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.88rem">
            <thead>
                <tr>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-500);border-bottom:2px solid var(--gray-200);background:var(--gray-50)">Gateway</th>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-500);border-bottom:2px solid var(--gray-200);background:var(--gray-50)">Message</th>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-500);border-bottom:2px solid var(--gray-200);background:var(--gray-50)">Status</th>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-500);border-bottom:2px solid var(--gray-200);background:var(--gray-50)">Admin Notes</th>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-500);border-bottom:2px solid var(--gray-200);background:var(--gray-50)">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gatewayRequests as $req)
                <tr>
                    <td style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-200);font-weight:600">{{ $req->gateway_name }}</td>
                    <td style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-200);color:var(--gray-600);font-size:.84rem;max-width:250px">{{ Str::limit($req->message, 80) ?: '—' }}</td>
                    <td style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-200)">
                        @php
                            $statusColors = ['pending' => 'background:#fef3c7;color:#92400e', 'approved' => 'background:#d1fae5;color:#065f46', 'rejected' => 'background:#fee2e2;color:#991b1b', 'in_progress' => 'background:#dbeafe;color:#1e40af'];
                        @endphp
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.76rem;font-weight:600;{{ $statusColors[$req->status] ?? $statusColors['pending'] }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </td>
                    <td style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-200);color:var(--gray-600);font-size:.84rem;max-width:200px">{{ $req->admin_notes ?: '—' }}</td>
                    <td style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-200);color:var(--gray-500);font-size:.82rem;white-space:nowrap">{{ $req->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function toggleKeyVisibility(id, btn) {
    var input = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
function copyWebhookUrl() {
    var el = document.getElementById('webhook-url');
    el.select();
    document.execCommand('copy');
    var fb = document.getElementById('copy-feedback');
    fb.style.display = 'block';
    setTimeout(function() { fb.style.display = 'none'; }, 2000);
}
function toggleGuide() {
    var content = document.getElementById('guide-content');
    var chevron = document.getElementById('guide-chevron');
    var text = document.getElementById('guide-toggle-text');
    var visible = content.style.display !== 'none';
    content.style.display = visible ? 'none' : 'block';
    chevron.className = visible ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
    text.textContent = visible ? 'Show Guide' : 'Hide Guide';
    localStorage.setItem('xendit_guide_visible', visible ? '0' : '1');
}
// Restore guide state from localStorage
(function() {
    if (localStorage.getItem('xendit_guide_visible') === '1') {
        document.getElementById('guide-content').style.display = 'block';
        document.getElementById('guide-chevron').className = 'fas fa-chevron-up';
        document.getElementById('guide-toggle-text').textContent = 'Hide Guide';
    }
})();
</script>
@endpush
