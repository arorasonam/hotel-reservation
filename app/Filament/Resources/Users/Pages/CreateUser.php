<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // Re-fetch as base User model so Spatie's morph map resolves correctly
        $user = User::find($this->record->getKey());
        $role = Role::findByName('user', 'web');

        $user->roles()->syncWithoutDetaching([$role->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
