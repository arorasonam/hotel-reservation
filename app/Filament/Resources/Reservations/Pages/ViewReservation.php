<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}