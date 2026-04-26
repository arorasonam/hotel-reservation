<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function deductFromOrder($order)
    {
        DB::transaction(function () use ($order) {
            
            foreach ($order->items as $orderItem) {

                $posItem = $orderItem->item;

                //Direct item
                if ($posItem->inventory_item_id) {
                    
                    $inventoryItem = InventoryItem::lockForUpdate() //prevents race condition
                        ->find($posItem->inventory_item_id);

                    if (! $inventoryItem || $inventoryItem->current_stock <= 0) {
                        throw new \Exception("Stock not available for {$posItem->name}");
                    }

                    if ($posItem->quantity > $inventoryItem->current_stock) {
                        throw new \Exception("Insufficient stock for {$posItem->name}");
                    }
                    
                    $this->deduct(
                        $posItem->directInventory,
                        $orderItem->quantity,
                        $order->id
                    );
                    continue;
                }

                //Recipe-based 
                // Not In Use for now GP//
                foreach ($posItem->recipeItems as $ingredient) {
                  
                    $qty = $ingredient->quantity * $orderItem->quantity;
                  
                    $this->deduct($ingredient, $qty, $order->id);
                }
            }
        });
    }

    private function deduct($inventoryItem, $qty, $orderId)
    {
        $inventoryItem->decrement('current_stock', $qty);
        InventoryTransaction::create([
            'inventory_item_id' => $inventoryItem->id,
            'type' => 'SALE',
            'quantity' => $qty,
            'reference_type' => 'POS_ORDER',
            'reference_id' => $orderId,
        ]);
    }

    public function reverseOrder($order)
    {
        foreach ($order->items as $orderItem) {

            $posItem = $orderItem->posItem;

            if ($posItem->inventory_item_id) {
                $this->addBack(
                    $posItem->directInventory,
                    $orderItem->quantity,
                    $order->id
                );
                continue;
            }

            foreach ($posItem->inventoryItems as $ingredient) {

                $qty = $ingredient->pivot->quantity * $orderItem->quantity;

                $this->addBack($ingredient, $qty, $order->id);
            }
        }
    }

    private function addBack($inventoryItem, $qty, $orderId)
    {
        $inventoryItem->increment('current_stock', $qty);

        InventoryTransaction::create([
            'inventory_item_id' => $inventoryItem->id,
            'type' => 'SALE_VOID',
            'quantity' => $qty,
            'reference_type' => 'POS_ORDER',
            'reference_id' => $orderId,
        ]);
    }
}