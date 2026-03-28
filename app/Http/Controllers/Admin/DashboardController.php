<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    private function buildStats(): array
    {
        return [
            'total_tours'        => Tour::count(),
            'active_tours'       => Tour::active()->count(),
            'total_bookings'     => Booking::count(),
            'pending_bookings'   => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_users'        => User::where('role', 'user')->count(),
            'total_revenue'      => Payment::where('status', 'completed')->sum('amount'),
            'pending_reviews'    => Review::where('is_approved', false)->count(),
        ];
    }

    public function index()
    {
        $stats = $this->buildStats();

        $recentBookings = Booking::with(['user', 'tour'])->latest()->take(10)->get();
        $topTours       = Tour::orderByDesc('total_bookings')->take(5)->get();

        // SECURITY NOTE: These raw queries are safe because they use no user input.
        // Never pass user-supplied values to selectRaw/groupByRaw/orderByRaw methods.
        $monthlyRevenue = Payment::where('status', 'completed')
            ->selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(amount) as total')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->orderByRaw('YEAR(paid_at) DESC, MONTH(paid_at) DESC')
            ->take(12)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'topTours', 'monthlyRevenue'));
    }

    /** Live-polling endpoint: current stats as JSON */
    public function liveStats()
    {
        return response()->json($this->buildStats());
    }

    /** Live-polling endpoint: 10 most recent bookings as JSON */
    public function liveBookings()
    {
        $bookings = Booking::with(['user', 'tour'])->latest()->take(10)->get();

        return response()->json($bookings->map(fn ($b) => [
            'booking_number' => $b->booking_number,
            'customer'       => $b->user->name,
            'tour'           => Str::limit($b->tour->title, 30),
            'url'            => route('admin.bookings.show', $b),
            'date'           => $b->tour_date->format('M d, Y'),
            'amount'         => '₱' . number_format($b->total_amount, 2),
            'status'         => $b->status,
        ]));
    }
}
