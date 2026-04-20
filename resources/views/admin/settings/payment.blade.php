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
</script>
@endpush
