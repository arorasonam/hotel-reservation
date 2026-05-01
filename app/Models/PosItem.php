<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosItem extends Model
{
    protected $fillable = [
        'pos_outlet_id',
        'pos_category_id',
        'name',
        'price',
        'currency_code',
        'exchange_rate',
        'tax_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:8',
        ];
    }

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
