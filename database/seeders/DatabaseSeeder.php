<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            LocationRoomSeeder::class,
            MenuMealSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
