<?php

namespace App\Support;

use App\Models\Hotel;

class MoneyConverter
{
    public static function baseCurrencyForHotel(null|int|string $hotelId): string
    {
        if ($hotelId) {
            $currencyCode = Hotel::query()->whereKey($hotelId)->value('base_currency_code');

            if ($currencyCode) {
                return (string) $currencyCode;
            }
        }

        return CurrencyDefaults::defaultCode();
    }

    public static function toBase(float|int|string|null $amount, float|int|string|null $exchangeRate): float
    {
        return round((float) ($amount ?? 0) * (float) ($exchangeRate ?: 1), 2);
    }
}
