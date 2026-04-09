<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Http\Request;

class TourScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /**
     * Overview: all upcoming schedules across all tours (sidebar "Slot Tracker").
     */
    public function overview(Request $request)
    {
        $filter = $request->input('filter', 'upcoming'); // upcoming | all | past

        $query = TourSchedule::with('tour')
            ->withCount([
                'bookings as pending_count'   => fn($q) => $q->where('status', 'pending'),
                'bookings as confirmed_count' => fn($q) => $q->where('status', 'confirmed'),
            ])
            ->orderBy('departure_date');

        if ($filter === 'upcoming') {
            $query->whereDate('departure_date', '>=', now()->toDateString());
        } elseif ($filter === 'past') {
            $query->whereDate('departure_date', '<', now()->toDateString());
        }

        $schedules = $query->get();

        // Summary stats
        $stats = [
            'total_schedules'  => $schedules->count(),
            'total_seats'      => $schedules->sum('available_seats'),
            'total_booked'     => $schedules->sum('booked_seats'),
            'total_available'  => $schedules->sum(fn($s) => max(0, $s->available_seats - $s->booked_seats)),
            'overbooked_count' => $schedules->filter(fn($s) => $s->booked_seats >= $s->available_seats)->count(),
        ];

        return view('admin.slot-tracker.index', compact('schedules', 'stats', 'filter'));
    }

    /**
     * Per-tour schedule management page.
     */
    public function index(Tour $tour)
    {
        $schedules = $tour->schedules()
            ->withCount([
                'bookings as pending_count'   => fn($q) => $q->where('status', 'pending'),
                'bookings as confirmed_count' => fn($q) => $q->where('status', 'confirmed'),
            ])
            ->get();

        // Load bookings per schedule for inline display
        $scheduleBookings = [];
        foreach ($schedules as $schedule) {
            $scheduleBookings[$schedule->id] = $schedule->bookings()
                ->with('user')
                ->orderByRaw("FIELD(status,'pending','confirmed','completed','cancelled','refunded')")
                ->get();
        }

        return view('admin.tours.schedules', compact('tour', 'schedules', 'scheduleBookings'));
    }

    /**
     * Store a new schedule slot for a tour.
     */
    public function store(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'departure_date'  => ['required', 'date'],
            'return_date'     => ['nullable', 'date', 'after_or_equal:departure_date'],
            'available_seats' => ['required', 'integer', 'min:1', 'max:500'],
            'price_override'  => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'status'          => ['required', 'in:active,sold_out,cancelled'],
        ]);

        $tour->schedules()->create($validated);

        return back()->with('success', 'Schedule slot added successfully.');
    }

    /**
     * Update an existing schedule slot.
     */
    public function update(Request $request, Tour $tour, TourSchedule $schedule)
    {
        abort_unless((int) $schedule->tour_id === (int) $tour->id, 404);

        $validated = $request->validate([
            'departure_date'  => ['required', 'date'],
            'return_date'     => ['nullable', 'date', 'after_or_equal:departure_date'],
            'available_seats' => ['required', 'integer', 'min:1', 'max:500'],
            'booked_seats'    => ['required', 'integer', 'min:0'],
            'price_override'  => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'status'          => ['required', 'in:active,sold_out,cancelled'],
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Schedule updated successfully.');
    }

    /**
     * Delete a schedule slot (blocked if active bookings exist).
     */
    public function destroy(Tour $tour, TourSchedule $schedule)
    {
        abort_unless((int) $schedule->tour_id === (int) $tour->id, 404);

        $activeBookings = $schedule->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($activeBookings > 0) {
            return back()->with('error', "Cannot delete — this slot has {$activeBookings} active booking(s). Cancel them first.");
        }

        $schedule->delete();

        return back()->with('success', 'Schedule slot deleted.');
    }
}
