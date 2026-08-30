<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'meal_type' => fake()->randomElement(['breakfast', 'lunch', 'dinner']),
            'food_type' => fake()->randomElement(['veg', 'non_veg', 'skip']),
            'status' => 'active',
        ];
    }
}
