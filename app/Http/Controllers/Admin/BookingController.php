<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        $this->authorize('view', $booking);
        $booking->load(['user', 'tour', 'payment', 'schedule']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);
        
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed,refunded'],
        ]);

        $booking->update($validated);

        return back()->with('success', 'Booking status updated.');
    }

    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update(['payment_status' => $validated['payment_status']]);

        return back()->with('success', 'Payment status updated to ' . ucfirst($validated['payment_status']) . '.');
    }

    public function updateInstallmentTerm(Request $request, Booking $booking, int $term)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid'],
        ]);

        $schedule = $booking->installment_schedule ?? [];

        foreach ($schedule as &$item) {
            if ((int) $item['term'] === $term) {
                $item['status']  = $validated['status'];
                $item['paid_at'] = $validated['status'] === 'paid' ? now()->toDateString() : null;
                break;
            }
        }
        unset($item);

        // Recalculate overall payment_status
        $total    = count($schedule);
        $paidCount = collect($schedule)->where('status', 'paid')->count();

        $paymentStatus = match(true) {
            $paidCount === 0       => 'unpaid',
            $paidCount < $total    => 'partial',
            default                => 'paid',
        };

        $booking->update([
            'installment_schedule' => $schedule,
            'payment_status'       => $paymentStatus,
        ]);

        return back()->with('success', 'Term ' . $term . ' marked as ' . $validated['status'] . '.');
    }
}
