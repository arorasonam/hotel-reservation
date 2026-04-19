<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_plan_id')
                ->nullable() // Allows for room-only bookings if needed
                ->constrained('meal_plans')
                ->onDelete('set null');
            $table->integer('rooms_count')->default(1);
            // Occupancy
            $table->unsignedTinyInteger('adults')->default(2);
            $table->unsignedTinyInteger('children')->default(0);
            $table->unsignedTinyInteger('infants')->default(0);

            // Room Allocation
            $table->foreignId('room_type_id')->nullable()->constrained('room_types');
            $table->string('room_number')->nullable(); // Links to hotel_rooms

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_rooms');
    }
};
