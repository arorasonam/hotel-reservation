<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPosOrder extends ViewRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
