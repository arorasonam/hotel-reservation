<?php

namespace App\Filament\Resources\HotelGroups\Pages;

use App\Filament\Resources\HotelGroups\HotelGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelGroups extends ListRecords
{
    protected static string $resource = HotelGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
