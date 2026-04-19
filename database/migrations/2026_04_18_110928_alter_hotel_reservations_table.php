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
        Schema::table('reservations', function (Blueprint $table) {
            // Remove the old string-based columns if they exist
            $table->dropColumn(['source_market', 'booking_source', 'booking_type']);

            // Add new foreignId columns for masters
            $table->foreignId('booking_source_id')->after('hotel_id')->constrained();
            $table->foreignId('source_market_id')->after('check_out_time')->constrained();
            $table->foreignId('booking_type_id')->after('source_market_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['booking_source_id', 'source_market_id', 'booking_type_id']);
            $table->dropColumn(['booking_source_id', 'source_market_id', 'booking_type_id']);

            // Re-add the string columns if rolling back
            $table->string('booking_source')->nullable();
            $table->string('source_market')->nullable();
            $table->string('booking_type')->nullable();
        });
    }
};
