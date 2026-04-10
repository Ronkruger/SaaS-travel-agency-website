<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'tour', 'payment', 'payments', 'schedule']);

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
            'status' => ['required', 'in:pending,paid'],
        ]);

        $schedule = $booking->installment_schedule ?? [];
        $found = false;

        foreach ($schedule as $index => $item) {
            if ((int) ($item['term'] ?? -1) === $term) {
                $schedule[$index]['status']  = $validated['status'];
                $schedule[$index]['paid_at'] = $validated['status'] === 'paid'
                    ? now()->toDateString()
                    : null;
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

        return back()->with('success', 'Term ' . $term . ' marked as ' . $validated['status'] . '.');
    }

    public function destroy(Booking $booking)
    {
        $bookingNumber = $booking->booking_number;
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking {$bookingNumber} has been deleted.");
    }

    public function destroyAll(Request $request)
    {
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
}
