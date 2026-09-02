<?php

use App\Models\AdminUser;
use App\Models\Menu;
use Illuminate\Support\Carbon;

function actingAsAdmin(): AdminUser
{
    test()->seed(\Database\Seeders\RolePermissionSeeder::class);

    $admin = AdminUser::factory()->create(['status' => 'active']);
    $admin->syncRoles(['admin']);

    test()->actingAs($admin, 'admin');

    return $admin;
}

test('a new menu cannot be created as published', function () {
    actingAsAdmin();

    $response = test()->post('/admin/menus', [
        'menu_date' => Carbon::tomorrow()->format('Y-m-d'),
        'status' => 'published',
    ]);

    $response->assertSessionHasErrors('status');
    expect(Menu::query()->exists())->toBeFalse();
});

test('publishing a menu with no meals is rejected', function () {
    actingAsAdmin();
    $menu = Menu::factory()->create(['status' => 'draft']);

    test()->patch("/admin/menus/{$menu->id}/publish");

    expect($menu->fresh()->status)->toBe('draft');
});

test('saving a menu as published with no meals is rejected', function () {
    actingAsAdmin();
    $menu = Menu::factory()->create(['status' => 'draft']);

    $response = test()->put("/admin/menus/{$menu->id}", [
        'menu_date' => $menu->menu_date->format('Y-m-d'),
        'status' => 'published',
    ]);

    $response->assertSessionHasErrors('status');
    expect($menu->fresh()->status)->toBe('draft');
});

test('a menu with meals can be published and is immediately visible to users', function () {
    actingAsAdmin();
    $menu = Menu::factory()->create(['status' => 'draft', 'menu_date' => Carbon::tomorrow()->format('Y-m-d')]);
    $menu->meals()->create(['meal_type' => 'breakfast', 'food_type' => 'veg', 'food_name' => 'Idli', 'status' => 'available']);

    test()->patch("/admin/menus/{$menu->id}/publish");

    expect($menu->fresh()->status)->toBe('published');

    $day = app(\App\Services\MenuService::class)->dayPayload($menu->menu_date);

    expect($day['menus']['breakfast'])->toHaveCount(1);
});
