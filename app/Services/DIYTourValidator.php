<?php

namespace App\Services;

/**
 * Pre-booking validation service for DIY tour itineraries.
 * Returns issues (blocking) and warnings (advisory) with a quality score.
 */
class DIYTourValidator
{
    private const BAD_SEASON_MAP = [
        'Venice'     => ['November', 'December'],       // acqua alta
        'Swiss Alps' => ['November', 'December', 'March', 'April'], // closed passes
        'Santorini'  => ['January', 'February'],         // limited services
        'Dubrovnik'  => ['July', 'August'],              // extreme overcrowding
    ];

    private const BEST_MONTHS_MAP = [
        'Venice'     => 'May, June, September, October',
        'Swiss Alps' => 'May–October',
        'Santorini'  => 'May–October',
        'Dubrovnik'  => 'May, June, September, October',
    ];

    /**
     * Run all validation checks on a completed itinerary.
     *
     * @param  array  $itinerary         Full itinerary_data JSON
     * @param  array  $preferences       User preferences JSON
     * @return array{
     *   is_valid: bool,
     *   issues: array,
     *   warnings: array,
     *   good_points: array,
     *   overall_score: int,
     *   recommendation: string
     * }
     */
    public function validate(array $itinerary, array $preferences): array
    {
        $issues   = [];
        $warnings = [];
        $good     = [];

        // 1. Travel time feasibility
        foreach (($itinerary['transportation'] ?? []) as $transfer) {
            $hours = (float) ($transfer['duration_hours'] ?? 0);
            if ($hours > 6) {
                $warnings[] = [
                    'severity'   => 'medium',
                    'message'    => "Long travel day: {$transfer['from']} → {$transfer['to']} ({$hours}h)",
                    'suggestion' => 'Consider adding an overnight stop or taking a direct flight.',
                ];
            }
        }

        // 2. City duration adequacy
        $cityDays = [];
        foreach (($itinerary['day_by_day'] ?? []) as $day) {
            $cityDays[$day['city']][] = $day['day'];
        }
        foreach ($cityDays as $city => $days) {
            $actCount = 0;
            foreach (($itinerary['day_by_day'] ?? []) as $d) {
                if ($d['city'] === $city) {
                    $actCount += count($d['activities'] ?? []);
                }
            }
            $stayDays = count($days);
            if ($stayDays > 0 && $actCount / $stayDays > 4) {
                $warnings[] = [
                    'severity'   => 'high',
                    'message'    => "{$city}: {$actCount} activities in {$stayDays} days is very rushed.",
                    'suggestion' => 'Add 1–2 more days or remove ' . ($actCount - $stayDays * 3) . ' activities.',
                    'action_add_day'  => $city,
                ];
            } elseif ($stayDays > 0 && $actCount / $stayDays < 1.5 && $stayDays > 1) {
                $warnings[] = [
                    'severity'   => 'low',
                    'message'    => "{$city}: only {$actCount} activities in {$stayDays} days.",
                    'suggestion' => 'Add more activities or reduce the stay to ' . max(1, (int) ceil($actCount / 2)) . ' days.',
                ];
            }
        }

        // 3. Budget realism
        $totalCost   = (float) ($preferences['_estimated_cost'] ?? 0);
        $budgetRange = $preferences['budget_range'] ?? '140000-200000';
        [$budgetMin, $budgetMax] = array_map(
            'intval',
            explode('-', str_replace([',', ' '], '', $budgetRange) . '-0')
        );
        if ($totalCost > 0 && $budgetMax > 0 && $totalCost > $budgetMax * 1.1) {
            $overage  = (int) round($totalCost - $budgetMax);
            $issues[] = [
                'severity'   => 'critical',
                'message'    => 'Tour cost (₱' . number_format($totalCost) . ') exceeds your budget by ₱' . number_format($overage) . '.',
                'suggestion' => 'Reduce optional tours, downgrade hotel tiers, or adjust dates to shoulder season.',
            ];
        }

        // 4. Seasonal appropriateness
        $travelMonth = $preferences['travel_month'] ?? null;
        if ($travelMonth) {
            foreach ($cityDays as $city => $days) {
                foreach (self::BAD_SEASON_MAP as $pattern => $badMonths) {
                    if (stripos($city, $pattern) !== false && in_array($travelMonth, $badMonths, true)) {
                        $bestMonths = self::BEST_MONTHS_MAP[$pattern] ?? 'Spring or Autumn';
                        $warnings[] = [
                            'severity'   => 'medium',
                            'message'    => "{$city} in {$travelMonth}: potential seasonal issues.",
                            'suggestion' => "Best months: {$bestMonths}.",
                        ];
                    }
                }
            }
        }

        // 5. Must-visit coverage
        $mustVisit = (array) ($preferences['must_visit'] ?? []);
        $uncovered = [];
        $allDayText = implode(', ', array_column($itinerary['day_by_day'] ?? [], 'city'));
        foreach ($mustVisit as $place) {
            if (!empty($place) && stripos($allDayText, $place) === false) {
                $uncovered[] = $place;
            }
        }
        if (!empty($uncovered)) {
            $warnings[] = [
                'severity'   => 'high',
                'message'    => 'Must-visit places not in your itinerary: ' . implode(', ', $uncovered) . '.',
                'suggestion' => 'Add these destinations or confirm you are OK skipping them.',
            ];
        }

        // 6. Visa reminder for Schengen
        $countries = (array) ($preferences['countries'] ?? []);
        $schengen  = ['France', 'Switzerland', 'Italy', 'Germany', 'Austria', 'Spain', 'Netherlands', 'Belgium', 'Portugal', 'Greece', 'Czech Republic', 'Hungary'];
        if (!empty(array_intersect($countries, $schengen))) {
            $warnings[] = [
                'severity'   => 'high',
                'message'    => 'A Schengen visa is required for the countries in your itinerary.',
                'suggestion' => 'Apply at least 45 days before departure (processing usually 15–30 days).',
            ];
        }

        // 7. Good points
        if (empty($uncovered)) {
            $good[] = 'All must-visit destinations included.';
        }
        if (empty($issues)) {
            $good[] = 'Tour cost within budget.';
        }
        if (!empty($itinerary['day_by_day'])) {
            $good[] = 'Itinerary has detailed day-by-day activities.';
        }

        $score = $this->scoreItinerary($issues, $warnings, $itinerary);

        return [
            'is_valid'       => empty($issues),
            'issues'         => $issues,
            'warnings'       => $warnings,
            'good_points'    => $good,
            'overall_score'  => $score,
            'recommendation' => $this->buildRecommendation($score, $issues, $warnings),
        ];
    }

    private function scoreItinerary(array $issues, array $warnings, array $itinerary): int
    {
        $score = 100;

        foreach ($issues as $issue) {
            $score -= 20;
        }
        foreach ($warnings as $w) {
            $deduct = match($w['severity'] ?? 'low') {
                'high'   => 8,
                'medium' => 4,
                default  => 2,
            };
            $score -= $deduct;
        }
        if (!empty($itinerary['day_by_day'])) $score += 5;
        if (!empty($itinerary['transportation'])) $score += 5;

        return max(0, min(100, $score));
    }

    private function buildRecommendation(int $score, array $issues, array $warnings): string
    {
        if (!empty($issues)) {
            return 'There are critical issues that must be resolved before booking. Please review the issues above.';
        }
        if ($score >= 90) {
            return 'Excellent! Your itinerary is well-optimised — go ahead and request a quote.';
        }
        if ($score >= 75) {
            return "Your tour is {$score}% optimised. Addressing the warnings above will improve your experience.";
        }
        return "Your tour scores {$score}/100. We recommend reviewing the warnings before booking to ensure the best trip.";
    }
}
