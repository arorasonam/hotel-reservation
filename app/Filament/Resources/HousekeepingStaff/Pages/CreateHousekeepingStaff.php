<?php

namespace App\Filament\Resources\HousekeepingStaff\Pages;

use App\Filament\Resources\HousekeepingStaff\HousekeepingStaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateHousekeepingStaff extends CreateRecord
{
    protected static string $resource = HousekeepingStaffResource::class;

    protected function afterCreate(): void
    {
        $user = User::find($this->record->getKey());
        $role = Role::firstOrCreate([
            'name' => 'housekeeping',
            'guard_name' => 'web',
        ]);

        $user->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
