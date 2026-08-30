<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'meal_type' => fake()->randomElement(['breakfast', 'lunch', 'dinner']),
            'food_type' => fake()->randomElement(['veg', 'non_veg']),
            'food_name' => fake()->randomElement(['Paneer Butter Masala Thali', 'Chicken Curry Meal', 'Veg Biryani', 'Egg Curry Combo', 'Dal Tadka Thali']),
            'status' => 'available',
        ];
    }
}
