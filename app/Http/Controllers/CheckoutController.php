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
     * Initiate Xendit payment for an installment term.
     * Accepts an optional custom_amount; if it covers multiple pending terms,
     * all covered terms are included in one invoice.
     *
     * Route: POST /checkout/{booking}/installment/{term}
     */
    public function payInstallmentTerm(Request $request, Booking $booking, int $term)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'installment_pay', $booking->id);
            abort(403);
        }

        if ($booking->payment_method !== 'installment') {
            return back()->withErrors(['error' => 'This booking does not use installment payment.']);
        }

        $customAmount = $request->input('custom_amount')
            ? (float) $request->input('custom_amount')
            : null;

        $schedule = $booking->installment_schedule ?? [];

        // Find this term's index
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

        // Collect all pending terms in order, starting from the requested one
        $pendingTerms = collect($schedule)
            ->filter(fn($t) => $t['status'] !== 'paid')
            ->sortBy('term')
            ->values();

        // Determine which terms this payment covers
        $amount      = $customAmount ?? (float) $schedule[$scheduleIndex]['amount'];
        $coveredTerms = [];
        $remaining   = $amount;

        foreach ($pendingTerms as $t) {
            if ($remaining <= 0) break;
            $termAmt = (float) $t['amount'];
            if ($remaining >= $termAmt) {
                $coveredTerms[] = ['index' => array_search($t, $schedule), 'term' => $t, 'paid_amount' => $termAmt];
                $remaining -= $termAmt;
            } else {
                // Partial coverage of this term — only include if it's the first term
                if (empty($coveredTerms)) {
                    $coveredTerms[] = ['index' => array_search($t, $schedule), 'term' => $t, 'paid_amount' => $remaining];
                    $remaining = 0;
                }
                break;
            }
        }

        if (empty($coveredTerms)) {
            return back()->withErrors(['error' => 'Could not determine payment terms.']);
        }

        try {
            $booking->load('tour');
            $invoiceUrl = XenditController::createMultiTermInvoice($booking, $coveredTerms, $amount);
            return redirect()->away($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('Xendit installment invoice creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Could not initiate payment. Please try again.']);
        }
    }

    /**
     * Pay remaining balance — covers all pending terms in one invoice.
     * Route: POST /checkout/{booking}/pay-balance
     */
    public function payBalance(Request $request, Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'pay_balance', $booking->id);
            abort(403);
        }

        $schedule     = $booking->installment_schedule ?? [];
        $pendingTerms = collect($schedule)
            ->filter(fn($t) => $t['status'] !== 'paid')
            ->sortBy('term')
            ->values();

        if ($pendingTerms->isEmpty()) {
            return back()->with('info', 'All terms are already paid.');
        }

        $customAmount = $request->input('custom_amount')
            ? (float) $request->input('custom_amount')
            : null;

        $amount      = $customAmount ?? $pendingTerms->sum('amount');
        $coveredTerms = [];
        $remaining   = $amount;

        foreach ($pendingTerms as $t) {
            if ($remaining <= 0) break;
            $termAmt = (float) $t['amount'];
            $idx = collect($schedule)->search(fn($s) => $s['term'] === $t['term']);
            if ($remaining >= $termAmt) {
                $coveredTerms[] = ['index' => $idx, 'term' => $t, 'paid_amount' => $termAmt];
                $remaining -= $termAmt;
            } else {
                $coveredTerms[] = ['index' => $idx, 'term' => $t, 'paid_amount' => $remaining];
                $remaining = 0;
                break;
            }
        }

        try {
            $booking->load('tour');
            $invoiceUrl = XenditController::createMultiTermInvoice($booking, $coveredTerms, $amount);
            return redirect()->away($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('Xendit balance payment failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Could not initiate payment. Please try again.']);
        }
    }
}

