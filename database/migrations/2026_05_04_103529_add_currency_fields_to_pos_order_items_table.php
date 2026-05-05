<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {

            // Currency info
            $table->string('currency_code', 3)
                ->default('INR')
                ->after('pos_item_id');

            $table->decimal('exchange_rate_used', 12, 6)
                ->default(1)
                ->after('currency_code');

            // Base values (INR)
            $table->decimal('base_price', 12, 2)
                ->nullable()
                ->after('price');

            $table->decimal('base_tax', 12, 2)
                ->default(0)
                ->after('base_price');

            $table->decimal('base_total', 12, 2)
                ->nullable()
                ->after('total');

            // Index
            $table->index('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {

            $table->dropIndex(['currency_code']);

            $table->dropColumn([
                'currency_code',
                'exchange_rate_used',
                'base_price',
                'base_tax',
                'base_total',
            ]);
        });
    }
};
