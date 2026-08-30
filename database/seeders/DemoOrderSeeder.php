<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::query()->where('name', 'North Campus Hostel')->first();
        $room = Room::query()->where('location_id', $location->id)->first();

        $demoUser = User::query()->firstOrCreate(
            ['mobile' => '9999999999'],
            [
                'name' => 'Demo User',
                'location_id' => $location->id,
                'room_id' => $room->id,
                'status' => 'active',
            ]
        );

        for ($i = 1; $i <= 2; $i++) {
            $date = Carbon::today()->addDays($i);
            $menu = Menu::query()->where('menu_date', $date->format('Y-m-d'))->first();
            if (! $menu) {
                continue;
            }

            $order = Order::query()->firstOrCreate(
                ['user_id' => $demoUser->id, 'order_date' => $date->format('Y-m-d')],
                ['status' => 'confirmed']
            );

            $order->orderDetails()->firstOrCreate(['meal_type' => 'breakfast'], ['food_type' => 'veg', 'status' => 'active']);
            $order->orderDetails()->firstOrCreate(['meal_type' => 'lunch'], ['food_type' => 'non_veg', 'status' => 'active']);
            $order->orderDetails()->firstOrCreate(['meal_type' => 'dinner'], ['food_type' => 'skip', 'status' => 'active']);
        }
    }
}
