<?php

use App\Models\Location;
use App\Models\Meal;
use App\Models\Menu;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

function seedOrderableWorld(): array
{
    Cache::flush();

    Setting::set('advance_order_days', '5');
    Setting::set('order_cutoff_time', '20:00');
    Setting::set('allow_order_edit', '1');

    $location = Location::factory()->create();
    $room = Room::factory()->create(['location_id' => $location->id]);
    $user = User::factory()->create(['location_id' => $location->id, 'room_id' => $room->id]);

    foreach (range(0, 6) as $offset) {
        $menu = Menu::factory()->create([
            'menu_date' => Carbon::today()->addDays($offset)->format('Y-m-d'),
            'status' => 'published',
        ]);

        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            foreach (['veg', 'non_veg'] as $foodType) {
                Meal::factory()->create([
                    'menu_id' => $menu->id,
                    'meal_type' => $mealType,
                    'food_type' => $foodType,
                    'status' => 'available',
                ]);
            }
        }
    }

    return [$user, $location];
}

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-01-10 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('order can be placed within the advance order window', function () {
    [$user] = seedOrderableWorld();

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        'selections' => [
            ['meal_type' => 'breakfast', 'food_type' => 'veg'],
            ['meal_type' => 'lunch', 'food_type' => 'non_veg'],
            ['meal_type' => 'dinner', 'food_type' => 'skip'],
        ],
    ]);

    $response->assertStatus(201)->assertJson(['success' => true]);
    expect($response->json('data.order_details'))->toHaveCount(3);
});

test('order beyond the advance order window is rejected', function () {
    [$user] = seedOrderableWorld();

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'veg']],
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false, 'message' => 'Orders can only be placed up to 5 day(s) in advance.']);
});

test('order for today is rejected once the cutoff time has passed', function () {
    [$user] = seedOrderableWorld();

    // Cutoff for "today" is 20:00 yesterday; "now" (2026-01-10 10:00) is already past it.
    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => Carbon::today()->format('Y-m-d'),
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'veg']],
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false, 'message' => 'Order cutoff time has passed']);
});

test('order for tomorrow succeeds before its cutoff and locks after it', function () {
    [$user] = seedOrderableWorld();

    // Cutoff for tomorrow is 20:00 today; "now" is 10:00 today, so it's still open.
    $tomorrow = Carbon::today()->addDay()->format('Y-m-d');

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => $tomorrow,
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'veg']],
    ]);
    $response->assertStatus(201);

    Carbon::setTestNow(Carbon::parse('2026-01-10 21:00:00'));

    $orderId = $response->json('data.id');
    $updateResponse = actingAsUser($user)->putJson("/api/orders/{$orderId}", [
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'non_veg']],
    ]);

    $updateResponse->assertStatus(422)
        ->assertJson(['success' => false, 'message' => 'Order cutoff time has passed']);
});

test('an upcoming date within the advance window is orderable even before its menu is published', function () {
    [$user] = seedOrderableWorld();
    $tomorrow = Carbon::today()->addDay();

    // Unlike seedOrderableWorld(), no Menu row exists yet for this date.
    Menu::query()->whereDate('menu_date', $tomorrow->format('Y-m-d'))->delete();

    $response = actingAsUser($user)->getJson('/api/menus/'.$tomorrow->format('Y-m-d'));

    $response->assertStatus(200)->assertJsonPath('data.is_orderable', true);
});

test('a date the admin has disabled is locked even though it is within the advance window', function () {
    [$user] = seedOrderableWorld();
    $tomorrow = Carbon::today()->addDay();

    Menu::query()->whereDate('menu_date', $tomorrow->format('Y-m-d'))->update(['status' => 'disabled']);

    $response = actingAsUser($user)->getJson('/api/menus/'.$tomorrow->format('Y-m-d'));

    $response->assertStatus(200)->assertJsonPath('data.is_orderable', false);
});

test('duplicate orders for the same date are rejected', function () {
    [$user] = seedOrderableWorld();
    $date = Carbon::today()->addDays(1)->format('Y-m-d');

    actingAsUser($user)->postJson('/api/orders', [
        'order_date' => $date,
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'veg']],
    ])->assertStatus(201);

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => $date,
        'selections' => [['meal_type' => 'lunch', 'food_type' => 'veg']],
    ]);

    $response->assertStatus(409)
        ->assertJson(['success' => false, 'message' => 'An order already exists for this date. Use update instead.']);
});

test('ordering an unavailable meal is rejected', function () {
    [$user] = seedOrderableWorld();
    $date = Carbon::today()->addDays(1);

    Meal::query()
        ->whereHas('menu', fn ($q) => $q->whereDate('menu_date', $date->format('Y-m-d')))
        ->where('meal_type', 'lunch')
        ->where('food_type', 'veg')
        ->update(['status' => 'unavailable']);

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => $date->format('Y-m-d'),
        'selections' => [['meal_type' => 'lunch', 'food_type' => 'veg']],
    ]);

    $response->assertStatus(422)->assertJsonPath('success', false);
});

test('ordering a date with no published menu is rejected', function () {
    [$user] = seedOrderableWorld();

    Menu::query()->whereDate('menu_date', Carbon::today()->addDays(1)->format('Y-m-d'))->update(['status' => 'draft']);

    $response = actingAsUser($user)->postJson('/api/orders', [
        'order_date' => Carbon::today()->addDays(1)->format('Y-m-d'),
        'selections' => [['meal_type' => 'breakfast', 'food_type' => 'veg']],
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false, 'message' => 'No menu is available for the selected date.']);
});

test('registration rejects a duplicate mobile number', function () {
    [, $location] = seedOrderableWorld();
    $room = Room::factory()->create(['location_id' => $location->id]);
    $existing = User::factory()->create(['location_id' => $location->id, 'room_id' => $room->id]);

    $response = $this->postJson('/api/user/register', [
        'name' => 'Someone Else',
        'mobile' => $existing->mobile,
        'otp' => '0000',
        'location_id' => $location->id,
        'room_id' => $room->id,
    ]);

    $response->assertStatus(422)->assertJsonPath('errors.mobile.0', 'This mobile number is already registered.');
});

function actingAsUser(User $user): \Tests\TestCase
{
    return test()->actingAs($user, 'sanctum');
}
