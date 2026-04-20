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

{{-- Interactive Xendit Setup Guide --}}
<div class="card mb-4" id="setup-guide">
    <div class="card-header" style="cursor:pointer" onclick="toggleGuide()">
        <h3><i class="fas fa-book-open" style="color:var(--primary);margin-right:6px"></i> Xendit Setup Guide</h3>
        <div style="display:flex;align-items:center;gap:12px">
            <span id="guide-progress-label" style="font-size:.78rem;color:var(--gray-500);font-weight:600">0 / 7 completed</span>
            <button type="button" class="btn btn-sm btn-outline" id="guide-toggle-btn">
                <i class="fas fa-chevron-down" id="guide-chevron"></i>
                <span id="guide-toggle-text">Show Guide</span>
            </button>
        </div>
    </div>
    <div id="guide-content" style="display:none">
        {{-- Progress Bar --}}
        <div style="padding:16px 24px 0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-size:.82rem;font-weight:600;color:var(--gray-600)">Setup Progress</span>
                <span id="guide-pct" style="font-size:.82rem;font-weight:700;color:var(--primary)">0%</span>
            </div>
            <div style="height:8px;background:var(--gray-100,#f3f4f6);border-radius:99px;overflow:hidden">
                <div id="guide-bar" style="height:100%;background:linear-gradient(90deg,var(--primary),#10b981);border-radius:99px;width:0%;transition:width .5s ease"></div>
            </div>
        </div>

        <div style="padding:20px 24px 24px;display:flex;flex-direction:column;gap:4px" id="guide-steps">

            {{-- Step 1 --}}
            <div class="guide-step" data-step="1">
                <div class="guide-step-header" onclick="toggleStep(1)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(1)">
                        <input type="checkbox" id="step-check-1" class="guide-checkbox">
                        <label for="step-check-1" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">1</div>
                    <div class="guide-step-title">Create a Xendit Account</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Go to <a href="https://dashboard.xendit.co/register" target="_blank" rel="noopener">dashboard.xendit.co/register</a> and sign up for a free account.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-clock"></i> Takes about 5 minutes</div>
                        <div class="guide-detail"><i class="fas fa-file-alt"></i> You'll need: Business email, phone number</div>
                    </div>
                    <div class="guide-step-actions">
                        <a href="https://dashboard.xendit.co/register" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open Xendit Signup
                        </a>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(1); openNextStep(2)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="guide-step" data-step="2">
                <div class="guide-step-header" onclick="toggleStep(2)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(2)">
                        <input type="checkbox" id="step-check-2" class="guide-checkbox">
                        <label for="step-check-2" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">2</div>
                    <div class="guide-step-title">Complete Business Verification</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Submit your business details and documents in the Xendit dashboard. You can use <strong>Test Mode</strong> while waiting for verification.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-clock"></i> Verification: 1–2 business days</div>
                        <div class="guide-detail"><i class="fas fa-file-alt"></i> You'll need: Business registration, valid ID, bank account details</div>
                        <div class="guide-detail"><i class="fas fa-lightbulb" style="color:#f59e0b"></i> <strong>Tip:</strong> You can skip this and use test keys first</div>
                    </div>
                    <div class="guide-step-actions">
                        <a href="https://dashboard.xendit.co/settings/account" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open Verification Page
                        </a>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(2); openNextStep(3)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="guide-step" data-step="3">
                <div class="guide-step-header" onclick="toggleStep(3)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(3)">
                        <input type="checkbox" id="step-check-3" class="guide-checkbox">
                        <label for="step-check-3" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">3</div>
                    <div class="guide-step-title">Get Your Secret API Key</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Navigate to <strong>Settings → Developers → API Keys</strong> in your Xendit dashboard. Copy the <strong>Secret API Key</strong>.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-key"></i> Starts with <code>xnd_production_</code> or <code>xnd_development_</code></div>
                        <div class="guide-detail"><i class="fas fa-shield-alt" style="color:#dc2626"></i> <strong>Security:</strong> Never share this key publicly</div>
                    </div>
                    <div class="guide-step-actions">
                        <a href="https://dashboard.xendit.co/settings/developers#api-keys" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open API Keys Page
                        </a>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(3); openNextStep(4)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="guide-step" data-step="4">
                <div class="guide-step-header" onclick="toggleStep(4)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(4)">
                        <input type="checkbox" id="step-check-4" class="guide-checkbox">
                        <label for="step-check-4" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">4</div>
                    <div class="guide-step-title">Set Up Webhooks</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>In Xendit, go to <strong>Settings → Developers → Webhooks</strong>. Add the webhook URL below and copy the <strong>Verification Token</strong>.</p>
                    <div style="background:var(--gray-50,#f9fafb);border:1px solid var(--gray-200,#e5e7eb);border-radius:8px;padding:10px 14px;margin:10px 0;display:flex;align-items:center;gap:8px">
                        <code style="font-size:.82rem;flex:1;word-break:break-all">{{ route('xendit.webhook') }}</code>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ route('xendit.webhook') }}'); this.innerHTML='<i class=\'fas fa-check\' style=\'color:#10b981\'></i>'; setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',2000)" style="background:none;border:none;cursor:pointer;padding:4px;color:var(--gray-400)">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-link"></i> Paste this URL in Xendit's webhook settings</div>
                        <div class="guide-detail"><i class="fas fa-lock"></i> Copy the Verification Token from the same page</div>
                    </div>
                    <div class="guide-step-actions">
                        <a href="https://dashboard.xendit.co/settings/developers#webhooks" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open Webhooks Page
                        </a>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(4); openNextStep(5)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="guide-step" data-step="5">
                <div class="guide-step-header" onclick="toggleStep(5)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(5)">
                        <input type="checkbox" id="step-check-5" class="guide-checkbox">
                        <label for="step-check-5" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">5</div>
                    <div class="guide-step-title">Enter Your Keys Below</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Scroll down to the <strong>Xendit API Keys</strong> section on this page and paste your Secret API Key and Webhook Verification Token.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-arrow-down"></i> The form is right below this guide</div>
                        <div class="guide-detail"><i class="fas fa-save"></i> Don't forget to click <strong>Save Payment Settings</strong></div>
                    </div>
                    <div class="guide-step-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('xendit_secret_key').focus(); document.getElementById('xendit_secret_key').scrollIntoView({behavior:'smooth', block:'center'})">
                            <i class="fas fa-arrow-down"></i> Jump to API Keys
                        </button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(5); openNextStep(6)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 6 --}}
            <div class="guide-step" data-step="6">
                <div class="guide-step-header" onclick="toggleStep(6)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(6)">
                        <input type="checkbox" id="step-check-6" class="guide-checkbox">
                        <label for="step-check-6" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">6</div>
                    <div class="guide-step-title">Choose Payment Methods</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Select which payment methods to accept (Credit Card, GCash, GrabPay, etc.). Make sure these are also <strong>activated in your Xendit dashboard</strong>.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-credit-card"></i> Enable methods in both Xendit AND this page</div>
                        <div class="guide-detail"><i class="fas fa-lightbulb" style="color:#f59e0b"></i> <strong>Tip:</strong> Leave all unchecked to accept all available methods</div>
                    </div>
                    <div class="guide-step-actions">
                        <a href="https://dashboard.xendit.co/settings/payment-methods" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt"></i> Xendit Payment Methods
                        </a>
                        <button type="button" class="btn btn-sm btn-outline" onclick="markStep(6); openNextStep(7)">
                            <i class="fas fa-check"></i> Done — Next Step
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 7 --}}
            <div class="guide-step" data-step="7">
                <div class="guide-step-header" onclick="toggleStep(7)">
                    <div class="guide-step-check" onclick="event.stopPropagation(); markStep(7)">
                        <input type="checkbox" id="step-check-7" class="guide-checkbox">
                        <label for="step-check-7" class="guide-check-label"></label>
                    </div>
                    <div class="guide-step-num">🚀</div>
                    <div class="guide-step-title">Test & Go Live</div>
                    <i class="fas fa-chevron-right guide-step-arrow"></i>
                </div>
                <div class="guide-step-body">
                    <p>Use a <strong>test/development key</strong> first to verify payments work correctly, then switch to your live production key when ready.</p>
                    <div class="guide-step-details">
                        <div class="guide-detail"><i class="fas fa-vial"></i> Test key: <code>xnd_development_...</code></div>
                        <div class="guide-detail"><i class="fas fa-rocket"></i> Live key: <code>xnd_production_...</code></div>
                        <div class="guide-detail"><i class="fas fa-check-circle" style="color:#10b981"></i> The <strong>Connection Status</strong> card below will turn green when connected</div>
                    </div>
                    <div class="guide-step-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="markStep(7)">
                            <i class="fas fa-party-horn"></i> Complete Setup! 🎉
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- All done banner --}}
        <div id="guide-complete-banner" style="display:none;margin:0 24px 24px;padding:16px 20px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #6ee7b7;border-radius:12px;text-align:center">
            <div style="font-size:1.5rem;margin-bottom:6px">🎉</div>
            <div style="font-weight:700;font-size:.95rem;color:#065f46">All Steps Completed!</div>
            <p style="font-size:.84rem;color:#047857;margin:4px 0 10px">Your Xendit payment gateway is fully configured. You're ready to accept payments!</p>
            <button type="button" class="btn btn-sm" style="background:#065f46;color:#fff" onclick="resetGuide()">
                <i class="fas fa-redo"></i> Reset Guide
            </button>
        </div>

        <div style="padding:0 24px 20px">
            <div style="padding:14px 18px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;font-size:.84rem;color:#0c4a6e">
                <i class="fas fa-info-circle" style="margin-right:6px"></i>
                <strong>Need help?</strong> Visit the <a href="https://docs.xendit.co/" target="_blank" rel="noopener" style="color:#0c4a6e;font-weight:600;text-decoration:underline">Xendit Documentation</a> or contact our support team for assistance.
            </div>
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

