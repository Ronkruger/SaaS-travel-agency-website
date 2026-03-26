<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Tour;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('secure.resource:review')->only(['destroy']);
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
        if (Gate::denies('delete', $review)) {
            SecurityLogger::logUnauthorizedAccess(request(), 'review', $review->id);
            abort(403, 'You are not authorized to delete this review.');
        }

        $tour = $review->tour;
        $review->delete();
        $tour->updateRating();

        return back()->with('success', 'Review deleted.');
    }
}
