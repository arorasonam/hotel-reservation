<?php

namespace App\Support;

use App\Helpers\ExchangeRate;
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

    public static function fromBase(float|int|string|null $amount, float|int|string|null $exchangeRate): float
    {
        $rate = (float) ($exchangeRate ?: 1);

        if ($rate <= 0) {
            $rate = 1;
        }

        return round((float) ($amount ?? 0) / $rate, 2);
    }

    public static function exchangeRateToBase(?string $currencyCode, ?string $baseCurrencyCode = null, mixed $datetime = null): string
    {
        $currencyCode = strtoupper($currencyCode ?: CurrencyDefaults::defaultCode());
        $baseCurrencyCode = strtoupper($baseCurrencyCode ?: CurrencyDefaults::defaultCode());

        if ($currencyCode === $baseCurrencyCode) {
            return CurrencyDefaults::defaultExchangeRate();
        }

        return ExchangeRate::getExchangeRate($currencyCode, $baseCurrencyCode, $datetime)
            ?? CurrencyDefaults::defaultExchangeRate();
    }
}
