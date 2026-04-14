<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'password'     => Hash::make('password'),
            'role'         => 'staff',
            'is_onboarded' => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }
}
