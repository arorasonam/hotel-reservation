<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservations', 'currency_code')) {
                $table->string('currency_code', 3)
                    ->default('INR')
                    ->after('rate_plan');
            }

            if (! Schema::hasColumn('reservations', 'exchange_rate_used')) {
                $table->decimal('exchange_rate_used', 12, 6)
                    ->default(1)
                    ->after('currency_code');
            }

            if (! Schema::hasColumn('reservations', 'base_rate')) {
                $table->decimal('base_rate', 12, 2)
                    ->nullable()
                    ->after('rate');
            }

            if (! Schema::hasColumn('reservations', 'base_tax_amount')) {
                $table->decimal('base_tax_amount', 12, 2)
                    ->default(0)
                    ->after('tax_amount');
            }

            if (! Schema::hasColumn('reservations', 'base_discount_amount')) {
                $table->decimal('base_discount_amount', 12, 2)
                    ->default(0)
                    ->after('discount_amount');
            }

            if (! Schema::hasColumn('reservations', 'base_total_amount')) {
                $table->decimal('base_total_amount', 12, 2)
                    ->nullable()
                    ->after('total_amount');
            }

            $table->index('currency_code');
        });

        Schema::table('reservation_rooms', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservation_rooms', 'currency_code')) {
                $table->string('currency_code', 3)
                    ->default('INR')
                    ->after('rate');
            }

            if (! Schema::hasColumn('reservation_rooms', 'exchange_rate_used')) {
                $table->decimal('exchange_rate_used', 12, 6)
                    ->default(1)
                    ->after('currency_code');
            }

            if (! Schema::hasColumn('reservation_rooms', 'base_rate')) {
                $table->decimal('base_rate', 12, 2)
                    ->nullable()
                    ->after('exchange_rate_used');
            }

            $table->index('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_rooms', function (Blueprint $table): void {
            $table->dropIndex(['currency_code']);

            $table->dropColumn([
                'currency_code',
                'exchange_rate_used',
                'base_rate',
            ]);
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex(['currency_code']);

            $table->dropColumn([
                'currency_code',
                'exchange_rate_used',
                'base_rate',
                'base_tax_amount',
                'base_discount_amount',
                'base_total_amount',
            ]);
        });
    }
};
