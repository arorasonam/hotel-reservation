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
        Schema::create('booking_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name'); // e.g., Booking.com, Sharma Travels, Direct
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_sources');
    }
};
