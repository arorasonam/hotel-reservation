<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    protected string $baseCurrency;

    public function __construct()
    {
        $this->baseCurrency = Currency::where('is_base', true)->value('code') ?? 'INR';
    }

    /**
     * Get exchange rate (from given currency → base currency)
     */
    public function getRate(string $currencyCode): float
    {
        if ($currencyCode === $this->baseCurrency) {
            return 1.0;
        }

        return Cache::remember(
            "rate_{$currencyCode}_{$this->baseCurrency}",
            now()->addMinutes(60),
            function () use ($currencyCode) {

                return ExchangeRate::query()
                    ->whereHas('fromCurrency', fn ($q) => $q->where('code', $currencyCode))
                    ->whereHas('toCurrency', fn ($q) => $q->where('code', $this->baseCurrency))
                    ->latest('fetched_at')
                    ->value('rate') ?? 1.0;
            }
        );
    }

    /**
     * Convert to base currency
     */
    public function toBase(float $amount, float $rate): float
    {
        return round($amount * $rate, 2);
    }

    /**
     * Convert from base to another currency
     */
    public function fromBase(float $amount, float $rate): float
    {
        return round($amount / $rate, 2);
    }
}
