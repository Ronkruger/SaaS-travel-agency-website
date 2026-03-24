<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::active();

        // Search
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by continent
        if ($continent = $request->input('continent')) {
            $query->where('continent', $continent);
        }

        // Filter by duration
        if ($duration = $request->input('duration')) {
            match ($duration) {
                '1-3'   => $query->whereBetween('duration_days', [1, 3]),
                '4-7'   => $query->whereBetween('duration_days', [4, 7]),
                '8-14'  => $query->whereBetween('duration_days', [8, 14]),
                '15+'   => $query->where('duration_days', '>=', 15),
                default => null,
            };
        }

        // Filter by price range
        if ($minPrice = $request->input('min_price')) {
            $query->where('regular_price_per_person', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('regular_price_per_person', '<=', $maxPrice);
        }

        // Sort
        match ($request->input('sort', 'latest')) {
            'price_asc'  => $query->orderBy('regular_price_per_person'),
            'price_desc' => $query->orderByDesc('regular_price_per_person'),
            'rating'     => $query->orderByDesc('average_rating'),
            'popular'    => $query->orderByDesc('total_bookings'),
            default      => $query->latest(),
        };

        $tours      = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $continents = Tour::active()->whereNotNull('continent')->distinct()->pluck('continent')->sort()->values();

        return view('tours.index', compact('tours', 'categories', 'continents', 'request'));
    }

    public function show(string $slug)
    {
        $tour = Tour::where('slug', $slug)
            ->active()
            ->with(['reviews.user'])
            ->firstOrFail();

        $relatedTours = Tour::active()
            ->where('continent', $tour->continent)
            ->where('id', '!=', $tour->id)
            ->take(4)
            ->get();

        $isWishlisted = false;
        if (auth()->check()) {
            $isWishlisted = $tour->wishlistedBy()->where('user_id', auth()->id())->exists();
        }

        return view('tours.show', compact('tour', 'relatedTours', 'isWishlisted'));
    }

    public function wishlistToggle(Request $request, Tour $tour)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Please login first.'], 401);
        }

        $user = auth()->user();
        $exists = $user->wishedTours()->where('tour_id', $tour->id)->exists();

        if ($exists) {
            $user->wishedTours()->detach($tour->id);
            return response()->json(['wishlisted' => false, 'message' => 'Removed from wishlist.']);
        } else {
            $user->wishedTours()->attach($tour->id);
            return response()->json(['wishlisted' => true, 'message' => 'Added to wishlist.']);
        }
    }

    public function wishlist()
    {
        $tours = auth()->user()->wishedTours()->paginate(12);
        return view('tours.wishlist', compact('tours'));
    }
}
