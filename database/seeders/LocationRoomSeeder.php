<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocationRoomSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'North Campus Hostel' => ['A101', 'A102', 'A103', 'B201', 'B202'],
            'South Campus Hostel' => ['C101', 'C102', 'C103', 'D201', 'D202'],
        ];

        foreach ($locations as $name => $roomNumbers) {
            $location = Location::query()->firstOrCreate(['name' => $name], ['status' => 'active']);

            foreach ($roomNumbers as $roomNumber) {
                Room::query()->firstOrCreate([
                    'location_id' => $location->id,
                    'room_number' => $roomNumber,
                ], ['status' => 'active']);
            }
        }

        $teamLead = AdminUser::query()->firstOrCreate(
            ['email' => 'teamlead@dinelify.test'],
            [
                'name' => 'North Campus Team Lead',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $teamLead->syncRoles(['team-lead']);
        $teamLead->locations()->sync(
            Location::query()->where('name', 'North Campus Hostel')->pluck('id')
        );
    }
}
