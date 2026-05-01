<?php

namespace App\Filament\Resources\HousekeepingStaff\Pages;

use App\Filament\Resources\HousekeepingStaff\HousekeepingStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHousekeepingStaff extends EditRecord
{
    protected static string $resource = HousekeepingStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
