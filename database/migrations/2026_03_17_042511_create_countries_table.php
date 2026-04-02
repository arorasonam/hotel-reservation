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
        Schema::create('countries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('iso2', 2);
            $table->string('iso3', 3);
            $table->string('capital');
            $table->string('region');
            $table->string('name');
            $table->string('subregion');
            $table->jsonb('timezones');
            $table->string('emoji');
            $table->string('currency');
            $table->unsignedBigInteger('ref_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
