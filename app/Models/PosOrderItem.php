<?php

namespace App\Models;

use App\Support\CurrencyDefaults;
use App\Support\MoneyConverter;
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
        'tax_ids',
        'tax_amount',
        'tax_percentage',
        'subtotal',
        'total',
        'currency_code',
        'exchange_rate',
        'base_currency_code',
        'base_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_ids' => 'array',
            'exchange_rate' => 'decimal:8',
            'base_amount' => 'decimal:2',
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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
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

    protected static function booted(): void
    {
        static::saving(function (PosOrderItem $item): void {
            $order = $item->order;

            $item->currency_code ??= $order?->currency_code ?? CurrencyDefaults::defaultCode();
            $item->base_currency_code ??= $order?->base_currency_code ?? MoneyConverter::baseCurrencyForHotel($order?->hotel_id);
            $item->exchange_rate ??= $order?->exchange_rate ?? MoneyConverter::exchangeRateToBase($item->currency_code, $item->base_currency_code);
            $item->base_amount = MoneyConverter::toBase($item->total, $item->exchange_rate);
        });

        // static::creating(function ($item) {
        //     $item->total = $item->quantity * $item->price;
        // });

        // static::updating(function ($item) {
        //     $item->total = $item->quantity * $item->price;
        // });
    }
}
