<?php

namespace App\Http\Controllers;

use App\Models\DIYTourSession;
use App\Models\DIYTourItinerary;
use App\Models\DIYTourCollaborator;
use App\Services\AIItineraryService;
use App\Services\DIYPricingEngine;
use App\Services\RouteOptimizerService;
use App\Services\DIYTourValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DIYTourController extends Controller
{
    public function __construct(
        private AIItineraryService   $ai,
        private DIYPricingEngine     $pricing,
        private RouteOptimizerService $optimizer,
        private DIYTourValidator     $validator,
    ) {}

    // -------------------------------------------------------------------------
    // Step 1 — Preference wizard landing page
    // -------------------------------------------------------------------------

    public function index()
    {
        return view('diy.index');
    }

    // -------------------------------------------------------------------------
    // Step 2 — Store session + call AI
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'duration_days'       => 'required|integer|min:3|max:60',
            'countries'           => 'required|array|min:1|max:10',
            'countries.*'         => 'string|max:100',
            'travel_style'        => 'required|array|min:1',
            'travel_style.*'      => 'string|in:cultural,nature,food,romantic,shopping,balanced',
            'budget_range'        => 'required|string|in:80000-140000,140000-200000,200000-280000,280000+,100000-150000,150000-200000,200000-250000,250000+',
            'must_visit'          => 'nullable|string|max:500',
            'pace'                => 'required|string|in:relaxed,moderate,fast',
            'group_size'          => 'required|integer|min:1|max:50',
            'travel_month'        => 'nullable|string|max:20',
            'flexible_dates'      => 'nullable|boolean',
        ]);

        // Sanitize must_visit — strip HTML to prevent XSS in AI prompt injection
        $mustVisit = [];
        if (!empty($validated['must_visit'])) {
            $raw = strip_tags($validated['must_visit']);
            $mustVisit = array_filter(
                array_map('trim', explode(',', $raw)),
                fn($p) => strlen($p) <= 100
            );
        }
        $validated['must_visit']   = array_values($mustVisit);
        $validated['session_id']   = 'diy_' . Str::uuid();
        $validated['timestamp']    = now()->toISOString();

        // Create session record
        $session = DB::transaction(function () use ($validated, $request) {
            $token = DIYTourSession::generateToken();

            $s = DIYTourSession::create([
                'user_id'       => Auth::id(),
                'session_token' => $token,
                'status'        => 'draft',
                'expires_at'    => now()->addDays(30),
            ]);

            // Store token in browser session so guests can reclaim their draft
            $request->session()->put('diy_token_' . $s->id, $token);

            return $s;
        });

        // Generate initial itinerary via AI (wrapped in try/catch — non-fatal)
        $itineraryData = [];
        $aiExplanation = '';
        try {
            $aiResult      = $this->ai->generateItinerary($validated);
            $itineraryData = $aiResult['itinerary']     ?? [];
            $aiExplanation = $aiResult['ai_explanation'] ?? '';
        } catch (\RuntimeException $e) {
            $aiExplanation = $e->getMessage();
        }

        // Calculate initial pricing
        $month            = $validated['travel_month'] ?? 'June';
        $groupSize        = (int) $validated['group_size'];
        $pricingData      = $this->pricing->calculate($itineraryData, $groupSize, $month);

        // Run validation
        $validated['_estimated_cost'] = $pricingData['total_per_person'];
        $validationResults = $this->validator->validate($itineraryData, $validated);

        // Persist itinerary
        $itinerary = DIYTourItinerary::create([
            'session_id'         => $session->id,
            'tour_name'          => $itineraryData['tour_name'] ?? 'My Custom Tour',
            'user_preferences'   => $validated,
            'itinerary_data'     => $itineraryData,
            'map_data'           => $this->buildMapData($itineraryData),
            'pricing_data'       => $pricingData,
            'validation_results' => $validationResults,
            'version'            => 1,
        ]);

        return redirect()->route('diy.builder', $session->session_token)
            ->with('ai_explanation', $aiExplanation);
    }

    // -------------------------------------------------------------------------
    // Step 3 — Interactive builder
    // -------------------------------------------------------------------------

    public function builder(Request $request, string $token)
    {
        $session = $this->resolveSession($request, $token);

        if (!$session) {
            return redirect()->route('diy.index')->with('error', 'Session not found or has expired.');
        }

        $itinerary = $session->latestItinerary;
        if (!$itinerary) {
            return redirect()->route('diy.index')->with('error', 'No itinerary found. Please start again.');
        }

        $mapboxToken = config('ai.mapbox_token', '');

        return view('diy.builder', compact('session', 'itinerary', 'mapboxToken'));
    }

    // -------------------------------------------------------------------------
    // Save draft (AJAX or form submit)
    // -------------------------------------------------------------------------

    public function saveDraft(Request $request, string $token)
    {
        $session = $this->resolveSession($request, $token);
        if (!$session) {
            return response()->json(['error' => 'Session not found.'], 404);
        }

        $validated = $request->validate([
            'itinerary_data' => 'required|array',
            'tour_name'      => 'nullable|string|max:255',
        ]);

        $itinerary = $session->latestItinerary;
        if (!$itinerary) {
            return response()->json(['error' => 'Itinerary not found.'], 404);
        }

        $prefs   = $itinerary->user_preferences ?? [];
        $month   = $prefs['travel_month'] ?? 'June';
        $group   = (int) ($prefs['group_size'] ?? 2);

        $pricing    = $this->pricing->calculate($validated['itinerary_data'], $group, $month);
        $prefs['_estimated_cost'] = $pricing['total_per_person'];
        $validation = $this->validator->validate($validated['itinerary_data'], $prefs);

        $itinerary->increment('version');
        $itinerary->update([
            'tour_name'          => $validated['tour_name'] ?? $itinerary->tour_name,
            'itinerary_data'     => $validated['itinerary_data'],
            'map_data'           => $this->buildMapData($validated['itinerary_data']),
            'pricing_data'       => $pricing,
            'validation_results' => $validation,
        ]);

        $session->update(['last_modified' => now()]);

        return response()->json([
            'success'   => true,
            'version'   => $itinerary->version,
            'pricing'   => $pricing,
            'validation'=> $validation,
        ]);
    }

    // -------------------------------------------------------------------------
    // Request a formal quote (marks session as pending_review)
    // -------------------------------------------------------------------------

    public function requestQuote(Request $request, string $token)
    {
        $session = $this->resolveSession($request, $token);
        if (!$session) {
            return redirect()->route('diy.index')->with('error', 'Session not found.');
        }

        if (Auth::guest()) {
            // Prompt to register/login before quoting
            return redirect()->route('register')
                ->with('info', 'Please create an account so we can save your quote and contact you.');
        }

        // Claim ownership if guest built it
        if (!$session->user_id) {
            $session->update(['user_id' => Auth::id()]);
        }

        $session->update(['status' => 'pending_review']);

        return redirect()->route('diy.quote', $token)
            ->with('success', 'Your quote request has been submitted. Our team will review it within 24 hours.');
    }

    // -------------------------------------------------------------------------
    // Quote review page
    // -------------------------------------------------------------------------

    public function quote(Request $request, string $token)
    {
        $session = $this->resolveSession($request, $token);
        if (!$session) {
            return redirect()->route('diy.index');
        }

        $itinerary = $session->latestItinerary;

        return view('diy.quote', compact('session', 'itinerary'));
    }

    // -------------------------------------------------------------------------
    // Share / Invite collaborator
    // -------------------------------------------------------------------------

    public function invite(Request $request, string $token)
    {
        $session = $this->resolveSession($request, $token);
        if (!$session || Auth::guest()) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        // Only the owner can invite
        if ($session->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Only the tour owner can invite collaborators.'], 403);
        }

        $validated = $request->validate([
            'email'            => 'required|email|max:255|exists:users,email',
            'permission_level' => 'required|in:view,suggest,edit',
        ]);

        $invitee = \App\Models\User::where('email', $validated['email'])->first();

        if ($session->collaborators()->where('user_id', $invitee->id)->exists()) {
            return response()->json(['message' => 'User is already a collaborator.']);
        }

        DIYTourCollaborator::create([
            'session_id'       => $session->id,
            'user_id'          => $invitee->id,
            'permission_level' => $validated['permission_level'],
            'invited_by'       => Auth::id(),
            'invited_at'       => now(),
        ]);

        return response()->json(['success' => true, 'message' => "Invitation sent to {$invitee->name}."]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve a DIY session from a token, allowing both authenticated owner
     * and guest access via the browser session token cookie.
     */
    private function resolveSession(Request $request, string $token): ?DIYTourSession
    {
        // Validate token format to prevent enumeration
        if (!Str::startsWith($token, 'diy_') || strlen($token) > 70) {
            return null;
        }

        $session = DIYTourSession::where('session_token', $token)->first();

        if (!$session || $session->isExpired()) {
            return null;
        }

        // Access check: authenticated owner, admin, collaborator, or guest with matching browser token
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) return $session;
            if ($session->user_id === Auth::id()) return $session;
            if ($session->collaborators()->where('user_id', Auth::id())->exists()) return $session;
        }

        // Guest: verify their browser session holds this token
        $storedToken = $request->session()->get('diy_token_' . $session->id);
        if ($storedToken && hash_equals($storedToken, $token)) {
            return $session;
        }

        return null;
    }

    private function buildMapData(array $itinerary): array
    {
        $cities     = [];
        $cityNames  = [];

        foreach (($itinerary['day_by_day'] ?? []) as $day) {
            $city = $day['city'] ?? '';
            if ($city && !in_array($city, $cityNames, true)) {
                $cityNames[] = $city;
                $cities[]    = [
                    'name'    => $city,
                    'country' => $day['country'] ?? '',
                    'day'     => $day['day'],
                ];
            }
        }

        return [
            'cities'   => $cities,
            'route'    => $cityNames,
            'generated'=> now()->toDateTimeString(),
        ];
    }
}
