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
        $data['room_no']  = request()->query('rooam_no');

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // We remove 'room_requirements' from the main data array 
        // because we will handle the insertion manually
        $this->temporary_requirements = $data['room_requirements'] ?? [];
        unset($data['room_requirements']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $reservation = $this->record;

        foreach ($this->temporary_requirements as $category) {
            foreach ($category['requirements'] as $roomDetails) {
                // Create a separate row for EVERY physical room
                $reservation->reservationRooms()->create([
                    'room_type_id' => $category['room_type_id'],
                    'meal_plan_id' => $category['meal_plan_id'],
                    'room_number'  => $roomDetails['room_number'] ?? 'Auto',
                    'adults'       => $roomDetails['adults'] ?? 2,
                    'children'     => $roomDetails['children'] ?? 0,
                    'infant'       => $roomDetails['infant'] ?? 0,
                    'status'       => 'confirmed', // Initial status
                ]);
            }
        }
    }
}
