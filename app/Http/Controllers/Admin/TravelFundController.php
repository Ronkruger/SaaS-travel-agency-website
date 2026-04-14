<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingRejectionMail;
use App\Models\Booking;
use App\Models\TravelFund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TravelFundController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Add a travel fund credit or debit for a user.
     */
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1', 'max:9999999'],
            'type'        => ['required', 'in:credit,debit'],
            'description' => ['required', 'string', 'max:255'],
            'booking_id'  => ['nullable', 'exists:bookings,id'],
        ]);

        TravelFund::create([
            'user_id'       => $user->id,
            'amount'        => $validated['amount'],
            'type'          => $validated['type'],
            'description'   => $validated['description'],
            'booking_id'    => $validated['booking_id'] ?? null,
            'admin_user_id' => auth('admin')->id(),
        ]);

        return back()->with('success', ucfirst($validated['type']) . ' of ₱' . number_format($validated['amount'], 2) . ' added to Travel Fund.');
    }

    /**
     * Move a cancelled booking's paid amount to the client's travel fund.
     * Also sends the client an updated rejection email with travel fund notice.
     */
    public function fromBooking(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if (!$booking->user_id) {
            return back()->with('error', 'This booking has no linked user account. Travel fund cannot be applied.');
        }

        $user = $booking->user;

        TravelFund::create([
            'user_id'       => $user->id,
            'amount'        => $validated['amount'],
            'type'          => 'credit',
            'description'   => $validated['description']
                ?? 'Travel Fund from cancelled booking ' . $booking->booking_number,
            'booking_id'    => $booking->id,
            'admin_user_id' => auth('admin')->id(),
        ]);

        // Send updated rejection email notifying of the travel fund credit
        try {
            Mail::to($booking->contact_email)
                ->send(new BookingRejectionMail(
                    booking: $booking,
                    travelFundAdded: true,
                    travelFundAmount: (float) $validated['amount'],
                ));
        } catch (\Throwable $e) {
            Log::warning('Travel fund notification email failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return back()->with('success',
            '₱' . number_format($validated['amount'], 2) . ' moved to ' . $user->name . '\'s Travel Fund. Notification email sent.');
    }

    /**
     * Delete a travel fund entry.
     */
    public function destroy(TravelFund $travelFund)
    {
        $user = $travelFund->user;
        $travelFund->delete();

        return back()->with('success', 'Travel fund entry removed.');
    }
}