@push('styles')
<style>
.guide-step { border:1px solid var(--gray-200,#e5e7eb); border-radius:12px; overflow:hidden; transition:all .3s ease; background:#fff; }
.guide-step:hover { border-color:var(--gray-300,#d1d5db); }
.guide-step.active { border-color:var(--primary); box-shadow:0 2px 12px rgba(10,45,116,.1); }
.guide-step.completed { background:#f0fdf4; border-color:#bbf7d0; }
.guide-step.completed .guide-step-title { color:#065f46; text-decoration:line-through; text-decoration-color:#6ee7b7; }
.guide-step.completed .guide-step-num { background:#10b981 !important; }

.guide-step-header { display:flex; align-items:center; gap:12px; padding:14px 18px; cursor:pointer; user-select:none; }
.guide-step-header:hover { background:var(--gray-50,#f9fafb); }
.guide-step.completed .guide-step-header:hover { background:#ecfdf5; }

.guide-step-check { flex-shrink:0; }
.guide-checkbox { display:none; }
.guide-check-label { width:22px; height:22px; border:2px solid var(--gray-300,#d1d5db); border-radius:6px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; background:#fff; }
.guide-check-label:hover { border-color:var(--primary); background:var(--primary-light,#eff6ff); }
.guide-checkbox:checked + .guide-check-label { background:#10b981; border-color:#10b981; }
.guide-checkbox:checked + .guide-check-label::after { content:'\f00c'; font-family:'Font Awesome 6 Free'; font-weight:900; color:#fff; font-size:.7rem; }

.guide-step-num { width:28px; height:28px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; transition:background .3s; }
.guide-step-title { flex:1; font-weight:600; font-size:.9rem; transition:all .3s; }
.guide-step-arrow { color:var(--gray-400); font-size:.75rem; transition:transform .3s; flex-shrink:0; }
.guide-step.active .guide-step-arrow { transform:rotate(90deg); color:var(--primary); }

.guide-step-body { max-height:0; overflow:hidden; transition:max-height .4s ease, padding .3s ease; padding:0 18px; }
.guide-step.active .guide-step-body { max-height:500px; padding:0 18px 18px; }
.guide-step-body p { font-size:.86rem; color:var(--gray-600,#6b7280); margin:0 0 12px; line-height:1.6; }
.guide-step-body p a { color:var(--primary); font-weight:600; }
.guide-step-body code { background:var(--gray-100,#f3f4f6); padding:2px 6px; border-radius:4px; font-size:.8rem; }

.guide-step-details { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
.guide-detail { display:flex; align-items:center; gap:8px; font-size:.82rem; color:var(--gray-500,#6b7280); }
.guide-detail i { width:16px; text-align:center; color:var(--primary); font-size:.8rem; }

.guide-step-actions { display:flex; gap:8px; flex-wrap:wrap; padding-top:4px; }
</style>
@endpush

@push('scripts')
<script>
var TOTAL_STEPS = 7;
var guideState = JSON.parse(localStorage.getItem('xendit_guide_state') || '{}');

function getCompleted() { return Object.keys(guideState).filter(function(k) { return guideState[k]; }); }

function updateProgress() {
    var done = getCompleted().length;
    var pct = Math.round((done / TOTAL_STEPS) * 100);
    document.getElementById('guide-bar').style.width = pct + '%';
    document.getElementById('guide-pct').textContent = pct + '%';
    document.getElementById('guide-progress-label').textContent = done + ' / ' + TOTAL_STEPS + ' completed';

    var banner = document.getElementById('guide-complete-banner');
    var steps = document.getElementById('guide-steps');
    if (done === TOTAL_STEPS) {
        banner.style.display = 'block';
        steps.style.opacity = '.6';
    } else {
        banner.style.display = 'none';
        steps.style.opacity = '1';
    }
}

function markStep(n) {
    var cb = document.getElementById('step-check-' + n);
    var step = document.querySelector('.guide-step[data-step="' + n + '"]');

    if (cb.checked) {
        cb.checked = false;
        step.classList.remove('completed');
        delete guideState[n];
    } else {
        cb.checked = true;
        step.classList.add('completed');
        guideState[n] = true;
    }

    localStorage.setItem('xendit_guide_state', JSON.stringify(guideState));
    updateProgress();
}

function toggleStep(n) {
    var step = document.querySelector('.guide-step[data-step="' + n + '"]');
    if (step.classList.contains('active')) {
        step.classList.remove('active');
    } else {
        // Close others
        document.querySelectorAll('.guide-step.active').forEach(function(s) { s.classList.remove('active'); });
        step.classList.add('active');
    }
}

function openNextStep(n) {
    if (n > TOTAL_STEPS) return;
    document.querySelectorAll('.guide-step.active').forEach(function(s) { s.classList.remove('active'); });
    var next = document.querySelector('.guide-step[data-step="' + n + '"]');
    if (next) {
        next.classList.add('active');
        setTimeout(function() { next.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 100);
    }
}

function resetGuide() {
    guideState = {};
    localStorage.setItem('xendit_guide_state', JSON.stringify(guideState));
    document.querySelectorAll('.guide-checkbox').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.guide-step').forEach(function(s) { s.classList.remove('completed', 'active'); });
    updateProgress();
    // Open step 1
    toggleStep(1);
}

// Restore state on load
function restoreGuide() {
    for (var n = 1; n <= TOTAL_STEPS; n++) {
        if (guideState[n]) {
            document.getElementById('step-check-' + n).checked = true;
            document.querySelector('.guide-step[data-step="' + n + '"]').classList.add('completed');
        }
    }
    updateProgress();

    // Auto-open first incomplete step
    var done = getCompleted();
    if (done.length < TOTAL_STEPS) {
        for (var i = 1; i <= TOTAL_STEPS; i++) {
            if (!guideState[i]) { toggleStep(i); break; }
        }
    }
}

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
    if (!visible) restoreGuide();
}

// Restore guide visibility
(function() {
    if (localStorage.getItem('xendit_guide_visible') === '1') {
        document.getElementById('guide-content').style.display = 'block';
        document.getElementById('guide-chevron').className = 'fas fa-chevron-up';
        document.getElementById('guide-toggle-text').textContent = 'Hide Guide';
        restoreGuide();
    }
})();
</script>
@endpush
