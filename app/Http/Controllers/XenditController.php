<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Mail\BookingConfirmationMail;
use App\Services\SecurityLogger;
use App\Services\XenditWebhookValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditController extends Controller
{
    /**
     * Create a Xendit Invoice covering multiple installment terms (or a custom amount).
     * $coveredTerms = [['index' => int, 'term' => array, 'paid_amount' => float], ...]
     * The external_id encodes all term numbers so the webhook can mark them all paid.
     */
    public static function createMultiTermInvoice(Booking $booking, array $coveredTerms, float $totalAmount): string
    {
        Configuration::setXenditKey(config('xendit.secret_key'));
        $apiInstance = new InvoiceApi();

        // Build term labels and items
        $termNums    = array_map(fn($c) => $c['term']['term'], $coveredTerms);
        $firstTerm   = $coveredTerms[0]['term']['term'];

        $termLabels = array_map(fn($c) => $c['term']['term'] === 0 ? 'Down Payment' : 'Month ' . $c['term']['term'], $coveredTerms);
        $description = count($coveredTerms) === 1
            ? 'Installment ' . $termLabels[0] . ': ' . $booking->tour->title
            : 'Installment (' . implode(', ', $termLabels) . '): ' . $booking->tour->title;

        // Encode term numbers in external_id: MULTITERM-{bookingId}-{t1}_{t2}_{t3}-{timestamp}
        $termsStr    = implode('_', $termNums);
        $externalId  = 'MULTITERM-' . $booking->id . '-' . $termsStr . '-' . time();

        $items = array_map(fn($c) => [
            'name'     => $booking->tour->title . ' — ' . ($c['term']['term'] === 0 ? 'Down Payment' : 'Month ' . $c['term']['term']),
            'quantity' => 1,
            'price'    => $c['paid_amount'],
            'category' => 'Tour Installment',
        ], $coveredTerms);

        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id'          => $externalId,
            'amount'               => $totalAmount,
            'description'          => $description,
            'payer_email'          => $booking->contact_email ?: config('mail.from.address', 'noreply@example.com'),
            'customer'             => [
                'given_names'   => $booking->contact_name,
                'email'         => $booking->contact_email ?: null,
                'mobile_number' => $booking->contact_phone ?: null,
            ],
            'success_redirect_url' => route('xendit.installment.success', ['booking' => $booking->id, 'term' => $firstTerm]),
            'failure_redirect_url' => route('xendit.failure', $booking),
            'currency'             => 'PHP',
            'items'                => $items,
            'payment_methods'      => ['CREDIT_CARD', 'BPI', 'BDO', 'GCASH', 'GRABPAY', 'PAYMAYA'],
        ]);

        $invoice = $apiInstance->createInvoice($createInvoiceRequest);

        // Store invoice ID on all covered schedule entries
        $schedule = $booking->installment_schedule;
        foreach ($coveredTerms as $covered) {
            $schedule[$covered['index']]['xendit_invoice_id']  = $invoice->getId();
            $schedule[$covered['index']]['xendit_paid_amount'] = $covered['paid_amount'];
        }
        $booking->update(['installment_schedule' => $schedule]);

        return $invoice->getInvoiceUrl();
    }

    /**
     * @deprecated Use createMultiTermInvoice instead.
     * Create a Xendit Invoice for a single installment term and return the pay URL.
     * Stores the invoice ID inside the schedule entry for later webhook verification.
     */
    public static function createInstallmentInvoice(Booking $booking, int $scheduleIndex, array $termEntry): string
    {
        Configuration::setXenditKey(config('xendit.secret_key'));

        $apiInstance = new InvoiceApi();

        $term       = $termEntry['term'];
        $amount     = (float) $termEntry['amount'];
        $termLabel  = $term === 0 ? 'Down Payment' : 'Month ' . $term;

        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id'          => 'INSTALLMENT-' . $booking->id . '-' . $term . '-' . time(),
            'amount'               => $amount,
            'description'          => 'Installment ' . $termLabel . ': ' . $booking->tour->title,
            'payer_email'          => $booking->contact_email ?: config('mail.from.address', 'noreply@example.com'),
            'customer'             => [
                'given_names'   => $booking->contact_name,
                'email'         => $booking->contact_email ?: null,
                'mobile_number' => $booking->contact_phone ?: null,
            ],
            'success_redirect_url' => route('xendit.installment.success', ['booking' => $booking->id, 'term' => $term]),
            'failure_redirect_url' => route('xendit.failure', $booking),
            'currency'             => 'PHP',
            'items'                => [[
                'name'     => $booking->tour->title . ' — ' . $termLabel,
                'quantity' => 1,
                'price'    => $amount,
                'category' => 'Tour Installment',
            ]],
            'payment_methods' => ['CREDIT_CARD', 'BPI', 'BDO', 'GCASH', 'GRABPAY', 'PAYMAYA'],
        ]);

        $invoice = $apiInstance->createInvoice($createInvoiceRequest);

        // Store the invoice ID inside the schedule entry for webhook verification
        $schedule = $booking->installment_schedule;
        $schedule[$scheduleIndex]['xendit_invoice_id'] = $invoice->getId();
        $booking->update(['installment_schedule' => $schedule]);

        return $invoice->getInvoiceUrl();
    }

    /**
     * Called by CheckoutController to create a Xendit Invoice and redirect.
     * Returns the Xendit invoice URL.
     */
    public static function createInvoice(Booking $booking): string
    {
        Configuration::setXenditKey(config('xendit.secret_key'));

        $apiInstance = new InvoiceApi();

        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id'       => 'BOOKING-' . $booking->id . '-' . time(),
            'amount'            => (float) $booking->total_amount,
            'description'       => 'Tour Booking: ' . $booking->tour->title,
            'payer_email'       => $booking->contact_email,
            'customer'          => [
                'given_names'   => $booking->contact_name,
                'email'         => $booking->contact_email,
                'mobile_number' => $booking->contact_phone,
            ],
            'success_redirect_url' => route('xendit.success', $booking),
            'failure_redirect_url' => route('xendit.failure', $booking),
            'currency'          => 'PHP',
            'items'             => [
                [
                    'name'     => $booking->tour->title,
                    'quantity' => $booking->total_guests,
                    'price'    => (float) $booking->total_amount,
                    'category' => 'Tour Package',
                ],
            ],
            'payment_methods'   => ['CREDIT_CARD', 'BPI', 'BDO', 'GCASH', 'GRABPAY', 'PAYMAYA'],
        ]);

        $invoice = $apiInstance->createInvoice($createInvoiceRequest);

        // Store the Xendit invoice ID on the booking so the webhook can match it
        $booking->update(['xendit_invoice_id' => $invoice->getId()]);

        return $invoice->getInvoiceUrl();
    }

    /**
     * Xendit webhook — called by Xendit when payment status changes.
     * Verifies the token, validates request, then marks booking as paid.
     */
    public function webhook(Request $request)
    {
        Log::info('Xendit webhook HIT', [
            'ip'          => $request->ip(),
            'external_id' => $request->input('external_id'),
            'status'      => $request->input('status'),
            'has_token'   => $request->hasHeader('x-callback-token'),
        ]);

        $token = $request->header('x-callback-token');
        $ip = $request->ip();
        $data = $request->all();

        // Comprehensive validation
        $errors = XenditWebhookValidator::validate($token, $ip, $data);
        
        if (!empty($errors)) {
            SecurityLogger::logSuspiciousAccess(
                $request,
                'xendit_webhook',
                $data['external_id'] ?? 'unknown',
                'webhook_validation_failed: ' . implode(', ', $errors)
            );
            
            if (in_array('invalid_token', $errors)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return response()->json(['error' => 'Invalid request', 'details' => $errors], 400);
        }

        $status     = strtoupper($data['status'] ?? '');
        $externalId = $data['external_id'];

        Log::info('Xendit webhook received', ['external_id' => $externalId, 'status' => $status]);

        // ── DIY TOUR QUOTE payment ───────────────────────────────────────
        if (str_starts_with($externalId, 'DIYQUOTE-')) {
            preg_match('/^DIYQUOTE-(\d+)-/', $externalId, $m);
            $quote = \App\Models\DIYTourQuote::find($m[1] ?? null);

            if (!$quote) {
                return response()->json(['error' => 'Quote not found'], 404);
            }

            if ($status === 'PAID' || $status === 'SETTLED') {
                $session = $quote->itinerary?->session;

                if ($session) {
                    try {
                        \App\Http\Controllers\DIYCheckoutController::recordDIYPayment($quote, $session, [
                            'id'             => $data['id'] ?? null,
                            'amount'         => $data['amount'] ?? 0,
                            'payment_method' => $data['payment_method'] ?? 'xendit',
                            'status'         => $status,
                            'source'         => 'webhook',
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('DIYQUOTE webhook payment recording failed', [
                            'external_id' => $externalId,
                            'quote_id'    => $quote->id,
                            'error'       => $e->getMessage(),
                        ]);
                        return response()->json(['error' => 'Processing failed'], 500);
                    }
                }
            } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
                Log::info('DIY quote payment expired/failed', ['quote_id' => $quote->id]);
            }

            return response()->json(['success' => true]);
        }

        // ── MULTI-TERM installment payment (custom amount / pay-balance) ──
        if (str_starts_with($externalId, 'MULTITERM-')) {
            // external_id format: MULTITERM-{bookingId}-{t1}_{t2}_{t3}-{timestamp}
            preg_match('/^MULTITERM-(\d+)-([\d_]+)-/', $externalId, $m);
            $booking     = Booking::find($m[1] ?? null);
            $termNumbers = isset($m[2]) ? array_map('intval', explode('_', $m[2])) : [];

            if (!$booking || empty($termNumbers)) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            if ($status === 'PAID' || $status === 'SETTLED') {
                // Build label and amount before entering the transaction so they're available for the email
                $emailTermLabel = count($termNumbers) === 1
                    ? ($termNumbers[0] === 0 ? 'Down Payment' : 'Month ' . $termNumbers[0])
                    : implode(' + ', array_map(fn($n) => $n === 0 ? 'Down Payment' : 'Month ' . $n, $termNumbers));
                $emailAmountPaid = (float) ($data['amount'] ?? 0);
                $termLabels      = implode(', ', array_map(fn($n) => $n === 0 ? 'Downpayment' : 'Month ' . $n, $termNumbers));
                $channel         = strtoupper($data['payment_channel'] ?? $data['payment_method'] ?? 'Xendit');
                $xenditRef       = substr($data['id'] ?? '', -8);

                try {
                    DB::transaction(function () use ($booking, $termNumbers, $data, $termLabels) {
                        $schedule  = $booking->installment_schedule ?? [];
                        $paidTotal = (float) ($data['amount'] ?? 0);

                        foreach ($schedule as &$entry) {
                            if (in_array((int) $entry['term'], $termNumbers) && $entry['status'] !== 'paid') {
                                $paidAmt = $entry['xendit_paid_amount'] ?? $entry['amount'];
                                $entry['status']        = 'paid';
                                $entry['paid_at']       = now()->toDateTimeString();
                                $entry['custom_amount'] = $paidAmt != $entry['amount'] ? $paidAmt : null;
                            }
                        }
                        unset($entry);

                        $allPaid = collect($schedule)->every(fn($t) => $t['status'] === 'paid');

                        Payment::create([
                            'transaction_id'         => Payment::generateTransactionId(),
                            'booking_id'             => $booking->id,
                            'user_id'                => $booking->user_id,
                            'amount'                 => $paidTotal,
                            'currency'               => 'PHP',
                            'method'                 => $data['payment_method'] ?? 'xendit',
                            'status'                 => 'completed',
                            'gateway_transaction_id' => $data['id'] ?? null,
                            'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse($data),
                            'notes'                  => 'Installment ' . $termLabels,
                            'paid_at'                => now(),
                        ]);

                        $booking->update([
                            'installment_schedule' => array_values($schedule),
                            'status'               => 'confirmed',
                            'payment_status'       => $allPaid ? 'paid' : 'partial',
                        ]);
                    });
                } catch (\Throwable $e) {
                    Log::error('MULTITERM webhook DB transaction failed', [
                        'external_id' => $externalId,
                        'booking_id'  => $booking->id,
                        'error'       => $e->getMessage(),
                    ]);
                    return response()->json(['error' => 'Processing failed'], 500);
                }

                // Notify admins AFTER transaction commits (not inside it, so rollback won't kill the notif)
                try {
                    \App\Models\AdminNotification::broadcast(
                        'payment_received',
                        'Payment Received — ' . $booking->booking_number,
                        $booking->contact_name . ': ₱' . number_format($emailAmountPaid, 2) . ' (' . $emailTermLabel . ')'
                            . ($channel ? ' via ' . $channel : '')
                            . ($xenditRef ? ' · Ref: #' . $xenditRef : ''),
                        route('admin.bookings.show', $booking) . '#payments',
                    );
                } catch (\Throwable $e) {
                    Log::error('MULTITERM admin notification failed: ' . $e->getMessage());
                }

                try {
                    if ($booking->contact_email) {
                        $booking->refresh()->load('tour');
                        Mail::to($booking->contact_email)
                            ->send(new BookingConfirmationMail($booking, $emailTermLabel, true, $emailAmountPaid));
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send multi-term confirmation email: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true]);
        }

        // ── INSTALLMENT term payment ──────────────────────────────────────
        if (str_starts_with($externalId, 'INSTALLMENT-')) {
            // external_id format: INSTALLMENT-{bookingId}-{term}-{timestamp}
            preg_match('/^INSTALLMENT-(\d+)-(\d+)-/', $externalId, $m);
            $booking = Booking::find($m[1] ?? null);
            $termNumber = isset($m[2]) ? (int) $m[2] : null;

            if (!$booking || $termNumber === null) {
                SecurityLogger::logNotFoundAccess($request, 'booking', $m[1] ?? 'unknown');
                return response()->json(['error' => 'Booking not found'], 404);
            }

            if ($status === 'PAID' || $status === 'SETTLED') {
                $notifTermLabel = $termNumber === 0 ? 'Down Payment' : 'Month ' . $termNumber;
                $notifAmount    = (float) ($data['amount'] ?? 0);
                $channel        = strtoupper($data['payment_channel'] ?? $data['payment_method'] ?? 'Xendit');
                $xenditRef      = substr($data['id'] ?? '', -8);

                try {
                    DB::transaction(function () use ($booking, $termNumber, $data) {
                        $schedule = $booking->installment_schedule ?? [];

                        $allPaid = true;
                        foreach ($schedule as &$entry) {
                            if ($entry['term'] == $termNumber && $entry['status'] !== 'paid') {
                                $storedId = $entry['xendit_invoice_id'] ?? null;
                                if ($storedId && $storedId !== ($data['id'] ?? null)) {
                                    Log::warning('Installment invoice ID mismatch', [
                                        'booking_id' => $booking->id, 'term' => $termNumber,
                                    ]);
                                }
                                $entry['status']  = 'paid';
                                $entry['paid_at'] = now()->toDateTimeString();
                            }
                            if ($entry['status'] !== 'paid') {
                                $allPaid = false;
                            }
                        }
                        unset($entry);

                        $termLabel = $termNumber === 0 ? 'Downpayment' : 'Installment Month ' . $termNumber;
                        Payment::create([
                            'transaction_id'         => Payment::generateTransactionId(),
                            'booking_id'             => $booking->id,
                            'user_id'                => $booking->user_id,
                            'amount'                 => $data['amount'] ?? collect($schedule)->firstWhere('term', $termNumber)['amount'] ?? 0,
                            'currency'               => 'PHP',
                            'method'                 => $data['payment_method'] ?? 'xendit',
                            'status'                 => 'completed',
                            'gateway_transaction_id' => $data['id'] ?? null,
                            'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse($data),
                            'notes'                  => $termLabel,
                            'paid_at'                => now(),
                        ]);

                        $booking->update([
                            'installment_schedule' => $schedule,
                            'status'               => 'confirmed',
                            'payment_status'       => $allPaid ? 'paid' : 'partial',
                        ]);
                    });
                } catch (\Throwable $e) {
                    Log::error('INSTALLMENT webhook DB transaction failed', [
                        'external_id' => $externalId,
                        'booking_id'  => $booking->id,
                        'error'       => $e->getMessage(),
                    ]);
                    return response()->json(['error' => 'Processing failed'], 500);
                }

                try {
                    \App\Models\AdminNotification::broadcast(
                        'payment_received',
                        'Payment Received — ' . $booking->booking_number,
                        $booking->contact_name . ': ₱' . number_format($notifAmount, 2) . ' (' . $notifTermLabel . ')'
                            . ($channel ? ' via ' . $channel : '')
                            . ($xenditRef ? ' · Ref: #' . $xenditRef : ''),
                        route('admin.bookings.show', $booking) . '#payments',
                    );
                } catch (\Throwable $e) {
                    Log::error('INSTALLMENT admin notification failed: ' . $e->getMessage());
                }

                // Send confirmation email outside the transaction
                try {
                    $termLabel = $termNumber === 0 ? 'Down Payment' : 'Month ' . $termNumber;
                    $booking->refresh()->load('tour');
                    $amountPaid = $notifAmount ?: (float) (collect($booking->installment_schedule)->firstWhere('term', $termNumber)['amount'] ?? 0);
                    Mail::to($booking->contact_email)
                        ->send(new BookingConfirmationMail($booking, $termLabel, true, $amountPaid));
                } catch (\Throwable $e) {
                    Log::error('Failed to send installment confirmation email: ' . $e->getMessage());
                }
            } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
                // No status change needed — term stays pending
                Log::info('Installment term payment expired/failed', [
                    'booking_id' => $booking->id, 'term' => $termNumber,
                ]);
            }

            return response()->json(['success' => true]);
        }

        // ── Regular full booking payment ──────────────────────────────────
        // Extract booking ID from external_id format: BOOKING-{id}-{timestamp}
        preg_match('/^BOOKING-(\d+)-/', $externalId, $m);
        $booking = Booking::find($m[1]);
        
        if (!$booking) {
            SecurityLogger::logNotFoundAccess($request, 'booking', $m[1]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Verify the invoice ID matches (extra security)
        if ($booking->xendit_invoice_id && $booking->xendit_invoice_id !== ($data['id'] ?? null)) {
            SecurityLogger::logSuspiciousAccess(
                $request,
                'xendit_webhook',
                $booking->id,
                'invoice_id_mismatch'
            );
            return response()->json(['error' => 'Invoice ID mismatch'], 400);
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            $channel   = strtoupper($data['payment_channel'] ?? $data['payment_method'] ?? 'Xendit');
            $xenditRef = substr($data['id'] ?? '', -8);

            try {
                DB::transaction(function () use ($booking, $data) {
                    if ($booking->payment_status === 'paid') return; // idempotency

                    Payment::create([
                        'transaction_id'         => Payment::generateTransactionId(),
                        'booking_id'             => $booking->id,
                        'user_id'                => $booking->user_id,
                        'amount'                 => $booking->total_amount,
                        'currency'               => 'PHP',
                        'method'                 => $data['payment_method'] ?? 'xendit',
                        'status'                 => 'completed',
                        'gateway_transaction_id' => $data['id'] ?? null,
                        // SECURITY: Only store sanitized gateway response
                        'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse($data),
                        'paid_at'                => now(),
                    ]);

                    $booking->update([
                        'status'         => 'confirmed',
                        'payment_status' => 'paid',
                    ]);

                    $booking->tour()->increment('total_bookings');
                });
            } catch (\Throwable $e) {
                Log::error('BOOKING webhook DB transaction failed', [
                    'external_id' => $externalId,
                    'booking_id'  => $booking->id,
                    'error'       => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Processing failed'], 500);
            }

            // Notify admins of full payment
            try {
                \App\Models\AdminNotification::broadcast(
                    'payment_received',
                    'Payment Received — ' . $booking->booking_number,
                    $booking->contact_name . ': ₱' . number_format($booking->total_amount, 2) . ' (Full Payment)'
                        . ($channel ? ' via ' . $channel : '')
                        . ($xenditRef ? ' · Ref: #' . $xenditRef : ''),
                    route('admin.bookings.show', $booking) . '#payments',
                );
            } catch (\Throwable $e) {
                Log::error('BOOKING admin notification failed: ' . $e->getMessage());
            }

            // Send booking confirmation email outside the transaction
            try {
                $booking->refresh()->load('tour');
                Mail::to($booking->contact_email)
                    ->send(new BookingConfirmationMail($booking));
            } catch (\Throwable $e) {
                Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
            }
        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            $booking->update(['payment_status' => 'unpaid']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * User is redirected here after successful Xendit payment.
     * Verifies with Xendit API and records the payment if the webhook missed it.
     */
    public function success(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'payment_success', $booking->id);
            abort(403);
        }

        sleep(2);
        $booking->refresh()->load(['tour', 'payment']);

        // If webhook already recorded the payment, just show confirmation
        if ($booking->payment_status === 'paid') {
            return view('checkout.confirmation', compact('booking'));
        }

        // Webhook hasn't processed — verify directly with Xendit
        if ($booking->xendit_invoice_id) {
            try {
                Configuration::setXenditKey(config('xendit.secret_key'));
                $api     = new InvoiceApi();
                $invoice = $api->getInvoiceById($booking->xendit_invoice_id);
                $xenditStatus = strtoupper($invoice->getStatus() ?? '');

                if (in_array($xenditStatus, ['PAID', 'SETTLED'])) {
                    $channel   = strtoupper($invoice->getPaymentMethod() ?? 'Xendit');
                    $xenditRef = substr($invoice->getId(), -8);

                    DB::transaction(function () use ($booking, $invoice) {
                        Payment::create([
                            'transaction_id'         => Payment::generateTransactionId(),
                            'booking_id'             => $booking->id,
                            'user_id'                => $booking->user_id,
                            'amount'                 => $booking->total_amount,
                            'currency'               => 'PHP',
                            'method'                 => 'xendit',
                            'status'                 => 'completed',
                            'gateway_transaction_id' => $invoice->getId(),
                            'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse([
                                'id' => $invoice->getId(), 'status' => 'PAID', 'source' => 'success_redirect',
                            ]),
                            'paid_at'                => now(),
                        ]);

                        $booking->update([
                            'status'         => 'confirmed',
                            'payment_status' => 'paid',
                        ]);

                        $booking->tour()->increment('total_bookings');
                    });

                    \App\Models\AdminNotification::broadcast(
                        'payment_received',
                        'Payment Received — ' . $booking->booking_number,
                        $booking->contact_name . ': ₱' . number_format($booking->total_amount, 2) . ' (Full Payment)'
                            . ' via ' . $channel . ' · Ref: #' . $xenditRef,
                        route('admin.bookings.show', $booking) . '#payments',
                    );

                    try {
                        $booking->refresh()->load('tour');
                        Mail::to($booking->contact_email)
                            ->send(new BookingConfirmationMail($booking));
                    } catch (\Throwable $e) {
                        Log::error('Success redirect email failed: ' . $e->getMessage());
                    }

                    Log::info('Full payment recorded via success redirect (webhook missed)', [
                        'booking_id' => $booking->id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Success redirect Xendit verification failed: ' . $e->getMessage());
            }
        }

        $booking->refresh()->load(['tour', 'payment']);
        return view('checkout.confirmation', compact('booking'));
    }

    /**
     * User is redirected here after failed/cancelled Xendit payment.
     */
    public function failure(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'payment_failure', $booking->id);
            abort(403);
        }

        return redirect()->route('checkout.show', $booking)
            ->withErrors(['error' => 'Payment was not completed. Please try again.']);
    }

    /**
     * User is redirected here after a successful installment term payment.
     * As a safety net, we check the Xendit invoice status directly and record
     * the payment here if the webhook hasn't done so yet (covers webhook failures).
     */
    public function installmentSuccess(Request $request, Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'installment_success', $booking->id);
            abort(403);
        }

        $term = (int) $request->query('term', 0);
        $termLabel = $term === 0 ? 'Down Payment' : 'Month ' . $term;

        // Give the webhook a moment to process first
        sleep(2);
        $booking->refresh();

        // Check if the webhook already handled this payment
        $schedule = $booking->installment_schedule ?? [];
        $entry    = collect($schedule)->firstWhere('term', $term);
        $alreadyPaid = $entry && ($entry['status'] ?? '') === 'paid';

        if (!$alreadyPaid) {
            // Webhook hasn't processed yet — verify directly with Xendit API
            $invoiceId = $entry['xendit_invoice_id'] ?? null;

            if ($invoiceId) {
                try {
                    Configuration::setXenditKey(config('xendit.secret_key'));
                    $api     = new InvoiceApi();
                    $invoice = $api->getInvoiceById($invoiceId);
                    $xenditStatus = strtoupper($invoice->getStatus() ?? '');

                    if (in_array($xenditStatus, ['PAID', 'SETTLED'])) {
                        $paidAmount = (float) ($invoice->getAmount() ?? $entry['amount'] ?? 0);
                        $channel    = strtoupper($invoice->getPaymentMethod() ?? 'Xendit');
                        $xenditId   = $invoice->getId();

                        // Find ALL terms covered by this invoice (multi-term support)
                        $coveredTerms = [];
                        foreach ($schedule as $idx => $t) {
                            if (($t['xendit_invoice_id'] ?? null) === $invoiceId && ($t['status'] ?? '') !== 'paid') {
                                $coveredTerms[] = $idx;
                            }
                        }

                        if (!empty($coveredTerms)) {
                            DB::transaction(function () use (&$schedule, $coveredTerms, $booking, $paidAmount, $xenditId) {
                                foreach ($coveredTerms as $idx) {
                                    $schedule[$idx]['status']  = 'paid';
                                    $schedule[$idx]['paid_at'] = now()->toDateTimeString();
                                }

                                $allPaid = collect($schedule)->every(fn($t) => $t['status'] === 'paid');

                                $termNums   = array_map(fn($idx) => $schedule[$idx]['term'], $coveredTerms);
                                $termLabels = implode(', ', array_map(fn($n) => $n === 0 ? 'Downpayment' : 'Month ' . $n, $termNums));

                                Payment::create([
                                    'transaction_id'         => Payment::generateTransactionId(),
                                    'booking_id'             => $booking->id,
                                    'user_id'                => $booking->user_id,
                                    'amount'                 => $paidAmount,
                                    'currency'               => 'PHP',
                                    'method'                 => 'xendit',
                                    'status'                 => 'completed',
                                    'gateway_transaction_id' => $xenditId,
                                    'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse([
                                        'id' => $xenditId, 'status' => 'PAID', 'source' => 'success_redirect',
                                    ]),
                                    'notes'                  => 'Installment ' . $termLabels . ' (redirect verify)',
                                    'paid_at'                => now(),
                                ]);

                                $booking->update([
                                    'installment_schedule' => array_values($schedule),
                                    'status'               => 'confirmed',
                                    'payment_status'       => $allPaid ? 'paid' : 'partial',
                                ]);
                            });

                            // Notify admins
                            $termNums   = array_map(fn($idx) => $schedule[$idx]['term'], $coveredTerms);
                            $termLabels = implode(', ', array_map(fn($n) => $n === 0 ? 'Down Payment' : 'Month ' . $n, $termNums));
                            $xenditRef  = substr($xenditId, -8);

                            \App\Models\AdminNotification::broadcast(
                                'payment_received',
                                'Payment Received — ' . $booking->booking_number,
                                $booking->contact_name . ': ₱' . number_format($paidAmount, 2) . ' (' . $termLabels . ')'
                                    . ' via ' . $channel . ' · Ref: #' . $xenditRef,
                                route('admin.bookings.show', $booking) . '#payments',
                            );

                            // Send confirmation email
                            try {
                                $booking->refresh()->load('tour');
                                if ($booking->contact_email) {
                                    Mail::to($booking->contact_email)
                                        ->send(new BookingConfirmationMail($booking, $termLabels, true, $paidAmount));
                                }
                            } catch (\Throwable $e) {
                                Log::error('Redirect verify email failed: ' . $e->getMessage());
                            }

                            Log::info('Payment recorded via success redirect (webhook missed)', [
                                'booking_id' => $booking->id,
                                'invoice_id' => $invoiceId,
                                'terms'      => $termNums,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Success redirect Xendit verification failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('checkout.show', $booking)
            ->with('success', "Payment for {$termLabel} has been confirmed!")
            ->with('payment_processing', true);
    }
}
