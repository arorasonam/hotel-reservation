<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();
        $eur = Currency::where('code', 'EUR')->first();

        ExchangeRate::insert([
            [
                'from_currency_id' => $usd->id,
                'to_currency_id' => $inr->id,
                'rate' => 83,
                'source' => 'seed',
            ],
            [
                'from_currency_id' => $eur->id,
                'to_currency_id' => $inr->id,
                'rate' => 90,
                'source' => 'seed',
            ],
        ]);
    }
}
