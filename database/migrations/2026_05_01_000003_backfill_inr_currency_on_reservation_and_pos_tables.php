<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $currencyColumns = [
        'reservations' => 'total_amount',
        'reservation_rooms' => 'rate',
        'reservation_folios' => 'amount',
        'pos_items' => 'price',
        'pos_orders' => 'grand_total',
        'pos_order_items' => 'total',
        'pos_payments' => 'amount',
    ];

    public function up(): void
    {
        foreach ($this->currencyColumns as $tableName => $afterColumn) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn): void {
                if (! Schema::hasColumn($tableName, 'currency_code')) {
                    $table->string('currency_code', 3)->default('INR')->after($afterColumn);
                    $table->foreign('currency_code')->references('code')->on('currencies');
                }

                if (! Schema::hasColumn($tableName, 'exchange_rate')) {
                    $table->decimal('exchange_rate', 18, 8)->default(1)->after('currency_code');
                }
            });

            DB::table($tableName)->whereNull('currency_code')->update([
                'currency_code' => 'INR',
                'exchange_rate' => 1,
            ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->currencyColumns) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'currency_code')) {
                    $table->dropForeign(['currency_code']);
                }

                $columns = array_filter([
                    Schema::hasColumn($tableName, 'currency_code') ? 'currency_code' : null,
                    Schema::hasColumn($tableName, 'exchange_rate') ? 'exchange_rate' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
