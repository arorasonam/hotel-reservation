<?php

namespace App\Filament\Resources\HotelAdmins\Pages;

use App\Filament\Resources\HotelAdmins\HotelAdminResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateHotelAdmin extends CreateRecord
{
    protected static string $resource = HotelAdminResource::class;

    protected function afterCreate(): void
    {
        // Re-fetch as base User model so Spatie's morph map resolves correctly
        $user = User::find($this->record->getKey());
        $role = Role::findByName('hotel_admin', 'web');

        $user->roles()->syncWithoutDetaching([$role->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
