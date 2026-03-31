<?php

namespace App\Http\Controllers;

use App\Models\Tour;

class DestinationsController extends Controller
{
    private const CONTINENT_META = [
        'Europe'        => ['icon' => 'fa-monument',    'gradient' => 'linear-gradient(135deg,#1e3a8a 0%,#3b82f6 100%)'],
        'Asia'          => ['icon' => 'fa-torii-gate',  'gradient' => 'linear-gradient(135deg,#78350f 0%,#f59e0b 100%)'],
        'Africa'        => ['icon' => 'fa-sun',         'gradient' => 'linear-gradient(135deg,#7f1d1d 0%,#ef4444 100%)'],
        'Americas'      => ['icon' => 'fa-landmark',    'gradient' => 'linear-gradient(135deg,#064e3b 0%,#10b981 100%)'],
        'North America' => ['icon' => 'fa-landmark',    'gradient' => 'linear-gradient(135deg,#064e3b 0%,#10b981 100%)'],
        'South America' => ['icon' => 'fa-mountain',    'gradient' => 'linear-gradient(135deg,#4c1d95 0%,#8b5cf6 100%)'],
        'Oceania'       => ['icon' => 'fa-water',       'gradient' => 'linear-gradient(135deg,#164e63 0%,#06b6d4 100%)'],
        'Middle East'   => ['icon' => 'fa-mosque',      'gradient' => 'linear-gradient(135deg,#78350f 0%,#d97706 100%)'],
        'Other'         => ['icon' => 'fa-globe',       'gradient' => 'linear-gradient(135deg,#1f2937 0%,#6b7280 100%)'],
    ];

    public function index()
    {
        $tours = Tour::where('is_active', 1)
            ->whereNotNull('full_stops')
            ->select('id', 'title', 'slug', 'continent', 'full_stops', 'main_image',
                     'regular_price_per_person', 'promo_price_per_person', 'duration_days')
            ->get();

        $destinations = [];

        foreach ($tours as $tour) {
            $continent = trim($tour->continent ?: 'Other');
            $stops     = $tour->full_stops ?: [];
            $seenCity  = [];

            foreach ($stops as $stop) {
                $country = trim($stop['country'] ?? '');
                $city    = trim($stop['city']    ?? '');
                if (!$country || !$city) continue;

                // Init country bucket
                if (!isset($destinations[$continent][$country])) {
                    $destinations[$continent][$country] = [
                        'name'       => $country,
                        'image'      => null,
                        'cities'     => [],
                        'tour_slugs' => [],
                    ];
                }

                // Register tour on country
                $destinations[$continent][$country]['tour_slugs'][$tour->slug] = true;

                // First available image → country cover
                if (!$destinations[$continent][$country]['image']) {
                    $img = !empty($stop['images'][0]) ? $stop['images'][0] : $tour->main_image;
                    $destinations[$continent][$country]['image'] = $img;
                }

                // Add city (deduplicated per country)
                $cityKey = mb_strtolower($country . '::' . $city);
                if (!isset($seenCity[$cityKey])) {
                    $seenCity[$cityKey] = true;
                    $destinations[$continent][$country]['cities'][] = $city;
                }
            }
        }

        // Sort everything alphabetically and compute counts
        ksort($destinations);
        foreach ($destinations as &$countries) {
            ksort($countries);
            foreach ($countries as &$c) {
                sort($c['cities']);
                $c['tour_count'] = count($c['tour_slugs']);
                $c['city_count'] = count($c['cities']);
            }
        }
        unset($countries, $c);

        $continentMeta = self::CONTINENT_META;

        return view('destinations.index', compact('destinations', 'continentMeta'));
    }
}
