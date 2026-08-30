<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_date' => fake()->unique()->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d'),
            'status' => 'confirmed',
        ];
    }
}
