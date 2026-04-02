<?php

namespace App\Filament\Resources\SuperAdmins\Pages;

use App\Filament\Resources\SuperAdmins\SuperAdminResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends CreateRecord
{
    protected static string $resource = SuperAdminResource::class;

    protected function afterCreate(): void
    {
        // Re-fetch as base User model so Spatie's morph map resolves correctly
        $user = User::find($this->record->getKey());
        $role = Role::findByName('super_admin', 'web');

        $user->roles()->syncWithoutDetaching([$role->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
