<?php

namespace App\Filament\Resources\HotelGroups\Pages;

use App\Filament\Resources\HotelGroups\HotelGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelGroup extends EditRecord
{
    protected static string $resource = HotelGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
