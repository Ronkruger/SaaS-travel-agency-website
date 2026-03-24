<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id'    => ['required', 'exists:tours,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // Prevent duplicate review
        $exists = Review::where('user_id', auth()->id())
            ->where('tour_id', $validated['tour_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['review' => 'You have already reviewed this tour.']);
        }

        Review::create([
            ...$validated,
            'user_id'    => auth()->id(),
            'is_approved' => false,
        ]);

        return back()->with('success', 'Review submitted! It will appear after approval.');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $tour = $review->tour;
        $review->delete();
        $tour->updateRating();

        return back()->with('success', 'Review deleted.');
    }
}
