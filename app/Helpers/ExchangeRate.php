<?php

namespace App\Helpers;

use App\Models\ExchangeRate as ExchangeRateModel;

class ExchangeRate
{
    public static function getExchangeRate(string $from, string $to, mixed $datetime = null): ?string
    {
        $datetime = $datetime ?? now();
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return '1';
        }

        return ExchangeRateModel::query()
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('effective_from', '<=', $datetime)
            ->orderByDesc('effective_from')
            ->value('rate');
    }

    public function rate(string $from, string $to, mixed $datetime = null): ?string
    {
        return self::getExchangeRate($from, $to, $datetime);
    }
}
