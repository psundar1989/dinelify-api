<?php

namespace Database\Seeders;

use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MenuMealSeeder extends Seeder
{
    private const array MENU_CATALOG = [
        'breakfast' => ['veg' => 'Poha & Sprouts Bowl', 'non_veg' => 'Egg Bhurji & Toast'],
        'lunch' => ['veg' => 'Dal Tadka, Rice & Roti Thali', 'non_veg' => 'Chicken Curry, Rice & Roti Thali'],
        'dinner' => ['veg' => 'Paneer Butter Masala & Naan', 'non_veg' => 'Mutton Curry & Naan'],
    ];

    public function run(): void
    {
        $start = Carbon::today()->subDays(2);
        $end = Carbon::today()->addDays(9);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $menu = Menu::query()->firstOrCreate(
                ['menu_date' => $date->format('Y-m-d')],
                ['status' => 'published']
            );

            foreach (self::MENU_CATALOG as $mealType => $foodTypes) {
                foreach ($foodTypes as $foodType => $foodName) {
                    $menu->meals()->firstOrCreate(
                        ['meal_type' => $mealType, 'food_type' => $foodType],
                        ['food_name' => $foodName, 'status' => 'available']
                    );
                }
            }
        }
    }
}
