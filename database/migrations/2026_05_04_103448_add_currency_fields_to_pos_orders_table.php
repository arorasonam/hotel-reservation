<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {

            // Currency info
            $table->string('currency_code', 3)
                ->default('INR')
                ->after('order_type');

            $table->decimal('exchange_rate_used', 12, 6)
                ->default(1)
                ->after('currency_code');

            // Base currency (INR)
            $table->decimal('base_subtotal', 12, 2)
                ->nullable()
                ->after('exchange_rate_used');

            $table->decimal('base_tax_amount', 12, 2)
                ->default(0)
                ->after('base_subtotal');

            $table->decimal('base_discount_amount', 12, 2)
                ->default(0)
                ->after('base_tax_amount');

            $table->decimal('base_grand_total', 12, 2)
                ->nullable()
                ->after('base_discount_amount');

            // Index for performance
            $table->index('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {

            $table->dropIndex(['currency_code']);

            $table->dropColumn([
                'currency_code',
                'exchange_rate_used',
                'base_subtotal',
                'base_tax_amount',
                'base_discount_amount',
                'base_grand_total',
            ]);
        });
    }
};
