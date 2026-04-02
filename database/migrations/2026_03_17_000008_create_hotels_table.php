<?php

use App\Models\HotelGroup;
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
        Schema::create('hotels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('recommended_score')->nullable()->default(0);
            $table->string('chain_code')->nullable();
            $table->uuid('ref_id')->default(\Illuminate\Support\Facades\DB::raw('gen_random_uuid()'));
            $table->float('rating')->default(0)->nullable();
            $table->unsignedBigInteger('user_ratings_total')->default(0)->nullable();
            $table->jsonb('address')->nullable();
            $table->jsonb('contact')->nullable();
            $table->foreignIdFor(HotelGroup::class)->nullable()->constrained()->cascadeOnDelete();
            $table->uuidMorphs('locationable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
