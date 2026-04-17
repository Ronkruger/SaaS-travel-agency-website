<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;
use Stripe\Subscription;

class TenantBillingController extends Controller
{
    public function index()
    {
        $tenant = $this->currentTenant();
        $plans = Plan::active()->get();
        return view('central.billing.index', compact('tenant', 'plans'));
    }

    public function plans()
    {
        $tenant = $this->currentTenant();
        $plans = Plan::active()->get();
        return view('central.billing.plans', compact('tenant', 'plans'));
    }

    public function subscribe(Request $request, string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->firstOrFail();
        $tenant = $this->currentTenant();
        $billing = $request->input('billing', 'monthly'); // monthly or yearly

        $priceId = $billing === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        if (!$priceId) {
            return back()->with('error', 'Stripe price not configured for this plan.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Create or fetch Stripe customer
        if (!$tenant->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $tenant->email,
                'name' => $tenant->company_name ?? $tenant->name,
                'metadata' => ['tenant_id' => $tenant->id],
            ]);
            $tenant->update(['stripe_customer_id' => $customer->id]);
        }

        $session = StripeSession::create([
            'customer' => $tenant->stripe_customer_id,
            'mode' => 'subscription',
            'line_items' => [['price' => $priceId, 'quantity' => 1]],
            'success_url' => route('central.billing.index') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('central.billing.plans'),
            'metadata' => ['tenant_id' => $tenant->id, 'plan_slug' => $planSlug],
        ]);

        return redirect()->away($session->url);
    }

    public function cancel(Request $request)
    {
        $tenant = $this->currentTenant();

        if (!$tenant->stripe_subscription_id) {
            return back()->with('error', 'No active subscription found.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        Subscription::update($tenant->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        return back()->with('success', 'Subscription will be cancelled at end of billing period.');
    }

    public function invoices()
    {
        $tenant = $this->currentTenant();
        $invoices = [];

        if ($tenant->stripe_customer_id) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeInvoices = \Stripe\Invoice::all(['customer' => $tenant->stripe_customer_id, 'limit' => 24]);
            $invoices = $stripeInvoices->data;
        }

        return view('central.billing.invoices', compact('tenant', 'invoices'));
    }

    protected function currentTenant(): Tenant
    {
        $tenantId = session('tenant_owner');
        return Tenant::findOrFail($tenantId);
    }
}
