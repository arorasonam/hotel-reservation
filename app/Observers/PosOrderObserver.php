<?php

namespace App\Observers;

use App\Models\PosOrder;
use App\Services\InventoryService;

class PosOrderObserver
{
     public function created(PosOrder $order): void
    {
        $this->handleInventory($order);
    }

    public function updated(PosOrder $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $this->handleInventory($order);
    }

    private function handleInventory(PosOrder $order): void
    {
        $service = app(InventoryService::class);

        if ($order->status === 'paid' || $order->status === 'confirmed') {
            $service->deductFromOrder($order);
        }

        if ($order->status === 'cancelled') {
            $service->reverseOrder($order);
        }
    }
}
