<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'hotel_id', 'name', 'unit',
        'current_stock', 'cost_price', 'reorder_level'
    ];

    public function posItems()
    {
        return $this->belongsToMany(PosItem::class, 'pos_item_inventory')
            ->withPivot('quantity');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}