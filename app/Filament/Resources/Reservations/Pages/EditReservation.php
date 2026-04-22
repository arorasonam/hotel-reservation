<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Intercept the nested array so it doesn't cause "Array to string" errors on save
        $this->temporary_room_data = $data['roomCategories'] ?? [];
        unset($data['roomCategories']);

        return $data;
    }

    protected function afterSave(): void
    {
        $reservation = $this->record;
        $formState = $this->form->getRawState();

        $activeCategoryIds = [];
        $activeDetailIds = [];

        foreach ($formState['roomCategories'] ?? [] as $categoryData) {
            // 1. Update existing category or create a new one
            $category = $reservation->roomCategories()->updateOrCreate(
                ['id' => $categoryData['id'] ?? null],
                [
                    'room_type_id' => $categoryData['room_type_id'],
                    'meal_plan_id' => $categoryData['meal_plan_id'],
                    'rooms_count'  => $categoryData['rooms_count'],
                ]
            );

            $activeCategoryIds[] = $category->id;

            // 2. Handle the nested Room Details
            if (isset($categoryData['roomDetails'])) {
                foreach ($categoryData['roomDetails'] as $roomDetail) {
                    $detail = $category->roomDetails()->updateOrCreate(
                        ['id' => $roomDetail['id'] ?? null],
                        [
                            'room_number' => $roomDetail['room_number'] ?? 'Auto',
                            'adults'      => $roomDetail['adults'] ?? 2,
                            'children'    => $roomDetail['children'] ?? 0,
                            'infants'     => $roomDetail['infant'] ?? 0,
                            // Preserve the 'status' (checked_in/confirmed) during edit
                            'status'      => $roomDetail['status'] ?? 'confirmed',
                        ]
                    );
                    $activeDetailIds[] = $detail->id;
                }
            }
        }

        // 3. Cleanup: Remove data that was deleted from the UI
        // First, delete details belonging to active categories that weren't in the form
        \App\Models\ReservationRoomDetail::whereIn('category_id', $activeCategoryIds)
            ->whereNotIn('id', $activeDetailIds)
            ->delete();

        // Then, delete categories (and their details via cascade) that were removed
        $reservation->roomCategories()->whereNotIn('id', $activeCategoryIds)->delete();
    }
}
