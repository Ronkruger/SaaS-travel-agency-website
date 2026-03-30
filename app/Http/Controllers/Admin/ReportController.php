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
    // ── Shared data builder ─────────────────────────────────────────────────
    private function buildReportData(int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $dailySalesRaw = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as bookings, SUM(total_amount) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy('date');

        $dailySales = [];
        for ($d = 1; $d <= $startOfMonth->daysInMonth; $d++) {
            $dateKey = Carbon::create($year, $month, $d)->format('Y-m-d');
            $dailySales[] = [
                'day'      => $d,
                'date'     => $dateKey,
                'bookings' => (int)   ($dailySalesRaw[$dateKey]->bookings ?? 0),
                'revenue'  => (float) ($dailySalesRaw[$dateKey]->revenue  ?? 0),
            ];
        }

        $monthSummary = [
            'total_bookings' => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'new_bookings'   => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereNotIn('status', ['cancelled'])->count(),
            'cancelled'      => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('status', 'cancelled')->count(),
            'total_revenue'  => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'cash_revenue'   => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('payment_method', 'cash')->where('payment_status', 'paid')->sum('total_amount'),
            'online_revenue' => Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('payment_method', 'xendit')->where('payment_status', 'paid')->sum('total_amount'),
            'new_users'      => User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
        ];

        $topTours = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->with('tour:id,title')
            ->selectRaw('tour_id, COUNT(*) as booking_count, SUM(total_amount) as revenue, SUM(total_guests) as guests')
            ->groupBy('tour_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $paymentBreakdown = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method')
            ->get();

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

        $availableYears = range(now()->year, max(2024, now()->year - 3));

        return compact(
            'year', 'month', 'startOfMonth',
            'dailySales', 'monthSummary', 'topTours',
            'paymentBreakdown', 'revenueTrend', 'availableYears'
        );
    }

    // ── Main report page ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        return view('admin.reports.index', $this->buildReportData($year, $month));
    }

    // ── CSV export ──────────────────────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);
        $data  = $this->buildReportData($year, $month);

        $startOfMonth = $data['startOfMonth'];
        $filename = 'report-' . $startOfMonth->format('Y-m') . '.csv';

        $callback = function () use ($data, $startOfMonth) {
            $out = fopen('php://output', 'w');

            // Summary section
            fputcsv($out, ['DISCOVER GROUP — Monthly Performance Report']);
            fputcsv($out, ['Period: ' . $startOfMonth->format('F Y')]);
            fputcsv($out, ['Generated: ' . now()->format('M d, Y H:i')]);
            fputcsv($out, []);

            fputcsv($out, ['SUMMARY']);
            fputcsv($out, ['Total Bookings', $data['monthSummary']['total_bookings']]);
            fputcsv($out, ['Active Bookings', $data['monthSummary']['new_bookings']]);
            fputcsv($out, ['Cancelled', $data['monthSummary']['cancelled']]);
            fputcsv($out, ['Gross Revenue', number_format($data['monthSummary']['total_revenue'], 2)]);
            fputcsv($out, ['Online Revenue (Xendit)', number_format($data['monthSummary']['online_revenue'], 2)]);
            fputcsv($out, ['Cash Revenue', number_format($data['monthSummary']['cash_revenue'], 2)]);
            fputcsv($out, ['New Customers', $data['monthSummary']['new_users']]);
            fputcsv($out, []);

            // Daily breakdown
            fputcsv($out, ['DAILY SALES BREAKDOWN']);
            fputcsv($out, ['Date', 'Day', 'Bookings', 'Revenue (PHP)']);
            foreach ($data['dailySales'] as $day) {
                fputcsv($out, [
                    $day['date'],
                    Carbon::parse($day['date'])->format('D'),
                    $day['bookings'],
                    number_format($day['revenue'], 2),
                ]);
            }
            fputcsv($out, []);

            // Top tours
            fputcsv($out, ['TOP TOURS THIS MONTH']);
            fputcsv($out, ['Tour', 'Bookings', 'Guests', 'Revenue (PHP)']);
            foreach ($data['topTours'] as $tb) {
                fputcsv($out, [
                    $tb->tour->title ?? 'N/A',
                    $tb->booking_count,
                    $tb->guests,
                    number_format($tb->revenue, 2),
                ]);
            }
            fputcsv($out, []);

            // Payment breakdown
            fputcsv($out, ['PAYMENT METHOD BREAKDOWN']);
            fputcsv($out, ['Method', 'Bookings', 'Revenue (PHP)']);
            foreach ($data['paymentBreakdown'] as $pm) {
                fputcsv($out, [
                    ucfirst($pm->payment_method),
                    $pm->count,
                    number_format($pm->revenue, 2),
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Print-friendly view ─────────────────────────────────────────────────
    public function print(Request $request)
    {
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        return view('admin.reports.print', $this->buildReportData($year, $month));
    }
}
