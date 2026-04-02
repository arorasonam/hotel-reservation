<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create Roles with Guard 'web'
        $superAdminRole = Role::where(['name' => 'super_admin', 'guard_name' => 'web'])->first();
        $hotelAdminRole = Role::where(['name' => 'hotel_admin', 'guard_name' => 'web'])->first();
        $userRole       = Role::where(['name' => 'user', 'guard_name' => 'web'])->first();

        // 5. Create the Super Admin Account
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($superAdminRole);

        // 6. Create a Sample Hotel Admin Account (Optional)
        $hotelAdmin = User::create([
            'name' => 'Hotel Manager',
            'email' => 'hotel@admin.com',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
        ]);

        $hotelAdmin->assignRole($hotelAdminRole);

        $this->command->info('Admin users created successfully!');
    }
}
