<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\Hotel;

class CurrencyDefaults
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return Currency::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->pluck('code', 'code')
            ->toArray();
    }

    public static function codeForHotel(null|int|string $hotelId): string
    {
        if ($hotelId) {
            $currencyCode = Hotel::query()->whereKey($hotelId)->value('base_currency_code');

            if ($currencyCode) {
                return (string) $currencyCode;
            }
        }

        return self::defaultCode();
    }

    public static function defaultCode(): string
    {
        return (string) (Currency::query()->where('is_default', true)->value('code') ?: 'INR');
    }

    public static function defaultExchangeRate(): string
    {
        return '1';
    }
}
