<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use Filament\Resources\Pages\CreateRecord;

class CreatePosOrder extends CreateRecord
{
    protected static string $resource = PosOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->data['items'] ?? [];

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $itemTax = (float) ($item['tax_amount'] ?? 0);

            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $grandTotal = $subtotal + $taxAmount - $discount;

        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['grand_total'] = $grandTotal;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
    }
}
