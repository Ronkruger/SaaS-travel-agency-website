<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Category;
use App\Models\Review;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::active();

        // Search - sanitize and escape LIKE wildcards
        if ($search = $request->input('search')) {
            $search = strip_tags(trim($search));
            $search = substr($search, 0, 200); // cap length
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped, $search) {
                $q->where('title', 'like', "%{$escaped}%")
                  ->orWhereJsonContains('full_stops', ['city' => $search])
                  ->orWhereJsonContains('full_stops', ['country' => $search]);
            });
        }

        // Filter by category slug - validate against DB values
        if ($categorySlug = $request->input('category')) {
            $categorySlug = strip_tags(trim($categorySlug));
            $category = Category::where('slug', $categorySlug)->where('is_active', true)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter by continent - validate against allowed values
        if ($continent = $request->input('continent')) {
            $allowedContinents = ['Asia', 'Europe', 'Africa', 'North America', 'South America', 'Oceania', 'Antarctica'];
            if (in_array($continent, $allowedContinents)) {
                $query->where('continent', $continent);
            }
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

        // Filter by price range - validate numeric input
        if ($minPrice = $request->input('min_price')) {
            if (is_numeric($minPrice) && $minPrice >= 0) {
                $query->where('regular_price_per_person', '>=', (float) $minPrice);
            }
        }
        if ($maxPrice = $request->input('max_price')) {
            if (is_numeric($maxPrice) && $maxPrice >= 0) {
                $query->where('regular_price_per_person', '<=', (float) $maxPrice);
            }
        }

        // Sort - validate against allowed values
        $allowedSorts = ['latest', 'price_asc', 'price_desc', 'rating', 'popular'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'latest';
        
        match ($sort) {
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

        // SECURITY: Verify tour is active and user can wishlist it
        if (Gate::denies('wishlist', $tour)) {
            SecurityLogger::logUnauthorizedAccess($request, 'tour_wishlist', $tour->id);
            return response()->json(['error' => 'This tour is not available.'], 403);
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

    /** Live-polling endpoint: current departure slot availability as JSON */
    public function liveDepartures(string $slug)
    {
        $tour  = Tour::where('slug', $slug)->active()->firstOrFail();
        $dates = $tour->departure_dates ?? [];

        $result = [];
        foreach ($dates as $date) {
            $maxCap    = isset($date['maxCapacity']) && $date['maxCapacity'] !== '' ? (int) $date['maxCapacity'] : null;
            $booked    = (int) ($date['currentBookings'] ?? 0);
            $remaining = $maxCap !== null ? $maxCap - $booked : null;
            $isFull    = ($date['isAvailable'] ?? true) === false
                         || ($remaining !== null && $remaining <= 0);

            if ($isFull) {
                $badgeClass = 'seats-full';
                $badgeText  = 'FULL';
            } elseif ($remaining !== null && $remaining <= 5) {
                $badgeClass = 'seats-low';
                $badgeText  = $remaining . ' slot' . ($remaining === 1 ? '' : 's') . ' left';
            } elseif ($remaining !== null) {
                $badgeClass = 'seats-open';
                $badgeText  = $remaining . ' slots open';
            } else {
                $badgeClass = 'seats-open';
                $badgeText  = 'Available';
            }

            $result[] = [
                'start'      => $date['start'],
                'isFull'     => $isFull,
                'badgeClass' => $badgeClass,
                'badgeText'  => $badgeText,
            ];
        }

        return response()->json($result);
    }
}
