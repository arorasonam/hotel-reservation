<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $fillable = [
        'pos_order_id',
        'pos_category_id',
        'pos_item_id',
        'quantity',
        'price',
        'tax_id',
        'tax_ids',
        'tax_amount',
        'tax_percentage',
        'subtotal',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'tax_ids' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }

    public function getTaxBreakdownAttribute(): array
    {
        $taxIds = $this->tax_ids ?: array_filter([$this->tax_id]);

        if (empty($taxIds)) {
            return [];
        }

        return Tax::query()
            ->whereIn('id', $taxIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Tax $tax): array => [
                'name' => $tax->name,
                'percentage' => (float) $tax->percentage,
                'amount' => ((float) $this->subtotal * (float) $tax->percentage) / 100,
            ])
            ->all();
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
