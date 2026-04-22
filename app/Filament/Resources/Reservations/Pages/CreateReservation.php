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
        $this->temporary_room_data = $data['roomCategories'] ?? [];

        // 2. Remove it so Filament doesn't try to save it as an array string
        unset($data['roomCategories']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $reservation = $this->record;
        // Extract the raw form state
        $formState = $this->form->getRawState();

        foreach ($formState['roomCategories'] as $categoryData) {
            // 1. Create the Category Summary
            $category = $reservation->roomCategories()->create([
                'room_type_id' => $categoryData['room_type_id'],
                'meal_plan_id' => $categoryData['meal_plan_id'],
                'rooms_count'  => $categoryData['rooms_count'],
            ]);

            // 2. Create individual Detail records based on the nested "Requirements" repeater
            if (isset($categoryData['roomDetails'])) {
                foreach ($categoryData['roomDetails'] as $roomDetail) {
                    $category->roomDetails()->create([
                        'room_number' => $roomDetail['room_number'] ?? 'Auto',
                        'adults'      => $roomDetail['adults'] ?? 2,
                        'children'    => $roomDetail['children'] ?? 0,
                        'infants'     => $roomDetail['infant'] ?? 0,
                        'status'      => 'confirmed',
                    ]);
                }
            }
        }
    }
}
