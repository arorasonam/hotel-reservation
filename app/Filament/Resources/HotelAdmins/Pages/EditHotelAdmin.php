<?php

namespace App\Filament\Resources\HotelAdmins\Pages;

use App\Filament\Resources\HotelAdmins\HotelAdminResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelAdmin extends EditRecord
{
    protected static string $resource = HotelAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
