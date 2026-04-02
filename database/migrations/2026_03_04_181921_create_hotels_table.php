<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relationship to the Parent Group
            $table->foreignId('hotel_group_id')
                ->constrained('hotel_groups')
                ->cascadeOnDelete();

            // Basic Hotel Details
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Address & Mapping Details
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            // Using decimal for precision in Lat/Long
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Inventory Details
            // This is the total physical rooms in the building
            $table->integer('total_rooms')->default(0);

            // Operational Details (From MYOB UI)
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('11:00:00');

            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
