<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->currencyCode();

        return [
            'code' => $code,
            'name' => $code,
            'symbol' => $code,
            'decimal_places' => 2,
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function inr(): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => 'INR',
            'name' => 'Indian Rupee',
            'symbol' => 'Rs',
            'decimal_places' => 2,
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
