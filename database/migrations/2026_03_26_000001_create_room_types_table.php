<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();

            // Core Identity
            $table->string('code', 10)->unique();                        // DLR, EXE, STE
            $table->string('name');                                       // Deluxe Room
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();

            // Bed Configuration
            $table->enum('bed_type', [
                'single',
                'double',
                'twin',
                'queen',
                'king',
                'bunk',
                'sofa_bed',
            ])->default('double');
            $table->unsignedTinyInteger('num_beds')->default(1);

            // Occupancy
            $table->unsignedTinyInteger('max_adults')->default(2);
            $table->unsignedTinyInteger('max_children')->default(1);
            $table->unsignedTinyInteger('max_infants')->default(1);
            $table->boolean('extra_bed_allowed')->default(false);
            $table->unsignedTinyInteger('max_extra_beds')->default(0);

            // Physical
            $table->unsignedSmallInteger('default_size_sqft')->nullable();
            $table->unsignedSmallInteger('default_size_sqm')->nullable();

            // Display
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
