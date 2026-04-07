<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-populates the form from URL parameters
        $data['hotel_id'] = request()->query('hotel_id');
        $data['check_in'] = request()->query('check_in');
        $data['room_no']  = request()->query('room_no');

        return $data;
    }
}
