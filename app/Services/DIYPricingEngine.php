<?php

namespace App\Services;

/**
 * Calculates real-time cost breakdown for a DIY tour itinerary.
 *
 * All prices are in PHP. The engine mirrors the spec's
 * DIYTourPricingEngine JavaScript class on the server side.
 */
class DIYPricingEngine
{
    // Base nightly rates per hotel tier per city (in PHP).
    // These reflect ~€80-130/night converted at 60 PHP/EUR with variation.
    private const HOTEL_RATES = [
        '3-star' => ['default' => 5_400],   // ~€90
        '4-star' => ['default' => 7_800],   // ~€130
        '5-star' => ['default' => 13_200],  // ~€220
    ];

    // Rough base inter-city train fares per person (PHP)
    private const TRAIN_ROUTES = [
        'paris-lucerne'    => 7_800,
        'paris-zurich'     => 7_200,
        'paris-milan'      => 9_000,
        'lucerne-milan'    => 5_400,
        'milan-venice'     => 3_600,
        'milan-florence'   => 5_400,
        'venice-florence'  => 4_200,
        'venice-rome'      => 7_800,
        'florence-rome'    => 3_900,
        'paris-barcelona'  => 8_400,
        'barcelona-madrid' => 3_600,
        'paris-amsterdam'  => 6_600,
        'amsterdam-berlin' => 7_200,
        'berlin-vienna'    => 7_800,
        'vienna-prague'    => 5_400,
        'default'          => 6_000,
    ];

    private const LOCAL_TRANSPORT_PER_DAY_PER_PERSON = 500;
    private const GUIDE_FEE_PER_DAY                  = 3_500;
    private const VISA_INSURANCE_PER_PERSON           = 3_000;
    private const MEALS_INCLUDED_RATE_PER_DAY         = 900;   // breakfast + partial dinners avg
    private const ACTIVITY_DEFAULT_COST               = 1_200; // per person per included activity

    /**
     * Calculate a full pricing breakdown for an itinerary.
     *
     * @param  array  $itineraryData  The full itinerary JSON from the AI
     * @param  int    $groupSize      Number of travelers
     * @param  string $travelMonth   e.g. 'June' — for seasonal multiplier
     * @return array{
     *   accommodation: int,
     *   transportation: int,
     *   activities: int,
     *   meals: int,
     *   guide_services: int,
     *   visa_insurance: int,
     *   subtotal: int,
     *   markup: int,
     *   total_per_person: int,
     *   total_group: int,
     *   breakdown: array
     * }
     */
    public function calculate(array $itineraryData, int $groupSize = 2, string $travelMonth = 'June'): array
    {
        $groupSize  = max(1, $groupSize);
        $dayByDay   = $itineraryData['day_by_day']      ?? [];
        $transfers  = $itineraryData['transportation']  ?? [];
        $totalDays  = (int) ($itineraryData['total_days'] ?? count($dayByDay));

        $accommodation    = $this->calcAccommodation($dayByDay, $groupSize, $travelMonth);
        $transportation   = $this->calcTransportation($transfers, $totalDays, $groupSize);
        $activities       = $this->calcActivities($dayByDay, $groupSize);
        $meals            = $this->calcMeals($dayByDay, $groupSize);
        $guideServices    = $this->calcGuideServices($totalDays, $groupSize);
        $visaInsurance    = self::VISA_INSURANCE_PER_PERSON * $groupSize;

        $subtotal = $accommodation + $transportation + $activities + $meals + $guideServices + $visaInsurance;

        $markupPct = $subtotal < 100_000 ? 0.20 : ($subtotal < 200_000 ? 0.18 : 0.15);
        $markup    = (int) round($subtotal * $markupPct);

        $total      = $subtotal + $markup;
        $perPerson  = $groupSize > 0 ? (int) ceil($total / $groupSize) : $total;

        // Build accommodation line-item detail
        $accDetail = [];
        $cityNights = [];
        foreach ($dayByDay as $day) {
            $city = $day['city'] ?? 'Unknown';
            $tier = 'default'; // city-level override possible in future
            $cityNights[$city]['nights'] = ($cityNights[$city]['nights'] ?? 0) + 1;
            $cityNights[$city]['tier']   = $tier;
        }

        return [
            'accommodation'   => $accommodation,
            'transportation'  => $transportation,
            'activities'      => $activities,
            'meals'           => $meals,
            'guide_services'  => $guideServices,
            'visa_insurance'  => $visaInsurance,
            'subtotal'        => $subtotal,
            'markup'          => $markup,
            'markup_percent'  => (int) round($markupPct * 100),
            'total_per_person'=> $perPerson,
            'total_group'     => $total,
            'group_size'      => $groupSize,
            'city_breakdown'  => $cityNights,
        ];
    }

