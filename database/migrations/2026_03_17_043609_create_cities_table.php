<?php

use App\Models\Country;
use App\Models\State;
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
        Schema::create('cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('state_code');
            $table->string('name');
            $table->string('natural_name');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('slug')->unique();
            $table->foreignIdFor(State::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Country::class)->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('ref_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
