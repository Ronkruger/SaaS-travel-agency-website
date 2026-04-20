<?php

namespace App\Http\Controllers;

use App\Mail\DIYPaymentConfirmationMail;
use App\Models\DIYTourQuote;
use App\Models\DIYTourSession;
use App\Models\Payment;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class DIYCheckoutController extends Controller
{
    /**
     * Show the DIY quote checkout page (handles both per-person and group payment).
     */
    public function show(string $token)
    {
        $session = DIYTourSession::where('session_token', $token)
            ->with(['user', 'latestItinerary.latestQuote.payments'])
            ->firstOrFail();

        // Must be the session owner
        if (!Auth::check() || $session->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this quote.');
        }

        $itinerary = $session->latestItinerary;
        $quote = $itinerary?->latestQuote;

        if (!$quote || $quote->status === 'expired' || $quote->isExpired()) {
            return redirect()->route('diy.quote', $token)
                ->with('error', 'This quote has expired. Please request a new one.');
        }

        if ($quote->status === 'accepted') {
            return redirect()->route('diy.checkout.confirmation', $token)
                ->with('info', 'This quote has already been paid.');
        }

        $pricing = $itinerary->pricing_data ?? [];
        $groupSize = (int) ($pricing['group_size'] ?? $quote->pax_count ?? 1);
        $totalGroupPrice = $quote->quoted_price_php * $groupSize;
        $totalPaid = $quote->totalPaid();

        return view('diy.checkout', compact('session', 'itinerary', 'quote', 'groupSize', 'totalGroupPrice', 'totalPaid'));
    }

    /**
     * Process the Xendit payment for a DIY quote.
     */
    public function process(Request $request, string $token)
    {
        $session = DIYTourSession::where('session_token', $token)
            ->with(['user', 'latestItinerary.latestQuote'])
            ->firstOrFail();

        if (!Auth::check() || $session->user_id !== Auth::id()) {
            abort(403);
        }

        $quote = $session->latestItinerary?->latestQuote;

        if (!$quote || $quote->isExpired()) {
            return back()->with('error', 'Quote has expired.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:per_person,group',
            'pax_count'    => 'required|integer|min:1|max:50',
        ]);

        $paxCount = (int) $validated['pax_count'];
        $paymentType = $validated['payment_type'];

        if ($paymentType === 'per_person') {
            $amount = (float) $quote->quoted_price_php;
            $description = 'DIY Tour Quote — 1 person: ' . ($session->latestItinerary->tour_name ?? 'Custom Tour');
        } else {
            $amount = (float) $quote->quoted_price_php * $paxCount;
            $description = 'DIY Tour Quote — ' . $paxCount . ' persons: ' . ($session->latestItinerary->tour_name ?? 'Custom Tour');
        }

        // Store payment details on quote
        $quote->update([
            'payment_type' => $paymentType,
            'pax_count'    => $paxCount,
        ]);

        try {
            $invoiceUrl = self::createDIYInvoice($quote, $session, $amount, $description);
            return redirect($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('DIY Xendit invoice creation failed', [
                'quote_id' => $quote->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to create payment. Please try again.');
        }
    }

    /**
     * Create a Xendit Invoice for a DIY tour quote.
     */
    public static function createDIYInvoice(DIYTourQuote $quote, DIYTourSession $session, float $amount, string $description): string
    {
        Configuration::setXenditKey(config('xendit.secret_key'));
        $apiInstance = new InvoiceApi();

        $user = $session->user;
        $tourName = $session->latestItinerary->tour_name ?? 'Custom DIY Tour';
        $externalId = 'DIYQUOTE-' . $quote->id . '-' . time();

        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id'          => $externalId,
            'amount'               => $amount,
            'description'          => $description,
            'payer_email'          => $user?->email ?? config('mail.from.address', 'noreply@example.com'),
            'customer'             => [
                'given_names'   => $user?->name ?? 'Guest',
                'email'         => $user?->email ?? null,
                'mobile_number' => $user?->phone ?? null,
            ],
            'success_redirect_url' => route('diy.checkout.success', $session->session_token),
            'failure_redirect_url' => route('diy.checkout.failure', $session->session_token),
            'currency'             => 'PHP',
            'items'                => [[
                'name'     => $tourName,
                'quantity' => 1,
                'price'    => $amount,
                'category' => 'DIY Tour Package',
            ]],
            'payment_methods'      => ['CREDIT_CARD', 'BPI', 'BDO', 'GCASH', 'GRABPAY', 'PAYMAYA'],
        ]);

        $invoice = $apiInstance->createInvoice($createInvoiceRequest);

        $quote->update(['xendit_invoice_id' => $invoice->getId()]);

        return $invoice->getInvoiceUrl();
    }

    /**
     * User redirected here after successful Xendit payment.
     */
    public function success(string $token)
    {
        $session = DIYTourSession::where('session_token', $token)
            ->with(['user', 'latestItinerary.latestQuote'])
            ->firstOrFail();

        if (!Auth::check() || $session->user_id !== Auth::id()) {
            abort(403);
        }

        $quote = $session->latestItinerary?->latestQuote;

        // Give webhook a moment to process
        sleep(2);

        if ($quote && $quote->status !== 'accepted') {
            // Webhook may not have arrived yet — verify directly with Xendit
            if ($quote->xendit_invoice_id) {
                try {
                    Configuration::setXenditKey(config('xendit.secret_key'));
                    $api = new InvoiceApi();
                    $invoice = $api->getInvoiceById($quote->xendit_invoice_id);
                    $xenditStatus = strtoupper($invoice->getStatus() ?? '');

                    if (in_array($xenditStatus, ['PAID', 'SETTLED'])) {
                        self::recordDIYPayment($quote, $session, [
                            'id'              => $invoice->getId(),
                            'amount'          => $invoice->getAmount(),
                            'payment_method'  => $invoice->getPaymentMethod() ?? 'xendit',
                            'status'          => 'PAID',
                            'source'          => 'success_redirect',
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('DIY success redirect Xendit verification failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('diy.checkout.confirmation', $token);
    }

    /**
     * User redirected here after failed Xendit payment.
     */
    public function failure(string $token)
    {
        return redirect()->route('diy.checkout.show', $token)
            ->withErrors(['error' => 'Payment was not completed. Please try again.']);
    }

    /**
     * Payment confirmation page.
     */
    public function confirmation(string $token)
    {
        $session = DIYTourSession::where('session_token', $token)
            ->with(['user', 'latestItinerary.latestQuote.payments'])
            ->firstOrFail();

        if (!Auth::check() || $session->user_id !== Auth::id()) {
            abort(403);
        }

        $itinerary = $session->latestItinerary;
        $quote = $itinerary?->latestQuote;

        return view('diy.confirmation', compact('session', 'itinerary', 'quote'));
    }

    /**
     * Record a DIY payment in the database (reusable by webhook and success redirect).
     */
    public static function recordDIYPayment(DIYTourQuote $quote, DIYTourSession $session, array $data): void
    {
        // Prevent duplicate payments
        $existingPayment = Payment::where('gateway_transaction_id', $data['id'] ?? null)
            ->where('diy_quote_id', $quote->id)
            ->first();

        if ($existingPayment) {
            return;
        }

        DB::transaction(function () use ($quote, $session, $data) {
            $amount = (float) ($data['amount'] ?? 0);

            Payment::create([
                'transaction_id'         => Payment::generateTransactionId(),
                'diy_quote_id'           => $quote->id,
                'user_id'                => $session->user_id,
                'amount'                 => $amount,
                'currency'               => 'PHP',
                'method'                 => $data['payment_method'] ?? 'xendit',
                'status'                 => 'completed',
                'gateway_transaction_id' => $data['id'] ?? null,
                'gateway_response'       => $data,
                'notes'                  => 'DIY Tour Quote Payment',
                'paid_at'                => now(),
            ]);

            $quote->update(['status' => 'accepted']);
            $session->update(['status' => 'booked']);
        });

        // Admin notification
        try {
            $tourName = $session->latestItinerary->tour_name ?? 'Custom DIY Tour';
            $userName = $session->user?->name ?? 'Client';
            $paidAmount = (float) ($data['amount'] ?? 0);
            $channel = strtoupper($data['payment_method'] ?? 'Xendit');

            \App\Models\AdminNotification::broadcast(
                'payment_received',
                'DIY Tour Payment — ' . $tourName,
                $userName . ' paid ₱' . number_format($paidAmount, 2) . ' via ' . $channel,
                route('admin.diy.show', $session),
            );
        } catch (\Throwable $e) {
            Log::error('DIY payment admin notification failed: ' . $e->getMessage());
        }

        // Send confirmation email to client
        try {
            $client = $session->user;
            if ($client?->email) {
                $payment = Payment::where('diy_quote_id', $quote->id)
                    ->where('status', 'completed')
                    ->latest('paid_at')
                    ->first();

                if ($payment) {
                    $session->loadMissing('latestItinerary');
                    Mail::to($client->email)->send(new DIYPaymentConfirmationMail($payment, $quote, $session));
                }
            }
        } catch (\Throwable $e) {
            Log::error('DIY payment confirmation email failed: ' . $e->getMessage());
        }
    }
}
