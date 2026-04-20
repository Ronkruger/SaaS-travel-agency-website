<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $sections = PageSection::forPage('home');

        if ($sections->isEmpty()) {
            return view('home.coming-soon');
        }

        $categories    = Category::all();
        $featuredTours = Tour::active()->featured()->take(6)->get();
        $topRatedTours = Tour::active()->orderByDesc('average_rating')->take(6)->get();
        $latestReviews = Review::where('is_approved', true)->latest()->take(6)->get();

        return view('home.index', compact(
            'sections', 'categories', 'featuredTours', 'topRatedTours', 'latestReviews'
        ));
    }
}
