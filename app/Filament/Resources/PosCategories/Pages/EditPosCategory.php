<?php

namespace App\Filament\Resources\PosCategories\Pages;

use App\Filament\Resources\PosCategories\PosCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosCategory extends EditRecord
{
    protected static string $resource = PosCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
