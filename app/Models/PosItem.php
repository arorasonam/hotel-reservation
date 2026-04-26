<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $fillable = [
        'pos_outlet_id',
        'pos_category_id',
        'name',
        'price',
        'tax_amount',
        'status',
        'inventory_item_id'
    ];

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }

    public function recipeItems()
    {
        return $this->hasMany(PosItemInventory::class);
    }

    public function directInventory()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
