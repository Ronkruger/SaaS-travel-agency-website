<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('secure.resource:review');
    }

    public function index(Request $request)
    {
        $query = Review::with(['user', 'tour']);

        if ($status = $request->input('status')) {
            // Validate status against allowed values
            if (in_array($status, ['approved', 'pending'])) {
                $query->where('is_approved', $status === 'approved');
            }
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $review->tour->updateRating();

        return back()->with('success', 'Review approved.');
    }

    public function destroy(Review $review)
    {
        $tour = $review->tour;
        $review->delete();
        $tour->updateRating();

        return back()->with('success', 'Review deleted.');
    }
}
