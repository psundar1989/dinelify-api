<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => fake()->unique()->numerify('9#########'),
            'location_id' => Location::factory(),
            'room_id' => null,
            'status' => 'active',
        ];
    }

    public function inRoom(Room $room): static
    {
        return $this->state(fn () => [
            'location_id' => $room->location_id,
            'room_id' => $room->id,
        ]);
    }
}
