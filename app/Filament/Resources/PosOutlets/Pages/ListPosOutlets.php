<?php

namespace App\Filament\Resources\PosOutlets\Pages;

use App\Filament\Resources\PosOutlets\PosOutletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosOutlets extends ListRecords
{
    protected static string $resource = PosOutletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
