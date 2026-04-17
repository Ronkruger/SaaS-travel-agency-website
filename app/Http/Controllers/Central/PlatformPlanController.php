<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlatformPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('central.platform.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'unique:plans,slug'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_tours' => ['required', 'integer', 'min:1'],
            'max_bookings_per_month' => ['required', 'integer', 'min:1'],
            'max_admin_users' => ['required', 'integer', 'min:1'],
            'has_diy_builder' => ['boolean'],
            'has_custom_domain' => ['boolean'],
            'has_api_access' => ['boolean'],
            'has_advanced_reports' => ['boolean'],
            'has_email_marketing' => ['boolean'],
            'has_priority_support' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        Plan::create($validated);

        return back()->with('success', 'Plan created.');
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_tours' => ['required', 'integer', 'min:1'],
            'max_bookings_per_month' => ['required', 'integer', 'min:1'],
            'max_admin_users' => ['required', 'integer', 'min:1'],
            'has_diy_builder' => ['boolean'],
            'has_custom_domain' => ['boolean'],
            'has_api_access' => ['boolean'],
            'has_advanced_reports' => ['boolean'],
            'has_email_marketing' => ['boolean'],
            'has_priority_support' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $plan->update($validated);

        return back()->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        $plan->update(['is_active' => false]);
        return back()->with('success', 'Plan deactivated.');
    }
}
