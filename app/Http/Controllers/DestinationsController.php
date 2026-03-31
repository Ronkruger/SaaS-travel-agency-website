<?php

namespace App\Http\Controllers;

use App\Models\Tour;

class DestinationsController extends Controller
{
    // Maps a country name to its continent — used to exclude transit/departure
    // countries (e.g. Philippines from a Europe tour) from the wrong bucket.
    private const COUNTRY_CONTINENT = [
        // Asia
        'Philippines'=>'Asia','Japan'=>'Asia','China'=>'Asia','South Korea'=>'Asia',
        'Thailand'=>'Asia','Vietnam'=>'Asia','Indonesia'=>'Asia','Malaysia'=>'Asia',
        'Singapore'=>'Asia','India'=>'Asia','Nepal'=>'Asia','Maldives'=>'Asia',
        'Sri Lanka'=>'Asia','Cambodia'=>'Asia','Myanmar'=>'Asia','Laos'=>'Asia',
        'Taiwan'=>'Asia','Hong Kong'=>'Asia','Macau'=>'Asia','Mongolia'=>'Asia',
        'Bangladesh'=>'Asia','Pakistan'=>'Asia','Brunei'=>'Asia','East Timor'=>'Asia',
        // Europe
        'France'=>'Europe','Italy'=>'Europe','Spain'=>'Europe','Germany'=>'Europe',
        'United Kingdom'=>'Europe','UK'=>'Europe','Switzerland'=>'Europe',
        'Austria'=>'Europe','Netherlands'=>'Europe','Belgium'=>'Europe',
        'Portugal'=>'Europe','Greece'=>'Europe','Czech Republic'=>'Europe',
        'Poland'=>'Europe','Hungary'=>'Europe','Croatia'=>'Europe',
        'Slovenia'=>'Europe','Slovakia'=>'Europe','Romania'=>'Europe',
        'Bulgaria'=>'Europe','Serbia'=>'Europe','Montenegro'=>'Europe',
        'Albania'=>'Europe','North Macedonia'=>'Europe','Bosnia'=>'Europe',
        'Vatican City'=>'Europe','Vatican'=>'Europe','San Marino'=>'Europe',
        'Monaco'=>'Europe','Luxembourg'=>'Europe','Liechtenstein'=>'Europe',
        'Denmark'=>'Europe','Sweden'=>'Europe','Norway'=>'Europe',
        'Finland'=>'Europe','Iceland'=>'Europe','Ireland'=>'Europe',
        'Scotland'=>'Europe','England'=>'Europe','Wales'=>'Europe',
        'Russia'=>'Europe','Ukraine'=>'Europe','Turkey'=>'Europe',
        'Estonia'=>'Europe','Latvia'=>'Europe','Lithuania'=>'Europe',
        'Malta'=>'Europe','Cyprus'=>'Europe',
        // Americas
        'USA'=>'Americas','United States'=>'Americas','Canada'=>'Americas',
        'Mexico'=>'Americas','Brazil'=>'Americas','Argentina'=>'Americas',
        'Peru'=>'Americas','Colombia'=>'Americas','Chile'=>'Americas',
        'Ecuador'=>'Americas','Bolivia'=>'Americas','Uruguay'=>'Americas',
        'Paraguay'=>'Americas','Venezuela'=>'Americas','Cuba'=>'Americas',
        'Costa Rica'=>'Americas','Panama'=>'Americas','Guatemala'=>'Americas',
        // Africa
        'Egypt'=>'Africa','Morocco'=>'Africa','South Africa'=>'Africa',
        'Kenya'=>'Africa','Tanzania'=>'Africa','Ethiopia'=>'Africa',
        'Tunisia'=>'Africa','Zimbabwe'=>'Africa','Botswana'=>'Africa',
        'Namibia'=>'Africa','Uganda'=>'Africa','Rwanda'=>'Africa',
        'Mauritius'=>'Africa','Madagascar'=>'Africa','Seychelles'=>'Africa',
        // Middle East
        'UAE'=>'Middle East','United Arab Emirates'=>'Middle East',
        'Saudi Arabia'=>'Middle East','Qatar'=>'Middle East',
        'Kuwait'=>'Middle East','Bahrain'=>'Middle East','Oman'=>'Middle East',
        'Jordan'=>'Middle East','Israel'=>'Middle East','Lebanon'=>'Middle East',
        // Oceania
        'Australia'=>'Oceania','New Zealand'=>'Oceania','Fiji'=>'Oceania',
        'Papua New Guinea'=>'Oceania','Samoa'=>'Oceania','Vanuatu'=>'Oceania',
    ];

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

                // Skip stops whose country belongs to a DIFFERENT continent
                // (e.g. Manila/Philippines on a Europe tour = departure, not destination)
                $stopContinent = self::COUNTRY_CONTINENT[$country] ?? null;
                if ($stopContinent !== null && $stopContinent !== $continent) {
                    continue;
                }

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
