<?php

namespace App\Http\Controllers;

use App\Services\AIItineraryService;
use App\Services\DIYPricingEngine;
use App\Services\RouteOptimizerService;
use App\Services\DIYTourValidator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * JSON API endpoints consumed by the DIY builder frontend via AJAX.
 * All endpoints are rate-limited and CSRF-protected.
 */
class DIYTourApiController extends Controller
{
    public function __construct(
        private AIItineraryService    $ai,
        private DIYPricingEngine      $pricing,
        private RouteOptimizerService $optimizer,
        private DIYTourValidator      $validator,
    ) {}

    // -------------------------------------------------------------------------
    // POST /diy/api/suggestions
    // Get AI suggestions for a given itinerary state + user action
    // -------------------------------------------------------------------------

    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'itinerary' => 'required|array',
            'action'    => 'required|string|max:200',
        ]);

        // Sanitize free-text action to prevent prompt injection
        $action = strip_tags($validated['action']);
        $action = preg_replace('/[^\w\s\-.,!?\'"]/', '', $action);
        $action = substr($action, 0, 200);

        try {
            $suggestions = $this->ai->getSuggestions($validated['itinerary'], $action);
        } catch (\Throwable $e) {
            Log::warning('DIY API suggestions failed', ['error' => $e->getMessage()]);
            $suggestions = [];
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    // -------------------------------------------------------------------------
    // POST /diy/api/optimize-route
    // -------------------------------------------------------------------------

    public function optimizeRoute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cities'       => 'required|array|min:2|max:20',
            'cities.*'     => 'string|max:100',
            'must_visit'   => 'nullable|array',
            'must_visit.*' => 'string|max:100',
        ]);

        $result = $this->optimizer->optimizeRoute(
            $validated['cities'],
            $validated['must_visit'] ?? []
        );

        return response()->json($result);
    }

    // -------------------------------------------------------------------------
    // POST /diy/api/reachable-cities
    // Return cities reachable from a given city within X hours
    // -------------------------------------------------------------------------

    public function reachableCities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from'      => 'required|string|max:100',
            'max_hours' => 'nullable|numeric|min:1|max:24',
        ]);

        $cities = $this->optimizer->citiesReachableWithin(
            $validated['from'],
            (float) ($validated['max_hours'] ?? 4.0)
        );

        return response()->json(['cities' => $cities]);
    }

    // -------------------------------------------------------------------------
    // POST /diy/api/calculate-pricing
    // -------------------------------------------------------------------------

    public function calculatePricing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'itinerary_data' => 'required|array',
            'group_size'     => 'required|integer|min:1|max:50',
            'travel_month'   => 'nullable|string|max:20',
        ]);

        $pricing = $this->pricing->calculate(
            $validated['itinerary_data'],
            (int) $validated['group_size'],
            $validated['travel_month'] ?? 'June'
        );

        // Budget adjustment suggestions if budget_range provided
        $suggestions = [];
        if ($request->has('budget_range')) {
            $budgetParts = explode('-', str_replace('+', '-9999999', $request->input('budget_range') ?? ''));
            $budgetMax   = (int) ($budgetParts[1] ?? 0);
            if ($budgetMax > 0) {
                $suggestions = $this->pricing->suggestBudgetAdjustments(
                    $pricing['total_per_person'],
                    $budgetMax,
                    $validated['itinerary_data']
                );
            }
        }

        return response()->json([
            'pricing'     => $pricing,
            'suggestions' => $suggestions,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /diy/api/validate
    // -------------------------------------------------------------------------

    public function validateItinerary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'itinerary_data' => 'required|array',
            'preferences'    => 'required|array',
        ]);

        $result = $this->validator->validate(
            $validated['itinerary_data'],
            $validated['preferences']
        );

        return response()->json($result);
    }
}
