<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

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
        $booking->load(['user', 'tour', 'payment', 'schedule']);
        return view('admin.bookings.show', compact('booking'));
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
}
