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
                'HTTP-Referer'  => config('app.url', 'https://discovergrp.com'),
                'X-Title'       => config('app.name', 'DiscoverGRP'),
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
        return 'Generate a personalized tour itinerary for these user preferences: ' . json_encode($preferences, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Returns a basic fallback itinerary when the AI is unavailable,
     * so the UI is always functional for demo/dev purposes.
     */
    private function fallbackItinerary(array $preferences): array
    {
        $days      = (int) ($preferences['duration_days'] ?? 7);
        $countries = (array) ($preferences['countries'] ?? ['France']);
        $budget    = (int) str_replace([',', ' '], '', explode('-', $preferences['budget_range'] ?? '150000-200000')[0] ?? '150000');

        return [
            'itinerary' => [
                'tour_name'  => 'Custom ' . $days . '-Day Adventure',
                'total_days' => $days,
                'cities_count' => max(1, (int) ($days / 3)),
                'route_type' => 'linear',
                'estimated_cost_breakdown' => [
                    'base_package'   => $budget,
                    'optional_tours' => (int) ($budget * 0.15),
                    'total_max'      => (int) ($budget * 1.15),
                    'currency'       => 'PHP',
                ],
                'day_by_day'   => [],
                'transportation' => [],
                'suggested_optional_tours' => [],
                'customization_options' => [
                    'can_extend_days'        => true,
                    'can_swap_cities'        => [],
                    'can_adjust_pace'        => true,
                    'upgradeable_to_premium' => true,
                ],
            ],
            'ai_explanation' => 'AI service is currently unavailable. A basic itinerary shell has been created — you can build it manually using the editor below.',
        ];
    }
}
