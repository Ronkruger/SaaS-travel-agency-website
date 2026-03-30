<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('secure.resource:checkout');
    }

    public function show(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'checkout', $booking->id);
            abort(403, 'You are not authorized to access this checkout.');
        }

        if ($booking->payment_status === 'paid') {
            return redirect()->route('booking.show', $booking)->with('info', 'This booking is already paid.');
        }

        $booking->load('tour');
        return view('checkout.show', compact('booking'));
    }

    public function process(Request $request, Booking $booking)
    {
        if (Gate::denies('update', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'checkout', $booking->id);
            abort(403, 'You are not authorized to process this checkout.');
        }

        if ($booking->payment_status === 'paid') {
            return redirect()->route('booking.confirmation', $booking);
        }

        try {
            $invoiceUrl = XenditController::createInvoice($booking);
            return redirect()->away($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('Xendit invoice creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Could not initiate payment. Please try again.']);
        }
    }

    public function confirmation(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'checkout', $booking->id);
            abort(403, 'You are not authorized to view this confirmation.');
        }

        $booking->load(['tour', 'payment']);
        return view('checkout.confirmation', compact('booking'));
    }

    /**
     * Initiate Xendit payment for a single installment term.
     * Route: POST /checkout/{booking}/installment/{term}
     */
    public function payInstallmentTerm(Booking $booking, int $term)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'installment_pay', $booking->id);
            abort(403);
        }

        if ($booking->payment_method !== 'installment') {
            return back()->withErrors(['error' => 'This booking does not use installment payment.']);
        }

        $schedule = $booking->installment_schedule ?? [];
        $scheduleIndex = null;
        foreach ($schedule as $i => $entry) {
            if ((int) $entry['term'] === $term) {
                $scheduleIndex = $i;
                break;
            }
        }

        if ($scheduleIndex === null) {
            return back()->withErrors(['error' => 'Installment term not found.']);
        }

        if ($schedule[$scheduleIndex]['status'] === 'paid') {
            return back()->with('info', 'This term is already paid.');
        }

        try {
            $booking->load('tour');
            $invoiceUrl = XenditController::createInstallmentInvoice($booking, $scheduleIndex, $schedule[$scheduleIndex]);
            return redirect()->away($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('Xendit installment invoice creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Could not initiate payment. Please try again.']);
        }
    }
}

