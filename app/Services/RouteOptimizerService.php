<?php

namespace App\Services;

/**
 * Pure-algorithm route optimizer.
 * Scores city sequences and recommends the optimal order.
 */
class RouteOptimizerService
{
    // Approximate coordinates for cities worldwide
    private const CITY_COORDS = [
        // Europe
        'Paris'         => [48.8566,  2.3522],
        'Amsterdam'     => [52.3676,  4.9041],
        'Brussels'      => [50.8503,  4.3517],
        'London'        => [51.5074, -0.1278],
        'Barcelona'     => [41.3851,  2.1734],
        'Madrid'        => [40.4168, -3.7038],
        'Lisbon'        => [38.7223, -9.1393],
        'Porto'         => [41.1579, -8.6291],
        'Lyon'          => [45.7640,  4.8357],
        'Marseille'     => [43.2965,  5.3698],
        'Nice'          => [43.7102,  7.2620],
        'Monaco'        => [43.7384,  7.4246],
        'Geneva'        => [46.2044,  6.1432],
        'Zurich'        => [47.3769,  8.5417],
        'Lucerne'       => [47.0502,  8.3093],
        'Interlaken'    => [46.6863,  7.8632],
        'Bern'          => [46.9480,  7.4474],
        'Milan'         => [45.4654,  9.1859],
        'Venice'        => [45.4408, 12.3155],
        'Florence'      => [43.7696, 11.2558],
        'Rome'          => [41.9028, 12.4964],
        'Naples'        => [40.8522, 14.2681],
        'Vienna'        => [48.2082, 16.3738],
        'Salzburg'      => [47.8095, 13.0550],
        'Innsbruck'     => [47.2682, 11.3923],
        'Prague'        => [50.0755, 14.4378],
        'Budapest'      => [47.4979, 19.0402],
        'Krakow'        => [50.0647, 19.9450],
        'Berlin'        => [52.5200, 13.4050],
        'Munich'        => [48.1351, 11.5820],
        'Hamburg'       => [53.5753, 10.0153],
        'Frankfurt'     => [50.1109,  8.6821],
        'Cologne'       => [50.9333,  6.9500],
        'Copenhagen'    => [55.6761, 12.5683],
        'Stockholm'     => [59.3293, 18.0686],
        'Oslo'          => [59.9139, 10.7522],
        'Dubrovnik'     => [42.6507, 18.0944],
        'Athens'        => [37.9838, 23.7275],
        'Santorini'     => [36.3932, 25.4615],
        'Seville'       => [37.3891, -5.9845],
        'Granada'       => [37.1773, -3.5986],
        // Asia
        'Tokyo'         => [35.6762, 139.6503],
        'Osaka'         => [34.6937, 135.5023],
        'Kyoto'         => [35.0116, 135.7681],
        'Seoul'         => [37.5665, 126.9780],
        'Bangkok'       => [13.7563, 100.5018],
        'Chiang Mai'    => [18.7883, 98.9853],
        'Phuket'        => [7.8804, 98.3923],
        'Bali'          => [-8.3405, 115.0920],
        'Jakarta'       => [-6.2088, 106.8456],
        'Singapore'     => [1.3521, 103.8198],
        'Kuala Lumpur'  => [3.1390, 101.6869],
        'Manila'        => [14.5995, 120.9842],
        'Cebu'          => [10.3157, 123.8854],
        'Hanoi'         => [21.0285, 105.8542],
        'Ho Chi Minh'   => [10.8231, 106.6297],
        'Hoi An'        => [15.8801, 108.3380],
        'Beijing'       => [39.9042, 116.4074],
        'Shanghai'      => [31.2304, 121.4737],
        'Hong Kong'     => [22.3193, 114.1694],
        'Mumbai'        => [19.0760, 72.8777],
        'Delhi'         => [28.7041, 77.1025],
        'Kathmandu'     => [27.7172, 85.3240],
        'Colombo'       => [6.9271, 79.8612],
        'Siem Reap'     => [13.3671, 103.8448],
        'Yangon'        => [16.8661, 96.1951],
        'Male'          => [4.1755, 73.5093],
        // Middle East
        'Dubai'         => [25.2048, 55.2708],
        'Abu Dhabi'     => [24.4539, 54.3773],
        'Istanbul'      => [41.0082, 28.9784],
        'Cappadocia'    => [38.6431, 34.8289],
        'Amman'         => [31.9454, 35.9284],
        'Petra'         => [30.3285, 35.4444],
        'Doha'          => [25.2854, 51.5310],
        'Tel Aviv'      => [32.0853, 34.7818],
        'Jerusalem'     => [31.7683, 35.2137],
        'Muscat'        => [23.5880, 58.3829],
        // Americas
        'New York'      => [40.7128, -74.0060],
        'Los Angeles'   => [34.0522, -118.2437],
        'Miami'         => [25.7617, -80.1918],
        'San Francisco' => [37.7749, -122.4194],
        'Las Vegas'     => [36.1699, -115.1398],
        'Chicago'       => [41.8781, -87.6298],
        'Washington DC' => [38.9072, -77.0369],
        'Toronto'       => [43.6532, -79.3832],
        'Vancouver'     => [49.2827, -123.1207],
        'Mexico City'   => [19.4326, -99.1332],
        'Cancun'        => [21.1619, -86.8515],
        'Rio de Janeiro'=> [-22.9068, -43.1729],
        'Buenos Aires'  => [-34.6037, -58.3816],
        'Lima'          => [-12.0464, -77.0428],
        'Cusco'         => [-13.5320, -71.9675],
        'Bogota'        => [4.7110,  -74.0721],
        'Santiago'      => [-33.4489, -70.6693],
        // Africa & Oceania
        'Cairo'         => [30.0444, 31.2357],
        'Marrakech'     => [31.6295, -7.9811],
        'Casablanca'    => [33.5731, -7.5898],
        'Nairobi'       => [-1.2864, 36.8172],
        'Cape Town'     => [-33.9249, 18.4241],
        'Johannesburg'  => [-26.2041, 28.0473],
        'Sydney'        => [-33.8688, 151.2093],
        'Melbourne'     => [-37.8136, 144.9631],
        'Auckland'      => [-36.8485, 174.7633],
        'Queenstown'    => [-45.0312, 168.6626],
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
