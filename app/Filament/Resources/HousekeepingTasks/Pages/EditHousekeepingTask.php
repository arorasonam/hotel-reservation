<?php

namespace App\Filament\Resources\HousekeepingTasks\Pages;

use App\Filament\Resources\HousekeepingTasks\HousekeepingTaskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHousekeepingTask extends EditRecord
{
    protected static string $resource = HousekeepingTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
