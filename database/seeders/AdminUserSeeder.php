<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = AdminUser::query()->firstOrCreate(
            ['email' => 'superadmin@dinelify.test'],
            [
                'name' => 'Dinelify Super Admin',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        $admin = AdminUser::query()->firstOrCreate(
            ['email' => 'admin@dinelify.test'],
            [
                'name' => 'Dinelify Admin',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $admin->syncRoles(['admin']);
    }
}
