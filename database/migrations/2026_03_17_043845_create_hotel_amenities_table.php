<?php

use App\Models\Amenity;
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
        Schema::create('hotel_amenities', function (Blueprint $table) {
            $table->foreignIdFor(Hotel::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Amenity::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_amenities');
    }
};
