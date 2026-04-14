<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingClaimController extends Controller
{
    public function show()
    {
        return view('booking.claim');
    }

    public function claim(Request $request)
    {
        $request->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'tour_date'    => ['required', 'date'],
        ]);

        $user = Auth::user();

        // Find an unclaimed imported booking matching name + tour date
        $booking = Booking::whereNull('user_id')
            ->whereRaw('LOWER(TRIM(contact_name)) = ?', [strtolower(trim($request->contact_name))])
            ->whereDate('tour_date', $request->tour_date)
            ->first();

        if (! $booking) {
            return back()
                ->withInput()
                ->withErrors(['contact_name' => 'No booking found matching that name and tour date. Please double-check your details.']);
        }

        // Link the booking to this user account
        $booking->user_id = $user->id;
        $booking->save();

        return redirect()->route('booking.show', $booking)
            ->with('success', 'Booking successfully linked to your account! You can now view and pay for your reservation here.');
    }
}
