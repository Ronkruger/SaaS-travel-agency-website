<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\Payment;
use App\Models\Setting;
use App\Mail\BookingReservationMail;
use App\Services\SecurityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('secure.resource:booking')->only(['show', 'cancel']);
    }

    public function index()
    {
        $bookings = auth()->user()
            ->bookings()
            ->with('tour')
            ->latest()
            ->paginate(10);

        return view('booking.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $tour = Tour::active()
            ->findOrFail($request->input('tour_id'));

        // Count booked guests per departure start date for live availability display
        $bookedByDate = Booking::where('tour_id', $tour->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['tour_date', 'total_guests'])
            ->groupBy(fn($b) => \Carbon\Carbon::parse($b->tour_date)->format('Y-m-d'))
            ->map(fn($g) => $g->sum('total_guests'));

        return view('booking.create', compact('tour', 'bookedByDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id'           => ['required', 'exists:tours,id'],
            'schedule_id'       => ['nullable', 'exists:tour_schedules,id'],
            'tour_date'         => ['required', 'date'],
            'adults'            => ['required', 'integer', 'min:1', 'max:50'],
            'children'          => ['required', 'integer', 'min:0', 'max:50'],
            'infants'           => ['required', 'integer', 'min:0', 'max:10'],
            'contact_name'      => ['required', 'string', 'max:255'],
            'contact_email'     => ['required', 'email', 'max:255'],
            'contact_phone'     => ['required', 'string', 'max:20'],
            'special_requests'  => ['nullable', 'string', 'max:1000'],
            'traveler_details'  => ['nullable', 'array'],
            'payment_method'    => ['required', 'in:xendit,cash,installment'],
            'installment_months'=> ['nullable', 'integer', 'min:1', 'max:15'],
        ]);

        $tour = Tour::findOrFail($validated['tour_id']);

        $totalGuests = $validated['adults'] + $validated['children'] + $validated['infants'];

        // Calculate pricing - check multiple price sources
        $pricePerAdult = null;

        // 1. Check if schedule has a price override
        if (!empty($validated['schedule_id'])) {
            $schedule = TourSchedule::find($validated['schedule_id']);
            if ($schedule && $schedule->price_override > 0) {
                $pricePerAdult = (float) $schedule->price_override;
            }
        }

        // 2. Check if selected departure_date has a price in tour's departure_dates array
        if ($pricePerAdult === null && !empty($validated['tour_date'])) {
            $departureDates = $tour->departure_dates ?? [];
            foreach ($departureDates as $dateEntry) {
                $startDate = $dateEntry['start'] ?? null;
                if ($startDate && $startDate === $validated['tour_date'] && !empty($dateEntry['price'])) {
                    $pricePerAdult = (float) $dateEntry['price'];
                    break;
                }
            }
        }

        // 3. Fallback to tour's effective price
        if ($pricePerAdult === null) {
            $pricePerAdult = (float) ($tour->effective_price ?? 0);
        }

        $pricePerChild = $pricePerAdult * 0.75; // Children 25% off
        $subtotal      = ($validated['adults'] * $pricePerAdult) + ($validated['children'] * $pricePerChild);
        $taxableCount  = $validated['adults'] + $validated['children']; // infants exempt
        $taxAmount     = $taxableCount * 1620; // Philippine Travel Tax ₱1,620/person (economy)
        $totalAmount   = $subtotal + $taxAmount;

        DB::beginTransaction();
        try {
            // Build installment schedule for installment payments
            $paymentMethod      = $validated['payment_method'];
            $installmentMonths  = null;
            $downpaymentAmount  = null;
            $installmentSchedule = null;

            if ($paymentMethod === 'installment') {
                $installmentMonths = min(
                    (int) ($validated['installment_months'] ?? ($tour->installment_months ?? 10)),
                    15
                );

                $downpaymentAmount = $tour->fixed_downpayment_amount ?? 0;

                // Use fixed monthly amount from tour if set, otherwise divide total/months
                $monthlyAmount = $tour->monthly_installment_amount > 0
                    ? (float) $tour->monthly_installment_amount
                    : (float) ceil($totalAmount / $installmentMonths);

                $schedule = [];
                $today = now();

                if ($downpaymentAmount > 0) {
                    $schedule[] = [
                        'type'     => 'downpayment',
                        'term'     => 0,
                        'due_date' => $today->copy()->addDays(7)->toDateString(),
                        'amount'   => (float) $downpaymentAmount,
                        'status'   => 'pending',
                    ];
                }

                for ($i = 1; $i <= $installmentMonths; $i++) {
                    $schedule[] = [
                        'type'     => 'installment',
                        'term'     => $i,
                        'due_date' => $today->copy()->addMonths($i)->toDateString(),
                        'amount'   => $monthlyAmount,
                        'status'   => 'pending',
                    ];
                }

                $installmentSchedule = $schedule;
            }

            $booking = Booking::create([
                'booking_number'      => Booking::generateBookingNumber(),
                'user_id'             => auth()->id(),
                'tour_id'             => $tour->id,
                'schedule_id'         => $validated['schedule_id'] ?? null,
                'tour_date'           => $validated['tour_date'],
                'adults'              => $validated['adults'],
                'children'            => $validated['children'],
                'infants'             => $validated['infants'],
                'total_guests'        => $totalGuests,
                'price_per_adult'     => $pricePerAdult,
                'price_per_child'     => $pricePerChild,
                'subtotal'            => $subtotal,
                'discount_amount'     => 0,
                'tax_amount'          => $taxAmount,
                'total_amount'        => $totalAmount,
                'status'              => 'pending',
                'payment_status'      => 'unpaid',
                'payment_method'      => $paymentMethod,
                'installment_months'  => $installmentMonths,
                'downpayment_amount'  => $downpaymentAmount,
                'installment_schedule'=> $installmentSchedule,
                'contact_name'        => $validated['contact_name'],
                'contact_email'       => $validated['contact_email'],
                'contact_phone'       => $validated['contact_phone'],
                'special_requests'    => $validated['special_requests'] ?? null,
                'traveler_details'    => $validated['traveler_details'] ?? null,
            ]);

            DB::commit();

            // Send reservation email
            try {
                $booking->load('tour');
                Mail::to($booking->contact_email)
                    ->send(new BookingReservationMail($booking));
            } catch (\Throwable $e) {
                Log::error('Failed to send reservation email: ' . $e->getMessage());
            }

            return redirect()->route('checkout.show', $booking)->with('success', 'Booking created! Please complete your payment.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Booking failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Booking failed. Please try again.'])->withInput();
        }
    }

    public function show(Booking $booking)
    {
        // Use policy for authorization
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'booking', $booking->id);
            abort(403, 'You are not authorized to view this booking.');
        }

        $booking->load(['tour', 'payment', 'review']);
        return view('booking.show', compact('booking'));
    }

    public function downloadPdf(Booking $booking)
    {
        if (Gate::denies('view', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'booking', $booking->id);
            abort(403, 'You are not authorized to download this booking confirmation.');
        }

        $booking->load(['tour', 'payment', 'payments', 'schedule', 'user']);

        $settings = [
            'logo_url'     => Setting::get('pdf_logo_url'),
            'accent_color' => Setting::get('pdf_accent_color', '#1e3a8a'),
            'header_text'  => Setting::get('pdf_header_text', 'OFFICIAL BOOKING CONFIRMATION'),
            'footer_text'  => Setting::get('pdf_footer_text', 'Thank you for choosing us. This document serves as your official booking confirmation.'),
            'show_payments'=> (bool) Setting::get('pdf_show_payments', true),
            'contact_email'=> Setting::get('pdf_contact_email', ''),
            'contact_phone'=> Setting::get('pdf_contact_phone', ''),
            'contact_address'=> Setting::get('pdf_contact_address', ''),
            'facebook_url' => Setting::get('pdf_facebook_url', ''),
        ];

        $pdf = Pdf::loadView('admin.bookings.confirmation-pdf', compact('booking', 'settings'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 150,
            ]);

        return $pdf->download("booking-{$booking->booking_number}.pdf");
    }

    public function cancel(Booking $booking)
    {
        // Use policy for authorization
        if (Gate::denies('cancel', $booking)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'booking', $booking->id);
            abort(403, 'You are not authorized to cancel this booking.');
        }

        if (!$booking->isCancellable()) {
            return back()->withErrors(['error' => 'This booking cannot be cancelled.']);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
