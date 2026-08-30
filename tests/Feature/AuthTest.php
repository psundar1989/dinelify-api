<?php

use App\Models\Location;
use App\Models\Room;
use App\Models\User;

test('login by mobile returns a token for an existing active user', function () {
    $location = Location::factory()->create();
    $room = Room::factory()->create(['location_id' => $location->id]);
    $user = User::factory()->create([
        'mobile' => '9876543210',
        'location_id' => $location->id,
        'room_id' => $room->id,
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/auth/login-by-mobile', ['mobile' => '9876543210']);

    $response->assertOk()
        ->assertJsonPath('data.is_new_user', false)
        ->assertJsonPath('data.user.id', $user->id);
    expect($response->json('data.token'))->not->toBeNull();
});

test('login by mobile reports a new user when the number is not registered', function () {
    $response = $this->postJson('/api/auth/login-by-mobile', ['mobile' => '9876500000']);

    $response->assertOk()
        ->assertJsonPath('data.is_new_user', true)
        ->assertJsonPath('data.mobile', '9876500000');
});

test('login by mobile rejects a disabled account', function () {
    $location = Location::factory()->create();
    $room = Room::factory()->create(['location_id' => $location->id]);
    User::factory()->create([
        'mobile' => '9876543211',
        'location_id' => $location->id,
        'room_id' => $room->id,
        'status' => 'inactive',
    ]);

    $response = $this->postJson('/api/auth/login-by-mobile', ['mobile' => '9876543211']);

    $response->assertStatus(403);
});

test('registration succeeds with only the required website fields, no email', function () {
    $location = Location::factory()->create();
    $room = Room::factory()->create(['location_id' => $location->id]);

    $response = $this->postJson('/api/user/register', [
        'name' => 'Jane Doe',
        'mobile' => '9123456789',
        'location_id' => $location->id,
        'room_id' => $room->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user.mobile', '9123456789');
    expect($response->json('data.token'))->not->toBeNull();
});

test('registration requires a mobile number', function () {
    $location = Location::factory()->create();
    $room = Room::factory()->create(['location_id' => $location->id]);

    $response = $this->postJson('/api/user/register', [
        'name' => 'Jane Doe',
        'location_id' => $location->id,
        'room_id' => $room->id,
    ]);

    $response->assertStatus(422)->assertJsonPath('errors.mobile.0', 'The mobile field is required.');
});
