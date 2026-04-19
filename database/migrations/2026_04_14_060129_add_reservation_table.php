<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Core Stay Details
            if (!Schema::hasColumn('reservations', 'hotel_id')) {
                // Use foreignUuid to match the UUID primary key in the hotels table
                $table->foreignUuid('hotel_id')
                    ->nullable()
                    ->constrained('hotels')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('reservations', 'reservation_number')) $table->string('reservation_number')->unique();
            if (!Schema::hasColumn('reservations', 'check_in')) $table->date('check_in')->nullable();
            if (!Schema::hasColumn('reservations', 'check_out')) $table->date('check_out')->nullable();
            if (!Schema::hasColumn('reservations', 'check_in_time')) $table->time('check_in_time')->default('14:00');
            if (!Schema::hasColumn('reservations', 'check_out_time')) $table->time('check_out_time')->default('11:00');
            if (!Schema::hasColumn('reservations', 'nights')) $table->unsignedSmallInteger('nights')->default(1);
            if (!Schema::hasColumn('reservations', 'rooms_count')) $table->unsignedTinyInteger('rooms_count')->default(1);

            // Classification & Source
            if (!Schema::hasColumn('reservations', 'booking_source')) $table->string('booking_source')->nullable(); // OTA, Direct
            if (!Schema::hasColumn('reservations', 'booking_type')) $table->string('booking_type')->nullable(); // Confirmed, Tentative
            if (!Schema::hasColumn('reservations', 'source_market')) $table->string('source_market')->nullable(); // Domestic, Int
            if (!Schema::hasColumn('reservations', 'reservation_type')) $table->string('reservation_type')->nullable(); // Individual, Group
            if (!Schema::hasColumn('reservations', 'status')) $table->string('status')->default('tentative')->index();

            // Inclusions
            if (!Schema::hasColumn('reservations', 'breakfast')) $table->boolean('breakfast')->default(false);

            // Billing Summary
            if (!Schema::hasColumn('reservations', 'base_price')) $table->decimal('base_price', 12, 2)->default(0);
            if (!Schema::hasColumn('reservations', 'discount_amount')) $table->decimal('discount_amount', 12, 2)->default(0);
            if (!Schema::hasColumn('reservations', 'tax_amount')) $table->decimal('tax_amount', 12, 2)->default(0);
            if (!Schema::hasColumn('reservations', 'total_amount')) $table->decimal('total_amount', 12, 2)->default(0);

            if (!Schema::hasColumn('reservations', 'special_requests')) $table->text('special_requests')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'hotel_id',
                'check_in',
                'check_out',
                'check_in_time',
                'check_out_time',
                'nights',
                'rooms_count',
                'booking_source',
                'booking_type',
                'source_market',
                'reservation_type',
                'status',
                'breakfast',
                'base_price',
                'discount_amount',
                'tax_amount',
                'total_amount',
                'special_requests',
                'reservation_number',
            ]);
        });
    }
};
