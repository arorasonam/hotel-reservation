<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::query()->updateOrCreate(
            ['code' => 'INR'],
            [
                'name' => 'Indian Rupee',
                'symbol' => 'Rs',
                'decimal_places' => 2,
                'is_active' => true,
                'is_default' => true,
            ],
        );
    }
}
