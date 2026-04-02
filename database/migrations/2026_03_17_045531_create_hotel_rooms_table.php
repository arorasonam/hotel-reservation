<?php

use App\Models\Hotel;
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
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('room_number'); // The door number
            $table->string('floor');       // The floor it sits on
            $table->string('status')->default('vacant');
            $table->foreignUuid('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->boolean('is_visible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
