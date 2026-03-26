<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\Payment;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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

        return view('booking.create', compact('tour'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id'          => ['required', 'exists:tours,id'],
            'schedule_id'      => ['nullable', 'exists:tour_schedules,id'],
            'tour_date'        => ['required', 'date', 'after_or_equal:today'],
            'adults'           => ['required', 'integer', 'min:1', 'max:50'],
            'children'         => ['required', 'integer', 'min:0', 'max:50'],
            'infants'          => ['required', 'integer', 'min:0', 'max:10'],
            'contact_name'     => ['required', 'string', 'max:255'],
            'contact_email'    => ['required', 'email', 'max:255'],
            'contact_phone'    => ['required', 'string', 'max:20'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'traveler_details' => ['nullable', 'array'],
        ]);

        $tour = Tour::findOrFail($validated['tour_id']);

        $totalGuests = $validated['adults'] + $validated['children'] + $validated['infants'];

        // Calculate pricing
        $pricePerAdult = (float) ($tour->effective_price ?? 0);
        $pricePerChild = $pricePerAdult * 0.75; // Children 25% off
        $subtotal      = ($validated['adults'] * $pricePerAdult) + ($validated['children'] * $pricePerChild);
        $taxableCount  = $validated['adults'] + $validated['children']; // infants exempt
        $taxAmount     = $taxableCount * 1620; // Philippine Travel Tax ₱1,620/person (economy)
        $totalAmount   = $subtotal + $taxAmount;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'booking_number'   => Booking::generateBookingNumber(),
                'user_id'          => auth()->id(),
                'tour_id'          => $tour->id,
                'schedule_id'      => $validated['schedule_id'] ?? null,
                'tour_date'        => $validated['tour_date'],
                'adults'           => $validated['adults'],
                'children'         => $validated['children'],
                'infants'          => $validated['infants'],
                'total_guests'     => $totalGuests,
                'price_per_adult'  => $pricePerAdult,
                'price_per_child'  => $pricePerChild,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'tax_amount'       => $taxAmount,
                'total_amount'     => $totalAmount,
                'status'           => 'pending',
                'payment_status'   => 'unpaid',
                'contact_name'     => $validated['contact_name'],
                'contact_email'    => $validated['contact_email'],
                'contact_phone'    => $validated['contact_phone'],
                'special_requests' => $validated['special_requests'] ?? null,
                'traveler_details' => $validated['traveler_details'] ?? null,
            ]);

            DB::commit();

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
