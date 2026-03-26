<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\SecurityLogger;
use App\Services\XenditWebhookValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditController extends Controller
{
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

        $status = strtoupper($data['status'] ?? '');
        $externalId = $data['external_id'];

        Log::info('Xendit webhook received', ['external_id' => $externalId, 'status' => $status]);

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
        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            $booking->update(['payment_status' => 'unpaid']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * User is redirected here after successful Xendit payment.
     */
    public function success(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'payment_success', $booking->id);
            abort(403);
        }

        $booking->load(['tour', 'payment']);
        return view('checkout.confirmation', compact('booking'))
            ->with('success', 'Payment successful! Your booking is confirmed.');
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
}
