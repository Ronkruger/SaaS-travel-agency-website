<?php

namespace App\Services;

/**
 * Pure-algorithm route optimizer.
 * Scores city sequences and recommends the optimal order.
 */
class RouteOptimizerService
{
    // Approximate coordinates for common European cities
    private const CITY_COORDS = [
        'Paris'       => [48.8566, 2.3522],
        'Amsterdam'   => [52.3676, 4.9041],
        'Brussels'    => [50.8503, 4.3517],
        'London'      => [51.5074, -0.1278],
        'Barcelona'   => [41.3851, 2.1734],
        'Madrid'      => [40.4168, -3.7038],
        'Lisbon'      => [38.7223, -9.1393],
        'Lyon'        => [45.7640, 4.8357],
        'Marseille'   => [43.2965, 5.3698],
        'Nice'        => [43.7102, 7.2620],
        'Monaco'      => [43.7384, 7.4246],
        'Geneva'      => [46.2044, 6.1432],
        'Zurich'      => [47.3769, 8.5417],
        'Lucerne'     => [47.0502, 8.3093],
        'Interlaken'  => [46.6863, 7.8632],
        'Bern'        => [46.9480, 7.4474],
        'Milan'       => [45.4654, 9.1859],
        'Venice'      => [45.4408, 12.3155],
        'Florence'    => [43.7696, 11.2558],
        'Rome'        => [41.9028, 12.4964],
        'Naples'      => [40.8522, 14.2681],
        'Vienna'      => [48.2082, 16.3738],
        'Salzburg'    => [47.8095, 13.0550],
        'Innsbruck'   => [47.2682, 11.3923],
        'Prague'      => [50.0755, 14.4378],
        'Budapest'    => [47.4979, 19.0402],
        'Krakow'      => [50.0647, 19.9450],
        'Berlin'      => [52.5200, 13.4050],
        'Munich'      => [48.1351, 11.5820],
        'Hamburg'     => [53.5753, 10.0153],
        'Frankfurt'   => [50.1109, 8.6821],
        'Cologne'     => [50.9333, 6.9500],
        'Copenhagen'  => [55.6761, 12.5683],
        'Stockholm'   => [59.3293, 18.0686],
        'Oslo'        => [59.9139, 10.7522],
        'Dubrovnik'   => [42.6507, 18.0944],
        'Athens'      => [37.9838, 23.7275],
        'Santorini'   => [36.3932, 25.4615],
        'Lisbon'      => [38.7223, -9.1393],
        'Porto'       => [41.1579, -8.6291],
        'Seville'     => [37.3891, -5.9845],
        'Granada'     => [37.1773, -3.5986],
    ];

    /**
     * Suggest an optimized city ordering, minimizing total travel distance.
     * Uses a greedy nearest-neighbour heuristic (adequate for ≤12 cities).
     *
     * @param  array  $cities       Array of city name strings
     * @param  array  $mustVisitOrder  Cities that prefer to appear early (in order)
     * @return array{
     *   suggestion: array|null,
     *   reason: string,
     *   savings_km: int,
     *   confidence: float
     * }
     */
    public function optimizeRoute(array $cities, array $mustVisitOrder = []): array
    {
        if (count($cities) <= 2) {
            return ['suggestion' => null, 'reason' => 'Route already optimal.', 'savings_km' => 0, 'confidence' => 1.0];
        }

        $currentDistance  = $this->totalDistance($cities);
        $optimized        = $this->nearestNeighbour($cities, $mustVisitOrder);
        $optimizedDistance = $this->totalDistance($optimized);

        if ($optimizedDistance >= $currentDistance * 0.97) {
            // Less than 3% improvement — not worth suggesting
            return ['suggestion' => null, 'reason' => 'Current route is already near-optimal.', 'savings_km' => 0, 'confidence' => 0.95];
        }

        $savingsKm = max(0, (int) round($currentDistance - $optimizedDistance));

        return [
            'suggestion'  => $optimized,
            'reason'      => "Re-ordering saves approximately {$savingsKm} km of travel.",
            'savings_km'  => $savingsKm,
            'confidence'  => 0.88,
        ];
    }

    /**
     * Return cities reachable within a max travel time from a given city.
     * Approximates using straight-line distance (1 km ≈ 1.2 min by train avg).
     *
     * @param  string  $fromCity
     * @param  float   $maxHours
     * @return array  City names
     */
    public function citiesReachableWithin(string $fromCity, float $maxHours = 4.0): array
    {
        $fromCoords = self::CITY_COORDS[$fromCity] ?? null;
        if (!$fromCoords) return [];

        $maxKm   = $maxHours * 250; // ~250 km/h avg high-speed train
        $result  = [];

        foreach (self::CITY_COORDS as $city => $coords) {
            if ($city === $fromCity) continue;
            if ($this->haversineKm($fromCoords, $coords) <= $maxKm) {
                $result[] = $city;
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function nearestNeighbour(array $cities, array $mustVisitOrder): array
    {
        // Start from first must-visit or first city
        $start     = !empty($mustVisitOrder) ? $mustVisitOrder[0] : $cities[0];
        $remaining = array_values(array_filter($cities, fn($c) => $c !== $start));
        $route     = [$start];

        // Honour must-visit order for subsequent forced cities
        $mustQueue = array_slice($mustVisitOrder, 1);

        while (!empty($remaining)) {
            if (!empty($mustQueue)) {
                $next = array_shift($mustQueue);
                if (in_array($next, $remaining, true)) {
                    $route[]   = $next;
                    $remaining = array_values(array_filter($remaining, fn($c) => $c !== $next));
                    continue;
                }
            }

            $last    = end($route);
            $nearest = $this->findNearest($last, $remaining);
            $route[] = $nearest;
            $remaining = array_values(array_filter($remaining, fn($c) => $c !== $nearest));
        }

        return $route;
    }

    private function findNearest(string $from, array $cities): string
    {
        $fromCoords = self::CITY_COORDS[$from] ?? null;
        $best = $cities[0];
        $bestDist = PHP_FLOAT_MAX;

        foreach ($cities as $city) {
            $coords = self::CITY_COORDS[$city] ?? null;
            if (!$coords || !$fromCoords) continue;
            $d = $this->haversineKm($fromCoords, $coords);
            if ($d < $bestDist) {
                $bestDist = $d;
                $best     = $city;
            }
        }

        return $best;
    }

    private function totalDistance(array $cities): float
    {
        $total = 0.0;

        for ($i = 0; $i < count($cities) - 1; $i++) {
            $a = self::CITY_COORDS[$cities[$i]]     ?? null;
            $b = self::CITY_COORDS[$cities[$i + 1]] ?? null;
            if ($a && $b) {
                $total += $this->haversineKm($a, $b);
            }
        }

        return $total;
    }

    /** Haversine formula — returns great-circle distance in kilometres. */
    private function haversineKm(array $a, array $b): float
    {
        [$lat1, $lon1] = $a;
        [$lat2, $lon2] = $b;

        $R     = 6371.0;
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lon2 - $lon1);
        $sinDl = sin($dLat / 2);
        $sinDo = sin($dLon / 2);
        $a_    = $sinDl * $sinDl + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * $sinDo * $sinDo;
        $c     = 2 * atan2(sqrt($a_), sqrt(1 - $a_));

        return $R * $c;
    }
}
