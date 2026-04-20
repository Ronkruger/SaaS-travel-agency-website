<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Wraps OpenAI Chat Completions API to generate DIY tour itineraries.
 */
class AIItineraryService
{
    private Client $client;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.openai_api_key', '');
        $this->model  = config('ai.openai_model', 'llama-3.3-70b-versatile');

        $this->client = new Client([
            'base_uri' => rtrim(config('ai.openai_base_url', 'https://api.groq.com/openai'), '/') . '/',
            'timeout'  => config('ai.openai_timeout', 30),
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url', 'https://example.com'),
                'X-Title'       => config('app.name', 'TourSaaS'),
            ],
        ]);
    }

    /**
     * Generate a full day-by-day itinerary from user preferences.
     *
     * @param  array  $preferences  Validated preference data from the wizard
     * @return array{itinerary: array, ai_explanation: string}
     * @throws \RuntimeException on API failure
     */
    public function generateItinerary(array $preferences): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt   = $this->buildUserPrompt($preferences);

        if (trim($this->apiKey) === '') {
            Log::warning('AI itinerary: API key missing, using fallback itinerary');
            return $this->fallbackItinerary($preferences);
        }

        try {
            $response = $this->client->post('v1/chat/completions', [
                'json' => [
                    'model'    => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userPrompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 4096,
                ],
            ]);

            $body    = json_decode((string) $response->getBody(), true);
            $content = $body['choices'][0]['message']['content'] ?? '{}';

            $parsed = json_decode($this->extractJson($content), true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($parsed['itinerary'])) {
                Log::warning('AI itinerary: malformed JSON from OpenAI', ['raw' => substr($content, 0, 500)]);
                return $this->fallbackItinerary($preferences);
            }

            return $parsed;

        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            Log::error('AI itinerary: OpenAI request failed', [
                'status'  => $status,
                'message' => $e->getMessage(),
            ]);
            // Always fall back so DIY builder stays usable even on auth/validation/network issues.
            return $this->fallbackItinerary($preferences);
        } catch (\Throwable $e) {
            Log::error('AI itinerary: unexpected failure', ['message' => $e->getMessage()]);
            return $this->fallbackItinerary($preferences);
        }
    }

    /**
     * Ask the AI for smart suggestions when user makes manual edits.
     *
     * @param  array  $itinerary  Current itinerary state
     * @param  string $action     What the user just did (e.g. "added Venice")
     * @return array  List of suggestion objects with {message, type, auto_apply_data}
     */
    public function getSuggestions(array $itinerary, string $action): array
    {
        try {
            $response = $this->client->post('v1/chat/completions', [
                'json' => [
                    'model'    => $this->model,
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'You are an expert world tour planner. Respond ONLY with a valid JSON object containing a "suggestions" array. Each suggestion must have: message (string), type (route_optimization|add_city|remove_city|budget|timing), auto_apply_data (object or null). Be concise, max 3 suggestions.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => 'The user just ' . $action . '. Current itinerary: ' . json_encode($itinerary, JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                    'temperature' => 0.5,
                    'max_tokens'  => 512,
                ],
            ]);

            $body    = json_decode((string) $response->getBody(), true);
            $content = $body['choices'][0]['message']['content'] ?? '{}';
            $parsed  = json_decode($this->extractJson($content), true);

            return $parsed['suggestions'] ?? [];

        } catch (\Throwable $e) {
            Log::warning('AI suggestions failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert world tour planner with 20+ years of experience designing optimal itineraries for Filipino travelers going anywhere in the world. You always respond with a valid JSON object.

CONSTRAINTS:
0. MANDATORY DEPARTURE STRUCTURE (non-negotiable):
   - Day 1 is ALWAYS the departure day from Manila, Philippines.
     city="Manila", country="Philippines", overnight="In-flight".
     Activity: "Depart Ninoy Aquino International Airport (NAIA) — Flight to [first destination city and airport code]".
   - The LAST day is ALWAYS the return day back to Manila, Philippines.
     city=[last destination city], country=[last destination country], overnight="In-flight".
     Activity: "Depart [last city airport name and code] — Return flight to Manila Ninoy Aquino International Airport (NAIA)".
   - ALL destination/sightseeing days are sandwiched between these two transit days.
   - The total_days count INCLUDES these two transit days.
1. Travel Time Rules:
   - Prefer land/train connections when cities are within 4 hours of each other
   - Allow flights for longer distances or when crossing regions/continents
   - Include 1 rest day per 7 days for relaxed pace
2. Route Logic:
   - Minimize backtracking
   - Follow logical geographic flow based on the selected countries/region
   - Consider visa logistics (e.g. Schengen for Europe, e-visa countries for Asia)
3. Budget Distribution (of total PHP budget per person):
   - 40% accommodation (adjust to local 3/4/5-star pricing per region)
   - 25% transportation (trains, flights, local)
   - 20% activities/tours
   - 15% meals not included
4. Daily Activity Balance:
   - Max 3 major activities per day
   - Include 2-3 hours free time daily
   - Mix indoor/outdoor

KNOWLEDGE BASE:
- Popular Asian routes: Tokyo-Kyoto-Osaka, Bangkok-Chiang Mai-Phuket, Singapore-KL-Bali, Hanoi-Hoi An-Ho Chi Minh
- Popular European routes: Paris-Swiss Alps-Italian Lakes, Barcelona-French Riviera-Rome
- Popular Americas routes: NYC-Washington-Miami, Cancun-Mexico City, Lima-Cusco-Machu Picchu
- PHP exchange rates (approx): EUR 60, USD 57, JPY 0.38, THB 1.6, SGD 43, AUD 37
- Budget accommodations vary by region: SE Asia €20-50/night, Europe €60-150/night, Americas €50-120/night
- Visa tips: Schengen covers most of Europe, Japan/South Korea are easy for Filipinos, US visa required

OUTPUT: Respond ONLY with a JSON object matching this EXACT structure:
{
  "itinerary": {
    "tour_name": "string",
    "total_days": number,
    "cities_count": number,
    "route_type": "linear|loop",
    "estimated_cost_breakdown": {
      "base_package": number,
      "optional_tours": number,
      "total_max": number,
      "currency": "PHP"
    },
    "day_by_day": [
      {
        "day": number,
        "city": "string",
        "country": "string",
        "accommodation": "string",
        "activities": [
          {
            "time": "HH:MM",
            "name": "string",
            "duration_hours": number,
            "category": "must_visit|cultural|nature|food|romantic|shopping",
            "included": boolean,
            "cost_if_optional": number
          }
        ],
        "meals_included": ["breakfast"],
        "free_time": "string",
        "overnight": "string"
      }
    ],
    "transportation": [
      {
        "from": "string",
        "to": "string",
        "day": number,
        "method": "string",
        "duration_hours": number,
        "estimated_cost_php": number,
        "booking_notes": "string"
      }
    ],
    "suggested_optional_tours": [
      {
        "day": number,
        "name": "string",
        "duration_hours": number,
        "cost_php": number,
        "reason": "string",
        "popularity_score": number
      }
    ],
    "customization_options": {
      "can_extend_days": boolean,
      "can_swap_cities": ["string"],
      "can_adjust_pace": boolean,
      "upgradeable_to_premium": boolean
    }
  },
  "ai_explanation": "string"
}
PROMPT;
    }

    /**
     * Strip markdown code fences (```json ... ```) that some models wrap their output in.
     */
    private function extractJson(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]+?)\s*```/i', $raw, $m)) {
            return trim($m[1]);
        }
        return $raw;
    }

    private function buildUserPrompt(array $preferences): string
    {
        $days      = (int) ($preferences['duration_days'] ?? 7);
        $countries = implode(', ', (array) ($preferences['countries'] ?? ['France']));
        $styles    = implode(', ', array_map('ucfirst', (array) ($preferences['travel_style'] ?? ['balanced'])));
        $budget    = $preferences['budget_range'] ?? '140000-200000';
        $pace      = $preferences['pace'] ?? 'moderate';
        $groupSize = (int) ($preferences['group_size'] ?? 2);
        $month     = $preferences['travel_month'] ?? 'any time of year';
        $mustVisit = array_filter((array) ($preferences['must_visit'] ?? []));

        $budgetLabel = match (true) {
            str_starts_with($budget, '80000')  || str_starts_with($budget, '100000')
                => 'Budget — ₱80,000–₱140,000/person (3-star hotels, public transport, self-guided)',
            str_starts_with($budget, '140000') || str_starts_with($budget, '150000')
                => 'Standard — ₱140,000–₱200,000/person (4-star hotels, first-class trains, shared guide)',
            str_starts_with($budget, '200000') || str_starts_with($budget, '250000')
                => 'Premium — ₱200,000–₱280,000/person (4–5-star hotels, private transport, dedicated guide)',
            default
                => 'Luxury — ₱280,000+/person (5-star, private transfers, exclusive experiences)',
        };

        $paceLabel = match ($pace) {
            'relaxed' => 'Relaxed — spend more time in fewer cities (2–3 cities max), unhurried daily schedule',
            'fast'    => 'Fast-paced — see as many highlights as possible (6+ cities), packed daily schedule',
            default   => 'Moderate — balanced mix of cities and downtime (4–5 cities)',
        };

        $groupLabel = $groupSize === 1 ? '1 person (solo traveler)' : $groupSize . ' people';

        $prompt  = "Create a PERSONALISED {$days}-day tour itinerary for a Filipino traveler with the following EXACT preferences:\n\n";
        $prompt .= "CONTINENT: " . ($preferences['continent'] ?? 'Not specified') . "\n";
        $prompt .= "COUNTRIES TO VISIT: {$countries}\n";
        $prompt .= "TRAVEL STYLE: {$styles}\n";
        $prompt .= "BUDGET: {$budgetLabel}\n";
        $prompt .= "TRAVEL PACE: {$paceLabel}\n";
        $prompt .= "GROUP SIZE: {$groupLabel}\n";
        $prompt .= "TRAVEL MONTH: {$month}\n";

        if (!empty($mustVisit)) {
            $prompt .= "MUST-INCLUDE PLACES (non-negotiable — build the itinerary around these): " . implode(', ', $mustVisit) . "\n";
        }

        $prompt .= "\nCRITICAL requirements:\n";
        $prompt .= "1. The itinerary MUST be exactly {$days} days long (fill every single day).\n";
        $prompt .= "2. ONLY visit cities located in: {$countries}. Do NOT suggest cities in any other country or continent (except Manila, Philippines on Day 1 and the last day).\n";
        $prompt .= "3. Activities MUST match the travel style: {$styles}. E.g. if 'Food' is selected, include food markets, cooking classes, restaurant tours.\n";
        $prompt .= "4. Budget the trip for {$groupLabel} using the {$budgetLabel} tier.\n";
        $prompt .= "5. Follow the {$pace} pace rule: " . match ($pace) {
            'relaxed' => '2–3 cities only, minimum 3 nights per city.',
            'fast'    => '6+ cities, 1–2 nights per city.',
            default   => '4–5 cities, 2–3 nights per city.',
        } . "\n";
        if (!empty($mustVisit)) {
            $prompt .= "6. MUST include these specific places in the day-by-day schedule: " . implode(', ', $mustVisit) . "\n";
        }
        $prompt .= "7. Day 1 MUST be: city=\"Manila\", country=\"Philippines\", overnight=\"In-flight\", single activity = departure flight from NAIA Manila to the first destination city (name the exact destination airport code, e.g. CDG, NRT, BKK).\n";
        $prompt .= "8. The last day (Day {$days}) MUST be: the return flight day from the final destination city back to Manila NAIA. Set overnight=\"In-flight\" and name the departure airport (e.g. \"Leonardo da Vinci–Fiumicino Airport (FCO)\").\n";

        return $prompt;
    }

    /**
     * Returns a meaningful fallback itinerary built directly from the user's
     * preferences when the AI is unavailable. Produces a real day-by-day
     * schedule so the builder is never empty.
     */
    private function fallbackItinerary(array $preferences): array
    {
        $days      = (int) ($preferences['duration_days'] ?? 7);
        $countries = (array) ($preferences['countries'] ?? ['France']);
        $styles    = (array) ($preferences['travel_style'] ?? ['balanced']);
        $pace      = $preferences['pace'] ?? 'moderate';
        $budget    = $preferences['budget_range'] ?? '140000-200000';
        $groupSize = (int) ($preferences['group_size'] ?? 2);
        $mustVisit = array_values(array_filter((array) ($preferences['must_visit'] ?? [])));

        // --- Hotel tier from budget ---
        $hotelTier = match (true) {
            str_starts_with($budget, '80000')  || str_starts_with($budget, '100000') => '3-star',
            str_starts_with($budget, '200000') || str_starts_with($budget, '250000')
                || str_starts_with($budget, '280000') || $budget === '280000+'       => '5-star',
            default                                                                   => '4-star',
        };

        // --- City database keyed by country ---
        $citiesByCountry = [
            'France'         => ['Paris', 'Lyon', 'Nice', 'Marseille'],
            'Switzerland'    => ['Zurich', 'Lucerne', 'Geneva', 'Interlaken', 'Bern'],
            'Italy'          => ['Rome', 'Florence', 'Venice', 'Milan', 'Naples'],
            'Spain'          => ['Barcelona', 'Madrid', 'Seville', 'Granada'],
            'Portugal'       => ['Lisbon', 'Porto'],
            'Germany'        => ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne'],
            'Austria'        => ['Vienna', 'Salzburg', 'Innsbruck'],
            'Netherlands'    => ['Amsterdam'],
            'Belgium'        => ['Brussels', 'Bruges'],
            'Czech Republic' => ['Prague'],
            'Hungary'        => ['Budapest'],
            'Croatia'        => ['Dubrovnik', 'Split'],
            'Greece'         => ['Athens', 'Santorini', 'Mykonos'],
            'Poland'         => ['Krakow', 'Warsaw'],
            'Denmark'        => ['Copenhagen'],
            'Sweden'         => ['Stockholm'],
            'Norway'         => ['Oslo', 'Bergen'],
            'Ireland'        => ['Dublin'],
            'Slovakia'       => ['Bratislava'],
            'Japan'          => ['Tokyo', 'Kyoto', 'Osaka', 'Hiroshima'],
            'South Korea'    => ['Seoul', 'Busan'],
            'Thailand'       => ['Bangkok', 'Chiang Mai', 'Phuket'],
            'Vietnam'        => ['Hanoi', 'Hoi An', 'Ho Chi Minh'],
            'Indonesia'      => ['Bali', 'Jakarta', 'Yogyakarta'],
            'Philippines'    => ['Manila', 'Cebu', 'Palawan'],
            'Singapore'      => ['Singapore'],
            'Malaysia'       => ['Kuala Lumpur', 'Penang', 'Langkawi'],
            'Cambodia'       => ['Siem Reap', 'Phnom Penh'],
            'UAE'            => ['Dubai', 'Abu Dhabi'],
            'Turkey'         => ['Istanbul', 'Cappadocia', 'Antalya'],
            'Jordan'         => ['Amman', 'Petra'],
            'India'          => ['Delhi', 'Mumbai', 'Jaipur', 'Agra'],
            'Nepal'          => ['Kathmandu', 'Pokhara'],
            'Sri Lanka'      => ['Colombo', 'Kandy', 'Ella'],
            'China'          => ['Beijing', 'Shanghai'],
            'United States'  => ['New York', 'Los Angeles', 'Miami', 'Las Vegas', 'Chicago'],
            'Canada'         => ['Toronto', 'Vancouver', 'Montreal'],
            'Mexico'         => ['Mexico City', 'Cancun', 'Oaxaca'],
            'Brazil'         => ['Rio de Janeiro', 'São Paulo'],
            'Peru'           => ['Lima', 'Cusco'],
            'Argentina'      => ['Buenos Aires'],
            'Colombia'       => ['Bogota', 'Medellin', 'Cartagena'],
            'Chile'          => ['Santiago', 'Patagonia'],
            'Australia'      => ['Sydney', 'Melbourne', 'Cairns'],
            'New Zealand'    => ['Auckland', 'Queenstown', 'Christchurch'],
            'Morocco'        => ['Marrakech', 'Fes', 'Casablanca'],
            'South Africa'   => ['Cape Town', 'Johannesburg'],
            'Kenya'          => ['Nairobi', 'Mombasa'],
            'Egypt'          => ['Cairo', 'Luxor', 'Aswan'],
            'Surprise me'    => ['Paris', 'Tokyo', 'Rome'],
        ];

        // --- Collect candidate cities ---
        $candidateCities = [];
        foreach ($countries as $country) {
            foreach (($citiesByCountry[$country] ?? []) as $city) {
                $candidateCities[] = ['city' => $city, 'country' => $country];
            }
        }
        if (empty($candidateCities)) {
            $candidateCities = [['city' => 'Paris', 'country' => 'France']];
        }

        // --- Choose number of cities based on pace ---
        $numCities = match ($pace) {
            'relaxed' => max(1, min(2, (int) ceil($days / 4))),
            'fast'    => max(3, min(8, (int) ceil($days / 2))),
            default   => max(2, min(5, (int) ceil($days / 3))),
        };
        $numCities = min($numCities, count($candidateCities));

        // --- Pick cities (must-visit first, then fill) ---
        $selected = [];
        foreach ($mustVisit as $mv) {
            foreach ($candidateCities as $c) {
                if (stripos($c['city'], $mv) !== false || stripos($mv, $c['city']) !== false) {
                    if (!in_array($c, $selected, true)) {
                        $selected[] = $c;
                    }
                }
            }
        }
        foreach ($candidateCities as $c) {
            if (count($selected) >= $numCities) break;
            if (!in_array($c, $selected, true)) {
                $selected[] = $c;
            }
        }

        // --- Activity templates by travel style ---
        $actTemplates = [
            'cultural' => [
                ['time' => '09:00', 'name_tpl' => 'Historic Old Town Walking Tour in {city}',  'duration_hours' => 2.5, 'category' => 'cultural', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '14:00', 'name_tpl' => '{city} National Museum & Heritage Sites',   'duration_hours' => 2.0, 'category' => 'cultural', 'included' => false, 'cost_if_optional' => 800],
                ['time' => '17:30', 'name_tpl' => 'Cathedral & Monuments Visit in {city}',     'duration_hours' => 1.5, 'category' => 'cultural', 'included' => true,  'cost_if_optional' => 0],
            ],
            'nature' => [
                ['time' => '08:00', 'name_tpl' => 'Scenic Nature Hike near {city}',            'duration_hours' => 3.0, 'category' => 'nature', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '13:00', 'name_tpl' => 'Boat or Lake Tour around {city}',           'duration_hours' => 2.0, 'category' => 'nature', 'included' => false, 'cost_if_optional' => 1200],
                ['time' => '17:00', 'name_tpl' => 'Sunset at Scenic Viewpoint near {city}',   'duration_hours' => 1.5, 'category' => 'nature', 'included' => true,  'cost_if_optional' => 0],
            ],
            'food' => [
                ['time' => '10:00', 'name_tpl' => '{city} Local Market Food Tour',             'duration_hours' => 2.0, 'category' => 'food', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '13:00', 'name_tpl' => 'Hands-On Cooking Class with Local Chef',   'duration_hours' => 2.5, 'category' => 'food', 'included' => false, 'cost_if_optional' => 2500],
                ['time' => '19:00', 'name_tpl' => 'Dinner at Top-Rated Traditional Restaurant','duration_hours' => 2.0, 'category' => 'food', 'included' => false, 'cost_if_optional' => 1500],
            ],
            'romantic' => [
                ['time' => '10:00', 'name_tpl' => 'Leisurely Stroll Through {city} Old Quarter','duration_hours' => 2.0, 'category' => 'romantic', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '14:00', 'name_tpl' => 'Boat / Gondola Ride in {city}',             'duration_hours' => 1.5, 'category' => 'romantic', 'included' => false, 'cost_if_optional' => 1800],
                ['time' => '19:00', 'name_tpl' => 'Sunset Dinner with Panoramic {city} Views', 'duration_hours' => 2.5, 'category' => 'romantic', 'included' => false, 'cost_if_optional' => 2000],
            ],
            'shopping' => [
                ['time' => '10:00', 'name_tpl' => '{city} Shopping District & Boutiques Tour', 'duration_hours' => 3.0, 'category' => 'shopping', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '15:00', 'name_tpl' => '{city} Designer Outlets & Local Crafts',    'duration_hours' => 2.5, 'category' => 'shopping', 'included' => false, 'cost_if_optional' => 0],
            ],
            'balanced' => [
                ['time' => '09:00', 'name_tpl' => 'Morning City Sightseeing Tour of {city}',   'duration_hours' => 2.5, 'category' => 'cultural', 'included' => true,  'cost_if_optional' => 0],
                ['time' => '13:00', 'name_tpl' => '{city} Food Market & Local Lunch',          'duration_hours' => 1.5, 'category' => 'food',     'included' => true,  'cost_if_optional' => 0],
                ['time' => '16:00', 'name_tpl' => 'Scenic Walk & Photography in {city}',       'duration_hours' => 1.5, 'category' => 'nature',   'included' => true,  'cost_if_optional' => 0],
            ],
        ];

        // Primary and secondary style activity lists
        $primaryStyle  = in_array($styles[0], array_keys($actTemplates)) ? $styles[0] : 'balanced';
        $secondStyle   = count($styles) > 1 && isset($actTemplates[$styles[1]]) ? $styles[1] : null;
        $primaryActs   = $actTemplates[$primaryStyle];
        $secondaryActs = $secondStyle ? $actTemplates[$secondStyle] : [];

        // --- Reserve 2 days for Manila departure + Manila return ---
        // Destination days are sandwiched between those two transit days.
        $destinationDays = max(1, $days - 2);

        // Choose number of cities based on pace (using destination days budget)
        $numCities = match ($pace) {
            'relaxed' => max(1, min(2, (int) ceil($destinationDays / 4))),
            'fast'    => max(3, min(8, (int) ceil($destinationDays / 2))),
            default   => max(2, min(5, (int) ceil($destinationDays / 3))),
        };
        $numCities = min($numCities, count($selected));
        $selected  = array_slice($selected, 0, $numCities);

        // Redistribute destination days across (possibly trimmed) city list
        $base      = (int) floor($destinationDays / count($selected));
        $remainder = $destinationDays - ($base * count($selected));
        $cityDays  = array_fill(0, count($selected), $base);
        $cityDays[0] += $remainder;

        // --- Build day-by-day and transportation ---
        $dayByDay       = [];
        $transportation = [];
        $dayNum         = 1;

        $firstCity    = $selected[0]['city'];
        $lastCityInfo = end($selected);

        // Day 1 — Departure from Manila (Philippines)
        $dayByDay[] = [
            'day'            => 1,
            'city'           => 'Manila',
            'country'        => 'Philippines',
            'accommodation'  => 'In-flight',
            'activities'     => [[
                'time'             => '08:00',
                'name'             => 'Depart Ninoy Aquino International Airport (NAIA) — Flight to ' . $firstCity,
                'duration_hours'   => 12,
                'category'         => 'cultural',
                'included'         => true,
                'cost_if_optional' => 0,
            ]],
            'meals_included' => ['in-flight meal'],
            'free_time'      => 'During flight',
            'overnight'      => 'In-flight',
        ];
        $transportation[] = [
            'from'               => 'Manila (NAIA)',
            'to'                 => $firstCity,
            'day'                => 1,
            'method'             => 'International Flight',
            'duration_hours'     => 12,
            'estimated_cost_php' => 25000,
            'booking_notes'      => 'Book return flight early. Check if stopover is needed for your destination.',
        ];

        $dayNum = 2;
        $prevCity = $firstCity;

        foreach ($selected as $ci => $cityInfo) {
            $cityName    = $cityInfo['city'];
            $countryName = $cityInfo['country'];
            $stayDays    = $cityDays[$ci];

            // Transportation between destination cities
            if ($ci > 0) {
                $transportation[] = [
                    'from'               => $prevCity,
                    'to'                 => $cityName,
                    'day'                => $dayNum,
                    'method'             => 'Train / Flight',
                    'duration_hours'     => 2.5,
                    'estimated_cost_php' => 6000,
                    'booking_notes'      => 'Book in advance for best rates. Check rail pass options if travelling Europe.',
                ];
            }
            $prevCity = $cityName;

            $resolve = fn(array $act) => array_merge($act, [
                'name' => str_replace('{city}', $cityName, $act['name_tpl']),
            ]);

            for ($d = 0; $d < $stayDays; $d++) {
                $isArrival   = ($ci === 0 && $d === 0);
                $isDeparture = ($d === $stayDays - 1) && ($ci < count($selected) - 1);

                $activities = [];

                if ($isArrival) {
                    // Arrival day — lighter, afternoon only
                    $activities[] = ['time' => '14:00', 'name' => 'Arrive in ' . $cityName . ', hotel check-in & orientation walk', 'duration_hours' => 1.5, 'category' => 'cultural', 'included' => true, 'cost_if_optional' => 0];
                    $activities[] = $resolve(end($primaryActs));
                } elseif ($isDeparture) {
                    // Inter-city departure day — one morning activity then transfer
                    $activities[] = $resolve($primaryActs[0]);
                    $nextCity = $selected[$ci + 1]['city'];
                    $activities[] = ['time' => '14:00', 'name' => 'Check-out & transfer to ' . $nextCity, 'duration_hours' => 0.5, 'category' => 'cultural', 'included' => true, 'cost_if_optional' => 0];
                } else {
                    // Full day — primary style activities
                    foreach ($primaryActs as $act) {
                        $activities[] = $resolve($act);
                    }
                    // Alternate days: mix in secondary style
                    if ($secondaryActs && $d % 2 === 1) {
                        $activities[] = $resolve($secondaryActs[0]);
                    }
                }

                // Inject must-visit on day 2 of first city
                if ($ci === 0 && $d === 1 && !empty($mustVisit)) {
                    array_unshift($activities, [
                        'time'             => '09:00',
                        'name'             => 'Visit ' . $mustVisit[0],
                        'duration_hours'   => 2.5,
                        'category'         => 'must_visit',
                        'included'         => true,
                        'cost_if_optional' => 0,
                    ]);
                }

                $dayByDay[] = [
                    'day'            => $dayNum,
                    'city'           => $cityName,
                    'country'        => $countryName,
                    'accommodation'  => $hotelTier . ' hotel in ' . $cityName,
                    'activities'     => array_values($activities),
                    'meals_included' => ['breakfast'],
                    'free_time'      => '12:30–13:30, 20:00 onwards',
                    'overnight'      => $cityName,
                ];
                $dayNum++;
            }
        }

        // Last day — return flight to Manila (Philippines)
        $returnCity    = $lastCityInfo['city'];
        $returnCountry = $lastCityInfo['country'];
        $dayByDay[] = [
            'day'            => $days,
            'city'           => $returnCity,
            'country'        => $returnCountry,
            'accommodation'  => 'In-flight',
            'activities'     => [[
                'time'             => '10:00',
                'name'             => 'Check-out & transfer to airport — Return flight to Manila Ninoy Aquino International Airport (NAIA)',
                'duration_hours'   => 14,
                'category'         => 'cultural',
                'included'         => true,
                'cost_if_optional' => 0,
            ]],
            'meals_included' => ['in-flight meal'],
            'free_time'      => 'During flight',
            'overnight'      => 'In-flight',
        ];
        $transportation[] = [
            'from'               => $returnCity,
            'to'                 => 'Manila (NAIA)',
            'day'                => $days,
            'method'             => 'International Flight',
            'duration_hours'     => 12,
            'estimated_cost_php' => 0,
            'booking_notes'      => 'Return flight included in outbound booking.',
        ];

        // --- Tour name & budget values ---
        $budgetLabel  = match (true) {
            str_starts_with($budget, '80000')  || str_starts_with($budget, '100000') => 'Budget',
            str_starts_with($budget, '200000') || str_starts_with($budget, '250000')
                || str_starts_with($budget, '280000') || $budget === '280000+'       => 'Premium',
            default                                                                   => 'Standard',
        };
        $countryLabel = count($countries) === 1
            ? $countries[0]
            : implode(' & ', array_slice($countries, 0, 2));
        $tourName     = $days . '-Day ' . $budgetLabel . ' ' . $countryLabel . ' Tour';

        $budgetFloor = (int) str_replace([',', ' ', '+'], '', explode('-', $budget)[0] ?? '140000');

        $styleLabel   = implode(', ', $styles);
        $countryLabel2 = implode(', ', $countries);

        return [
            'itinerary' => [
                'tour_name'                => $tourName,
                'total_days'               => $days,
                'cities_count'             => count($selected),
                'route_type'               => 'linear',
                'estimated_cost_breakdown' => [
                    'base_package'   => $budgetFloor,
                    'optional_tours' => (int) ($budgetFloor * 0.15),
                    'total_max'      => (int) ($budgetFloor * 1.15),
                    'currency'       => 'PHP',
                ],
                'day_by_day'               => $dayByDay,
                'transportation'           => $transportation,
                'suggested_optional_tours' => [],
                'customization_options'    => [
                    'can_extend_days'        => true,
                    'can_swap_cities'        => array_column($selected, 'city'),
                    'can_adjust_pace'        => true,
                    'upgradeable_to_premium' => true,
                ],
            ],
            'ai_explanation' => 'AI service is currently unavailable. We\'ve generated your ' . $days . '-day itinerary based on your preferences: ' . $countryLabel2 . ' · ' . $styleLabel . ' style · ' . $pace . ' pace. You can customise every city, activity and detail in the builder below.',
        ];
    }
}
