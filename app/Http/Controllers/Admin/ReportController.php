<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Selected month/year — defaults to current month
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // ── Daily sales for the selected month ─────────────────────────────
        // SECURITY: $year and $month are cast to int and validated above; safe in raw query.
        $dailySalesRaw = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as bookings, SUM(total_amount) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy('date');

        // Build a complete day-by-day array (fill zeros for days with no bookings)
        $dailySales = [];
        $daysInMonth = $startOfMonth->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateKey = Carbon::create($year, $month, $d)->format('Y-m-d');
            $dailySales[] = [
                'day'      => $d,
                'date'     => $dateKey,
                'bookings' => (int)   ($dailySalesRaw[$dateKey]->bookings ?? 0),
                'revenue'  => (float) ($dailySalesRaw[$dateKey]->revenue  ?? 0),
            ];
        }

        // ── Monthly summary for the selected month ─────────────────────────
        $monthSummary = [
            'total_bookings'    => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'new_bookings'      => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereNotIn('status', ['cancelled'])->count(),
            'cancelled'         => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('status', 'cancelled')->count(),
            'total_revenue'     => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'cash_revenue'      => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('payment_method', 'cash')->where('payment_status', 'paid')->sum('total_amount'),
            'online_revenue'    => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('payment_method', 'xendit')->whereIn('payment_status', ['paid'])->sum('total_amount'),
            'new_users'         => User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
        ];

        // ── Top tours this month ────────────────────────────────────────────
        $topTours = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->with('tour:id,title,main_image')
            ->selectRaw('tour_id, COUNT(*) as booking_count, SUM(total_amount) as revenue, SUM(total_guests) as guests')
            ->groupBy('tour_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // ── Payment method breakdown ────────────────────────────────────────
        $paymentBreakdown = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method')
            ->get();

        // ── 12-month revenue trend (for the trend chart) ───────────────────
        $trendMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $trendMonths->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        $trendRaw = Booking::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total_amount) as revenue, COUNT(*) as bookings")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()
            ->keyBy('ym');

        $revenueTrend = $trendMonths->map(fn($ym) => [
            'label'    => Carbon::createFromFormat('Y-m', $ym)->format('M Y'),
            'revenue'  => (float) ($trendRaw[$ym]->revenue  ?? 0),
            'bookings' => (int)   ($trendRaw[$ym]->bookings ?? 0),
        ])->values();

        // ── Year/month selector options ─────────────────────────────────────
        $availableYears = range(now()->year, max(2024, now()->year - 3));

        return view('admin.reports.index', compact(
            'year', 'month', 'startOfMonth',
            'dailySales', 'monthSummary', 'topTours',
            'paymentBreakdown', 'revenueTrend', 'availableYears'
        ));
    }
}
