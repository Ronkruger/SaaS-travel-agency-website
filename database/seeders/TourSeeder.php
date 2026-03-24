<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;

class TourSeeder extends Seeder // DiscoverGroup seed data
{
    public function run(): void
    {
        Tour::withTrashed()->forceDelete();

        $tours = [
            [
                'title'                    => 'Maldives Paradise Escape',
                'slug'                     => 'maldives-paradise-escape',
                'summary'                  => 'Immerse yourself in crystal-clear waters and pristine white-sand beaches. Exclusive overwater bungalow surrounded by vibrant coral reefs.',
                'short_description'        => 'Luxury overwater stay with snorkeling and water sports.',
                'line'                     => 'Island Luxury',
                'continent'                => 'Asia',
                'duration_days'            => 7,
                'guaranteed_departure'     => false,
                'regular_price_per_person' => 3299.00,
                'promo_price_per_person'   => 2799.00,
                'is_sale_enabled'          => true,
                'travel_window'            => ['start' => '2025-06-01', 'end' => '2025-11-30'],
                'departure_dates'          => [
                    ['start' => '2025-07-10', 'end' => '2025-07-16', 'maxCapacity' => 20, 'currentBookings' => 8,  'isAvailable' => true],
                    ['start' => '2025-08-14', 'end' => '2025-08-20', 'maxCapacity' => 20, 'currentBookings' => 15, 'isAvailable' => true],
                ],
                'highlights' => [
                    'Overwater bungalow accommodation',
                    'Daily snorkeling excursions',
                    'Sunset dolphin cruise',
                    'All-inclusive meals',
                ],
                'full_stops' => [
                    ['city' => 'Male',      'country' => 'Maldives', 'days' => 1],
                    ['city' => 'Ari Atoll', 'country' => 'Maldives', 'days' => 5],
                    ['city' => 'Male',      'country' => 'Maldives', 'days' => 1],
                ],
                'additional_info' => [
                    'countriesVisited' => ['Maldives'],
                    'startingPoint'    => 'Male International Airport',
                    'endingPoint'      => 'Male International Airport',
                ],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival and Transfer',     'description' => 'Arrive at Male Airport and speedboat to your island resort.'],
                    ['day' => 2, 'title' => 'Snorkeling and Beach',     'description' => 'Morning snorkeling then relax on the pristine beach.'],
                    ['day' => 3, 'title' => 'Dolphin Cruise',           'description' => 'Evening sunset cruise to spot wild dolphins.'],
                    ['day' => 4, 'title' => 'Diving Excursion',         'description' => 'Guided dive for certified divers plus submarine tour.'],
                    ['day' => 5, 'title' => 'Island Hopping',           'description' => 'Visit local villages and uninhabited sandbanks.'],
                    ['day' => 6, 'title' => 'Spa and Relaxation',       'description' => 'Complimentary spa treatment and private beach dinner.'],
                    ['day' => 7, 'title' => 'Departure',                'description' => 'Speedboat transfer back to Male for departure.'],
                ],
                'allows_downpayment'             => true,
                'fixed_downpayment_amount'       => 500.00,
                'balance_due_days_before_travel' => 30,
                'is_active'      => true,
                'is_featured'    => true,
                'average_rating' => 4.8,
                'total_reviews'  => 26,
            ],
            [
                'title'                    => 'Nepal Everest Base Camp Trek',
                'slug'                     => 'nepal-everest-base-camp-trek',
                'summary'                  => 'Walk the path to Everest Base Camp at 5364m through Sherpa villages and jaw-dropping Himalayan landscapes.',
                'short_description'        => 'Classic EBC trek through Sherpa villages and Himalayan scenery.',
                'line'                     => 'Adventure Series',
                'continent'                => 'Asia',
                'duration_days'            => 14,
                'guaranteed_departure'     => true,
                'regular_price_per_person' => 2499.00,
                'travel_window'            => ['start' => '2025-03-01', 'end' => '2025-05-31'],
                'departure_dates'          => [
                    ['start' => '2025-03-15', 'end' => '2025-03-28', 'maxCapacity' => 12, 'currentBookings' => 7, 'isAvailable' => true],
                    ['start' => '2025-05-10', 'end' => '2025-05-23', 'maxCapacity' => 12, 'currentBookings' => 3, 'isAvailable' => true],
                ],
                'highlights' => [
                    'Reach Everest Base Camp at 5364m',
                    'Fly to Lukla airport',
                    'Namche Bazaar acclimatization',
                    'Tengboche Monastery visit',
                    'Experienced Sherpa guides',
                ],
                'full_stops' => [
                    ['city' => 'Kathmandu',          'country' => 'Nepal', 'days' => 2],
                    ['city' => 'Lukla',              'country' => 'Nepal', 'days' => 1],
                    ['city' => 'Namche Bazaar',       'country' => 'Nepal', 'days' => 2],
                    ['city' => 'Everest Base Camp',  'country' => 'Nepal', 'days' => 1],
                    ['city' => 'Kathmandu',          'country' => 'Nepal', 'days' => 2],
                ],
                'additional_info' => [
                    'countriesVisited' => ['Nepal'],
                    'startingPoint'    => 'Tribhuvan International Airport, Kathmandu',
                    'endingPoint'      => 'Tribhuvan International Airport, Kathmandu',
                ],
                'itinerary' => [
                    ['day' => 1,  'title' => 'Arrive Kathmandu',     'description' => 'Airport pickup and welcome briefing.'],
                    ['day' => 2,  'title' => 'Kathmandu Sightseeing','description' => 'Pashupatinath, Boudhanath, gear check.'],
                    ['day' => 3,  'title' => 'Fly to Lukla',         'description' => 'Mountain flight to Lukla then trek to Phakding.'],
                    ['day' => 4,  'title' => 'Namche Bazaar',        'description' => 'Trek through pine forests over Hillary Bridge.'],
                    ['day' => 5,  'title' => 'Acclimatization',      'description' => 'Rest day with optional hike to Everest View Hotel.'],
                    ['day' => 10, 'title' => 'Reach Base Camp',      'description' => 'Iconic day reaching EBC at 5364m.'],
                    ['day' => 14, 'title' => 'Departure',            'description' => 'Airport transfer for your onward journey.'],
                ],
                'allows_downpayment'             => true,
                'fixed_downpayment_amount'       => 300.00,
                'balance_due_days_before_travel' => 45,
                'is_active'      => true,
                'is_featured'    => true,
                'average_rating' => 4.9,
                'total_reviews'  => 18,
            ],
            [
                'title'                    => 'Japan Cultural Odyssey',
                'slug'                     => 'japan-cultural-odyssey',
                'summary'                  => 'Ancient temples, futuristic cities, and cherry blossom season across Tokyo, Kyoto and Osaka.',
                'short_description'        => 'Tokyo to Kyoto — temples, sushi, and cherry blossoms.',
                'line'                     => 'Cultural Immersion',
                'continent'                => 'Asia',
                'duration_days'            => 10,
                'guaranteed_departure'     => false,
                'regular_price_per_person' => 3899.00,
                'promo_price_per_person'   => 3499.00,
                'is_sale_enabled'          => true,
                'highlights' => [
                    'Cherry blossom season timing',
                    'Mt. Fuji day trip',
                    'Tea ceremony in Kyoto',
                    'Arashiyama Bamboo Grove',
                ],
                'full_stops' => [
                    ['city' => 'Tokyo', 'country' => 'Japan', 'days' => 4],
                    ['city' => 'Kyoto', 'country' => 'Japan', 'days' => 3],
                    ['city' => 'Osaka', 'country' => 'Japan', 'days' => 3],
                ],
                'additional_info' => [
                    'countriesVisited' => ['Japan'],
                    'startingPoint'    => 'Narita International Airport, Tokyo',
                    'endingPoint'      => 'Kansai International Airport, Osaka',
                ],
                'itinerary' => [
                    ['day' => 1,  'title' => 'Arrive Tokyo',         'description' => 'Transfer to hotel in Shibuya.'],
                    ['day' => 2,  'title' => 'Tokyo Highlights',     'description' => 'Senso-ji Temple, Shibuya, Akihabara.'],
                    ['day' => 3,  'title' => 'Mt. Fuji Day Trip',    'description' => 'Day trip to Mt. Fuji and Hakone.'],
                    ['day' => 5,  'title' => 'Shinkansen to Kyoto',  'description' => 'Bullet train to Kyoto.'],
                    ['day' => 6,  'title' => 'Kyoto Temples',        'description' => 'Kinkaku-ji, Nijo Castle, tea ceremony.'],
                    ['day' => 8,  'title' => 'Osaka',                'description' => 'Dotonbori street food tour.'],
                    ['day' => 10, 'title' => 'Departure',            'description' => 'Transfer to Kansai Airport.'],
                ],
                'cash_freebies' => [
                    ['label' => '7-day JR Rail Pass',   'type' => 'free', 'value' => null],
                    ['label' => 'Welcome Dinner Tokyo', 'type' => 'free', 'value' => null],
                ],
                'is_active'      => true,
                'is_featured'    => true,
                'average_rating' => 4.7,
                'total_reviews'  => 31,
            ],
            [
                'title'                    => 'European Capitals Grand Tour',
                'slug'                     => 'european-capitals-grand-tour',
                'summary'                  => 'Art, culture and cuisine of Paris, Amsterdam and Prague in one unforgettable multi-country journey.',
                'short_description'        => 'Paris, Amsterdam and Prague in one spectacular trip.',
                'line'                     => 'Grand Tour',
                'continent'                => 'Europe',
                'duration_days'            => 12,
                'guaranteed_departure'     => true,
                'regular_price_per_person' => 2899.00,
                'travel_window'            => ['start' => '2025-04-01', 'end' => '2025-10-31'],
                'highlights' => [
                    'Eiffel Tower light-up show',
                    'Anne Frank House Amsterdam',
                    'Old Town Prague at night',
                    'Skip-the-line Louvre',
                ],
                'full_stops' => [
                    ['city' => 'Paris',     'country' => 'France',          'days' => 4],
                    ['city' => 'Amsterdam', 'country' => 'Netherlands',     'days' => 3],
                    ['city' => 'Prague',    'country' => 'Czech Republic',  'days' => 5],
                ],
                'additional_info' => [
                    'countriesVisited' => ['France', 'Netherlands', 'Czech Republic'],
                    'startingPoint'    => 'Charles de Gaulle Airport, Paris',
                    'endingPoint'      => 'Vaclav Havel Airport, Prague',
                ],
                'is_active'      => true,
                'is_featured'    => false,
                'average_rating' => 4.6,
                'total_reviews'  => 14,
            ],
        ];

        foreach ($tours as $data) {
            Tour::create($data);
        }
    }
}
