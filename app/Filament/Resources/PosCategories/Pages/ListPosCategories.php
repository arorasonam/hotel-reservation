<?php

namespace App\Filament\Resources\PosCategories\Pages;

use App\Filament\Resources\PosCategories\PosCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosCategories extends ListRecords
{
    protected static string $resource = PosCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
