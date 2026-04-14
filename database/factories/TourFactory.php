<?php

namespace Database\Factories;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        $title = fake()->words(4, true);

        return [
            'title'                    => ucwords($title),
            'slug'                     => Str::slug($title) . '-' . fake()->unique()->randomNumber(4),
            'summary'                  => fake()->sentence(),
            'duration_days'            => fake()->numberBetween(3, 14),
            'regular_price_per_person' => fake()->randomFloat(2, 5000, 50000),
            'is_active'                => true,
            'is_featured'              => false,
        ];
    }
}
