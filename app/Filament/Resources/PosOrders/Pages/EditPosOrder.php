<?php

namespace App\Filament\Resources\PosOrders\Pages;

use App\Filament\Resources\PosOrders\PosOrderResource;
use App\Services\ReservationFolioService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosOrder extends EditRecord
{
    protected static string $resource = PosOrderResource::class;

    protected ?string $oldStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->oldStatus = $this->record->status;

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

    protected function afterSave(): void
    {
        $this->record->refreshTotals();
        app(ReservationFolioService::class)->syncPosOrderCharges($this->record->fresh(['reservation', 'reservationRoomDetail']));
        
        // call inventory Service //
      
        $order = $this->record;

        $oldStatus = $this->oldStatus;
        
        $currentStatus  = $order->status;

        if ($oldStatus !== $currentStatus) {
            $service = app(\App\Services\InventoryService::class);

            if ($order->status === 'paid' || $order->status === 'confirmed') {
                $service->deductFromOrder($order);
            }

            if ($order->status === 'cancelled') {
                $service->reverseOrder($order);
            }
        }
        
    }
    
}
