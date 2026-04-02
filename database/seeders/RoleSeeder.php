<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'user',
            'super_admin',
            'hotel_admin',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name'       => $role,
                'guard_name' => 'web',
            ]);
        }
    }
}
