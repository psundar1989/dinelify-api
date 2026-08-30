<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_date' => fake()->unique()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'status' => 'published',
        ];
    }
}
