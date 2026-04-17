<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Stripe;

class BillingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    protected function handleCheckoutCompleted(object $session): void
    {
        if ($session->mode !== 'subscription') {
            return;
        }

        $tenantId = $session->metadata->tenant_id ?? null;
        $planSlug = $session->metadata->plan_slug ?? null;

        if (!$tenantId) {
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $subscriptionEndsAt = now()->addMonth();
        if ($planSlug) {
            $plan = Plan::where('slug', $planSlug)->first();
            // Determine if monthly or yearly based on the session
            // For a monthly plan, add 1 month; for yearly, add 1 year
            $subscriptionEndsAt = now()->addMonth();
        }

        $tenant->update([
            'plan' => $planSlug ?? $tenant->plan,
            'stripe_subscription_id' => $session->subscription,
            'subscription_ends_at' => $subscriptionEndsAt,
            'is_active' => true,
        ]);
    }

    protected function handleSubscriptionUpdated(object $subscription): void
    {
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();
        if (!$tenant) {
            return;
        }

        $tenant->update([
            'subscription_ends_at' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
            'is_active' => in_array($subscription->status, ['active', 'trialing']),
        ]);
    }

    protected function handleSubscriptionDeleted(object $subscription): void
    {
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();
        if (!$tenant) {
            return;
        }

        $tenant->update([
            'stripe_subscription_id' => null,
            'subscription_ends_at' => now(),
        ]);
    }

    protected function handlePaymentFailed(object $invoice): void
    {
        $tenant = Tenant::where('stripe_customer_id', $invoice->customer)->first();
        if (!$tenant) {
            return;
        }

        // Could send email notification here
        \Log::warning("Payment failed for tenant: {$tenant->id}");
    }
}
