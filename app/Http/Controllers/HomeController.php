<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredTours = Tour::active()->featured()->with('schedules')->take(6)->get();
        $categories    = Category::where('is_active', true)->get();
        $latestTours   = Tour::active()->latest()->with('schedules')->take(8)->get();
        $topRatedTours = Tour::active()->orderByDesc('average_rating')->with('schedules')->take(4)->get();
        $latestReviews = Review::where('is_approved', true)->with(['user', 'tour'])->latest()->take(6)->get();

        $stats = [
            'total_tours'    => Tour::active()->count(),
            'total_reviews'  => Review::where('is_approved', true)->count(),
            'destinations'   => Tour::active()->whereNotNull('continent')->distinct('continent')->count('continent'),
        ];

        return view('home.index', compact(
            'featuredTours',
            'categories',
            'latestTours',
            'topRatedTours',
            'latestReviews',
            'stats'
        ));
    }
}
