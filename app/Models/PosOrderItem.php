<?php

namespace App\Models;

use App\Services\TaxService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    protected $fillable = [
        'pos_order_id',
        'pos_category_id',
        'pos_item_id',
        'quantity',
        'price',
        'tax_id',
        'base_price',
        // 'tax_ids',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }

    public function getTaxBreakdownAttribute(): array
    {
        if (empty($this->tax_id)) {
            return [];
        }

        return Tax::query()
            ->where('id', $this->tax_id)
            // ->whereIn('id', $this->tax_ids)
            ->get()
            ->map(fn (Tax $tax): array => [
                'name' => $tax->name,
                'type' => $tax->type,
                'percentage' => (float) $tax->percentage,
                'amount' => round(((float) $this->subtotal * (float) $tax->percentage) / 100, 2),
                'rule_id' => null,
                'tax_id' => $tax->id,
            ])
            ->all();
    }

    protected static function booted(): void
    {
        static::saving(function (PosOrderItem $orderItem): void {
            $item = $orderItem->item;
            $order = $orderItem->order ?: PosOrder::find($orderItem->pos_order_id);

            if (! $item || ! $order) {
                return;
            }

            $taxCalculation = app(TaxService::class)->calculatePosItem(
                order: $order,
                item: $item,
                quantity: $orderItem->quantity,
                price: $orderItem->price,
            );

            $orderItem->tax_id = $taxCalculation['tax_ids'][0] ?? null;
            // $orderItem->tax_ids = $taxCalculation['tax_ids'];
            $orderItem->tax_percentage = $taxCalculation['total_percentage'];
            $orderItem->subtotal = $taxCalculation['taxable_amount'];
            $orderItem->tax_amount = $taxCalculation['tax_amount'];
            $orderItem->total = round($taxCalculation['taxable_amount'] + $taxCalculation['tax_amount'], 2);
        });
    }
}
