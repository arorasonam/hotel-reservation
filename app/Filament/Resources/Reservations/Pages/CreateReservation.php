<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     // Pre-populates the form from URL parameters
    //     $data['hotel_id'] = request()->query('hotel_id');
    //     $data['check_in'] = request()->query('check_in');
    //     $data['room_no']  = request()->query('rooam_no');

    //     return $data;
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Pull the raw repeater data out of the main data array
        $this->temporary_room_data = $data['room_requirements'] ?? [];

        // 2. Remove it so Filament doesn't try to save it as an array string
        unset($data['room_requirements']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $reservation = $this->record;

        // 3. Loop through categories (e.g., Arena DBL, Garden Dbl)
        foreach ($this->temporary_room_data as $category) {

            // 4. Loop through each specific room requirement in that category
            foreach ($category['requirements'] as $roomDetail) {
                $reservation->reservationRooms()->create([
                    'room_type_id' => $category['room_type_id'],
                    'meal_plan_id' => $category['meal_plan_id'],
                    'room_number'  => $roomDetail['room_number'] ?? 'Auto',
                    'adults'       => $roomDetail['adults'] ?? 2,
                    'children'     => $roomDetail['children'] ?? 0,
                    'infant'       => $roomDetail['infant'] ?? 0,
                    'status'       => 'confirmed', // Required for your calendar
                ]);
            }
        }
    }
}
