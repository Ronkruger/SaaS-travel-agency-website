<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'customer'       => $b->user->name ?? $b->contact_name ?? '—',
            'tour'           => Str::limit($b->tour->title ?? '', 30),
            'url'            => route('admin.bookings.show', $b),
            'date'           => $b->tour_date->format('M d, Y'),
            'amount'         => '₱' . number_format($b->total_amount, 2),
            'status'         => $b->status,
        ]));
    }

    /**
     * AJAX: revenue data for the chart.
     * Accepts: from_date (Y-m-d), to_date (Y-m-d)
     * Returns monthly buckets between the two dates.
     */
    public function revenueChart(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date', 'before_or_equal:today'],
            'to_date'   => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $from = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfMonth()
            : Carbon::now()->subMonths(11)->startOfMonth();

        $to = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfMonth()
            : Carbon::now()->endOfMonth();

        // Clamp: no more than 36 months
        if ($from->lt($to->copy()->subMonths(35))) {
            $from = $to->copy()->subMonths(35)->startOfMonth();
        }

        $rows = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(amount) as total')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->orderByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->get()
            ->keyBy(fn ($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

        // Fill every month between from and to (zeros for empty months)
        $labels   = [];
        $totals   = [];
        $cursor   = $from->copy();
        while ($cursor->lte($to)) {
            $key      = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $totals[] = isset($rows[$key]) ? (float) $rows[$key]->total : 0;
            $cursor->addMonth();
        }

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
            'sum'    => array_sum($totals),
        ]);
    }
}
