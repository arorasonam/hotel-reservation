<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate',
        'is_base',
        'is_active',
    ];

    public function fromRates()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    public function toRates()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    public static function base()
    {
        return self::where('is_base', true)->first();
    }
}
