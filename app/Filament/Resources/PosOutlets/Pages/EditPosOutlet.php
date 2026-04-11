<?php

namespace App\Filament\Resources\PosOutlets\Pages;

use App\Filament\Resources\PosOutlets\PosOutletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosOutlet extends EditRecord
{
    protected static string $resource = PosOutletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
