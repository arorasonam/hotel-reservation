<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItemInventory extends Model
{
    protected $table = 'pos_item_inventory';
    protected $fillable = [
        'pos_item_id',
        'inventory_item_id',
        'quantity'
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
