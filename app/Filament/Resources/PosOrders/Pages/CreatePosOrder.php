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
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));

        // update Inventory //
        $order = $this->record;

        $service = app(\App\Services\InventoryService::class);

        if ($order->status === 'paid' || $order->status === 'confirmed') {
            $service->deductFromOrder($order);
        }
    }

    protected function beforeSave(): void
    {
        // check item stock //
        foreach ($this->data['orderItems'] as $item) {

            $posItem = \App\Models\PosItem::with('directInventory')
                ->find($item['pos_item_id']);

            if (! $posItem || ! $posItem->inventory_item_id) {
                throw new \Exception("Invalid item selected");
            }

            $stock = $posItem->directInventory->current_stock ?? 0;

            if ($stock <= 0) {
                throw new \Exception("{$posItem->name} is out of stock");
            }

            if ($item['quantity'] > $stock) {
                throw new \Exception("Only {$stock} available for {$posItem->name}");
            }
        }
    }
}
