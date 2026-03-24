<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use App\Models\Payment;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tours'      => Tour::count(),
            'active_tours'     => Tour::active()->count(),
            'total_bookings'   => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_users'      => User::where('role', 'user')->count(),
            'total_revenue'    => Payment::where('status', 'completed')->sum('amount'),
            'pending_reviews'  => Review::where('is_approved', false)->count(),
        ];

        $recentBookings = Booking::with(['user', 'tour'])->latest()->take(10)->get();
        $topTours       = Tour::orderByDesc('total_bookings')->take(5)->get();
        $monthlyRevenue = Payment::where('status', 'completed')
            ->selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(amount) as total')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->orderByRaw('YEAR(paid_at) DESC, MONTH(paid_at) DESC')
            ->take(12)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'topTours', 'monthlyRevenue'));
    }
}
