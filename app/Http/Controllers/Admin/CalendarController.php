<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = $request->input('month'); // YYYY-MM
        $month = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $schedules = TourSchedule::with('tour')
            ->whereBetween('departure_date', [$start, $end])
            ->orderBy('departure_date')
            ->get()
            ->groupBy(fn($s) => $s->departure_date->format('Y-m-d'));

        $tours = Tour::active()->orderBy('title')->get(['id', 'title']);

        return view('admin.calendar.index', compact('schedules', 'month', 'tours'));
    }
}
