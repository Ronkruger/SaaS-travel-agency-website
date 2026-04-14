<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_number' => 'DG-TEST-' . fake()->unique()->numerify('######'),
            'user_id'        => User::factory(),
            'tour_id'        => Tour::factory(),
            'tour_date'      => fake()->dateTimeBetween('+30 days', '+180 days')->format('Y-m-d'),
            'adults'         => fake()->numberBetween(1, 4),
            'children'       => 0,
            'infants'        => 0,
            'total_guests'   => fake()->numberBetween(1, 4),
            'price_per_adult'=> 15000,
            'subtotal'       => 15000,
            'total_amount'   => 15000,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'xendit',
            'contact_name'   => fake()->name(),
            'contact_email'  => fake()->safeEmail(),
            'contact_phone'  => fake()->phoneNumber(),
        ];
    }
}
