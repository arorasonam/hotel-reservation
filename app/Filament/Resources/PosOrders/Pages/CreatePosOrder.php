<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use Filament\Resources\Pages\CreateRecord;

class CreatePosOrder extends CreateRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function afterCreate(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoom']));
    }

     protected function mutateFormDataBeforeCreate(array $data): array
    {
       foreach ($data['items'] as &$item) {

            if (!isset($item['tax_percentage']) || $item['tax_percentage'] === null) {

                if (!empty($item['tax_id'])) {
                    $tax = \App\Models\Tax::find($item['tax_id']);
                    $item['tax_percentage'] = $tax?->percentage ?? 0;
                } else {
                    $item['tax_percentage'] = 0;
                }
            }
        }

        return $data;
    }

}
