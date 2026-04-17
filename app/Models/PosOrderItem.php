<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PosOrderItem extends Model
{
    protected $fillable = [
        'pos_order_id',
        'pos_item_id',
        'quantity',
        'price',
        'tax_id',
        'tax_amount',
        'tax_percentage',
        'subtotal',
        'total'
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }

    protected static function booted()
    {
        // static::creating(function ($item) {
        //     $item->total = $item->quantity * $item->price;
        // });

        // static::updating(function ($item) {
        //     $item->total = $item->quantity * $item->price;
        // });
    }
}