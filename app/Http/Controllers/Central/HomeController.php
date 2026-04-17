<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Plan::active()->take(3)->get();
        return view('central.home', compact('plans'));
    }

    public function pricing()
    {
        $plans = Plan::active()->get();
        return view('central.pricing', compact('plans'));
    }

    public function features()
    {
        return view('central.features');
    }
}
