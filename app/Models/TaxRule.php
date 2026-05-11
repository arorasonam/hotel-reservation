<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRule extends Model
{
    protected $fillable = [
        'country_id',
        'tax_id',
        'name',
        'applies_to',
        'item_tax_category',
        'min_tariff',
        'max_tariff',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_tariff' => 'decimal:2',
            'max_tariff' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
