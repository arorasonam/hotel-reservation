<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {

            // Currency info
            $table->string('currency_code', 3)
                ->default('INR')
                ->after('description');

            $table->decimal('exchange_rate_used', 12, 6)
                ->default(1)
                ->after('currency_code');

            // Base amount (INR)
            $table->decimal('base_amount', 12, 2)
                ->nullable()
                ->after('amount');

            $table->index('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_folios', function (Blueprint $table) {

            $table->dropIndex(['currency_code']);

            $table->dropColumn([
                'currency_code',
                'exchange_rate_used',
                'base_amount',
            ]);
        });
    }
};
