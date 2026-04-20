<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories    = Category::all();
        $featuredTours = Tour::active()->featured()->take(6)->get();
        $topRatedTours = Tour::active()->orderByDesc('average_rating')->take(6)->get();
        $latestReviews = Review::where('is_approved', true)->latest()->take(6)->get();

        $stats = [
            'total_tours'   => Tour::active()->count(),
            'destinations'  => Tour::active()->whereNotNull('continent')->distinct('continent')->count('continent'),
            'total_reviews' => Review::where('is_approved', true)->count(),
        ];

        $promoBannerUrl  = Setting::get('promo_banner_url');
        $promoBannerLink = Setting::get('promo_banner_link');
        $fbEmbeds        = array_filter((array) json_decode(Setting::get('facebook_embeds', '[]')));
        $ytEmbeds        = array_filter((array) json_decode(Setting::get('youtube_embeds', '[]')));

        return view('home.index', compact(
            'categories', 'featuredTours', 'topRatedTours', 'latestReviews',
            'stats', 'promoBannerUrl', 'promoBannerLink', 'fbEmbeds', 'ytEmbeds'
        ));
    }
}
