<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array{amount: string, after: string}>
     */
    private array $tables = [
        'pos_orders' => ['amount' => 'grand_total', 'after' => 'exchange_rate'],
        'pos_order_items' => ['amount' => 'total', 'after' => 'exchange_rate'],
        'pos_payments' => ['amount' => 'amount', 'after' => 'exchange_rate'],
        'reservation_folios' => ['amount' => 'amount', 'after' => 'exchange_rate'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName => $details) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $details): void {
                if (! Schema::hasColumn($tableName, 'base_currency_code')) {
                    $table->string('base_currency_code', 3)->default('INR')->after($details['after']);
                    $table->foreign('base_currency_code')->references('code')->on('currencies');
                }

                if (! Schema::hasColumn($tableName, 'base_amount')) {
                    $table->decimal('base_amount', 12, 2)->default(0)->after('base_currency_code');
                }
            });

            DB::table($tableName)->update([
                'base_currency_code' => 'INR',
                'base_amount' => DB::raw($details['amount']),
            ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'base_currency_code')) {
                    $table->dropForeign(['base_currency_code']);
                }

                $columns = array_filter([
                    Schema::hasColumn($tableName, 'base_currency_code') ? 'base_currency_code' : null,
                    Schema::hasColumn($tableName, 'base_amount') ? 'base_amount' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
