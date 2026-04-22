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
        Schema::create('reservation_room_details', function (Blueprint $table) {
            $table->id();
            // Links back to the category above
            $table->foreignId('category_id')->constrained('reservation_room_categories')->onDelete('cascade');
            $table->string('room_number')->default('Auto');
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->string('status')->default('confirmed'); // Crucial for Partial Check-in
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_room_details');
    }
};
