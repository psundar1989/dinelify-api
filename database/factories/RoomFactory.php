<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'room_number' => strtoupper(fake()->bothify('?##')),
            'status' => 'active',
        ];
    }
}
