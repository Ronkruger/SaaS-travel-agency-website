<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentFollowupMail;
use App\Models\AdminActivityLog;
use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\BookingNote;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Services\XenditWebhookValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('secure.resource:booking');
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'tour']);

        if ($status = $request->input('status')) {
            // Validate status against allowed values
            $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed', 'refunded'];
            if (in_array($status, $allowedStatuses)) {
                $query->where('status', $status);
            }
        }
        if ($method = $request->input('payment_method')) {
            if (in_array($method, ['xendit', 'cash', 'installment'])) {
                $query->where('payment_method', $method);
            }
        }
        if ($search = $request->input('search')) {
            // Sanitize search input
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Detect duplicate bookings: same contact_email + schedule_id across multiple bookings
        // Only flag when contact_email is actually set (imported rows have no email — don't flag them)
        $duplicateIds = [];
        $pageItems = $bookings->getCollection();
        $emailSchedulePairs = $pageItems
            ->filter(fn($b) => $b->schedule_id && !empty(trim($b->contact_email ?? '')))
            ->map(fn($b) => ['email' => $b->contact_email, 'sid' => $b->schedule_id])
            ->unique(fn($p) => $p['email'] . '|' . $p['sid'])
            ->values();

        if ($emailSchedulePairs->isNotEmpty()) {
            $dupeQuery = Booking::query();
            foreach ($emailSchedulePairs as $pair) {
                $dupeQuery->orWhere(function ($q) use ($pair) {
                    $q->where('contact_email', $pair['email'])
                      ->where('schedule_id', $pair['sid']);
                });
            }
            $dupeKeys = $dupeQuery
                ->select('contact_email', 'schedule_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('contact_email', 'schedule_id')
                ->havingRaw('cnt > 1')
                ->get()
                ->map(fn($row) => $row->contact_email . '|' . $row->schedule_id)
                ->toArray();

            $duplicateIds = $pageItems->filter(
                fn($b) => $b->schedule_id && in_array($b->contact_email . '|' . $b->schedule_id, $dupeKeys)
            )->pluck('id')->all();
        }

        return view('admin.bookings.index', compact('bookings', 'duplicateIds'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'tour', 'payment', 'payments', 'schedule',
            'notes' => fn($q) => $q->with('adminUser')->orderByDesc('is_pinned')->latest()]);

        // Resolve schedule for slot availability display
        $schedule = $booking->schedule;
        if (!$schedule && $booking->tour_date && $booking->tour_id) {
            $schedule = \App\Models\TourSchedule::where('tour_id', $booking->tour_id)
                ->whereDate('departure_date', $booking->tour_date)
                ->first();
        }

        return view('admin.bookings.show', compact('booking', 'schedule'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed,refunded'],
        ]);

        $booking->update($validated);

        return back()->with('success', 'Booking status updated.');
    }

    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update(['payment_status' => $validated['payment_status']]);

        return back()->with('success', 'Payment status updated to ' . ucfirst($validated['payment_status']) . '.');
    }

    public function updateInstallmentTerm(Request $request, Booking $booking, int $term)
    {
        $validated = $request->validate([
            'status'        => ['required', 'in:pending,paid'],
            'custom_amount' => ['nullable', 'numeric', 'min:1'],
        ]);

        $schedule = $booking->installment_schedule ?? [];
        $found = false;
        $termLabel       = '';
        $scheduledAmount = 0;

        foreach ($schedule as $index => $item) {
            if ((int) ($item['term'] ?? -1) === $term) {
                $termLabel       = $item['type'] === 'downpayment' ? 'Down Payment' : 'Month ' . $term;
                $scheduledAmount = (float) ($item['amount'] ?? 0);

                $schedule[$index]['status']  = $validated['status'];
                $schedule[$index]['paid_at'] = $validated['status'] === 'paid'
                    ? now()->toDateString()
                    : null;

                if ($validated['status'] === 'paid' && !empty($validated['custom_amount'])) {
                    $schedule[$index]['custom_amount'] = (float) $validated['custom_amount'];
                } else {
                    unset($schedule[$index]['custom_amount']);
                }

                $found = true;
                break;
            }
        }

        if (!$found) {
            return back()->with('error', 'Term ' . $term . ' not found in schedule.');
        }

        $paidCount = collect($schedule)->where('status', 'paid')->count();
        $total     = count($schedule);

        $paymentStatus = match(true) {
            $paidCount === 0    => 'unpaid',
            $paidCount < $total => 'partial',
            default             => 'paid',
        };

        // Directly reassign attribute so Eloquent detects the change and saves correctly
        $booking->installment_schedule = array_values($schedule);
        $booking->payment_status       = $paymentStatus;
        $booking->save();

        // Fire a notification to all admins when a payment is marked paid
        if ($validated['status'] === 'paid') {
            $customAmt  = !empty($validated['custom_amount']) ? (float) $validated['custom_amount'] : $scheduledAmount;
            $paidAmount = '₱' . number_format($customAmt, 2);
            $isCustom   = !empty($validated['custom_amount']) && abs($customAmt - $scheduledAmount) > 0.01;
            $note       = $isCustom
                ? ' (custom — scheduled ₱' . number_format($scheduledAmount, 2) . ')'
                : '';

            \App\Models\AdminNotification::broadcast(
                'payment_received',
                'Payment Received',
                $booking->booking_number . ' — ' . $booking->contact_name . ': ' . $termLabel . ' ' . $paidAmount . $note,
                route('admin.bookings.show', $booking),
            );
        }

        return back()->with('success', 'Term ' . $term . ' marked as ' . $validated['status'] . '.');
    }

    public function updateSecondPaymentStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'second_payment_status' => ['nullable', 'string', 'max:255'],
        ]);

        $booking->update(['second_payment_status' => $validated['second_payment_status'] ?: null]);

        return back()->with('success', '2nd payment status updated.');
    }

    public function sendPaymentReminder(Request $request, Booking $booking)
    {
        if ($booking->payment_method !== 'installment') {
            return back()->with('error', 'This booking does not use installment payments.');
        }

        $schedule = $booking->installment_schedule ?? [];
        $termNum  = $request->input('term');

        // Find the target term — specific term if provided, else next pending
        $term = null;
        if ($termNum !== null) {
            foreach ($schedule as $t) {
                if ((string) ($t['term'] ?? '') === (string) $termNum) {
                    $term = $t;
                    break;
                }
            }
        } else {
            foreach ($schedule as $t) {
                if (($t['status'] ?? '') !== 'paid') {
                    $term = $t;
                    break;
                }
            }
        }

        if (!$term) {
            return back()->with('error', 'No pending installment term found to send a reminder for.');
        }

        $daysUntilDue = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($term['due_date']), false);

        try {
            Mail::to($booking->contact_email)
                ->send(new PaymentFollowupMail($booking, $term, $daysUntilDue, isManual: true));

            $termLabel = $term['type'] === 'downpayment' ? 'down payment' : "term {$term['term']}";
            AdminActivityLog::record(
                auth('admin')->user(),
                'manual_payment_reminder',
                "Sent manual payment reminder to {$booking->contact_email} for {$booking->booking_number} ({$termLabel})",
                $booking
            );
        } catch (\Throwable $e) {
            Log::error("Manual payment reminder failed for {$booking->booking_number}: " . $e->getMessage());
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

        return back()->with('success', "Payment reminder sent to {$booking->contact_email}.");
    }

    public function destroy(Booking $booking)
    {
        $user = auth('admin')->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admins can delete bookings directly.');
        }

        $bookingNumber = $booking->booking_number;
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking {$bookingNumber} has been deleted.");
    }

    public function destroyAll(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admins can delete all bookings.');
        }

        $count = Booking::count();

        if ($count === 0) {
            return redirect()->route('admin.bookings.index')
                ->with('warning', 'No bookings to delete.');
        }

        // Reset booked_seats on all schedules
        \App\Models\TourSchedule::where('booked_seats', '>', 0)->update(['booked_seats' => 0]);

        Booking::withTrashed()->forceDelete();

        return redirect()->route('admin.bookings.index')
            ->with('success', "All {$count} bookings have been deleted.");
    }

    /* -------------------------------------------------------------------
     | GET /admin/bookings/{booking}/transfer
     * ----------------------------------------------------------------- */
    public function showTransfer(Booking $booking)
    {
        $booking->load(['tour', 'schedule']);

        $tours = Tour::active()
            ->orderBy('title')
            ->get(['id', 'title']);

        // Pre-load schedules for the current tour so the form can show them
        $currentTourSchedules = TourSchedule::where('tour_id', $booking->tour_id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->get();

        return view('admin.bookings.transfer', compact('booking', 'tours', 'currentTourSchedules'));
    }

    /* -------------------------------------------------------------------
     | POST /admin/bookings/{booking}/transfer
     * ----------------------------------------------------------------- */
    public function transfer(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'tour_id'     => ['required', 'exists:tours,id'],
            'schedule_id' => ['required', 'exists:tour_schedules,id'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ]);

        $newSchedule = TourSchedule::where('id', $validated['schedule_id'])
            ->where('tour_id', $validated['tour_id'])
            ->firstOrFail();

        $oldTourName     = $booking->tour?->title ?? 'Unknown';
        $oldScheduleDate = $booking->tour_date?->format('M d, Y') ?? '—';

        DB::transaction(function () use ($booking, $newSchedule, $validated, $oldTourName, $oldScheduleDate) {
            // Decrement booked_seats on old schedule
            if ($booking->schedule_id) {
                $oldSchedule = TourSchedule::find($booking->schedule_id);
                if ($oldSchedule) {
                    $oldSchedule->decrement('booked_seats', $booking->total_guests);
                    if ($oldSchedule->booked_seats < $oldSchedule->available_seats
                        && $oldSchedule->status === 'sold_out') {
                        $oldSchedule->update(['status' => 'active']);
                    }
                }
            }

            // Update booking to new tour + schedule
            $newRate = $newSchedule->price_override
                ?? Tour::where('id', $validated['tour_id'])->value('regular_price_per_person')
                ?? $booking->price_per_adult;

            $booking->update([
                'tour_id'         => $validated['tour_id'],
                'schedule_id'     => $newSchedule->id,
                'tour_date'       => $newSchedule->departure_date,
                'price_per_adult' => $newRate,
                'subtotal'        => $newRate * $booking->total_guests,
                'total_amount'    => $newRate * $booking->total_guests,
                'payment_status'      => 'pending',
                'downpayment_amount'  => null,
                'installment_months'  => null,
                'installment_schedule' => null,
                'special_requests' => trim(
                    ($booking->special_requests ? $booking->special_requests . ' | ' : '')
                    . 'Transferred from ' . $oldTourName
                    . ' (' . $oldScheduleDate . ')'
                    . ($validated['reason'] ? ' — ' . $validated['reason'] : '')
                ),
            ]);

            // Increment booked_seats on new schedule
            $newSchedule->increment('booked_seats', $booking->total_guests);
            if ($newSchedule->booked_seats >= $newSchedule->available_seats) {
                $newSchedule->update(['status' => 'sold_out']);
            }
        });

        $newTourName = Tour::where('id', $validated['tour_id'])->value('title');

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking transferred from {$oldTourName} ({$oldScheduleDate}) to {$newTourName} ({$newSchedule->departure_date->format('M d, Y')}).");
    }

    /* -------------------------------------------------------------------
     | GET /admin/bookings/{booking}/rebook
     * ----------------------------------------------------------------- */
    public function showRebook(Booking $booking)
    {
        $booking->load(['tour', 'schedule']);

        $tours = Tour::active()
            ->orderBy('title')
            ->get(['id', 'title']);

        $currentTourSchedules = TourSchedule::where('tour_id', $booking->tour_id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->get();

        return view('admin.bookings.rebook', compact('booking', 'tours', 'currentTourSchedules'));
    }

    /* -------------------------------------------------------------------
     | POST /admin/bookings/{booking}/rebook
     * ----------------------------------------------------------------- */
    public function rebook(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'tour_id'          => ['required', 'exists:tours,id'],
            'schedule_id'      => ['required', 'exists:tour_schedules,id'],
            'reason'           => ['nullable', 'string', 'max:500'],
            'cancel_original'  => ['nullable', 'in:1'],
            'new_status'       => ['required', 'in:pending,confirmed'],
        ]);

        $newSchedule = TourSchedule::where('id', $validated['schedule_id'])
            ->where('tour_id', $validated['tour_id'])
            ->firstOrFail();

        $newTour = Tour::findOrFail($validated['tour_id']);

        $newBooking = null;

        DB::transaction(function () use ($booking, $newSchedule, $newTour, $validated, &$newBooking) {
            // Decrement old schedule if original booking was active
            if (in_array($booking->status, ['pending', 'confirmed']) && $booking->schedule_id) {
                $oldSchedule = TourSchedule::find($booking->schedule_id);
                if ($oldSchedule) {
                    $oldSchedule->decrement('booked_seats', $booking->total_guests);
                    if ($oldSchedule->booked_seats < $oldSchedule->available_seats
                        && $oldSchedule->status === 'sold_out') {
                        $oldSchedule->update(['status' => 'active']);
                    }
                }
            }

            // Determine price for new booking
            $newRate = $newSchedule->price_override
                ?? $newTour->regular_price_per_person
                ?? $booking->price_per_adult;

            $remark = 'Rebooked from ' . $booking->booking_number
                . ' (' . ($booking->tour?->title ?? '—') . ', ' . ($booking->tour_date?->format('M d, Y') ?? '—') . ')'
                . ($validated['reason'] ? ' — ' . $validated['reason'] : '');

            // Create the new booking as a copy with updated tour/date
            $newBooking = Booking::create([
                'booking_number'   => Booking::generateBookingNumber(),
                'user_id'          => $booking->user_id,
                'tour_id'          => $newTour->id,
                'schedule_id'      => $newSchedule->id,
                'tour_date'        => $newSchedule->departure_date,
                'adults'           => $booking->adults,
                'children'         => $booking->children,
                'infants'          => $booking->infants ?? 0,
                'total_guests'     => $booking->total_guests,
                'price_per_adult'  => $newRate,
                'price_per_child'  => $booking->price_per_child ?? 0,
                'subtotal'         => $newRate * $booking->total_guests,
                'discount_amount'  => 0,
                'tax_amount'       => 0,
                'total_amount'     => $newRate * $booking->total_guests,
                'status'           => $validated['new_status'],
                'payment_status'   => 'unpaid',
                'payment_method'   => $booking->payment_method,
                'contact_name'     => $booking->contact_name,
                'contact_email'    => $booking->contact_email,
                'contact_phone'    => $booking->contact_phone,
                'traveler_details' => $booking->traveler_details,
                'special_requests' => $remark,
            ]);

            // Increment booked_seats on new schedule
            $newSchedule->increment('booked_seats', $booking->total_guests);
            if ($newSchedule->booked_seats >= $newSchedule->available_seats) {
                $newSchedule->update(['status' => 'sold_out']);
            }

            // Optionally cancel old booking and annotate it
            if (!empty($validated['cancel_original'])) {
                $booking->update([
                    'status'           => 'cancelled',
                    'special_requests' => trim(
                        ($booking->special_requests ? $booking->special_requests . ' | ' : '')
                        . 'Cancelled — rebooked as ' . $newBooking->booking_number
                        . ($validated['reason'] ? ' (' . $validated['reason'] . ')' : '')
                    ),
                ]);
            } else {
                // Just annotate the original
                $booking->update([
                    'special_requests' => trim(
                        ($booking->special_requests ? $booking->special_requests . ' | ' : '')
                        . 'Rebooked as ' . $newBooking->booking_number
                    ),
                ]);
            }
        });

        return redirect()->route('admin.bookings.show', $newBooking)
            ->with('success', "New booking {$newBooking->booking_number} created from {$booking->booking_number}.");
    }

    /* -------------------------------------------------------------------
     | GET /admin/bookings/schedules-for-tour (AJAX)
     * ----------------------------------------------------------------- */
    public function schedulesForTour(Request $request)
    {
        $request->validate(['tour_id' => ['required', 'integer', 'exists:tours,id']]);

        $schedules = TourSchedule::where('tour_id', $request->tour_id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'departure_date'  => $s->departure_date->format('Y-m-d'),
                'return_date'     => $s->return_date?->format('Y-m-d'),
                'label'           => $s->departure_date->format('M d, Y')
                                   . ($s->return_date ? ' – ' . $s->return_date->format('M d, Y') : ''),
                'available_seats' => $s->available_seats,
                'booked_seats'    => $s->booked_seats,
                'remaining'       => $s->available_seats - $s->booked_seats,
                'status'          => $s->status,
                'price_override'  => $s->price_override,
            ]);

        return response()->json($schedules);
    }

    /* -------------------------------------------------------------------
     | Booking Notes
     * ----------------------------------------------------------------- */
    public function storeNote(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'note'      => ['required', 'string', 'max:2000'],
            'is_pinned' => ['boolean'],
        ]);

        $note = BookingNote::create([
            'booking_id'    => $booking->id,
            'admin_user_id' => auth('admin')->id(),
            'note'          => $validated['note'],
            'is_pinned'     => $request->boolean('is_pinned'),
        ]);

        AdminActivityLog::record(
            'booking.note_added',
            $booking,
            'Note added to booking ' . $booking->booking_number . '.'
        );

        return back()->with('success', 'Note added.');
    }

    public function destroyNote(Booking $booking, BookingNote $note)
    {
        abort_unless($note->booking_id === $booking->id, 404);
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }

    public function pinNote(Booking $booking, BookingNote $note)
    {
        abort_unless($note->booking_id === $booking->id, 404);
        $note->update(['is_pinned' => !$note->is_pinned]);

        return back()->with('success', $note->is_pinned ? 'Note pinned.' : 'Note unpinned.');
    }

    /* -------------------------------------------------------------------
     | POST /admin/bookings/bulk
     * ----------------------------------------------------------------- */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:confirm,cancel,email'],
            'ids'    => ['required', 'array', 'min:1', 'max:100'],
            'ids.*'  => ['integer', 'exists:bookings,id'],
        ]);

        $ids   = $validated['ids'];
        $count = count($ids);
        $admin = auth('admin')->user();

        switch ($validated['action']) {
            case 'confirm':
                Booking::whereIn('id', $ids)
                    ->whereNotIn('status', ['confirmed', 'completed', 'cancelled', 'refunded'])
                    ->update(['status' => 'confirmed']);
                AdminActivityLog::record('booking.bulk_confirmed', null, "Bulk confirmed {$count} booking(s).");
                return back()->with('success', "Confirmed {$count} booking(s).");

            case 'cancel':
                if (!$admin->isSuperAdmin()) {
                    abort(403, 'Only super admins can bulk-cancel bookings.');
                }
                Booking::whereIn('id', $ids)
                    ->whereNotIn('status', ['cancelled', 'completed', 'refunded'])
                    ->update(['status' => 'cancelled']);
                AdminActivityLog::record('booking.bulk_cancelled', null, "Bulk cancelled {$count} booking(s).");
                return back()->with('success', "Cancelled {$count} booking(s).");

            case 'email':
                $sent     = 0;
                $bookings = Booking::with('tour')->whereIn('id', $ids)->get();
                foreach ($bookings as $booking) {
                    try {
                        Mail::to($booking->contact_email)
                            ->send(new BookingConfirmationMail($booking));
                        $sent++;
                    } catch (\Throwable $e) {
                        Log::error('Bulk email failed for booking ' . $booking->booking_number . ': ' . $e->getMessage());
                    }
                }
                AdminActivityLog::record('booking.bulk_email', null, "Bulk sent confirmation emails: {$sent}/{$count}.");
                return back()->with('success', "Confirmation emails sent to {$sent} booking(s).");
        }
    }

    /**
     * Resync a pending Xendit installment term by fetching the invoice from Xendit.
     * Used to recover payments where the webhook was dropped (e.g. worker starvation).
     */
    public function resyncXenditPayment(Request $request, Booking $booking, int $term)
    {
        $schedule = $booking->installment_schedule ?? [];
        $entry    = collect($schedule)->firstWhere('term', $term);

        if (!$entry) {
            return back()->with('error', "Term {$term} not found in installment schedule.");
        }

        if (($entry['status'] ?? '') === 'paid') {
            return back()->with('info', "Term {$term} is already marked as paid.");
        }

        $invoiceId = $entry['xendit_invoice_id'] ?? null;
        if (!$invoiceId) {
            return back()->with('error', "No Xendit invoice ID found for term {$term}. The client may not have initiated payment yet.");
        }

        try {
            Configuration::setXenditKey(config('xendit.secret_key'));
            $api     = new InvoiceApi();
            $invoice = $api->getInvoiceById($invoiceId);
        } catch (\Throwable $e) {
            Log::error('Resync Xendit invoice lookup failed', [
                'booking_id' => $booking->id, 'term' => $term, 'invoice_id' => $invoiceId, 'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Could not reach Xendit API: ' . $e->getMessage());
        }

        $xenditStatus = strtoupper($invoice->getStatus() ?? '');

        if (!in_array($xenditStatus, ['PAID', 'SETTLED'])) {
            return back()->with('warning', "Xendit invoice status is \"{$xenditStatus}\" — payment has not been completed on Xendit's end.");
        }

        // Build payment info from the Xendit invoice
        $paidAmount = (float) ($invoice->getAmount() ?? $entry['amount']);
        $channel    = strtoupper($invoice->getPaymentMethod() ?? 'Xendit');
        $xenditId   = $invoice->getId();
        $termLabel  = $term === 0 ? 'Downpayment' : 'Month ' . $term;

        try {
            DB::transaction(function () use ($booking, $schedule, $term, $paidAmount, $xenditId, $termLabel, &$entry) {
                foreach ($schedule as &$t) {
                    if ((int) $t['term'] === $term && $t['status'] !== 'paid') {
                        $t['status']  = 'paid';
                        $t['paid_at'] = now()->toDateTimeString();
                    }
                }
                unset($t);

                $allPaid = collect($schedule)->every(fn($t) => $t['status'] === 'paid');

                Payment::create([
                    'transaction_id'         => Payment::generateTransactionId(),
                    'booking_id'             => $booking->id,
                    'user_id'                => $booking->user_id,
                    'amount'                 => $paidAmount,
                    'currency'               => 'PHP',
                    'method'                 => 'xendit',
                    'status'                 => 'completed',
                    'gateway_transaction_id' => $xenditId,
                    'gateway_response'       => XenditWebhookValidator::sanitizeGatewayResponse(['id' => $xenditId, 'status' => 'PAID', 'resync' => true]),
                    'notes'                  => 'Installment ' . $termLabel . ' (admin resync)',
                    'paid_at'                => now(),
                ]);

                $booking->update([
                    'installment_schedule' => array_values($schedule),
                    'status'               => 'confirmed',
                    'payment_status'       => $allPaid ? 'paid' : 'partial',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Resync DB transaction failed', ['booking_id' => $booking->id, 'term' => $term, 'error' => $e->getMessage()]);
            return back()->with('error', 'Database error during resync: ' . $e->getMessage());
        }

        AdminActivityLog::record('booking.resync_xendit', $booking->id, "Resynced Xendit payment for {$booking->booking_number} - {$termLabel}.");
        AdminNotification::broadcast(
            'payment_received',
            'Payment Synced — ' . $booking->booking_number,
            $booking->contact_name . ': ₱' . number_format($paidAmount, 2) . ' (' . ($term === 0 ? 'Down Payment' : 'Month ' . $term) . ')'
                . ' via ' . $channel . ' · Ref: #' . substr($xenditId, -8) . ' [admin resync]',
            route('admin.bookings.show', $booking) . '#payments',
        );

        return back()->with('success', "Term {$term} successfully resynced from Xendit. Payment of ₱" . number_format($paidAmount, 2) . " recorded.");
    }
}
