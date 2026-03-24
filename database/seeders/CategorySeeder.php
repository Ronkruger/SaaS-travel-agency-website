<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Beach & Island',   'icon' => 'fa-umbrella-beach',    'description' => 'Sun, sand, and crystal-clear waters.'],
            ['name' => 'Adventure',        'icon' => 'fa-mountain',           'description' => 'Thrilling outdoor activities and extreme sports.'],
            ['name' => 'Cultural',         'icon' => 'fa-landmark',           'description' => 'Immerse yourself in history and local traditions.'],
            ['name' => 'Wildlife Safari',  'icon' => 'fa-paw',                'description' => 'Encounter exotic animals in their natural habitat.'],
            ['name' => 'City Tours',       'icon' => 'fa-city',               'description' => 'Explore vibrant urban landscapes and iconic sights.'],
            ['name' => 'Cruise',           'icon' => 'fa-ship',               'description' => 'Sail to multiple destinations in luxury comfort.'],
            ['name' => 'Mountain',         'icon' => 'fa-mountain-sun',       'description' => 'Breathtaking peaks and scenic highland trails.'],
            ['name' => 'Eco Tourism',      'icon' => 'fa-leaf',               'description' => 'Sustainable travel connecting you with nature.'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'        => $data['name'],
                    'slug'        => Str::slug($data['name']),
                    'icon'        => $data['icon'],
                    'description' => $data['description'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
