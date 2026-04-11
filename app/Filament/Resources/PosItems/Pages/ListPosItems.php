<?php

namespace App\Filament\Resources\PosItems\Pages;

use App\Filament\Resources\PosItems\PosItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosItems extends ListRecords
{
    protected static string $resource = PosItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
