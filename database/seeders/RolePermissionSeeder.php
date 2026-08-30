<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-locations',
            'manage-rooms',
            'manage-menus',
            'manage-users',
            'manage-orders',
            'manage-admin-users',
            'manage-roles',
            'view-reports',
            'export-reports',
            'view-audit-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $admin->syncPermissions([
            'manage-locations',
            'manage-rooms',
            'manage-menus',
            'manage-users',
            'manage-orders',
            'view-reports',
            'export-reports',
        ]);

        $teamLead = Role::query()->firstOrCreate(['name' => 'team-lead', 'guard_name' => 'admin']);
        $teamLead->syncPermissions([
            'manage-orders',
            'view-reports',
            'export-reports',
        ]);
    }
}
