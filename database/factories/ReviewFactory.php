<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'tour_id'     => Tour::factory(),
            'user_id'     => User::factory(),
            'rating'      => fake()->numberBetween(1, 5),
            'title'       => fake()->sentence(5),
            'body'        => fake()->paragraph(),
            'is_approved' => false,
        ];
    }
}