    /**
     * Suggest cost-saving or cost-adding changes based on budget delta.
     *
     * @param  int   $currentCost
     * @param  int   $targetBudget
     * @param  array $itinerary
     * @return array List of suggestion objects
     */
    public function suggestBudgetAdjustments(int $currentCost, int $targetBudget, array $itinerary): array
    {
        $diff        = $targetBudget - $currentCost;
        $suggestions = [];

        if ($diff < -10_000) {
            // Over budget — suggest reductions
            $suggestions[] = [
                'type'   => 'accommodation_downgrade',
                'action' => 'Switch to 3-star hotels for standard cities',
                'savings'=> 15_000,
                'impact' => 'Slight comfort reduction, still central locations',
            ];
            $optionals = collect($itinerary['suggested_optional_tours'] ?? [])
                ->sortByDesc('cost_php')->first();
            if ($optionals) {
                $suggestions[] = [
                    'type'   => 'remove_optional',
                    'action' => 'Remove optional tour: ' . $optionals['name'],
                    'savings'=> (int) ($optionals['cost_php'] ?? 0),
                    'impact' => 'Can do self-guided alternative',
                ];
            }
            $suggestions[] = [
                'type'   => 'timing',
                'action' => 'Travel in shoulder season (May or September)',
                'savings'=> 12_000,
                'impact' => 'Fewer crowds + lower hotel rates',
            ];
        } elseif ($diff > 20_000) {
            // Under budget — suggest upgrades
            $suggestions[] = [
                'type'   => 'add_activity',
                'action' => 'Add a premium cooking class or wine tasting',
                'cost'   => 8_500,
                'value'  => 'Memorable experience, 95% user satisfaction',
            ];
            $suggestions[] = [
                'type'   => 'accommodation_upgrade',
                'action' => 'Upgrade to 5-star for your first and last nights',
                'cost'   => 18_000,
                'value'  => 'Luxury arrival + departure experience',
            ];
        }

        return $suggestions;
    }

    // -------------------------------------------------------------------------
    // Private calculation helpers
    // -------------------------------------------------------------------------

    private function calcAccommodation(array $dayByDay, int $groupSize, string $month): int
    {
        $total  = 0;
        $rooms  = max(1, (int) ceil($groupSize / 2));
        $multiplier = $this->seasonalMultiplier($month);

        foreach ($dayByDay as $day) {
            $tier = 'default';
            $rate = self::HOTEL_RATES['4-star'][$tier];   // default 4-star
            $total += (int) round($rate * $rooms * $multiplier);
        }

        return $total;
    }

    private function calcTransportation(array $transfers, int $totalDays, int $groupSize): int
    {
        $total = 0;

        foreach ($transfers as $t) {
            $from  = strtolower(str_replace(' ', '', $t['from'] ?? ''));
            $to    = strtolower(str_replace(' ', '', $t['to'] ?? ''));
            $key   = $from . '-' . $to;
            $altKey = $to . '-' . $from;

            $base = self::TRAIN_ROUTES[$key]
                ?? self::TRAIN_ROUTES[$altKey]
                ?? (int) (($t['estimated_cost_php'] ?? self::TRAIN_ROUTES['default']));

            $total += $base * $groupSize;
        }

        // Local transportation (metro/taxi)
        $total += self::LOCAL_TRANSPORT_PER_DAY_PER_PERSON * $totalDays * $groupSize;

        return $total;
    }

    private function calcActivities(array $dayByDay, int $groupSize): int
    {
        $total = 0;

        foreach ($dayByDay as $day) {
            foreach (($day['activities'] ?? []) as $act) {
                if ($act['included'] ?? false) {
                    $cost   = (int) ($act['cost_if_optional'] ?? 0);
                    $total += ($cost > 0 ? $cost : self::ACTIVITY_DEFAULT_COST) * $groupSize;
                }
            }
        }

        return $total;
    }

    private function calcMeals(array $dayByDay, int $groupSize): int
    {
        return self::MEALS_INCLUDED_RATE_PER_DAY * count($dayByDay) * $groupSize;
    }

    private function calcGuideServices(int $totalDays, int $groupSize): int
    {
        // Guide fee is shared across the group, distributed per person
        return (int) ceil((self::GUIDE_FEE_PER_DAY * $totalDays) / max(1, $groupSize)) * $groupSize;
    }

    private function seasonalMultiplier(string $month): float
    {
        $peak       = ['July', 'August'];
        $shoulder   = ['June', 'September'];
        $offSeason  = ['November', 'December', 'January', 'February'];

        if (in_array($month, $peak, true))      return 1.3;
        if (in_array($month, $shoulder, true))  return 1.1;
        if (in_array($month, $offSeason, true)) return 0.85;
        return 1.0; // spring / autumn standard
    }
}
