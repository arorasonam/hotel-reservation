<?php

namespace App\Filament\Resources\PosItems\Pages;

use App\Filament\Resources\PosItems\PosItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosItem extends EditRecord
{
    protected static string $resource = PosItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
