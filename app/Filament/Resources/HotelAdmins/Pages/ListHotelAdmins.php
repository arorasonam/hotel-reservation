<?php

namespace App\Filament\Resources\HotelAdmins\Pages;

use App\Filament\Resources\HotelAdmins\HotelAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelAdmins extends ListRecords
{
    protected static string $resource = HotelAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
