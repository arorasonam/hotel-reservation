<?php

use App\Models\Hotel;
use App\Models\HotelRoom;
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
        Schema::create('hotel_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('url');
            $table->foreignIdFor(Hotel::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(HotelRoom::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_media');
    }
};
